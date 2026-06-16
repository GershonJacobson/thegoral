<?php
	require("../config/db.php");
	session_start();
	require("../config/session.php");

	if($getUserID == "") {
		http_response_code(403);
		echo json_encode([[], 0]);
		exit;
	}

	$row = intval($_POST['row'] ?? 0);
	$currentNo = intval($_POST['currentNo'] ?? 0);
	$selectedFilter = $_POST['filter'] ?? '';
	$rowperpage = 5;

	$list = [];

	$allcount_stmt = $con->prepare("SELECT count(*) as allcount FROM tbl_ticket WHERE purchased_by = ?");
	$allcount_stmt->bind_param("i", $getUserID);
	$allcount_stmt->execute();
	$allcount_fetch = $allcount_stmt->get_result()->fetch_assoc();
	$allcount = $allcount_fetch['allcount'];
	$allcount_stmt->close();

	$baseSelect = "SELECT campaignid_fk, ticket_no, DATE_FORMAT(purchased_date,'%m/%d/%Y') AS purchased_date, total_price, card_last4 AS card_number, payment_status FROM tbl_ticket WHERE purchased_by = ?";

	if($selectedFilter == "tm") {
		$currentMonth = date("m");
		$sql = $baseSelect . " AND DATE_FORMAT(purchased_date,'%m') = ? ORDER BY purchased_date DESC LIMIT ?, ?";
		$stmt = $con->prepare($sql);
		$stmt->bind_param("isii", $getUserID, $currentMonth, $row, $rowperpage);
	}
	else if($selectedFilter == "ty") {
		$currentYear = date("Y");
		$sql = $baseSelect . " AND DATE_FORMAT(purchased_date,'%Y') = ? ORDER BY purchased_date DESC LIMIT ?, ?";
		$stmt = $con->prepare($sql);
		$stmt->bind_param("isii", $getUserID, $currentYear, $row, $rowperpage);
	}
	else if($selectedFilter == "ltm") {
		$startQuery = date("Y-m-d H:i:s",strtotime("-3 month"));
		$endQuery = date("Y-m-d H:i:s");
		$sql = $baseSelect . " AND purchased_date BETWEEN ? AND ? ORDER BY purchased_date DESC LIMIT ?, ?";
		$stmt = $con->prepare($sql);
		$stmt->bind_param("issii", $getUserID, $startQuery, $endQuery, $row, $rowperpage);
	}
	else {
		$sql = $baseSelect . " ORDER BY purchased_date DESC LIMIT ?, ?";
		$stmt = $con->prepare($sql);
		$stmt->bind_param("iii", $getUserID, $row, $rowperpage);
	}

	$stmt->execute();
	$qTicketPurchased = $stmt->get_result();

	while($dTicketPurchased = $qTicketPurchased->fetch_assoc()) {
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

		$cStmt = $con->prepare("SELECT campaign_id, campaign_name, status, page_url FROM tbl_campaign WHERE campaign_id = ? LIMIT 1");
		$cStmt->bind_param("s", $campaignID);
		$cStmt->execute();
		$dCampaignName = $cStmt->get_result()->fetch_assoc();
		$cStmt->close();

		$campaignName = $dCampaignName['campaign_name'] ?? '';

		// Open campaigns route by page_url; closed ones by campaign_id
		// (campaign.php resolves closed weeklies by page_url as well)
		if(($dCampaignName['status'] ?? '') == "open") {
			$url = $dCampaignName['page_url'] ?? '';
		}
		else {
			$url = $dCampaignName['campaign_id'] ?? '';
		}

		array_push($list, (object)[
			"campaignID" => $campaignID,
			"ticketNo" => $dTicketPurchased['ticket_no'],
			"paymentStatus" => $paymentStatus,
			"paymentMethod" => $paymentMethod,
			"campaignName" => $campaignName,
			"url" => $url,
			"purchasedDate" => $purchasedDate,
			"totalPrice" => $totalPrice
		]);
	}
	$stmt->close();

	echo json_encode(array($list,$allcount));
?>
