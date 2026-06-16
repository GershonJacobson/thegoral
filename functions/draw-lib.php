<?php
/**
 * The Goral - Shared draw library.
 *
 * Holds the winner-selection + winner-email logic so it can be called from
 * BOTH the cron (functions/cron-job.php, the backstop) AND on demand the
 * instant a countdown hits zero (functions/draw-now.php, real-time reveal).
 *
 * goral_draw_winner() is idempotent and transaction-guarded: whoever calls it
 * first picks the winner and emails them; everyone else no-ops. So the draw is
 * decided exactly once, no matter how many cron ticks or page views race it.
 *
 * Side-effect free on include (only defines a constant + functions).
 */

require_once(__DIR__ . "/../config/db.php");
require_once(__DIR__ . "/../config/email-config.php");
require_once(__DIR__ . "/../PHPMailer/src/PHPMailer.php");
require_once(__DIR__ . "/../PHPMailer/src/SMTP.php");
require_once(__DIR__ . "/../PHPMailer/src/Exception.php");

// How long a finished pot lingers on its drawing page (showing the winner)
// before it is archived to /drawing<N> and a fresh pot opens at /drawing.
if (!defined('GORAL_REVEAL_MINUTES')) {
	define('GORAL_REVEAL_MINUTES', 30);
}

/**
 * Draw one winner for a campaign, weighted by tickets per purchase, and send
 * the winner email. Does nothing if a winner already exists. Returns true only
 * when THIS call actually drew the winner.
 */
if (!function_exists('goral_draw_winner')) {
function goral_draw_winner($con, $campaignID, $campaignName) {
	// Run the whole "is there a winner? no -> pick one" inside a transaction.
	// FOR UPDATE locks the (campaignid_fk, win) range so a concurrent draw for
	// the same campaign blocks until we commit, then sees the winner and bails.
	$con->begin_transaction();

	try {
		$stmt = $con->prepare("SELECT ticket_id FROM tbl_ticket WHERE campaignid_fk = ? AND win = 'Y' LIMIT 1 FOR UPDATE");
		$stmt->bind_param("s", $campaignID);
		$stmt->execute();
		$hasWinner = $stmt->get_result()->num_rows > 0;
		$stmt->close();

		if($hasWinner) {
			$con->commit();
			return false;
		}

		// Weighted random pick: a 2-ticket purchase has twice the chance
		$stmt = $con->prepare("SELECT ticket_id, ticket_no, first_name, email
		                       FROM tbl_ticket
		                       WHERE campaignid_fk = ? AND ticket_no IS NOT NULL
		                       ORDER BY -LN(RAND()) / GREATEST(total_ticket, 1) ASC
		                       LIMIT 1");
		$stmt->bind_param("s", $campaignID);
		$stmt->execute();
		$result = $stmt->get_result();

		if($result->num_rows == 0) {
			$stmt->close();
			$con->commit();
			return false;
		}

		$winner = $result->fetch_assoc();
		$stmt->close();

		$ticketID = $winner['ticket_id'];

		$stmt = $con->prepare("UPDATE tbl_ticket SET win = 'Y' WHERE ticket_id = ?");
		$stmt->bind_param("i", $ticketID);
		$stmt->execute();
		$stmt->close();

		$stmt = $con->prepare("UPDATE tbl_ticket SET win_ticket_id = ? WHERE campaignid_fk = ?");
		$stmt->bind_param("is", $ticketID, $campaignID);
		$stmt->execute();
		$stmt->close();

		$con->commit();
	} catch (Exception $e) {
		$con->rollback();
		error_log("[Draw Winner] " . $e->getMessage());
		return false;
	}

	// Prize is half the pot (see homepage: "take home half of the pot")
	$stmt = $con->prepare("SELECT COALESCE(SUM(total_price), 0) AS pot FROM tbl_ticket WHERE campaignid_fk = ?");
	$stmt->bind_param("s", $campaignID);
	$stmt->execute();
	$pot = $stmt->get_result()->fetch_assoc()['pot'];
	$stmt->close();

	$prize = number_format(((float)$pot) / 2, 2);

	goral_send_winner_email($winner['email'], $winner['first_name'], $winner['ticket_no'], $campaignName, $prize);

	if (function_exists('cron_say')) { cron_say("Winner drawn for " . $campaignName . "<br>"); }
	return true;
}
}

if (!function_exists('goral_send_winner_email')) {
function goral_send_winner_email($winnerEmail, $winnerFirstName, $ticketNumber, $campaignName, $prize) {
	try {
		$templatePath = __DIR__ . '/../winner-email-template.html';
		if (!file_exists($templatePath) || empty($winnerEmail)) {
			return;
		}

		// The name is buyer-supplied and goes into an HTML email — escape it.
		$safeName = htmlspecialchars($winnerFirstName, ENT_QUOTES, 'UTF-8');
		$safeCampaign = htmlspecialchars($campaignName, ENT_QUOTES, 'UTF-8');

		$emailContent = file_get_contents($templatePath);
		$emailContent = str_replace(
			['%winnerName%', '%ticketNo%', '%campaignName%', '%prize%', '%date%'],
			[$safeName, $ticketNumber, $safeCampaign, $prize, date('M d, Y')],
			$emailContent
		);

		if (defined('GORAL_SMTP_DISABLED') && GORAL_SMTP_DISABLED) {
			return;
		}

		$mail = new PHPMailer\PHPMailer\PHPMailer();
		$mail->Timeout = 10;
		$mail->isSMTP();
		$mail->Host = SMTP_HOST;
		$mail->Port = SMTP_PORT;
		$mail->SMTPAuth = SMTP_AUTH;
		$mail->SMTPAutoTLS = false;
		$mail->Username = SMTP_USERNAME;
		$mail->Password = SMTP_PASSWORD;

		$mail->setFrom(EMAIL_FROM_ADDRESS, EMAIL_FROM_NAME);
		$mail->addReplyTo(EMAIL_REPLY_TO, EMAIL_FROM_NAME);
		$mail->addAddress($winnerEmail, $winnerFirstName);

		$mail->isHTML(true);
		$mail->Subject = "You Won! - The Goral Raffle";
		$mail->Body = $emailContent;

		if (!$mail->send()) {
			error_log("[Winner Email] Failed to send to {$winnerEmail}: " . $mail->ErrorInfo);
		}
	} catch (Exception $e) {
		error_log("[Winner Email Exception] " . $e->getMessage());
	}
}
}
?>
