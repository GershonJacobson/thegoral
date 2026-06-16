<?php
/**
 * The Goral - Raffle cron job (the backstop)
 *
 * Run every minute (hosting cron):
 *   curl -s "https://thegoral.com/functions/cron-job.php?key=XXXX"
 *
 * Lifecycle of a weekly pot:
 *   1. end_date passes -> close it but KEEP it at /drawing, and draw a winner.
 *      The pot now sits in its REVEAL WINDOW (GORAL_REVEAL_MINUTES, default 30):
 *      homepage shows "draw complete -> go to drawing", /drawing shows the
 *      winning number spinning to whoever opens it.
 *   2. Reveal window ends (end + 30 min) -> archive the pot to /drawing<N>
 *      (so it drops into "Previous 5 pots") and open a fresh empty pot at
 *      /drawing with a new countdown.
 *
 * The real-time reveal is driven by the page itself (functions/draw-now.php the
 * instant the clock hits zero); this cron is the safety net that still draws +
 * rolls over even if nobody is watching.
 *
 * SECURITY: drives money/lifecycle logic, so it is NOT public. Runs from CLI
 * (php cron-job.php) or over HTTP with ?key=GORAL_CRON_KEY.
 */

require_once(__DIR__ . "/../config/db.php");
require_once(__DIR__ . "/../config/yomtov.php");
require_once(__DIR__ . "/draw-lib.php");

$cronKey = getenv('GORAL_CRON_KEY') ?: (defined('GORAL_CRON_KEY') ? GORAL_CRON_KEY : '');
$isCli = (php_sapi_name() === 'cli');
$keyOk = ($cronKey !== '' && isset($_GET['key']) && hash_equals($cronKey, (string) $_GET['key']));
if (!$isCli && !$keyOk) {
	http_response_code(404);
	exit;
}

// Only stream the human-readable progress when run interactively with the key;
// keep automated/cron runs quiet so internals aren't echoed.
$CRON_VERBOSE = $isCli || $keyOk;
if (!function_exists('cron_say')) {
	function cron_say($msg) { global $CRON_VERBOSE; if ($CRON_VERBOSE) echo $msg; }
}

/**
 * Open the next weekly pot at /drawing (empty, fresh countdown). Yom Tov rule:
 * the draw never lands in a holiday week — see config/yomtov.php.
 */
function goral_create_weekly($con) {
	// Scope to weekly campaigns so a stray weekly_no on another row can't skew it.
	$qID = mysqli_query($con, "SELECT MAX(weekly_no) AS max_no FROM tbl_campaign WHERE category = 'weekly'");
	$dID = mysqli_fetch_array($qID);
	$id = ($dID['max_no'] ?? 0) + 1;

	$campaignID = bin2hex(random_bytes(15));
	$dateAdded = date("Y-m-d H:i:s");
	$startDate = date("Y-m-d") . " 20:30";

	list($endTs, $yomtovNames) = goral_weekly_end_date(strtotime($startDate));
	$endDate = date("Y-m-d H:i", $endTs);
	$campaignName = goral_weekly_campaign_name($yomtovNames);

	$stmt = $con->prepare("INSERT INTO tbl_campaign(campaign_id, campaign_name, start_date, end_date, date_added, status, category, page_url, public, keep_show, weekly_no) VALUES(?, ?, ?, ?, ?, 'open', 'weekly', 'drawing', 1, 0, ?)");
	$stmt->bind_param("sssssi", $campaignID, $campaignName, $startDate, $endDate, $dateAdded, $id);
	$stmt->execute();
	$stmt->close();

	$stmt = $con->prepare("INSERT INTO tbl_ticket_price(campaignid_fk, `1ticket_price`, `2ticket_price`) VALUES(?, 2, 3)");
	$stmt->bind_param("s", $campaignID);
	$stmt->execute();
	$stmt->close();

	cron_say("New weekly raffle #" . $id . " created<br>");
}

$qData = mysqli_query($con, "SELECT campaign_id, campaign_name, end_date, status, category, weekly_no, page_url FROM tbl_campaign WHERE (status = 'open' AND keep_show = 0) OR (status = 'closed' AND keep_show = 1)");

$now = time();

