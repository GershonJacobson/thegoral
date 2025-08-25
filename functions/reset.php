<?php
if(isset( $_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
	require("../config/db.php");
	require("../config/decrypt.php");
	
	$email = mysqli_real_escape_string($con, $_POST['email']);
	$password = mysqli_real_escape_string($con, $_POST['password']);
	$passwordEncrypt = password_hash($password, PASSWORD_DEFAULT);
	
	$qChkData = mysqli_query($con, "SELECT * FROM tbl_users WHERE email_address = '" . $email . "'");
	if(mysqli_num_rows($qChkData) > 0) {
		$qSave = mysqli_query($con, "UPDATE tbl_users SET password = '" . $passwordEncrypt . "', fp_code = '' WHERE email_address = '" . $email . "'");
		
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