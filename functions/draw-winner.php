<?php
/**
 * The Goral - Draw Winner Handler (manual draw by an admin)
 * Selects and marks ONE winner for a closed campaign.
 */

if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
    http_response_code(403);
    exit(json_encode(['result' => 'error', 'message' => 'Unauthorized']));
}

require("../config/db.php");
session_start();
require("../config/session.php");
require("../config/email-config.php");
require("../PHPMailer/src/PHPMailer.php");
require("../PHPMailer/src/SMTP.php");
require("../PHPMailer/src/Exception.php");

// Check admin role (1 = super admin, 2 = delegate with edit access)
if (!in_array((int)$getUserRole, [1, 2], true)) {
    http_response_code(403);
    exit(json_encode(['result' => 'error', 'message' => 'Admin access required']));
}

$response = ['result' => 'error', 'message' => 'An error occurred'];

try {
    if (empty($_POST['campaignID'])) {
        throw new Exception("Campaign ID is required");
    }

    $campaignID = trim($_POST['campaignID']);

    $stmt = $con->prepare("SELECT campaign_id, campaign_name, status FROM tbl_campaign WHERE campaign_id = ? LIMIT 1");
    $stmt->bind_param("s", $campaignID);
    $stmt->execute();
    $campaignResult = $stmt->get_result();

    if ($campaignResult->num_rows === 0) {
        throw new Exception("Campaign not found");
    }

    $campaign = $campaignResult->fetch_assoc();
    $stmt->close();

    if ($campaign['status'] !== 'closed') {
        throw new Exception("Campaign must be closed before drawing a winner");
    }

    // Draw inside a transaction with FOR UPDATE so a simultaneous cron draw
    // (or a second admin click) can't produce two winners — the same guarantee
    // the cron path uses.
    $con->begin_transaction();
    try {
        $stmt = $con->prepare("SELECT ticket_id FROM tbl_ticket WHERE campaignid_fk = ? AND win = 'Y' LIMIT 1 FOR UPDATE");
        $stmt->bind_param("s", $campaignID);
        $stmt->execute();
        $alreadyDrawn = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if ($alreadyDrawn) {
            $con->commit();
            throw new Exception("Winner already drawn for this campaign");
        }

        // Weighted random pick: a 2-ticket purchase has twice the chance
        $stmt = $con->prepare("SELECT ticket_id, ticket_no, first_name, last_name, email
                         FROM tbl_ticket
                         WHERE campaignid_fk = ? AND ticket_no IS NOT NULL
                         ORDER BY -LN(RAND()) / GREATEST(total_ticket, 1) ASC
                         LIMIT 1");
        $stmt->bind_param("s", $campaignID);
        $stmt->execute();
        $ticketsResult = $stmt->get_result();

        if ($ticketsResult->num_rows === 0) {
            $stmt->close();
            $con->commit();
            throw new Exception("No eligible tickets for this campaign");
        }

        $winner = $ticketsResult->fetch_assoc();
        $stmt->close();

        // Mark ONLY this ticket as winner
        $stmt = $con->prepare("UPDATE tbl_ticket SET win = 'Y' WHERE ticket_id = ? LIMIT 1");
        $stmt->bind_param("i", $winner['ticket_id']);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new Exception("DB_UPDATE_WINNER");
        }
        $stmt->close();

        // Record the winning ticket on every purchase in the campaign
        $stmt = $con->prepare("UPDATE tbl_ticket SET win_ticket_id = ? WHERE campaignid_fk = ?");
        $stmt->bind_param("is", $winner['ticket_id'], $campaignID);
        $stmt->execute();
        $stmt->close();

        $con->commit();
    } catch (Exception $inner) {
        $con->rollback();
        if ($inner->getMessage() === "DB_UPDATE_WINNER") {
            error_log("[DB] update winner failed for {$campaignID}");
            throw new Exception("Could not draw the winner. Please try again.");
        }
        throw $inner; // "already drawn" / "no eligible tickets" bubble up as-is
    }

    // Prize is half the pot
    $stmt = $con->prepare("SELECT COALESCE(SUM(total_price), 0) AS pot FROM tbl_ticket WHERE campaignid_fk = ?");
    $stmt->bind_param("s", $campaignID);
    $stmt->execute();
    $pot = $stmt->get_result()->fetch_assoc()['pot'];
    $stmt->close();

    $prize = number_format(((float)$pot) / 2, 2);

    sendWinnerNotificationEmail(
        $winner['email'],
        $winner['first_name'],
        $winner['ticket_no'],
        $campaign['campaign_name'],
        $prize
    );

    $response = [
        'result' => 'OK',
        'message' => 'Winner drawn successfully',
        'winner' => $winner['first_name'] . ' ' . $winner['last_name'],
        'ticket' => $winner['ticket_no']
    ];

} catch (Exception $e) {
    error_log("[Draw Winner Error] " . $e->getMessage());

    $response = [
        'result' => 'error',
        'message' => $e->getMessage()
    ];

    http_response_code(400);
}

header('Content-Type: application/json');
echo json_encode($response);

/**
 * Send winner notification email
 * (template placeholders: %winnerName%, %ticketNo%, %campaignName%, %prize%, %date%)
 */
function sendWinnerNotificationEmail($winnerEmail, $winnerFirstName, $ticketNumber, $campaignName, $prize) {
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
            error_log("[Winner Email Error] Failed to send to {$winnerEmail}: " . $mail->ErrorInfo);
        }

    } catch (Exception $e) {
        error_log("[Winner Email Exception] " . $e->getMessage());
    }
}
?>
