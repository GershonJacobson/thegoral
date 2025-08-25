<?php
if(isset( $_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
	session_start();
	require("../../config/session.php");

	$firstName = mysqli_real_escape_string($con, $_POST['firstName']);
	$lastName = mysqli_real_escape_string($con, $_POST['lastName']);
	$email = mysqli_real_escape_string($con, $_POST['email']);
	$phone = mysqli_real_escape_string($con, $_POST['phone']);
	
	$qChk = mysqli_query($con, "SELECT * FROM tbl_ticket WHERE email = '" . $email . "'");
	if(mysqli_num_rows($qChk) > 0) {
		$date_added = date("Y-m-d H:i:s");
		
		$qUpdate = mysqli_query($con, "UPDATE tbl_ticket SET first_name = '$firstName', last_name = '$lastName', phone = '$phone' WHERE email = '" . $email . "'");
		
		$data = array(
			"result" => "OK"
		);
	}
	else {
		$data = array(
			"result" => "emailNotExisted"
		);
	}
	
	echo json_encode($data);
}
else {
	header("Location: 403");
}
?>