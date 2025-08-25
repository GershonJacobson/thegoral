<?php
	session_start();
	require("../config/session.php");

	$row = $_POST['row'];
	$currentNo = $_POST['currentNo'];
	$selectedFilter = $_POST['filter'];
	$rowperpage = 5;
	$i = $currentNo + 1;

	$list = [];
	
	$allcount_query = "SELECT count(*) as allcount FROM tbl_ticket WHERE purchased_by = '$getUserID'";
	$allcount_result = mysqli_query($con,$allcount_query);
	$allcount_fetch = mysqli_fetch_array($allcount_result);
	$allcount = $allcount_fetch['allcount'];
	
	if($selectedFilter == "" || $selectedFilter == "at") {
		$qTicketPurchased = mysqli_query($con, "
			SELECT campaignid_fk, DATE_FORMAT(purchased_date,'%m/%d/%Y') AS purchased_date, total_price, RIGHT(card_number,4) as card_number, payment_status FROM tbl_ticket WHERE purchased_by = '$getUserID' ORDER BY purchased_date DESC LIMIT ".$row.",".$rowperpage);
	}
	else if($selectedFilter == "tm") {
		$currentMonth = date("m");
		
		$qTicketPurchased = mysqli_query($con, "
			SELECT campaignid_fk, DATE_FORMAT(purchased_date,'%m/%d/%Y') AS purchased_date, total_price, RIGHT(card_number,4) as card_number, payment_status FROM tbl_ticket WHERE purchased_by = '$getUserID' AND DATE_FORMAT(purchased_date,'%m') = '$currentMonth' ORDER BY purchased_date DESC LIMIT ".$row.",".$rowperpage);
	}
	else if($selectedFilter == "ty") {
		$currentYear = date("Y");
		
		$qTicketPurchased = mysqli_query($con, "
			SELECT campaignid_fk, DATE_FORMAT(purchased_date,'%m/%d/%Y') AS purchased_date, total_price, RIGHT(card_number,4) as card_number, payment_status FROM tbl_ticket WHERE purchased_by = '$getUserID' AND DATE_FORMAT(purchased_date,'%Y') = '$currentYear' ORDER BY purchased_date DESC LIMIT ".$row.",".$rowperpage);
	}
	else if($selectedFilter == "ltm") {
		$startQuery = date("Y-m-d H:i:s",strtotime("-3 month"));
		$endQuery = date("Y-m-d H:i:s");
		
		$qTicketPurchased = mysqli_query($con, "
			SELECT campaignid_fk, DATE_FORMAT(purchased_date,'%m/%d/%Y') AS purchased_date, total_price, RIGHT(card_number,4) as card_number, payment_status FROM tbl_ticket WHERE purchased_by = '$getUserID' AND purchased_date BETWEEN '$startQuery' AND '$endQuery' ORDER BY purchased_date DESC LIMIT ".$row.",".$rowperpage);
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
		$url = $dCampaignName['page_url'];
			
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

	echo json_encode(array($list,$allcount));
?>