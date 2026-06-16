<?php
if(isset( $_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
	session_start();
	require("../../config/session.php");

	if((int)$getUserRole !== 1) {
		http_response_code(403);
		echo json_encode(["result" => "forbidden"]);
		exit;
	}

	$userID = intval($_POST['userID'] ?? 0);
	
	$qChkData = mysqli_query($con, "SELECT * FROM tbl_users WHERE user_id = '" . $userID . "'");
	if(mysqli_num_rows($qChkData) > 0) {
		$qSave = mysqli_query($con, "UPDATE tbl_users SET admin = 0 WHERE user_id = '" . $userID . "'");
		
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