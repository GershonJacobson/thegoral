<?php
if(isset( $_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
	session_start();
	require("../config/session.php");

	$ccID = $_POST['ccID'];
	$ccName = mysqli_real_escape_string($con, $_POST['ccName']);
	$ccNumber = mysqli_real_escape_string($con, $_POST['ccNumber']);
	$ccExpired = mysqli_real_escape_string($con, $_POST['ccExpired']);
	$ccCVV = mysqli_real_escape_string($con, $_POST['ccCVV']);
	$zip = mysqli_real_escape_string($con, $_POST['zip']);
	
	$qChkData = mysqli_query($con, "SELECT * FROM tbl_card WHERE card_id = '$ccID'");
	if(mysqli_num_rows($qChkData) > 0) {
		$qSave = mysqli_query($con, "UPDATE tbl_card SET card_number = '" . $ccNumber . "', card_name = '" . $ccName . "', expired = '" . $ccExpired . "', cvv = '" . $ccCVV . "', zip = '" . $zip . "' WHERE card_id = '" . $ccID . "'");
		
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