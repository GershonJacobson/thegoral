<?php
if(isset( $_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
	session_start();
	require("../config/session.php");

	$ccName = mysqli_real_escape_string($con, $_POST['ccName']);
	$ccNumber = mysqli_real_escape_string($con, $_POST['ccNumber']);
	$ccExpired = mysqli_real_escape_string($con, $_POST['ccExpired']);
	$ccCVV = mysqli_real_escape_string($con, $_POST['ccCVV']);
	$zip = mysqli_real_escape_string($con, $_POST['zip']);
	
	$qChkData = mysqli_query($con, "SELECT * FROM tbl_card WHERE card_number = '$ccNumber'");
	if(mysqli_num_rows($qChkData) > 0) {
		$data = array(
			"result" => "existed"
		);
	}
	else {
		$qSave = mysqli_query($con, "INSERT INTO tbl_card(card_number, card_name, expired, cvv, zip, userid_fk) VALUES('" . $ccNumber . "', '" . $ccName . "', '" . $ccExpired . "', '" . $ccCVV . "', '" . $zip . "', '$getUserID')");
		
		$qLastID = mysqli_query($con, "
			SELECT max(card_id) AS card_id FROM tbl_card
		");
		$dLastID = mysqli_fetch_array($qLastID);
			
		$data = array(
			"id" => $dLastID['card_id'],
			"number" => "Card"." ".substr($ccNumber, -4),
			"result" => "OK"
		);
	}
	
	echo json_encode($data);
}
else {
	header("Location: 403");
}
?>