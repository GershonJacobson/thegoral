<?php
/**
 * The Goral - Draw Winner Handler
 * Selects and marks ONE winner for a campaign
 * 
 * @version 2.0
 * @author Professional Rewrite
 */

// Verify AJAX request and admin access
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
    http_response_code(403);
    exit(json_encode(['result' => 'error', 'message' => 'Unauthorized']));
}

session_start();
require("../config/session.php");
require("../PHPMailer/src/PHPMailer.php");
require("../PHPMailer/src/SMTP.php");
require("../PHPMailer/src/Exception.php");

// Check admin role (1 = super admin, 2 = delegate)
if (!isset($getUserRole) || !in_array($getUserRole, [1, 2])) {
    http_response_code(403);
    exit(json_encode(['result' => 'error', 'message' => 'Admin access required']));
}

$response = ['result' => 'error', 'message' => 'An error occurred'];

try {
    // Validate input
    if (!isset($_POST['campaignID']) || empty($_POST['campaignID'])) {
        throw new Exception("Campaign ID is required");
    }
    
    $campaignID = intval($_POST['campaignID']);
    
    // Get campaign details
    $campaignQuery = "SELECT campaign_id, campaign_name, status FROM tbl_campaign WHERE campaign_id = ? LIMIT 1";
    $stmt = $con->prepare($campaignQuery);
    
    if (!$stmt) {
        throw new Exception("Database error: " . $con->error);
    }
    
    $stmt->bind_param("i", $campaignID);
    $stmt->execute();
    $campaignResult = $stmt->get_result();
    
    if ($campaignResult->num_rows === 0) {
        throw new Exception("Campaign not found");
    }
    
    $campaign = $campaignResult->fetch_assoc();
    $stmt->close();
    
    // Check if campaign is closed
    if ($campaign['status'] !== 'closed') {
        throw new Exception("Campaign must be closed before drawing a winner");
    }
    
    // Check if winner already exists
    $existingWinnerQuery = "SELECT ticket_id FROM tbl_ticket WHERE campaignid_fk = ? AND win = 'Y' LIMIT 1";
    $stmt = $con->prepare($existingWinnerQuery);
    $stmt->bind_param("i", $campaignID);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        throw new Exception("Winner already drawn for this campaign");
    }
    $stmt->close();
    
    // Get all tickets for this campaign
    $ticketsQuery = "SELECT ticket_id, ticket_no, first_name, last_name, email, total_price 
                     FROM tbl_ticket 
                     WHERE campaignid_fk = ? AND ticket_no IS NOT NULL 
                     ORDER BY RAND() LIMIT 1";
    $stmt = $con->prepare($ticketsQuery);
    
    if (!$stmt) {
        throw new Exception("Database error: " . $con->error);
    }
    
    $stmt->bind_param("i", $campaignID);
    $stmt->execute();
    $ticketsResult = $stmt->get_result();
    
    if ($ticketsResult->num_rows === 0) {
        $stmt->close();
        throw new Exception("No eligible tickets for this campaign");
    }
    
    $winner = $ticketsResult->fetch_assoc();
    $stmt->close();
    
    // Mark ONLY this ticket as winner
    $updateQuery = "UPDATE tbl_ticket SET win = 'Y' WHERE ticket_id = ? LIMIT 1";
    $stmt = $con->prepare($updateQuery);
    
    if (!$stmt) {
        throw new Exception("Database error: " . $con->error);
    }
    
    $stmt->bind_param("i", $winner['ticket_id']);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to update winner: " . $stmt->error);
    }
    $stmt->close();
    
    // Send winner notification email
    sendWinnerNotificationEmail(
        $winner['email'],
        $winner['first_name'],
        $winner['ticket_no'],
        $campaign['campaign_name'],
        $winner['total_price'] / 2
    );
    
    // Success response
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
 * 
 * @param string $winnerEmail
 * @param string $winnerFirstName
 * @param int $ticketNumber
 * @param string $campaignName
 * @param float $prize
 */
function sendWinnerNotificationEmail($winnerEmail, $winnerFirstName, $ticketNumber, $campaignName, $prize) {
    try {
        $senderEmail = SMTP_USERNAME ?? 'noreply@thegoral.com';
        $senderName = 'The Goral';
        
        // Load email template
        $templatePath = __DIR__ . '/../winner-email-template.html';
        if (!file_exists($templatePath)) {
            throw new Exception("Winner email template not found");
        }
        
        $emailContent = file_get_contents($templatePath);
        
        // Replace placeholders
        $emailContent = str_replace(
            ['%winnerName%', '%ticketNo%', '%campaignName%', '%prize%', '%date%'],
            [$winnerFirstName, $ticketNumber, $campaignName, $prize, date('M d, Y')],
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
        $mail->addAddress($winnerEmail, $winnerFirstName);
        
        // Set email content
        $mail->isHTML(true);
        $mail->Subject = "You Won! - The Goral Raffle";
        $mail->Body = $emailContent;
        
        // Send email
        if (!$mail->send()) {
            error_log("[Winner Email Error] Failed to send to {$winnerEmail}: " . $mail->ErrorInfo);
        }
        
    } catch (Exception $e) {
        error_log("[Winner Email Exception] " . $e->getMessage());
    }
}
?>