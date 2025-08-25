<?php
	session_start();
	require("../config/session.php");

	$row = $_POST['row'];
	$currentNo = $_POST['currentNo'];
	$filter = $_POST['filter'];
	$rowperpage = 5;
	$i = $currentNo + 1;

	if($filter == "" || $filter == "pmmtl") {
		$query = "
			SELECT
			tbl_campaign.campaign_id,
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
			tbl_campaign.public = 1 AND (tbl_campaign.status = 'closed' AND (tbl_campaign.keep_show = 0 OR tbl_campaign.keep_show = 1)) AND tbl_campaign.category = ''
			GROUP BY tbl_campaign.campaign_id 
			ORDER BY rating DESC limit ".$row.",".$rowperpage;
	}
	else if($filter == "cmltl") {
		$query = "
			SELECT
			tbl_campaign.campaign_id,
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
			tbl_campaign.public = 1 AND (tbl_campaign.status = 'closed' AND (tbl_campaign.keep_show = 0 OR tbl_campaign.keep_show = 1)) AND tbl_campaign.category = ''
			GROUP BY tbl_campaign.campaign_id 
			ORDER BY end_date DESC limit ".$row.",".$rowperpage;
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
		
		if($data['status'] == "open") {
			$url = $data['page_url'];
		}
		else {
			$url = $data['campaign_id'];
		}
		
		$html .= '<div class="col-md-4 post" style="margin-top:10px" id="post_'.$campaignID.'">';
		$html .= '	<div class="card">';
		$html .= '	  <div class="card-body">';
		$html .= '		<a href="/'.$campaignID.'">';
		$html .= '		  <h5 class="card-title">';
		$html .= '			<img src="../assets/images/user-icon.png" alt="" />'.$campaignName.'';
		$html .= '		  </h5>';
		$html .= '		  <h6 class="card-subtitle mb-2">';
					$qAccumulateTicket = mysqli_query($con, "SELECT COALESCE(SUM(total_price), 0) AS total_accumulate FROM tbl_ticket WHERE campaignid_fk = '" . $campaignID . "'");
					$dAccumulateTicket = mysqli_fetch_array($qAccumulateTicket);
					
					$totalTicketPrice = $dAccumulateTicket['total_accumulate'];
		$html .= '$'.$totalTicketPrice.'</h6>';
		$html .= '		  <p class="card-text">';
					$qAccumulateParticipant = mysqli_query($con, "SELECT DISTINCT email AS total_participants FROM tbl_ticket WHERE campaignid_fk = '" . $campaignID . "'");
					
					$totalParticipant = mysqli_num_rows($qAccumulateParticipant);
		$html .= ''.$totalParticipant.' Participant</p>';
		$html .= '		  <div class="card-info">';
		$html .= '			<span class="winner">Winner</span>';
		$html .= '			<span class="win-name">';
					$qWinner = mysqli_query($con, "SELECT first_name, last_name FROM tbl_ticket WHERE campaignid_fk = '" . $campaignID . "' AND win = 'Y'");
					$dWinner = mysqli_fetch_array($qWinner);
					$winner = $dWinner['first_name']." ". $dWinner['last_name'];
		$html .= ''.$winner.'</span>';
		$html .= '		  </div>';
		$html .= '		</a>';
		$html .= '	  </div>';
		$html .= '	</div>';
		$html .= '  </div>';
	}

	echo $html;

?>