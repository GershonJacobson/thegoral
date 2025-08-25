<?php
require("../config/db.php");

// Set timezone
date_default_timezone_set('America/New_York'); // Adjust as needed

/**
 * Log messages to a file instead of echoing
 */
function logMessage($message) {
    file_put_contents('../logs/campaign.log', date('Y-m-d H:i:s') . " - $message\n", FILE_APPEND);
}

/**
 * Close a campaign and optionally pick a winner
 */
function closeCampaign($con, $campaign) {
    $campaignID = $campaign['campaignID'];
    $category = $campaign['category'];
    $weeklyNo = $campaign['weeklyNo'];
    $pageURL = $campaign['pageURL'];

    if($campaign['status'] === 'open') {
        // Update campaign to closed
        if($category === 'weekly') {
            $updateSQL = "UPDATE tbl_campaign SET status='closed', keep_show=1, page_url='" . $pageURL . $weeklyNo . "' WHERE campaign_id='" . $campaignID . "'";
        } else {
            $updateSQL = "UPDATE tbl_campaign SET status='closed', keep_show=1 WHERE campaign_id='" . $campaignID . "'";
        }
        mysqli_query($con, $updateSQL);

        // Pick a winner safely
        mysqli_begin_transaction($con);
        try {
            $qCheckWinner = mysqli_query($con, "SELECT * FROM tbl_ticket WHERE campaignid_fk='$campaignID' AND win='Y' FOR UPDATE");
            if(mysqli_num_rows($qCheckWinner) === 0) {
                $qTicket = mysqli_query($con, "SELECT * FROM tbl_ticket WHERE campaignid_fk='$campaignID' ORDER BY RAND() LIMIT 1 FOR UPDATE");
                if(mysqli_num_rows($qTicket) > 0) {
                    $ticket = mysqli_fetch_assoc($qTicket);
                    $ticketID = $ticket['ticket_id'];

                    mysqli_query($con, "UPDATE tbl_ticket SET win='Y' WHERE ticket_id='$ticketID'");
                    mysqli_query($con, "UPDATE tbl_ticket SET win_ticket_id='$ticketID' WHERE campaignid_fk='$campaignID'");
                    logMessage("Winner selected for campaign $campaignID: ticket $ticketID");
                }
            }
            mysqli_commit($con);
        } catch (Exception $e) {
            mysqli_rollback($con);
            logMessage("Error selecting winner for campaign $campaignID: " . $e->getMessage());
        }
    } else {
        // Handle keep_show expiration
        $keepShow = strtotime($campaign['countdownDateOriginal'] . '+12 hour');
        if(time() > $keepShow) {
            mysqli_query($con, "UPDATE tbl_campaign SET status='closed', keep_show=0 WHERE campaign_id='$campaignID'");
            logMessage("Campaign $campaignID keep_show expired, closed.");
        }
    }
}

// Fetch campaigns
$qData = mysqli_query($con, "SELECT * FROM tbl_campaign WHERE (status='open' AND keep_show=0) OR (status='closed' AND keep_show=1)");
$countdowns = [];

while($data = mysqli_fetch_assoc($qData)) {
    $data['countdownDate'] = strtotime($data['end_date']);
    $countdowns[] = $data;
}

// Process each campaign
$now = time();
foreach(array_reverse($countdowns) as $campaign) {
    $distance = $campaign['countdownDate'] - $now;
    if($distance < 0) {
        closeCampaign($con, $campaign);
    } else {
        $days = floor($distance / 86400);
        $hours = floor(($distance % 86400) / 3600);
        $minutes = floor(($distance % 3600) / 60);
        $seconds = $distance % 60;
        logMessage("Countdown for {$campaign['campaign_name']}: {$days}d {$hours}h {$minutes}m {$seconds}s");
    }
}

// Auto-insert weekly campaign every Thursday 20:30
$todayDay = date("D");
$todayTime = date("H:i:s");

if($todayDay === "Thu" && $todayTime >= "20:30:00") {
    $qTicketPurchased = mysqli_query($con, "SELECT * FROM tbl_campaign WHERE category='weekly' AND status='open'");
    if(mysqli_num_rows($qTicketPurchased) === 0) {
        $qID = mysqli_query($con, "SELECT weekly_no FROM tbl_campaign ORDER BY weekly_no DESC LIMIT 1");
        $id = mysqli_num_rows($qID) > 0 ? (mysqli_fetch_assoc($qID)['weekly_no'] + 1) : 1;

        $campaignID = substr(str_shuffle("0123456789abcdefghijklmnopqrstvwxyz"), 0, 30);
        $dateAdded = date("Y-m-d H:i:s");
        $startDate = date("Y-m-d") . " 20:30";
        $endDate = date("Y-m-d", strtotime("+7 days")) . " 20:00";

        mysqli_query($con, "INSERT INTO tbl_campaign(campaign_id, campaign_name, start_date, end_date, date_added, status, category, page_url, public, weekly_no)
            VALUES('$campaignID', 'Weeks Pot', '$startDate', '$endDate', '$dateAdded', 'open', 'weekly', 'drawing', 1, '$id')");
        logMessage("New weekly campaign inserted: $campaignID");
    }
}
?>
