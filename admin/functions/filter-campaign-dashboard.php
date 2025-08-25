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
	
	$allcount_query = "SELECT count(*) as allcount FROM tbl_campaign WHERE public = 1 AND (status = 'open' AND keep_show = 0) OR (status = 'closed' AND keep_show = 1)";
	$allcount_result = mysqli_query($con,$allcount_query);
	$allcount_fetch = mysqli_fetch_array($allcount_result);
	$allcount = $allcount_fetch['allcount'];
	
	if($selectedFilter == "" || $selectedFilter == "pmmtl") {
		$query = mysqli_query($con, "
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
			tbl_campaign.category,
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
			ORDER BY category DESC, rating DESC limit 0,".$rowperpage);
	}
	else if($selectedFilter == "plmtm") {
		$query = mysqli_query($con, "
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
			tbl_campaign.category,
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
			ORDER BY category DESC, rating ASC limit 0,".$rowperpage);
	}
	else if($selectedFilter == "clltm") {
		$query = mysqli_query($con, "
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
			FROM tbl_campaign WHERE public = 1 AND (status = 'open' AND keep_show = 0) OR (status = 'closed' AND keep_show = 1) ORDER BY category DESC, end_date ASC limit 0,".$rowperpage);
	}
	else if($selectedFilter == "cmltl") {
		$query = mysqli_query($con, "
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
			FROM tbl_campaign WHERE public = 1 AND (status = 'open' AND keep_show = 0) OR (status = 'closed' AND keep_show = 1) ORDER BY category DESC, end_date DESC limit 0,".$rowperpage);
	}
	
	while($data = mysqli_fetch_array($query)) {
		$campaignID = $data['campaign_id'];
		$campaignName = $data['campaign_name'];
		$status = $data['status'];
		$endDate = $data['end_date'];
		$category = $data['category'];
		$startDateF = $data['start_date_f'];
		$endDateF = $data['end_date_f'];
		$startTime = $data['start_time'];
		$endTime = $data['end_time'];
		$public = $data['public'];
		$pageURL = $data['page_url'];
		
		$qAccumulateParticipant = mysqli_query($con, "SELECT DISTINCT email AS total_participants FROM tbl_ticket WHERE campaignid_fk = '" . $data['campaign_id'] . "'");
		$totalParticipant = mysqli_num_rows($qAccumulateParticipant);
		
		$qAccumulateTicket = mysqli_query($con, "SELECT COALESCE(SUM(total_price), 0) AS total_accumulate FROM tbl_ticket WHERE campaignid_fk = '" . $data['campaign_id'] . "'");
		$dAccumulateTicket = mysqli_fetch_array($qAccumulateTicket);
		$totalAccumulate = $dAccumulateTicket['total_accumulate'];
		
		array_push($list, (object)[
			"campaignID" => $campaignID,
			"campaignName" => $campaignName,
			"status" => $status,
			"totalParticipant" => $totalParticipant,
			"totalAccumulate" => $totalAccumulate,
			"endDate" => $endDate,
			"category" => $category,
			"startDateF" => $startDateF,
			"endDateF" => $endDateF,
			"startTime" => $startTime,
			"endTime" => $endTime,
			"public" => $public,
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