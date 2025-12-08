<?php
/**
 * The Goral - Automatic Raffle Processing
 * Runs every minute via cron job to:
 * 1. Draw winners for completed raffles
 * 2. Create new weekly raffle if needed
 * 
 * CRON SETUP: Add to crontab with:
 * * * * * curl -s https://thegoral.com/cron/process-raffles.php > /dev/null 2>&1
 * OR
 * * * * * php /path/to/cron/process-raffles.php >> /var/log/thegoral-cron.log 2>&1
 * 
 * @version 2.0
 * @author Professional Rewrite
 */

// Prevent direct browser access
if (php_sapi_name() !== 'cli' && $_SERVER['REMOTE_ADDR'] !== '127.0.0.1') {
    http_response_code(403);
    exit("Access denied");
}

// Suppress output for cron
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/cron-errors.log');

// Load configuration
require(__DIR__ . '/../db.php');
require(__DIR__ . '/../config/email-config.php');
require(__DIR__ . '/../PHPMailer/src/PHPMailer.php');
require(__DIR__ . '/../PHPMailer/src/SMTP.php');
require(__DIR__ . '/../PHPMailer/src/Exception.php');

date_default_timezone_set('America/New_York');

try {
    // Step 1: Draw winners for raffles that just ended
    drawExpiredRaffleWinners($con);
    
    // Step 2: Create new weekly raffle if one doesn't exist
    ensureWeeklyRaffleExists($con);
    
    // Log success
    logCronActivity("Raffle processing completed successfully");
    
} catch (Exception $e) {
    error_log("[Raffle Cron Error] " . $e->getMessage());
    logCronActivity("Error: " . $e->getMessage(), 'error');
}

/**
 * Draw winners for all raffles that have ended
 */
function drawExpiredRaffleWinners($con) {
    global $con;
    
    // Find all raffles that:
    // 1. Status is 'closed' (past end_date)
    // 2. Don't have a winner yet (win = 'Y')
    // 3. Have at least one ticket
    
    $query = "SELECT 
              c.campaign_id, 
              c.campaign_name, 
              c.end_date,
              COUNT(t.ticket_id) as total_tickets
              FROM tbl_campaign c
              LEFT JOIN tbl_ticket t ON c.campaign_id = t.campaignid_fk
              WHERE c.status = 'closed' 
              AND c.category = 'weekly'
              AND c.campaign_id NOT IN (
                  SELECT DISTINCT campaignid_fk FROM tbl_ticket WHERE win = 'Y'
              )
              AND c.end_date <= NOW()
              GROUP BY c.campaign_id
              HAVING total_tickets > 0";
    
    $result = $con->query($query);
    
    if (!$result) {
        throw new Exception("Database error: " . $con->error);
    }
    
    $rafflesCount = 0;
    while ($raffle = $result->fetch_assoc()) {
        if (drawSingleWinner($con, $raffle['campaign_id'])) {
            $rafflesCount++;
            logCronActivity("Winner drawn for raffle {$raffle['campaign_id']}: {$raffle['campaign_name']}");
        }
    }
    
    return $rafflesCount;
}

/**
 * Draw ONE winner for a specific raffle
 */
