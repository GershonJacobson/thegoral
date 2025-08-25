<?php
if(isset( $_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
	session_start();
	require("../config/session.php");
	
	$selectedFilter = mysqli_real_escape_string($con, $_POST['selectedFilter']);
	
	$list = [];
	
	$allcount_query = "SELECT count(*) as allcount FROM tbl_ticket WHERE purchased_by = '$getUserID'";
	$allcount_result = mysqli_query($con,$allcount_query);
	$allcount_fetch = mysqli_fetch_array($allcount_result);
	$allcount = $allcount_fetch['allcount'];
	
	if($selectedFilter == "at") {
		$qAccumulateTicketPrice = mysqli_query($con, "SELECT COALESCE(SUM(total_price), 0) AS total_ticket_price FROM tbl_ticket WHERE purchased_by = '" . $getUserID . "'");
		$dAccumulateTicketPrice = mysqli_fetch_array($qAccumulateTicketPrice);
		$totalTicketPrice = $dAccumulateTicketPrice['total_ticket_price'];
		
		$qAccumulateTicketBought = mysqli_query($con, "SELECT COALESCE(COUNT(*), 0) AS total_ticket FROM tbl_ticket WHERE purchased_by = '" . $getUserID . "'");
		$dAccumulateTicketBought = mysqli_fetch_array($qAccumulateTicketBought);
		$totalTicket = $dAccumulateTicketBought['total_ticket'];
		
		$qRafflesJoined = mysqli_query($con, "SELECT DISTINCT campaignid_fk FROM tbl_ticket WHERE purchased_by = '" . $getUserID . "'");
		$totalRafflesJoined = mysqli_num_rows($qRafflesJoined);
		
		$qRafflesWon = mysqli_query($con, "SELECT COALESCE(SUM(total_price),0) AS total_won FROM tbl_ticket WHERE purchased_by = '" . $getUserID . "' AND win = 'Y'");
		$dRafflesWon = mysqli_fetch_array($qRafflesWon);
		$totalRafflesWon = $dRafflesWon['total_won'];
		
		$qTicketPurchased = mysqli_query($con, "
			SELECT campaignid_fk, DATE_FORMAT(purchased_date,'%m/%d/%Y') AS purchased_date, total_price, RIGHT(card_number,4) as card_number, payment_status FROM tbl_ticket WHERE purchased_by = '$getUserID' ORDER BY purchased_date DESC LIMIT 5
		");
	}
	else if($selectedFilter == "tm") {
		$currentMonth = date("m");
		
		$qAccumulateTicketPrice = mysqli_query($con, "SELECT COALESCE(SUM(total_price), 0) AS total_ticket_price FROM tbl_ticket WHERE purchased_by = '" . $getUserID . "' AND DATE_FORMAT(purchased_date,'%m') = '$currentMonth'");
		$dAccumulateTicketPrice = mysqli_fetch_array($qAccumulateTicketPrice);
		$totalTicketPrice = $dAccumulateTicketPrice['total_ticket_price'];
		
		$qAccumulateTicketBought = mysqli_query($con, "SELECT COALESCE(COUNT(*), 0) AS total_ticket FROM tbl_ticket WHERE purchased_by = '" . $getUserID . "' AND DATE_FORMAT(purchased_date,'%m') = '$currentMonth'");
		$dAccumulateTicketBought = mysqli_fetch_array($qAccumulateTicketBought);
		$totalTicket = $dAccumulateTicketBought['total_ticket'];
		
		$qRafflesJoined = mysqli_query($con, "SELECT DISTINCT campaignid_fk FROM tbl_ticket WHERE purchased_by = '" . $getUserID . "' AND DATE_FORMAT(purchased_date,'%m') = '$currentMonth'");
		$totalRafflesJoined = mysqli_num_rows($qRafflesJoined);
		
		$qRafflesWon = mysqli_query($con, "SELECT COALESCE(SUM(total_price),0) AS total_won FROM tbl_ticket WHERE purchased_by = '" . $getUserID . "' AND win = 'Y' AND DATE_FORMAT(purchased_date,'%m') = '$currentMonth'");
		$dRafflesWon = mysqli_fetch_array($qRafflesWon);
		$totalRafflesWon = $dRafflesWon['total_won'];
		
		$qTicketPurchased = mysqli_query($con, "
			SELECT campaignid_fk, DATE_FORMAT(purchased_date,'%m/%d/%Y') AS purchased_date, total_price, RIGHT(card_number,4) as card_number, payment_status FROM tbl_ticket WHERE purchased_by = '$getUserID' AND DATE_FORMAT(purchased_date,'%m') = '$currentMonth' ORDER BY purchased_date DESC LIMIT 5
		");
	}
	else if($selectedFilter == "ty") {
		$currentYear = date("Y");
		$qAccumulateTicketPrice = mysqli_query($con, "SELECT COALESCE(SUM(total_price), 0) AS total_ticket_price FROM tbl_ticket WHERE purchased_by = '" . $getUserID . "' AND DATE_FORMAT(purchased_date,'%Y') = '$currentYear'");
		$dAccumulateTicketPrice = mysqli_fetch_array($qAccumulateTicketPrice);
		$totalTicketPrice = $dAccumulateTicketPrice['total_ticket_price'];
		
		$qAccumulateTicketBought = mysqli_query($con, "SELECT COALESCE(COUNT(*), 0) AS total_ticket FROM tbl_ticket WHERE purchased_by = '" . $getUserID . "' AND DATE_FORMAT(purchased_date,'%Y') = '$currentYear'");
		$dAccumulateTicketBought = mysqli_fetch_array($qAccumulateTicketBought);
		$totalTicket = $dAccumulateTicketBought['total_ticket'];
		
		$qRafflesJoined = mysqli_query($con, "SELECT DISTINCT campaignid_fk FROM tbl_ticket WHERE purchased_by = '" . $getUserID . "' AND DATE_FORMAT(purchased_date,'%Y') = '$currentYear'");
		$totalRafflesJoined = mysqli_num_rows($qRafflesJoined);
		
		$qRafflesWon = mysqli_query($con, "SELECT COALESCE(SUM(total_price),0) AS total_won FROM tbl_ticket WHERE purchased_by = '" . $getUserID . "' AND win = 'Y' AND DATE_FORMAT(purchased_date,'%Y') = '$currentYear'");
		$dRafflesWon = mysqli_fetch_array($qRafflesWon);
		$totalRafflesWon = $dRafflesWon['total_won'];
		
		$qTicketPurchased = mysqli_query($con, "
			SELECT campaignid_fk, DATE_FORMAT(purchased_date,'%m/%d/%Y') AS purchased_date, total_price, RIGHT(card_number,4) as card_number, payment_status FROM tbl_ticket WHERE purchased_by = '$getUserID' AND DATE_FORMAT(purchased_date,'%Y') = '$currentYear' ORDER BY purchased_date DESC LIMIT 5
		");
	}
	else if($selectedFilter == "ltm") {
		$startQuery = date("Y-m-d H:i:s",strtotime("-3 month"));
		$endQuery = date("Y-m-d H:i:s");
		
		$qAccumulateTicketPrice = mysqli_query($con, "SELECT COALESCE(SUM(total_price), 0) AS total_ticket_price FROM tbl_ticket WHERE purchased_by = '" . $getUserID . "' AND purchased_date BETWEEN '$startQuery' AND '$endQuery'");
		$dAccumulateTicketPrice = mysqli_fetch_array($qAccumulateTicketPrice);
		$totalTicketPrice = $dAccumulateTicketPrice['total_ticket_price'];
		
		$qAccumulateTicketBought = mysqli_query($con, "SELECT COALESCE(COUNT(*), 0) AS total_ticket FROM tbl_ticket WHERE purchased_by = '" . $getUserID . "' AND purchased_date BETWEEN '$startQuery' AND '$endQuery'");
		$dAccumulateTicketBought = mysqli_fetch_array($qAccumulateTicketBought);
		$totalTicket = $dAccumulateTicketBought['total_ticket'];
		
		$qRafflesJoined = mysqli_query($con, "SELECT DISTINCT campaignid_fk FROM tbl_ticket WHERE purchased_by = '" . $getUserID . "'  AND purchased_date BETWEEN '$startQuery' AND '$endQuery'");
		$totalRafflesJoined = mysqli_num_rows($qRafflesJoined);
		
		$qRafflesWon = mysqli_query($con, "SELECT COALESCE(SUM(total_price),0) AS total_won FROM tbl_ticket WHERE purchased_by = '" . $getUserID . "' AND win = 'Y' AND purchased_date BETWEEN '$startQuery' AND '$endQuery'");
		$dRafflesWon = mysqli_fetch_array($qRafflesWon);
		$totalRafflesWon = $dRafflesWon['total_won'];
		
		$qTicketPurchased = mysqli_query($con, "
			SELECT campaignid_fk, DATE_FORMAT(purchased_date,'%m/%d/%Y') AS purchased_date, total_price, RIGHT(card_number,4) as card_number, payment_status FROM tbl_ticket WHERE purchased_by = '$getUserID' AND purchased_date BETWEEN '$startQuery' AND '$endQuery' ORDER BY purchased_date DESC LIMIT 5
		");
	}
	
	while($dTicketPurchased = mysqli_fetch_array($qTicketPurchased)) {
		$campaignID = $dTicketPurchased['campaignid_fk'];
		$purchasedDate = $dTicketPurchased['purchased_date'];
		$totalPrice = $dTicketPurchased['total_price'];
		
		if($dTicketPurchased['payment_status'] == 0) {
			$paymentStatus = "Process";
		}
		else if($dTicketPurchased['payment_status'] == 1) {
			$paymentStatus = "Success";
		}
		else {
			$paymentStatus = "Failed";
		}
		
		if($dTicketPurchased['card_number'] != "") {
			$paymentMethod = "Card ".$dTicketPurchased['card_number'];
		}
		else {
			$paymentMethod = "";
		}
			
		$qCampaignName = mysqli_query($con, "SELECT campaign_id, campaign_name, status, page_url FROM tbl_campaign WHERE campaign_id = '$campaignID'");
		$dCampaignName = mysqli_fetch_array($qCampaignName);
		
		$campaignName = $dCampaignName['campaign_name'];
		
		if($dCampaignName['status'] == "open") {
			$url = $dCampaignName['page_url'];
		}
		else {
			$url = $dCampaignName['campaign_id'];
		}
			
		array_push($list, (object)[
			"campaignID" => $campaignID,
			"paymentStatus" => $paymentStatus,
			"paymentMethod" => $paymentMethod,
			"campaignName" => $campaignName,
			"url" => $url,
			"purchasedDate" => $purchasedDate,
			"totalPrice" => $totalPrice
		]);
	}
	
	$data = array(
		"totalTicketPrice" => $totalTicketPrice,
		"totalTicket" => $totalTicket,
		"totalRafflesJoined" => $totalRafflesJoined,
		"totalRafflesWon" => $totalRafflesWon,
		"userID" => $getUserID,
		"list" => $list,
		"allcount" => $allcount
	);
	
	echo json_encode($data);
}
else {
	header("Location: 403");
}
?>