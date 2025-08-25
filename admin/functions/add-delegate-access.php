<?php
if(isset( $_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
	session_start();
	require("../../config/session.php");

	$emailAddress = mysqli_real_escape_string($con, $_POST['emailAddress']);
	$access = mysqli_real_escape_string($con, $_POST['access']);
	
	$qChk = mysqli_query($con, "SELECT * FROM tbl_users WHERE email_address = '" . $emailAddress . "'");
	if(mysqli_num_rows($qChk) > 0) {
		if($access == "full-admin") {
			$roleID = 1;
		}
		else if($access == "edit-cant-see-money") {
			$roleID = 2;
		}
		else if($access == "just-view-cant-edit") {
			$roleID = 3;
		}
		else {
			$roleID = 0;
		}
		
		$qChk2 = mysqli_query($con, "SELECT * FROM tbl_users WHERE email_address = '" . $emailAddress . "' AND admin != 0");
		if(mysqli_num_rows($qChk2) > 0) {
			$data = array(
				"result" => "existed"
			);
		}
		else {
			$qUsers = mysqli_query($con, "SELECT * FROM tbl_users WHERE email_address = '" . $emailAddress . "'");
			$dataUser = mysqli_fetch_array($qUsers);
			$userID = $dataUser['user_id'];
			
			$date_added = date("Y-m-d H:i:s");
			
			$qUpdate = mysqli_query($con, "UPDATE tbl_users SET admin = '$roleID', delegate_date = '$date_added' WHERE email_address = '" . $emailAddress . "'");
				
			$data = array(
				"userID" => $userID,
				"result" => "OK"
			);
		}
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