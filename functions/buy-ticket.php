<?php
/**
 * The Goral - Buy Ticket Handler
 * Records a ticket purchase and sends the confirmation email.
 *
 * Note: campaign_id is a 30-char random string, never an integer.
 * The ticket price is always taken from tbl_ticket_price server-side;
 * the posted inputPrice is ignored so the amount cannot be tampered with.
 */

// Verify this is an AJAX request
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
    http_response_code(403);
    exit(json_encode(['result' => 'error', 'message' => 'Unauthorized']));
}

// The charge + ticket write is a money-critical sequence — never let a client
// disconnect (closed browser, crash) abort us mid-way and leave a charge with
// no ticket. Keep running to completion regardless.
ignore_user_abort(true);
set_time_limit(60);

require("../config/db.php");
session_start();
require("../config/session.php");
require("../config/email-config.php");
require("../config/payarc.php");

// Load PHPMailer
require("../PHPMailer/src/PHPMailer.php");
require("../PHPMailer/src/SMTP.php");
require("../PHPMailer/src/Exception.php");

$response = ['result' => 'error', 'message' => 'An error occurred'];
$chargeLogId = null; // durable record of this attempt, set before the gateway call

try {
    // The card is tokenized in the browser by PayArc — this server only ever
    // sees a token (paymentToken), never the raw PAN/CVV.
    $requiredFields = ['campaignID', 'firstName', 'lastname', 'email', 'phone', 'paymentToken', 'inputPurchase'];

    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
            throw new Exception("Missing required field: {$field}");
        }
    }

    // Sanitize and validate input
    $campaignID = trim($_POST['campaignID']);
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $cardHolderName = trim($_POST['cardHolderName'] ?? '');
    $zip = trim($_POST['zip'] ?? '');
    $paymentToken = trim($_POST['paymentToken']);
    // Display-only card info from the tokenizer (not sensitive: last 4 + brand).
    $cardLast4 = substr(preg_replace('/\D/', '', $_POST['cardLast4'] ?? ''), -4);
    $cardBrand = substr(trim($_POST['cardBrand'] ?? ''), 0, 40);
    $ticketQuantity = intval($_POST['inputPurchase']);
    $saveCardRaw = $_POST['saveCard'] ?? '';
    $saveCard = ($saveCardRaw === 'Y' || $saveCardRaw === 'save-card');

    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        throw new Exception("Invalid email address");
    }

    // Names must look like names (letters/spaces/hyphens/apostrophes only) —
    // winners are announced publicly by name.
    $namePattern = "/^\p{L}[\p{L} .'-]{0,48}\p{L}$/u";
    if (!preg_match($namePattern, $firstName) || !preg_match($namePattern, $lastName)) {
        echo json_encode(['result' => 'invalidName', 'message' => 'Please enter your real first and last name — winners are announced by name.']);
        exit;
    }

    if ($ticketQuantity < 1) $ticketQuantity = 1;
    if ($ticketQuantity > 2) $ticketQuantity = 2;

    // Validate campaign exists, is open, AND the clock hasn't run out. The
    // end_date check closes the gap between the draw time and the cron flipping
    // status to 'closed' — no sales once the countdown hits zero.
    $stmt = $con->prepare("SELECT campaign_id, campaign_name, page_url, status FROM tbl_campaign WHERE campaign_id = ? AND status = 'open' AND end_date > NOW() LIMIT 1");
    if (!$stmt) {
        error_log("[DB] " . $con->error); throw new Exception("A database error occurred. Please try again.");
    }

    $stmt->bind_param("s", $campaignID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['result' => 'notExisted']);
        exit;
    }

    $campaign = $result->fetch_assoc();
    $stmt->close();

    // Price comes from the database, never from the client
    $stmt = $con->prepare("SELECT `1ticket_price`, `2ticket_price` FROM tbl_ticket_price WHERE campaignid_fk = ? LIMIT 1");
    $stmt->bind_param("s", $campaignID);
    $stmt->execute();
    $priceRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $price1 = $priceRow ? (float)$priceRow['1ticket_price'] : 2.0;
    $price2 = $priceRow ? (float)$priceRow['2ticket_price'] : 3.0;
    $ticketPrice = ($ticketQuantity === 2) ? $price2 : $price1;

    $amountCents = (int) round($ticketPrice * 100);
    $clientIp = substr($_SERVER['REMOTE_ADDR'] ?? '', 0, 45);

    // Rate limit the public checkout: cap charge ATTEMPTS per IP per minute so
    // the endpoint can't be used for card-testing / gateway abuse.
    $rl = $con->prepare("SELECT COUNT(*) AS c FROM tbl_charge_log WHERE ip = ? AND created_at > (NOW() - INTERVAL 1 MINUTE)");
    $rl->bind_param("s", $clientIp);
    $rl->execute();
    if ((int) $rl->get_result()->fetch_assoc()['c'] >= 8) {
        $rl->close();
        http_response_code(429);
        echo json_encode(['result' => 'rateLimited', 'message' => 'Too many attempts. Please wait a minute and try again.']);
        exit;
    }
    $rl->close();

    // Durable record of this attempt BEFORE the gateway call. Guarantees that a
    // charge can always be reconciled even if the response is lost or fulfillment
    // fails. (See tbl_charge_log.)
    $logStmt = $con->prepare("INSERT INTO tbl_charge_log (campaignid_fk, payarc_token, amount_cents, email, ip, status, created_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW())");
    $logStmt->bind_param("ssiss", $campaignID, $paymentToken, $amountCents, $email, $clientIp);
    $logStmt->execute();
    $chargeLogId = $con->insert_id;
    $logStmt->close();

    // Charge the card through PayArc. Amount is server-side (cents) so it can't
    // be tampered with. No successful charge => no ticket.
    $charge = goral_payarc_charge($paymentToken, $amountCents, [
        'email'       => $email,
        'name'        => trim($firstName . ' ' . $lastName),
        'description' => 'The Goral - ' . $campaign['campaign_name'],
    ]);

    if (!$charge['ok']) {
        // Timeout/network ('unreachable') is NOT a clean decline — the charge may
        // have gone through. Flag it 'unknown' for reconciliation, not 'declined'.
        if (!empty($charge['unreachable'])) {
            goral_charge_log_update($con, $chargeLogId, 'unknown', $charge['error'] ?? 'no gateway response');
            echo json_encode(['result' => 'error', 'message' => "We couldn't confirm your payment. Do NOT retry — please contact support and we'll check before any charge stands."]);
            exit;
        }
        goral_charge_log_update($con, $chargeLogId, 'declined', $charge['error'] ?? 'declined');
        echo json_encode(['result' => 'declined', 'message' => $charge['error'] ?? 'Your card was declined.']);
        exit;
    }

    $chargeId = $charge['charge_id'] ?? null;

    // A 'successful' charge with no charge id is unreconcilable — refuse to issue
    // a ticket we couldn't tie to a real charge.
    if (empty($chargeId)) {
        goral_charge_log_update($con, $chargeLogId, 'error_nochargeid', 'ok response without charge id');
        error_log("[Buy Ticket] charge ok but no charge_id; token={$paymentToken}");
        echo json_encode(['result' => 'error', 'message' => 'Payment could not be confirmed. Please contact support.']);
        exit;
    }

    // Charge captured — record the id immediately so it always survives.
    goral_charge_log_update($con, $chargeLogId, 'captured', $chargeId, $chargeId);

    // Prefer the gateway's card details; fall back to the values from the iframe.
    if (!empty($charge['last4'])) $cardLast4 = $charge['last4'];
    if (!empty($charge['brand'])) $cardBrand = $charge['brand'];

    // Prepare ticket data for insertion
    $purchaseDate = date("Y-m-d H:i:s");
    $paymentMethod = isset($_POST['defaultPaymentMethod']) && trim($_POST['defaultPaymentMethod']) !== '' ? trim($_POST['defaultPaymentMethod']) : 'Credit Card';
    $userID = (!empty($getUserID)) ? (int)$getUserID : 0;

    // Assign the next per-campaign ticket number and insert. Because the number
    // is MAX()+1 and tbl_ticket has UNIQUE(campaignid_fk, ticket_no), two
    // simultaneous buyers can compute the same number; the loser hits a
    // duplicate-key error (1062). We retry on exactly that, so concurrent
    // purchases queue cleanly instead of failing the customer.
    $nextTicketNo = null;
    $inserted = false;
    $maxAttempts = 6;

    // CRITICAL: the card is now charged. ANY failure from here until the ticket
    // exists must trigger a refund, so a customer is never charged for nothing.
    // We catch \Throwable (not just Exception) so even a fatal-class error
    // (e.g. a failed prepare) still refunds.
    try {
        for ($attempt = 1; $attempt <= $maxAttempts && !$inserted; $attempt++) {
            $stmt = $con->prepare("SELECT COALESCE(MAX(ticket_no), 0) + 1 AS next_no FROM tbl_ticket WHERE campaignid_fk = ?");
            if (!$stmt) { throw new RuntimeException("prepare failed: " . $con->error); }
            $stmt->bind_param("s", $campaignID);
            $stmt->execute();
            $nextTicketNo = (int) $stmt->get_result()->fetch_assoc()['next_no'];
            $stmt->close();

            $stmt = $con->prepare("INSERT INTO tbl_ticket
                            (ticket_no, campaignid_fk, first_name, last_name, email, phone,
                             card_holder_name, card_last4, card_brand, zip, payarc_token, payarc_charge_id,
                             total_ticket, total_price, purchased_by, purchased_date, payment_method, payment_status, win, win_ticket_id)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 'N', 0)");
            if (!$stmt) { throw new RuntimeException("prepare failed: " . $con->error); }

            $stmt->bind_param(
                "isssssssssssidiss",
                $nextTicketNo, $campaignID, $firstName, $lastName, $email, $phone,
                $cardHolderName, $cardLast4, $cardBrand, $zip, $paymentToken, $chargeId,
                $ticketQuantity, $ticketPrice, $userID, $purchaseDate, $paymentMethod
            );

            if ($stmt->execute()) {
                $inserted = true;
            } else if ($con->errno === 1062 && strpos($stmt->error, 'uq_ticket_charge') !== false) {
                // This exact charge already produced a ticket (idempotent replay,
                // e.g. a double-submit). Use the existing one — never double-issue or refund.
                $stmt->close();
                $dup = $con->prepare("SELECT ticket_no FROM tbl_ticket WHERE payarc_charge_id = ? LIMIT 1");
                $dup->bind_param("s", $chargeId);
                $dup->execute();
                $dupRow = $dup->get_result()->fetch_assoc();
                $dup->close();
                $nextTicketNo = $dupRow ? (int) $dupRow['ticket_no'] : $nextTicketNo;
                $inserted = true;
                break;
            } else if ($con->errno === 1062) {
                // Two buyers computed the same ticket_no — retry with a fresh one.
                $stmt->close();
                continue;
            } else {
                $err = $stmt->error;
                $stmt->close();
                throw new RuntimeException("insert failed: " . $err);
            }
            $stmt->close();
        }

        if (!$inserted) {
            throw new RuntimeException("ticket_no contention exhausted");
        }
    } catch (\Throwable $t) {
        // Charged but could not create the ticket -> refund so the customer is
        // never charged for a ticket they didn't get. The charge is already in
        // tbl_charge_log for reconciliation.
        error_log("[Buy Ticket] POST-CHARGE FULFILLMENT FAILED charge_id={$chargeId} campaign={$campaignID} amount={$amountCents}: " . $t->getMessage());
        $refund = goral_payarc_refund($chargeId, $amountCents);
        if ($refund['ok']) {
            goral_charge_log_update($con, $chargeLogId, 'refunded', 'fulfillment failed; auto-refunded');
            echo json_encode(['result' => 'error', 'message' => "We couldn't issue your ticket, so your card has not been charged (any hold was reversed). Please try again."]);
        } else {
            goral_charge_log_update($con, $chargeLogId, 'refund_failed', 'fulfillment failed; refund failed');
            error_log("[Buy Ticket] AUTO-REFUND FAILED charge_id={$chargeId}: " . ($refund['error'] ?? '?'));
            echo json_encode(['result' => 'error', 'message' => "Something went wrong completing your order. Please contact support with reference {$chargeId}."]);
        }
        exit;
    }

    // Ticket issued — mark the charge fully fulfilled.
    goral_charge_log_update($con, $chargeLogId, 'fulfilled', 'ticket_no=' . $nextTicketNo);

    // Save the card for next time (tokenized) if requested and the user is logged in.
    if ($saveCard && $userID && $cardLast4) {
        $stmt = $con->prepare("SELECT card_id FROM tbl_card WHERE userid_fk = ? AND card_last4 = ? AND card_brand <=> ? LIMIT 1");
        $stmt->bind_param("iss", $userID, $cardLast4, $cardBrand);
        $stmt->execute();

        if ($stmt->get_result()->num_rows === 0) {
            $cardStmt = $con->prepare("INSERT INTO tbl_card
                               (card_name, card_last4, card_brand, zip, payarc_card_id, email_address, userid_fk)
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
            $cardStmt->bind_param("ssssssi", $cardHolderName, $cardLast4, $cardBrand, $zip, $paymentToken, $email, $userID);
            $cardStmt->execute();
            $cardStmt->close();
        }
        $stmt->close();
    }

    // Send confirmation email (failure must not block the purchase)
    sendTicketConfirmationEmail($email, $nextTicketNo, $campaign['campaign_name'], $campaign['page_url']);

    $response = [
        'result' => 'OK',
        'ticketNo' => $nextTicketNo,
        'campaignID' => $campaignID,
        'pageURL' => $campaign['page_url'],
        'message' => 'Ticket purchased successfully. Check your email for confirmation.'
    ];

} catch (Exception $e) {
    error_log("[Buy Ticket Error] " . $e->getMessage());

    $response = [
        'result' => 'error',
        'message' => $e->getMessage()
    ];

    http_response_code(400);
}

header('Content-Type: application/json');
echo json_encode($response);

/**
 * Send ticket confirmation email using purchase-email-template.html
 * (placeholders: %ticketNo%, %campaignName%, %pageURL%)
 */
function sendTicketConfirmationEmail($customerEmail, $ticketNumber, $campaignName, $pageURL) {
    try {
        $templatePath = __DIR__ . '/../purchase-email-template.html';
        if (!file_exists($templatePath)) {
            throw new Exception("Email template not found");
        }

        $emailContent = file_get_contents($templatePath);
        $emailContent = str_replace(
            ['%ticketNo%', '%campaignName%', '%pageURL%'],
            [(int)$ticketNumber, htmlspecialchars($campaignName, ENT_QUOTES, 'UTF-8'), rawurlencode($pageURL)],
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
        $mail->addAddress($customerEmail);

        $mail->isHTML(true);
        $mail->Subject = "Your ticket order with The Goral";
        $mail->Body = $emailContent;

        if (!$mail->send()) {
            error_log("[Email Error] Failed to send email to {$customerEmail}: " . $mail->ErrorInfo);
        }

    } catch (Exception $e) {
        error_log("[Email Exception] " . $e->getMessage());
    }
}
?>
