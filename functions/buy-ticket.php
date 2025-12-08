<?php
/**
 * The Goral - Buy Ticket Handler
 * Processes ticket purchases with payment and email confirmation
 * 
 * @version 2.0
 * @author Professional Rewrite
 */

// Verify this is an AJAX request
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
    http_response_code(403);
    exit(json_encode(['result' => 'error', 'message' => 'Unauthorized']));
}

// Start session and load configuration
session_start();
require("../config/session.php");
require("../config/email-config.php");

// Load PHPMailer
require("../PHPMailer/src/PHPMailer.php");
require("../PHPMailer/src/SMTP.php");
require("../PHPMailer/src/Exception.php");

// Initialize response
$response = ['result' => 'error', 'message' => 'An error occurred'];

try {
    // Validate required POST data
    $requiredFields = ['campaignID', 'firstName', 'lastname', 'email', 'phone', 'cardHolderName', 'cardNumber', 'expiry', 'cvv', 'zip', 'inputPurchase', 'inputPrice', 'campaignName'];
    
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            throw new Exception("Missing required field: {$field}");
        }
    }
    
    // Sanitize and validate input
    $campaignID = intval($_POST['campaignID']);
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $cardHolderName = trim($_POST['cardHolderName']);
    $cardNumber = preg_replace('/\D/', '', $_POST['cardNumber']); // Remove non-digits
    $expiry = trim($_POST['expiry']);
    $cvv = preg_replace('/\D/', '', $_POST['cvv']);
    $zip = trim($_POST['zip']);
    $ticketQuantity = intval($_POST['inputPurchase']);
    $ticketPrice = floatval($_POST['inputPrice']);
    $campaignName = trim($_POST['campaignName']);
    $saveCard = isset($_POST['saveCard']) && $_POST['saveCard'] === 'Y' ? 'Y' : 'N';
    
    // Validate campaign exists and is open
    $campaignQuery = "SELECT campaign_id, campaign_name, page_url, status FROM tbl_campaign WHERE campaign_id = ? AND status = 'open' LIMIT 1";
    $stmt = $con->prepare($campaignQuery);
    
    if (!$stmt) {
        throw new Exception("Database error: " . $con->error);
    }
    
    $stmt->bind_param("i", $campaignID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Campaign not found or is closed");
    }
    
    $campaign = $result->fetch_assoc();
    $stmt->close();
    
    // Generate next ticket number
    $ticketQuery = "SELECT MAX(ticket_no) as max_ticket FROM tbl_ticket WHERE campaignid_fk = ? LIMIT 1";
    $stmt = $con->prepare($ticketQuery);
    $stmt->bind_param("i", $campaignID);
    $stmt->execute();
    $ticketResult = $stmt->get_result();
    $ticketRow = $ticketResult->fetch_assoc();
    $nextTicketNo = ($ticketRow['max_ticket'] ?? 0) + 1;
    $stmt->close();
    
    // Prepare ticket data for insertion
    $purchaseDate = date("Y-m-d H:i:s");
    $paymentMethod = isset($_POST['defaultPaymentMethod']) ? trim($_POST['defaultPaymentMethod']) : 'Credit Card';
    $userID = isset($getUserID) ? $getUserID : null;
    
    // Insert ticket
    $insertQuery = "INSERT INTO tbl_ticket 
                    (ticket_no, campaignid_fk, first_name, last_name, email, phone, 
                     card_holder_name, card_number, expiry, cvv, zip, 
                     total_ticket, total_price, purchased_by, purchased_date, payment_method, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $con->prepare($insertQuery);
    
    if (!$stmt) {
        throw new Exception("Database error: " . $con->error);
    }
    
    $stmt->bind_param(
        "iisssssssssidiss",
        $nextTicketNo,
        $campaignID,
        $firstName,
        $lastName,
        $email,
        $phone,
        $cardHolderName,
        $cardNumber,
        $expiry,
        $cvv,
        $zip,
        $ticketQuantity,
        $ticketPrice,
        $userID,
        $purchaseDate,
        $paymentMethod
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to insert ticket: " . $stmt->error);
    }
    $stmt->close();
    
    // Save card if requested and user is logged in
    if ($saveCard === 'Y' && $userID) {
        $checkCardQuery = "SELECT card_id FROM tbl_card WHERE userid_fk = ? AND card_number = ? LIMIT 1";
        $stmt = $con->prepare($checkCardQuery);
        $stmt->bind_param("is", $userID, $cardNumber);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows === 0) {
            $insertCardQuery = "INSERT INTO tbl_card 
                               (card_number, card_name, expired, cvv, zip, email_address, userid_fk) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)";
            $cardStmt = $con->prepare($insertCardQuery);
            $cardStmt->bind_param("isssssi", $cardNumber, $cardHolderName, $expiry, $cvv, $zip, $email, $userID);
            $cardStmt->execute();
            $cardStmt->close();
        }
        $stmt->close();
    }
    
    // Send confirmation email
    sendTicketConfirmationEmail($email, $firstName, $nextTicketNo, $campaign['campaign_name'], $ticketQuantity, $ticketPrice);
    
    // Success response
    $response = [
        'result' => 'OK',
        'ticketNo' => $nextTicketNo,
        'message' => 'Ticket purchased successfully. Check your email for confirmation.'
    ];
    
} catch (Exception $e) {
    // Log error
    error_log("[Buy Ticket Error] " . $e->getMessage());
    
    $response = [
        'result' => 'error',
        'message' => $e->getMessage()
    ];
    
    http_response_code(400);
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);

