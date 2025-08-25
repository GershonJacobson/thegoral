<?php
if(isset( $_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
	session_start();
	require("../config/session.php");

	$firstName = mysqli_real_escape_string($con, $_POST['firstName']);
	$lastName = mysqli_real_escape_string($con, $_POST['lastName']);
	$email = mysqli_real_escape_string($con, $_POST['email']);
	$phone = mysqli_real_escape_string($con, $_POST['phone']);
	$startDate = mysqli_real_escape_string($con, $_POST['startDate']);
	$endDate = mysqli_real_escape_string($con, $_POST['endDate']);
	$campaignName = mysqli_real_escape_string($con, $_POST['campaignName']);
	$pageUrl = mysqli_real_escape_string($con, $_POST['pageUrl']);

	$startDateC = date("Y-m-d H:i", strtotime($startDate));
	$endDateC = date("Y-m-d H:i", strtotime($endDate));
	
	if($_POST['publicOrPrivate'] == true) {
		$publicOrPrivate = 1;
	}
	else {
		$publicOrPrivate = 0;
	}
	
	$qChkData = mysqli_query($con, "SELECT * FROM tbl_campaign WHERE page_url = '" . $pageUrl . "' AND status = 'open'");
	if(mysqli_num_rows($qChkData) > 0) {
		$data = array(
			"result" => "pageURLExisted"
		);
	}
	else {
		$date_added = date("Y-m-d H:i:s");
		
		$campaignID = substr(str_shuffle("0123456789abcdefghijklmnopqrstvwxyz"), 0, 30);
		
		$qSave = mysqli_query($con, "INSERT INTO tbl_campaign(campaign_id, first_name, last_name, email_address, phone, start_date, end_date, campaign_name, page_url, public, date_added, status, added_by) VALUES('" . $campaignID . "', '" . $firstName . "', '" . $lastName . "', '" . $email . "', '" . $phone . "', '" . $startDateC . "', '" . $endDateC . "', '" . $campaignName . "', '" . $pageUrl . "', '" . $publicOrPrivate . "', '$date_added', 'open', '$getUserID' )");
		
		$data = array(
			"result" => "OK"
		);
	}
	
	echo json_encode($data);
}
else {
	header("Location: 403");
}
?>