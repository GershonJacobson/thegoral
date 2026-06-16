<?php
if(isset( $_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
	require("../config/db.php");
	require("../config/email-config.php");

	include("../PHPMailer/src/PHPMailer.php");
	include("../PHPMailer/src/SMTP.php");
	include("../PHPMailer/src/Exception.php");

	$email = trim(strtolower($_POST['email'] ?? ''));

	if($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
		echo json_encode(["result" => "emailNotFound"]);
		exit;
	}

	$stmt = $con->prepare("SELECT user_id FROM tbl_users WHERE email_address = ? LIMIT 1");
	$stmt->bind_param("s", $email);
	$stmt->execute();
	$result = $stmt->get_result();

	if($result->num_rows > 0) {
		$confirmationCode = bin2hex(random_bytes(30));
		$expiresAt = date("Y-m-d H:i:s", time() + 3600); // valid for 1 hour

		$update = $con->prepare("UPDATE tbl_users SET fp_code = ?, fp_code_expires = ? WHERE email_address = ?");
		$update->bind_param("sss", $confirmationCode, $expiresAt, $email);
		$update->execute();
		$update->close();

		$email_template = __DIR__ . '/../reset-email-template.html';
		$message = file_get_contents($email_template);
		$message = str_replace('%confirmationCode%', $confirmationCode, $message);
		$message = str_replace('%email%', $email, $message);

		$smtpDisabled = defined('GORAL_SMTP_DISABLED') && GORAL_SMTP_DISABLED;

		$mail = new PHPMailer\PHPMailer\PHPMailer();
		$mail->Timeout = 10;
		$mail->IsSMTP();
		$mail->SMTPAuth = SMTP_AUTH;
		$mail->SMTPAutoTLS = false;
		$mail->Host = SMTP_HOST;
		$mail->Port = SMTP_PORT;
		$mail->Username = SMTP_USERNAME;
		$mail->Password = SMTP_PASSWORD;
		$mail->SetFrom(EMAIL_FROM_ADDRESS, EMAIL_FROM_NAME);
		$mail->AddReplyTo(EMAIL_REPLY_TO, EMAIL_FROM_NAME);
		$mail->Subject = "Account Recovery - TheGoral";
		$mail->MsgHTML($message);
		$mail->AddAddress($email, 'TheGoral.com');
		$mail->IsHTML(true);

		if(!$smtpDisabled && !$mail->Send()) {
			error_log("[Forgot Password] Email failed for {$email}: " . $mail->ErrorInfo);
		}

		$data = array(
			"result" => "OK"
		);
	}
	else {
		$data = array(
			"result" => "emailNotFound"
		);
	}
	$stmt->close();

	echo json_encode($data);
}
else {
	header("Location: 403");
}
?>
