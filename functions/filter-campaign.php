<?php
if(isset( $_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
	session_start();
	require("../config/session.php");
	
	$selectedFilter = mysqli_real_escape_string($con, $_POST['selectedFilter']);
	$row = mysqli_real_escape_string($con, $_POST['row']);
	$rowperpage = 5;
	
	$list = [];
	
	if($selectedFilter == "pmmtl") {
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
			DATE_FORMAT(tbl_campaign.end_date,'%d') AS end_date,
			DATE_FORMAT(tbl_campaign.end_date,'%Y-%m-%d') AS end_date_f,
			DATE_FORMAT(tbl_campaign.start_date,'%H:%i') AS start_time,
			DATE_FORMAT(tbl_campaign.end_date,'%H:%i') AS end_time,
			COALESCE(SUM(total_price),0) as rating
			FROM tbl_campaign
			LEFT JOIN tbl_ticket ON tbl_campaign.campaign_id = tbl_ticket.campaignid_fk 
			WHERE
			tbl_campaign.added_by = '$getUserID'
			GROUP BY tbl_campaign.campaign_id 
			ORDER BY tbl_campaign.status DESC, rating DESC limit 0,".$rowperpage;
	}
	else if($selectedFilter == "plmtm") {
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
			DATE_FORMAT(tbl_campaign.end_date,'%d') AS end_date,
			DATE_FORMAT(tbl_campaign.end_date,'%Y-%m-%d') AS end_date_f,
			DATE_FORMAT(tbl_campaign.start_date,'%H:%i') AS start_time,
			DATE_FORMAT(tbl_campaign.end_date,'%H:%i') AS end_time,
			COALESCE(SUM(total_price),0) as rating
			FROM tbl_campaign
			LEFT JOIN tbl_ticket ON tbl_campaign.campaign_id = tbl_ticket.campaignid_fk 
			WHERE
			tbl_campaign.added_by = '$getUserID'
			GROUP BY tbl_campaign.campaign_id 
			ORDER BY tbl_campaign.status DESC, rating ASC limit 0,".$rowperpage;
	}
	else if($selectedFilter == "clltm") {
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
			DATE_FORMAT(tbl_campaign.end_date,'%d') AS end_date,
			DATE_FORMAT(tbl_campaign.end_date,'%Y-%m-%d') AS end_date_f,
			DATE_FORMAT(tbl_campaign.start_date,'%H:%i') AS start_time,
			DATE_FORMAT(tbl_campaign.end_date,'%H:%i') AS end_time
			FROM tbl_campaign
			WHERE
			tbl_campaign.added_by = '$getUserID'
			ORDER BY tbl_campaign.status DESC, end_date ASC limit 0,".$rowperpage;
	}
	else if($selectedFilter == "cmltl") {
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
			DATE_FORMAT(tbl_campaign.start_date,'%m/%d-%Y') AS start_date_f,
			DATE_FORMAT(tbl_campaign.end_date,'%d') AS end_date,
			DATE_FORMAT(tbl_campaign.end_date,'%m/%d-%Y') AS end_date_f,
			DATE_FORMAT(tbl_campaign.start_date,'%H:%i') AS start_time,
			DATE_FORMAT(tbl_campaign.end_date,'%H:%i') AS end_time
			FROM tbl_campaign
			WHERE
			tbl_campaign.added_by = '$getUserID'
			ORDER BY end_date DESC limit 0,".$rowperpage;
	}
	$result = mysqli_query($con,$query);
	
	$allcount_query = "SELECT count(*) as allcount FROM tbl_campaign WHERE added_by = '$getUserID'";
	$allcount_result = mysqli_query($con,$allcount_query);
	$allcount_fetch = mysqli_fetch_array($allcount_result);
	$allcount = $allcount_fetch['allcount'];
	
	while($data = mysqli_fetch_array($result)){
		if($data['status'] == "open") {
			$url = $data['page_url'];
		}
		else {
			$url = $data['campaign_id'];
		}
		
		$campaignID = $data['campaign_id'];
		$campaignName = $data['campaign_name'];
		$startDate = $data['start_date'];
		$endDate = $data['end_date'];
		$firstName = $data['first_name'];
		$lastName = $data['last_name'];
		$emailAddress = $data['email_address'];
		$phone = $data['phone'];
		$startDateF = $data['start_date_f'];
		$startTime = $data['start_time'];
		$endDateF = $data['end_date_f'];
		$endTime = $data['end_time'];
		$pageURL = $data['page_url'];
		$public = $data['public'];
		$status = $data['status'];
		
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
			"totalPrice" => $totalPrice,
			"status" => $status
		]);
	}
	
	echo json_encode(array($list,$allcount));
}
else {
	header("Location: 403");
}
?>