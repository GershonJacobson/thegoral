<?php
	session_start();
	require("../../config/session.php");

	$row = $_POST['row'];
	$currentNo = $_POST['currentNo'];
	$filter = $_POST['filter'];
	$rowperpage = 5;
	$i = $currentNo + 1;
	
	$list = [];

	if($filter == "" || $filter == "at") {
		$query = "
			SELECT campaign_id, DATE_FORMAT(end_date,'%d-%m-%Y') AS end_date, weekly_no, status, page_url FROM tbl_campaign WHERE category = '' ORDER BY status DESC, end_date DESC limit ".$row.",".$rowperpage;
	}
	else if($filter == "lm") {
		$startQuery = date("Y-m-d",strtotime("-1 month"))." 00:00:00";
		$endQuery = date("Y-m-d")." 23:59:59";
		
		$query = "
			SELECT campaign_id, DATE_FORMAT(end_date,'%d-%m-%Y') AS end_date, weekly_no, status, page_url FROM tbl_campaign WHERE category = '' AND end_date BETWEEN '$startQuery' AND '$endQuery' ORDER BY status DESC, end_date DESC LIMIT ".$row.",".$rowperpage;
	}
	else if($filter == "ltm") {
		$startQuery = date("Y-m-d",strtotime("-3 month"))." 00:00:00";
		$endQuery = date("Y-m-d")." 23:59:59";
		
		$query = "
		SELECT campaign_id, DATE_FORMAT(end_date,'%d-%m-%Y') AS end_date, weekly_no, status, page_url FROM tbl_campaign WHERE category = '' AND end_date BETWEEN '$startQuery' AND '$endQuery' ORDER BY status DESC, end_date DESC limit ".$row.",".$rowperpage;
	}
	else if($filter == "loy") {
		$startQuery = date("Y-m-d",strtotime("-1 year"))." 00:00:00";
		$endQuery = date("Y-m-d")." 23:59:59";
		
		$query = "
		SELECT campaign_id, DATE_FORMAT(end_date,'%d-%m-%Y') AS end_date, weekly_no, status, page_url FROM tbl_campaign WHERE category = '' AND end_date BETWEEN '$startQuery' AND '$endQuery' ORDER BY status DESC, end_date DESC limit ".$row.",".$rowperpage;
	}
	$result = mysqli_query($con,$query);

	while($data = mysqli_fetch_array($result)){
		$campaignID = $data['campaign_id'];
		$endDate = $data['end_date'];
		$status = $data['status'];
		
		if($data['status'] == "open") {
			$pageURL = $data['page_url'];
		}
		else {
			$pageURL = $data['campaign_id'];
		}
		
		$qAccumulateTicket = mysqli_query($con, "SELECT COALESCE(SUM(total_price), 0) AS total_accumulate FROM tbl_ticket WHERE campaignid_fk = '" . $campaignID . "'");
		$dAccumulateTicket = mysqli_fetch_array($qAccumulateTicket);
		$totalPrice = $dAccumulateTicket['total_accumulate'];
		
		$qWinner = mysqli_query($con, "SELECT first_name, last_name FROM tbl_ticket WHERE win = 'Y' AND campaignid_fk = '" . $campaignID . "'");
		$dWinner = mysqli_fetch_array($qWinner);
		$winner = $dWinner['first_name']." ".$dWinner['last_name'];
		
		$qChk = mysqli_query($con, "SELECT * FROM tbl_payment WHERE campaignid_fk = '" . $campaignID . "'");
		$dChk = mysqli_fetch_array($qChk);
		$paymentOption = $dChk['payment_option'];
		$total = $dChk['total'];
		
		array_push($list, (object)[
			"campaignID" => $campaignID,
			"endDate" => $endDate,
			"status" => $status,
			"totalPrice" => $totalPrice,
			"fee" => $totalPrice / 2,
			"winner" => $winner,
			"paymentOption" => $paymentOption,
			"total" => $total,
			"number" => $i,
			"userRole" => $getUserRole,
			"pageURL" => $pageURL
		]);
	}

	echo json_encode($list);
?>
