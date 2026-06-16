<?php
if(isset( $_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
	require("../config/db.php");

	$email = trim(strtolower($_POST['email'] ?? ''));
	$password = $_POST['password'] ?? '';
	$fpCode = trim($_POST['fpCode'] ?? '');

	if($email === '' || $password === '' || $fpCode === '') {
		echo json_encode(["result" => "missingFields"]);
		exit;
	}

	if(strlen($password) < 6) {
		echo json_encode(["result" => "weakPassword"]);
		exit;
	}

	// The reset code must match the one emailed to this address AND be unexpired.
	$stmt = $con->prepare("SELECT user_id FROM tbl_users WHERE email_address = ? AND fp_code = ? AND fp_code != '' AND fp_code_expires IS NOT NULL AND fp_code_expires > NOW() LIMIT 1");
	$stmt->bind_param("ss", $email, $fpCode);
	$stmt->execute();
	$result = $stmt->get_result();

	if($result->num_rows > 0) {
		$user = $result->fetch_assoc();
		$passwordEncrypt = password_hash($password, PASSWORD_DEFAULT);

		$update = $con->prepare("UPDATE tbl_users SET password = ?, fp_code = '', fp_code_expires = NULL WHERE user_id = ?");
		$update->bind_param("si", $passwordEncrypt, $user['user_id']);
		$update->execute();
		$update->close();

		// Invalidate any remember-me tokens for this account
		$tokens = $con->prepare("DELETE FROM tbl_remember_token WHERE userid_fk = ?");
		$tokens->bind_param("i", $user['user_id']);
		$tokens->execute();
		$tokens->close();

		$data = array(
			"result" => "OK"
		);
	}
	else {
		$data = array(
			"result" => "emailNotExisted"
		);
	}
	$stmt->close();

	echo json_encode($data);
}
else {
	header("Location: 403");
}
?>
