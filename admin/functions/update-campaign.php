<?php
if(isset( $_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
	session_start();
	require("../../config/session.php");

	$campaignID = $_POST['campaignID'];
	$startDate = mysqli_real_escape_string($con, $_POST['startDate']);
	$endDate = mysqli_real_escape_string($con, $_POST['endDate']);
	$campaignName = mysqli_real_escape_string($con, $_POST['campaignName']);
	$category = mysqli_real_escape_string($con, $_POST['category']);
	$ticketPrice1 = mysqli_real_escape_string($con, $_POST['ticketPrice1']);
	$ticketPrice2 = mysqli_real_escape_string($con, $_POST['ticketPrice2']);
	
	$startDateC = date("Y-m-d H:i", strtotime($startDate));
	$endDateC = date("Y-m-d H:i", strtotime($endDate));
	
	if($_POST['publicOrPrivate'] == "true") {
		$publicOrPrivate = 1;
	}
	else {
		$publicOrPrivate = 0;
	}
	
	if($category == "weekly") {
		$updatePublic = "";
	}
	else {
		$updatePublic = ", public = '" . $publicOrPrivate . "'";
	}
	
	$qChkData = mysqli_query($con, "SELECT * FROM tbl_campaign WHERE campaign_id = '$campaignID'");
	if(mysqli_num_rows($qChkData) > 0) {
		$qSave = mysqli_query($con, "UPDATE tbl_campaign SET start_date = '" . $startDateC . "', end_date = '" . $endDateC . "', campaign_name = '" . $campaignName . "' $updatePublic WHERE campaign_id = '" . $campaignID . "'");
		
		$qChkPrice = mysqli_query($con, "SELECT * FROM tbl_ticket_price WHERE campaignid_fk = '" . $campaignID . "'");
		if(mysqli_num_rows($qChkPrice) > 0) {
			$qUpdatePrice = mysqli_query($con, "UPDATE tbl_ticket_price SET 1ticket_price = '" . $ticketPrice1 . "', 2ticket_price = '" . $ticketPrice2 . "' WHERE campaignid_fk = '" . $campaignID . "'");
		}
		else {
			$qInsertPrice = mysqli_query($con, "INSERT INTO tbl_ticket_price(campaignid_fk, 1ticket_price, 2ticket_price) VALUES('" . $campaignID . "', '" . $ticketPrice1 . "', '" . $ticketPrice2 . "')");
		}
		
		$data = array(
			"result" => "OK",
			"ticketPrice1" => $ticketPrice1,
			"ticketPrice2" => $ticketPrice2
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