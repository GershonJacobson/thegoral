<?php
if(isset( $_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
	session_start();
	require("../../config/session.php");
	
	$row = $_POST['row'];
	$currentNo = $_POST['currentNo'];
	$selectedFilter = mysqli_real_escape_string($con, $_POST['selectedFilter']);
	$rowperpage = 5;
	$i = $currentNo + 1;
	
	$list = [];
	
	$allcount_query = "SELECT count(*) as allcount FROM tbl_campaign WHERE category = ''";
	$allcount_result = mysqli_query($con,$allcount_query);
	$allcount_fetch = mysqli_fetch_array($allcount_result);
	$allcount = $allcount_fetch['allcount'];
	
	if($selectedFilter == "" || $selectedFilter == "at") {
		$query = mysqli_query($con, "
			SELECT campaign_id, DATE_FORMAT(end_date,'%d-%m-%Y') AS end_date, weekly_no, status, page_url FROM tbl_campaign WHERE category = '' ORDER BY status DESC limit 0,".$rowperpage);
	}
	else if($selectedFilter == "lm") {
		$currentMonth = date("m");
		
		$query = mysqli_query($con, "
			SELECT campaign_id, DATE_FORMAT(end_date,'%d-%m-%Y') AS end_date, weekly_no, status, page_url FROM tbl_campaign WHERE category = '' AND 
			DATE_FORMAT(end_date,'%m') = '$currentMonth'
			ORDER BY status DESC limit 0,".$rowperpage);
	}
	else if($selectedFilter == "ltm") {
		$query = mysqli_query($con, "
			SELECT campaign_id, DATE_FORMAT(end_date,'%d-%m-%Y') AS end_date, weekly_no, status, page_url FROM tbl_campaign WHERE category = '' AND 
			end_date >= last_day(now()) + interval 1 day - interval 3 month
			ORDER BY status DESC limit 0,".$rowperpage);
	}
	else if($selectedFilter == "loy") {
		$startQuery = date("Y-m-d",strtotime("-1 year"))." 00:00:00";
		$endQuery = date("Y-m-d")." 23:59:59";
		
		$query = mysqli_query($con, "
			SELECT campaign_id, DATE_FORMAT(end_date,'%d-%m-%Y') AS end_date, weekly_no, status, page_url FROM tbl_campaign WHERE category = '' AND 
			end_date >= DATE_SUB(NOW(),INTERVAL 1 YEAR)
			ORDER BY status DESC limit 0,".$rowperpage);
	}
	
	while($data = mysqli_fetch_array($query)) {
		$campaignID = $data['campaign_id'];
		$endDate = $data['end_date'];
		$weeklyNo = $data['weekly_no'];
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
		if($dChk['payment_option'] == "") {
			$paymentOption = "";
		}
		else {
			$paymentOption = $dChk['payment_option'];
		}
		
		if($dChk['total'] == "") {
			$total = "";
		}
		else {
			$total = "$".$dChk['total'];
		}
		
		array_push($list, (object)[
			"campaignID" => $campaignID,
			"endDate" => $endDate,
			"status" => $status,
			"totalPrice" => $totalPrice,
			"fee" => $totalPrice / 2,
			"winner" => $winner,
			"paymentOption" => $paymentOption,
			"total" => $total,
			"userRole" => $getUserRole,
			"pageURL" => $pageURL
		]);
	}
	
	echo json_encode(array($list,$allcount));
}
else {
	header("Location: 403");
}
?>