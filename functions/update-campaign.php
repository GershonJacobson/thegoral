<?php
if(isset( $_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
	session_start();
	require("../config/session.php");

	$campaignID = $_POST['campaignID'];
	$startDate = mysqli_real_escape_string($con, $_POST['startDate']);
	$endDate = mysqli_real_escape_string($con, $_POST['endDate']);
	$campaignName = mysqli_real_escape_string($con, $_POST['campaignName']);
	
	$startDateC = date("Y-m-d H:i", strtotime($startDate));
	$endDateC = date("Y-m-d H:i", strtotime($endDate));
	
	if($_POST['publicOrPrivate'] == "true") {
		$publicOrPrivate = 1;
	}
	else {
		$publicOrPrivate = 0;
	}
	
	$qChkData = mysqli_query($con, "SELECT * FROM tbl_campaign WHERE campaign_id = '$campaignID'");
	if(mysqli_num_rows($qChkData) > 0) {
		$qSave = mysqli_query($con, "UPDATE tbl_campaign SET start_date = '" . $startDateC . "', end_date = '" . $endDateC . "', campaign_name = '" . $campaignName . "', public = '" . $publicOrPrivate . "' WHERE campaign_id = '" . $campaignID . "'");
		
		$data = array(
			"result" => "OK"
		);
	}
	else {
		$data = array(
			"result" => "notExisted"
		);
	}
	
	echo json_encode($data);
}
else {
	header("Location: 403");
}
?>