function drawSingleWinner($con, $campaignID) {
    global $con;
    
    try {
        // Get campaign details
        $campaignQuery = $con->prepare("SELECT campaign_name FROM tbl_campaign WHERE campaign_id = ? LIMIT 1");
        $campaignQuery->bind_param("i", $campaignID);
        $campaignQuery->execute();
        $campaignResult = $campaignQuery->get_result();
        
        if ($campaignResult->num_rows === 0) {
            return false;
        }
        
        $campaign = $campaignResult->fetch_assoc();
        $campaignQuery->close();
        
        // Get random winner - ONLY ONE with LIMIT 1
        $winnerQuery = $con->prepare("
            SELECT ticket_id, ticket_no, first_name, last_name, email, total_price 
            FROM tbl_ticket 
            WHERE campaignid_fk = ? AND ticket_no IS NOT NULL
            ORDER BY RAND() 
            LIMIT 1
        ");
        $winnerQuery->bind_param("i", $campaignID);
        $winnerQuery->execute();
        $winnerResult = $winnerQuery->get_result();
        
        if ($winnerResult->num_rows === 0) {
            $winnerQuery->close();
            return false;
        }
        
        $winner = $winnerResult->fetch_assoc();
        $winnerQuery->close();
        
        // Mark as winner - ONLY THIS TICKET
        $updateQuery = $con->prepare("UPDATE tbl_ticket SET win = 'Y' WHERE ticket_id = ? LIMIT 1");
        $updateQuery->bind_param("i", $winner['ticket_id']);
        
        if (!$updateQuery->execute()) {
            throw new Exception("Failed to update winner");
        }
        $updateQuery->close();
        
        // Send winner email
        sendWinnerEmail(
            $winner['email'],
            $winner['first_name'],
            $winner['ticket_no'],
            $campaign['campaign_name'],
            $winner['total_price'] / 2
        );
        
        return true;
        
    } catch (Exception $e) {
        error_log("[Draw Winner Error] Campaign {$campaignID}: " . $e->getMessage());
        return false;
    }
}

/**
 * Ensure a weekly raffle exists
 */
function ensureWeeklyRaffleExists($con) {
    global $con;
    
    // Check if open weekly raffle exists
    $checkQuery = "SELECT campaign_id FROM tbl_campaign WHERE category = 'weekly' AND status = 'open' LIMIT 1";
    $result = $con->query($checkQuery);
    
    if ($result && $result->num_rows > 0) {
        return true; // Active raffle exists
    }
    
    // Check if closed raffle needs replacement (more than 30 minutes passed)
    $replacementQuery = "SELECT campaign_id FROM tbl_campaign 
                        WHERE category = 'weekly' 
                        AND status = 'closed'
                        AND end_date <= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
                        ORDER BY end_date DESC 
                        LIMIT 1";
    
    $result = $con->query($replacementQuery);
    
    if ($result && $result->num_rows > 0) {
        return createNewWeeklyRaffle($con);
    }
    
    return false;
}

/**
 * Create a new weekly raffle
 */
function createNewWeeklyRaffle($con) {
    global $con;
    
    try {
        // Get next weekly number
        $numberQuery = "SELECT MAX(weekly_no) as max_no FROM tbl_campaign WHERE category = 'weekly'";
        $result = $con->query($numberQuery);
        $row = $result->fetch_assoc();
        $nextWeeklyNo = ($row['max_no'] ?? 0) + 1;
        
        // Calculate dates (1 week from now)
        $startDate = date('Y-m-d H:i:s');
        $endDate = date('Y-m-d H:i:s', strtotime('+1 week'));
        
        // Insert new campaign
        $insertQuery = "INSERT INTO tbl_campaign 
                       (weekly_no, campaign_name, category, status, start_date, end_date, page_url, public, keep_show, created_at)
                       VALUES (?, ?, 'weekly', 'open', ?, ?, 'weekly-raffle', 1, 0, NOW())";
        
        $stmt = $con->prepare($insertQuery);
        if (!$stmt) {
            throw new Exception("Database error: " . $con->error);
        }
        
        $campaignName = "Weekly Raffle #" . $nextWeeklyNo;
        $pageUrl = "weekly-raffle";
        
        $stmt->bind_param("isss", $nextWeeklyNo, $campaignName, $startDate, $endDate);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create campaign: " . $stmt->error);
        }
        
        $campaignID = $con->insert_id;
        $stmt->close();
        
        // Create ticket pricing for new raffle
        $priceQuery = "INSERT INTO tbl_ticket_price (campaignid_fk, 1ticket_price, 2ticket_price)
                       VALUES (?, 2, 3)";
        $stmt = $con->prepare($priceQuery);
        $stmt->bind_param("i", $campaignID);
        $stmt->execute();
        $stmt->close();
        
        logCronActivity("New weekly raffle created: #{$nextWeeklyNo} (ID: {$campaignID})");
        return true;
        
    } catch (Exception $e) {
        error_log("[Create Raffle Error] " . $e->getMessage());
        return false;
    }
}

/**
 * Send winner notification email
 */
function sendWinnerEmail($winnerEmail, $winnerName, $ticketNo, $campaignName, $prize) {
    try {
        $templatePath = __DIR__ . '/../winner-email-template.html';
        if (!file_exists($templatePath)) {
            throw new Exception("Email template not found");
        }
        
        $emailContent = file_get_contents($templatePath);
        $emailContent = str_replace(
            ['%winnerName%', '%ticketNo%', '%campaignName%', '%prize%', '%date%'],
            [$winnerName, $ticketNo, $campaignName, $prize, date('M d, Y')],
            $emailContent
        );
        
        $mail = new PHPMailer\PHPMailer\PHPMailer();
        $mail->isSMTP();
        $mail->Host = SMTP_HOST ?? 'localhost';
        $mail->Port = SMTP_PORT ?? 25;
        $mail->SMTPAuth = SMTP_AUTH ?? false;
        $mail->SMTPAutoTLS = false;
        $mail->Username = SMTP_USERNAME ?? '';
        $mail->Password = SMTP_PASSWORD ?? '';
        
        $mail->setFrom(SMTP_USERNAME ?? 'noreply@thegoral.com', 'The Goral');
        $mail->addReplyTo(SMTP_USERNAME ?? 'noreply@thegoral.com', 'The Goral');
        $mail->addAddress($winnerEmail, $winnerName);
        
        $mail->isHTML(true);
        $mail->Subject = "Congratulations! You Won - The Goral";
        $mail->Body = $emailContent;
        
        if (!$mail->send()) {
            error_log("[Winner Email Failed] {$winnerEmail}: " . $mail->ErrorInfo);
        }
        
    } catch (Exception $e) {
        error_log("[Winner Email Exception] " . $e->getMessage());
    }
}

/**
 * Log cron activity
 */
function logCronActivity($message, $level = 'info') {
    $logFile = __DIR__ . '/../logs/cron-activity.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] [{$level}] {$message}\n";
    
    if (!is_dir(__DIR__ . '/../logs')) {
        mkdir(__DIR__ . '/../logs', 0755, true);
    }
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Exit cleanly
exit(0);
?>