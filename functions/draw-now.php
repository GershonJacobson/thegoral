<?php
/**
 * The Goral - Real-time draw trigger.
 *
 * Called by the drawing page the instant a countdown reaches zero (and on a
 * cold load of an already-ended pot). It draws the winner immediately so the
 * reveal is real-time instead of waiting up to a minute for the cron tick.
 *
 * Safe to be public: it only ever draws for a WEEKLY pot whose end_date has
 * already passed, and goral_draw_winner() is idempotent + transaction-guarded,
 * so it can be hit repeatedly / concurrently and still produce one winner.
 * It does NOT close/archive the pot — that stays the cron's job.
 */

if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
	http_response_code(403);
	exit(json_encode(['result' => 'error', 'message' => 'Unauthorized']));
}

require("../config/db.php");
require("../functions/draw-lib.php");

header('Content-Type: application/json');

$campaignID = trim($_POST['campaignID'] ?? '');
if ($campaignID === '') {
	http_response_code(400);
	echo json_encode(['result' => 'error', 'message' => 'Missing campaign']);
	exit;
}

$stmt = $con->prepare("SELECT campaign_id, campaign_name, end_date FROM tbl_campaign WHERE campaign_id = ? AND category = 'weekly' LIMIT 1");
$stmt->bind_param("s", $campaignID);
$stmt->execute();
$campaign = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$campaign) {
	http_response_code(404);
	echo json_encode(['result' => 'error', 'message' => 'Campaign not found']);
	exit;
}

// Only draw once the clock has actually run out — never early.
if (strtotime($campaign['end_date']) > time()) {
	echo json_encode(['result' => 'tooEarly']);
	exit;
}

goral_draw_winner($con, $campaignID, $campaign['campaign_name']);

// Return the (now-decided) winning ticket number + display name so the client
// can reveal it in place without a reload.
$wStmt = $con->prepare("SELECT ticket_no, first_name, last_name FROM tbl_ticket WHERE campaignid_fk = ? AND win = 'Y' LIMIT 1");
$wStmt->bind_param("s", $campaignID);
$wStmt->execute();
$w = $wStmt->get_result()->fetch_assoc();
$wStmt->close();

$winnerName = '';
if ($w) {
	$wFirst = trim($w['first_name'] ?? '');
	$wInitial = strtoupper(substr(trim($w['last_name'] ?? ''), 0, 1));
	$winnerName = $wFirst . ($wInitial !== '' ? ' ' . $wInitial . '.' : '');
}

echo json_encode([
	'result' => 'OK',
	'winningTicketNo' => $w['ticket_no'] ?? null,
	'winnerName' => $winnerName
]);
?>
