<?php
require("../config/db.php");

$qData = mysqli_query($con, "SELECT * FROM tbl_campaign WHERE (status = 'open' AND keep_show = 0) OR (status = 'closed' AND keep_show = 1)");
if(mysqli_num_rows($qData) > 0) {
	$countdowns = [];
	
	while($data = mysqli_fetch_array($qData)) {
		$campaignID = $data['campaign_id'];
		$countdownDateOriginal = $data['end_date'];
		$countdownDate = strtotime($data['end_date']);
		$campaignName = $data['campaign_name'];
		$status = $data['status'];
		$category = $data['category'];
		$weeklyNo = $data['weekly_no'];
		$pageURL = $data['page_url'];
		
		array_push($countdowns, ['campaignID' => $campaignID, 'campaignName' => $campaignName, 'countdownDateOriginal' => $countdownDateOriginal, 'countdownDate' => $countdownDate, 'status' => $status, 'category' => $category, 'weeklyNo' => $weeklyNo, 'pageURL' => $pageURL]);
	}
	
	$now = time();
	$index = count($countdowns) - 1;
	
	while ($index >= 0) {
		$countdown = $countdowns[$index];
		$distance = $countdown['countdownDate'] - $now;
		
		$days = floor($distance / 86400);
		$hours  = floor(($distance % 86400) / 3600);
		$minutes = floor(($distance % 3600) / 60);
		$seconds = ($distance % 60);
		
		if($distance < 0) {
			echo $countdown['campaignName']." = COMPLETED<br>";
			
			if($countdown['status'] == "open") {
				if($countdown['category'] == "weekly") {
					$weeklyNo = $countdown['category'];
					$pageURL = $countdown['pageURL'];
					
					$qUpdate = mysqli_query($con, "UPDATE tbl_campaign SET status = 'closed', keep_show = 1, page_url = '" . $pageURL.$weeklyNo . "' WHERE campaign_id = '" . $countdown['campaignID'] . "'");
				}
				else {
					$qUpdate = mysqli_query($con, "UPDATE tbl_campaign SET status = 'closed', keep_show = 1 WHERE campaign_id = '" . $countdown['campaignID'] . "'");
				}
				
				// First, check if a winner has already been selected
$qCheckWinner = mysqli_query($con, "SELECT * FROM tbl_ticket WHERE campaignid_fk = '" . $countdown['campaignID'] . "' AND win = 'Y'");

if(mysqli_num_rows($qCheckWinner) == 0) { // Only proceed if no winner has been selected
    // First, check if a winner has already been selected
$qCheckWinner = mysqli_query($con, "SELECT * FROM tbl_ticket WHERE campaignid_fk = '" . $countdown['campaignID'] . "' AND win = 'Y'");

if(mysqli_num_rows($qCheckWinner) == 0) { // Only proceed if no winner has been selected
    $qChkData = mysqli_query($con, "
        SELECT * FROM tbl_ticket WHERE campaignid_fk = '" . $countdown['campaignID'] . "' ORDER BY rand() LIMIT 1
    ");
    if(mysqli_num_rows($qChkData) > 0) {
        $dData = mysqli_fetch_array($qChkData);
        
        $ticketID = $dData['ticket_id'];
        
        // Use a transaction to ensure data integrity
        mysqli_begin_transaction($con);

        try {
            $qSave = mysqli_query($con, "UPDATE tbl_ticket SET win = 'Y' WHERE ticket_id = '" . $ticketID . "'");
            $qUpdate = mysqli_query($con, "UPDATE tbl_ticket SET win_ticket_id = '" . $ticketID . "' WHERE campaignid_fk = '" . $countdown['campaignID'] . "'");
            mysqli_commit($con);
        } catch (mysqli_sql_exception $exception) {
            mysqli_rollback($con);
            throw $exception;
        }
    }
}
        } catch (mysqli_sql_exception $exception) {
            mysqli_rollback($con);
            throw $exception;
        }
    }
} . "'");
				}
			}
			else {
				$keep_show = strtotime(date('Y-m-d H:i:s', strtotime($countdown['countdownDateOriginal'].'+12 hour')));
			
				if($now > $keep_show) {
					echo "OK<br>";
					
					$qUpdate = mysqli_query($con, "UPDATE tbl_campaign SET status = 'closed', keep_show = 0 WHERE campaign_id = '" . $countdown['campaignID'] . "'");
				}
			}
		}
		else {
			echo $countdown['campaignName']." = ".$days." ".$hours." ".$minutes." ".$seconds."<br>";
		}
		
		$index -= 1;
	}
}

$todayDay = date("D");
$todayTime = date("H:i:s");

if($todayDay == "Thu" && ($todayTime >= "20:30:00" OR $todayTime >= "20:30:59")) {
	$qTicketPurchased = mysqli_query($con, "
		SELECT * FROM tbl_campaign WHERE category = 'weekly' AND status = 'open'
	");
	if(mysqli_num_rows($qTicketPurchased) > 0) {}
	else {
		$qID = mysqli_query($con, "SELECT weekly_no FROM tbl_campaign ORDER BY weekly_no DESC LIMIT 1");
		if(mysqli_num_rows($qID) > 0) {
			$dID = mysqli_fetch_array($qID);
			
			$id = $dID['weekly_no'] + 1;
		}
		else {
			$id = 1;
		}
		
		$campaignID = substr(str_shuffle("0123456789abcdefghijklmnopqrstvwxyz"), 0, 30);
		
		$dateAdded = date("Y-m-d H:i:s");
		$startDate = date("Y-m-d")." 20:30";
		$endDate = date("Y-m-d",strtotime("+7 days"))." 20:00";
		
		$qSave = mysqli_query($con, "INSERT INTO tbl_campaign(campaign_id, campaign_name, start_date, end_date, date_added, status, category, page_url, public, weekly_no) VALUES('" . $campaignID . "', 'Weeks Pot', '" . $startDate . "', '" . $endDate . "', '" . $dateAdded . "', 'open', 'weekly', 'drawing', '1', '" . $id . "' )");
	}
}
?>
