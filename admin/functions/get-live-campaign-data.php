<?php
	session_start();
	require("../../config/session.php");

	$row = $_POST['row'];
	$currentNo = $_POST['currentNo'];
	$filter = $_POST['filter'];
	$rowperpage = 5;
	$i = $currentNo + 1;
	
	$list = [];

	if($filter == "" || $filter == "pmmtl") {
		$query = "
			SELECT
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
			DATE_FORMAT(tbl_campaign.start_date,'%Y-%m-%d') AS start_date_f,
			tbl_campaign.end_date,
			DATE_FORMAT(tbl_campaign.end_date,'%Y-%m-%d') AS end_date_f,
			DATE_FORMAT(tbl_campaign.start_date,'%H:%i') AS start_time,
			DATE_FORMAT(tbl_campaign.end_date,'%H:%i') AS end_time,
			COALESCE(SUM(total_price),0) as rating
			FROM tbl_campaign
			LEFT JOIN tbl_ticket ON tbl_campaign.campaign_id = tbl_ticket.campaignid_fk 
			WHERE
			tbl_campaign.public = 1 AND (tbl_campaign.status = 'open' AND tbl_campaign.keep_show = 0) OR (tbl_campaign.status = 'closed' AND tbl_campaign.keep_show = 1)
			GROUP BY tbl_campaign.campaign_id 
			ORDER BY category DESC, rating DESC limit ".$row.",".$rowperpage;
	}
	else if($filter == "plmtm") {
		$query = "
			SELECT
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
			DATE_FORMAT(tbl_campaign.start_date,'%Y-%m-%d') AS start_date_f,
			tbl_campaign.end_date,
			DATE_FORMAT(tbl_campaign.end_date,'%Y-%m-%d') AS end_date_f,
			DATE_FORMAT(tbl_campaign.start_date,'%H:%i') AS start_time,
			DATE_FORMAT(tbl_campaign.end_date,'%H:%i') AS end_time,
			COALESCE(SUM(total_price),0) as rating
			FROM tbl_campaign
			LEFT JOIN tbl_ticket ON tbl_campaign.campaign_id = tbl_ticket.campaignid_fk 
			WHERE
			tbl_campaign.public = 1 AND (tbl_campaign.status = 'open' AND tbl_campaign.keep_show = 0) OR (tbl_campaign.status = 'closed' AND tbl_campaign.keep_show = 1)
			GROUP BY tbl_campaign.campaign_id 
			ORDER BY category DESC, rating ASC limit ".$row.",".$rowperpage;
	}
	else if($filter == "clltm") {
		$query = "
		SELECT
		campaign_id,
		first_name,
		last_name,
		email_address,
		phone,
		page_url,
		campaign_name,
		public,
		status,
		category,
		DATE_FORMAT(start_date,'%d') AS start_date,
		DATE_FORMAT(start_date,'%Y-%m-%d') AS start_date_f,
		end_date,
		DATE_FORMAT(end_date,'%Y-%m-%d') AS end_date_f,
		DATE_FORMAT(start_date,'%H:%i') AS start_time,
		DATE_FORMAT(end_date,'%H:%i') AS end_time
		FROM tbl_campaign WHERE public = 1 AND public = 1 AND (status = 'open' AND keep_show = 0) OR (status = 'closed' AND keep_show = 1) ORDER BY category DESC, end_date ASC limit ".$row.",".$rowperpage;
	}
	else if($filter == "cmltl") {
		$query = "
		SELECT
		campaign_id,
		first_name,
		last_name,
		email_address,
		phone,
		page_url,
		campaign_name,
		public,
		status,
		category,
		DATE_FORMAT(start_date,'%d') AS start_date,
		DATE_FORMAT(start_date,'%Y-%m-%d') AS start_date_f,
		end_date,
		DATE_FORMAT(end_date,'%Y-%m-%d') AS end_date_f,
		DATE_FORMAT(start_date,'%H:%i') AS start_time,
		DATE_FORMAT(end_date,'%H:%i') AS end_time
		FROM tbl_campaign WHERE public = 1 AND public = 1 AND (status = 'open' AND keep_show = 0) OR (status = 'closed' AND keep_show = 1) ORDER BY category DESC, end_date limit ".$row.",".$rowperpage;
	}
	$result = mysqli_query($con,$query);

	$html = '';

	while($data = mysqli_fetch_array($result)){
		$campaignID = $data['campaign_id'];
		$status = $data['status'];
		$pageURL = $data['page_url'];
		$campaignName = $data['campaign_name'];
		$startDate = $data['start_date'];
		$endDate = $data['end_date'];
		$firstName = $data['first_name'];
		$lastName = $data['last_name'];
		$email = $data['email'];
		$phone = $data['phone'];
		$startTime = $data['start_time'];
		$endTime = $data['end_time'];
		$public = $data['public'];
		$category = $data['category'];
		
		if($data['status'] == "open") {
			$url = $data['page_url'];
		}
		else {
			$url = $data['campaign_id'];
		}
		
		$qAccumulateParticipant = mysqli_query($con, "SELECT DISTINCT email AS total_participants FROM tbl_ticket WHERE campaignid_fk = '" . $campaignID . "'");
		$totalParticipant = mysqli_num_rows($qAccumulateParticipant);
		
		$qAccumulateTicket = mysqli_query($con, "SELECT COALESCE(SUM(total_price), 0) AS total_accumulate FROM tbl_ticket WHERE campaignid_fk = '" . $campaignID . "'");
		$dAccumulateTicket = mysqli_fetch_array($qAccumulateTicket);
		$totalPrice = $dAccumulateTicket['total_accumulate'];
		
		array_push($list, (object)[
			"campaignID" => $campaignID,
			"campaignName" => $campaignName,
			"startDate" => $startDate,
			"endDate" => $endDate,
			"firstName" => $firstName,
			"lastName" => $lastName,
			"emailAddress" => $emailAddress,
			"phone" => $phone,
			"startDateF" => $startDateF,
			"startTime" => $startTime,
			"endDateF" => $endDateF,
			"endTime" => $endTime,
			"pageURL" => $url,
			"public" => $public,
			"totalParticipant" => $totalParticipant,
			"totalAccumulate" => $totalPrice,
			"status" => $status,
			"category" => $category,
			"userRole" => $getUserRole
		]);
	}

	echo json_encode($list);
?>
