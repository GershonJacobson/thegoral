<?php
	session_start();
	require("../../config/session.php");

	$row = $_POST['row'];
	$currentNo = $_POST['currentNo'];
	$filter = $_POST['filter'];
	$rowperpage = 5;
	$i = $currentNo + 1;
	
	$list = [];

	if($filter == "" || $filter == "wr-at") {
		$query = "
			SELECT
			tbl_campaign.weekly_no,
			tbl_campaign.campaign_id,
			tbl_campaign.first_name,
			tbl_campaign.last_name,
			tbl_campaign.email_address,
			tbl_campaign.phone,
			tbl_campaign.page_url,
			tbl_campaign.campaign_name,
			tbl_campaign.public,
			tbl_campaign.status,
			DATE_FORMAT(tbl_campaign.start_date,'%d') AS start_date,
			DATE_FORMAT(tbl_campaign.start_date,'%m/%d/%Y') AS start_date_f,
			DATE_FORMAT(tbl_campaign.end_date,'%m/%d/%Y') AS end_date,
			DATE_FORMAT(tbl_campaign.end_date,'%m/%d/%Y') AS end_date_f,
			DATE_FORMAT(tbl_campaign.start_date,'%H:%i') AS start_time,
			DATE_FORMAT(tbl_campaign.end_date,'%H:%i') AS end_time,
			COALESCE(SUM(tbl_ticket.total_price),0) as rating
			FROM tbl_campaign
			LEFT JOIN tbl_ticket ON tbl_campaign.campaign_id = tbl_ticket.campaignid_fk 
			WHERE
			tbl_campaign.category = 'weekly'
			GROUP BY tbl_campaign.campaign_id 
			ORDER BY tbl_campaign.weekly_no DESC, rating DESC limit ".$row.",".$rowperpage;
	}
	else if($filter == "wr-lm") {
		$currentMonth = date("m");
		
		$query = "
			SELECT
			tbl_campaign.weekly_no,
			tbl_campaign.campaign_id,
			tbl_campaign.first_name,
			tbl_campaign.last_name,
			tbl_campaign.email_address,
			tbl_campaign.phone,
			tbl_campaign.page_url,
			tbl_campaign.campaign_name,
			tbl_campaign.public,
			tbl_campaign.status,
			DATE_FORMAT(tbl_campaign.start_date,'%d') AS start_date,
			DATE_FORMAT(tbl_campaign.start_date,'%m/%d/%Y') AS start_date_f,
			DATE_FORMAT(tbl_campaign.end_date,'%m/%d/%Y') AS end_date,
			DATE_FORMAT(tbl_campaign.end_date,'%m/%d/%Y') AS end_date_f,
			DATE_FORMAT(tbl_campaign.start_date,'%H:%i') AS start_time,
			DATE_FORMAT(tbl_campaign.end_date,'%H:%i') AS end_time,
			COALESCE(SUM(tbl_ticket.total_price),0) as rating
			FROM tbl_campaign
			LEFT JOIN tbl_ticket ON tbl_campaign.campaign_id = tbl_ticket.campaignid_fk 
			WHERE
			tbl_campaign.category = 'weekly' AND
			DATE_FORMAT(tbl_campaign.end_date,'%m') = '$currentMonth'
			GROUP BY tbl_campaign.campaign_id 
			ORDER BY tbl_campaign.weekly_no DESC, rating DESC limit ".$row.",".$rowperpage;
	}
	else if($filter == "wr-ltm") {
		$query = "
			SELECT
			tbl_campaign.weekly_no,
			tbl_campaign.campaign_id,
			tbl_campaign.first_name,
			tbl_campaign.last_name,
			tbl_campaign.email_address,
			tbl_campaign.phone,
			tbl_campaign.page_url,
			tbl_campaign.campaign_name,
			tbl_campaign.public,
			tbl_campaign.status,
			DATE_FORMAT(tbl_campaign.start_date,'%d') AS start_date,
			DATE_FORMAT(tbl_campaign.start_date,'%m/%d/%Y') AS start_date_f,
			DATE_FORMAT(tbl_campaign.end_date,'%m/%d/%Y') AS end_date,
			DATE_FORMAT(tbl_campaign.end_date,'%m/%d/%Y') AS end_date_f,
			DATE_FORMAT(tbl_campaign.start_date,'%H:%i') AS start_time,
			DATE_FORMAT(tbl_campaign.end_date,'%H:%i') AS end_time,
			COALESCE(SUM(tbl_ticket.total_price),0) as rating
			FROM tbl_campaign
			LEFT JOIN tbl_ticket ON tbl_campaign.campaign_id = tbl_ticket.campaignid_fk 
			WHERE
			tbl_campaign.category = 'weekly' AND
			tbl_campaign.end_date >= last_day(now()) + interval 1 day - interval 3 month
			GROUP BY tbl_campaign.campaign_id 
			ORDER BY tbl_campaign.weekly_no DESC, rating DESC limit ".$row.",".$rowperpage;
	}
	else if($filter == "wr-loy") {
		$query = "
			SELECT
			tbl_campaign.weekly_no,
			tbl_campaign.campaign_id,
			tbl_campaign.first_name,
			tbl_campaign.last_name,
			tbl_campaign.email_address,
			tbl_campaign.phone,
			tbl_campaign.page_url,
			tbl_campaign.campaign_name,
			tbl_campaign.public,
			tbl_campaign.status,
			DATE_FORMAT(tbl_campaign.start_date,'%d') AS start_date,
			DATE_FORMAT(tbl_campaign.start_date,'%m/%d/%Y') AS start_date_f,
			DATE_FORMAT(tbl_campaign.end_date,'%m/%d/%Y') AS end_date,
			DATE_FORMAT(tbl_campaign.end_date,'%m/%d/%Y') AS end_date_f,
			DATE_FORMAT(tbl_campaign.start_date,'%H:%i') AS start_time,
			DATE_FORMAT(tbl_campaign.end_date,'%H:%i') AS end_time,
			COALESCE(SUM(tbl_ticket.total_price),0) as rating
			FROM tbl_campaign
			LEFT JOIN tbl_ticket ON tbl_campaign.campaign_id = tbl_ticket.campaignid_fk 
			WHERE
			tbl_campaign.category = 'weekly' AND
			tbl_campaign.end_date >= DATE_SUB(NOW(),INTERVAL 1 YEAR)
			GROUP BY tbl_campaign.campaign_id 
			ORDER BY tbl_campaign.weekly_no DESC, rating DESC limit ".$row.",".$rowperpage;
	}
	$result = mysqli_query($con,$query);

	while($data = mysqli_fetch_array($result)){
		$campaignID = $data['campaign_id'];
		$endDate = $data['end_date'];
		$weeklyNo = $data['weekly_no'];
		$status = $data['status'];
		$pageURL = $data['page_url'];
		
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
			"weeklyNo" => $weeklyNo,
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

	echo json_encode($list);
?>