while($data = mysqli_fetch_array($qData)) {
	$campaignID = $data['campaign_id'];
	$campaignName = $data['campaign_name'];
	$endDate = strtotime($data['end_date']);
	$status = $data['status'];
	$category = $data['category'];
	$weeklyNo = $data['weekly_no'];
	$pageURL = $data['page_url'];

	if($endDate - $now >= 0) {
		cron_say($campaignName . " = still running<br>");
		continue;
	}

	cron_say($campaignName . " = COMPLETED<br>");

	if($status == "open") {
		// Atomically CLAIM: flip open -> closed only if still open. If two cron
		// runs overlap, only the one whose UPDATE changes a row proceeds to
		// draw (prevents the "two winners" bug). We deliberately do NOT rename
		// the page_url here — the pot stays at /drawing for its reveal window.
		$stmt = $con->prepare("UPDATE tbl_campaign SET status = 'closed', keep_show = 1 WHERE campaign_id = ? AND status = 'open'");
		$stmt->bind_param("s", $campaignID);
		$stmt->execute();
		$claimed = ($stmt->affected_rows === 1);
		$stmt->close();

		if($claimed) {
			goral_draw_winner($con, $campaignID, $campaignName);
		}
	}
	else {
		// Closed + still showing. Self-heal the draw in case a previous run
		// crashed between claim and draw (idempotent — no-ops if already drawn).
		goral_draw_winner($con, $campaignID, $campaignName);

		// Reveal window over? -> archive and roll to the next pot.
		$archiveAt = $endDate + (GORAL_REVEAL_MINUTES * 60);

		if($now >= $archiveAt) {
			if($category == "weekly" && $pageURL == "drawing") {
				// Lock so overlapping cron runs can't double-archive / double-create.
				$gotLock = false;
				$lockRes = mysqli_query($con, "SELECT GET_LOCK('goral_weekly_rollover', 5) AS l");
				if($lockRes) { $gotLock = ((int)mysqli_fetch_assoc($lockRes)['l'] === 1); }

				if($gotLock) {
					// Archive URL = base + weekly number (e.g. "drawing19"); fall
					// back to the unique id so two archives can never collide.
					$archiveURL = ($weeklyNo !== null && $weeklyNo !== "") ? ($pageURL . $weeklyNo) : ("archive-" . $campaignID);

					// Only the row still at the live slot is archived (idempotent).
					$stmt = $con->prepare("UPDATE tbl_campaign SET keep_show = 0, page_url = ? WHERE campaign_id = ? AND page_url = 'drawing'");
					$stmt->bind_param("ss", $archiveURL, $campaignID);
					$stmt->execute();
					$rolled = ($stmt->affected_rows === 1);
					$stmt->close();

					if($rolled) {
						cron_say($campaignName . " = archived to /" . $archiveURL . "<br>");
						// Open the next weekly if none is live.
						$qOpen = mysqli_query($con, "SELECT campaign_id FROM tbl_campaign WHERE category = 'weekly' AND status = 'open' LIMIT 1");
						if(mysqli_num_rows($qOpen) == 0) {
							goral_create_weekly($con);
						}
					}

					mysqli_query($con, "SELECT RELEASE_LOCK('goral_weekly_rollover')");
				}
			}
			else {
				// Non-weekly: just stop showing it once the reveal window ends.
				$stmt = $con->prepare("UPDATE tbl_campaign SET keep_show = 0 WHERE campaign_id = ?");
				$stmt->bind_param("s", $campaignID);
				$stmt->execute();
				$stmt->close();
			}
		}
		// else: still inside the reveal window — leave the pot at /drawing.
	}
}

// Safety net: there must always be a weekly the public can reach at /drawing —
// either a live (open) one or one mid-reveal. Covers a fresh DB or a failed
// rollover. (A normal rollover already created the next pot above.)
$qLive = mysqli_query($con, "SELECT campaign_id FROM tbl_campaign WHERE category = 'weekly' AND (status = 'open' OR (status = 'closed' AND page_url = 'drawing')) LIMIT 1");
if(mysqli_num_rows($qLive) == 0) {
	$gotLock = false;
	$lockRes = mysqli_query($con, "SELECT GET_LOCK('goral_weekly_rollover', 5) AS l");
	if($lockRes) { $gotLock = ((int)mysqli_fetch_assoc($lockRes)['l'] === 1); }

	if($gotLock) {
		$qLive2 = mysqli_query($con, "SELECT campaign_id FROM tbl_campaign WHERE category = 'weekly' AND (status = 'open' OR (status = 'closed' AND page_url = 'drawing')) LIMIT 1");
		if(mysqli_num_rows($qLive2) == 0) {
			goral_create_weekly($con);
		}
		mysqli_query($con, "SELECT RELEASE_LOCK('goral_weekly_rollover')");
	}
}
?>
