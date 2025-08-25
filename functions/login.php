<?php
if(isset( $_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
	require("../config/db.php");
	require("../config/decrypt.php");
	
	session_start();
	
	$email = mysqli_real_escape_string($con, $_POST['email']);
	$password = mysqli_real_escape_string($con, $_POST['password']);
	$time = time(); 
	
	$qChkData = mysqli_query($con, "SELECT user_id, password, active FROM tbl_users WHERE email_address = '" . $email . "'");
	if(mysqli_num_rows($qChkData) > 0) {
		$data = mysqli_fetch_array($qChkData);
		
		$hash = $data['password'];
		
		if(password_verify($password, $hash)) {
			if($data['active'] == 0) {
				$data = array(
					"result" => "notActive"
				);
			}
			else {
				$rememberMe = $_POST['rememberMe'];
				
				$_SESSION['userGoral'] = $data['user_id'];
				
				if($rememberMe == 1) {
					setcookie("cookielogin[user]", $data['user_id'], $time + (60*60*24*7), "/", "", "", TRUE);
				}
				
				$data = array(
					"result" => "OK"
				);
			}
		}
		else {
			$data = array(
				"result" => "wrongPassword"
			);
		}
	}
	else {
		$data = array(
			"result" => "emailNotFound"
		);
	}
	
	echo json_encode($data);
}
else {
	header("Location: 403");
}
?>