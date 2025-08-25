<?php
if(isset( $_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
	session_start();
	require("../../config/session.php");

	$campaignID = mysqli_real_escape_string($con, $_POST['campaignID']);
	$payOption = mysqli_real_escape_string($con, $_POST['payOption']);
	$pay = mysqli_real_escape_string($con, $_POST['pay']);
	
	$qChk = mysqli_query($con, "SELECT * FROM tbl_payment WHERE campaignid_fk = '" . $campaignID . "'");
	if(mysqli_num_rows($qChk) > 0) {
		$qUpdate = mysqli_query($con, "
			UPDATE tbl_payment SET payment_option = '" . $payOption . "' WHERE campaignid_fk = '" . $campaignID . "'
		");
			
		$data = array(
			"result" => "OK"
		);
	}
	else {
		$qSave = mysqli_query($con, "
			INSERT INTO tbl_payment(campaignid_fk, payment_option, total) VALUES('" . $campaignID . "', '" . $payOption . "', '" . $pay . "')
		");
			
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