/**
 * Send ticket confirmation email
 * 
 * @param string $customerEmail
 * @param string $customerName
 * @param int $ticketNumber
 * @param string $campaignName
 * @param int $quantity
 * @param float $price
 */
function sendTicketConfirmationEmail($customerEmail, $customerName, $ticketNumber, $campaignName, $quantity, $price) {
    try {
        // Load email configuration
        $senderEmail = SMTP_USERNAME ?? 'noreply@thegoral.com';
        $senderName = 'The Goral';
        
        // Load email template
        $templatePath = __DIR__ . '/../purchase-email-template.html';
        if (!file_exists($templatePath)) {
            throw new Exception("Email template not found");
        }
        
        $emailContent = file_get_contents($templatePath);
        
        // Replace placeholders
        $emailContent = str_replace(
            ['%customerName%', '%ticketNo%', '%campaignName%', '%quantity%', '%price%', '%date%'],
            [$customerName, $ticketNumber, $campaignName, $quantity, $price, date('M d, Y')],
            $emailContent
        );
        
        // Initialize PHPMailer
        $mail = new PHPMailer\PHPMailer\PHPMailer();
        $mail->isSMTP();
        $mail->Host = SMTP_HOST ?? 'localhost';
        $mail->Port = SMTP_PORT ?? 25;
        $mail->SMTPAuth = SMTP_AUTH ?? false;
        $mail->SMTPAutoTLS = false;
        $mail->Username = SMTP_USERNAME ?? '';
        $mail->Password = SMTP_PASSWORD ?? '';
        
        // Set email properties
        $mail->setFrom($senderEmail, $senderName);
        $mail->addReplyTo($senderEmail, $senderName);
        $mail->addAddress($customerEmail, $customerName);
        
        // Set email content
        $mail->isHTML(true);
        $mail->Subject = "Ticket Confirmation - The Goral";
        $mail->Body = $emailContent;
        
        // Send email
        if (!$mail->send()) {
            error_log("[Email Error] Failed to send email to {$customerEmail}: " . $mail->ErrorInfo);
            // Don't throw - email failure shouldn't block ticket purchase
        }
        
    } catch (Exception $e) {
        error_log("[Email Exception] " . $e->getMessage());
        // Don't throw - email failure shouldn't block ticket purchase
    }
}
?>