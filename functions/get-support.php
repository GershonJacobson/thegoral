<?php
if(isset( $_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
	require("../config/db.php");
	require("../config/email-config.php");

	include("../PHPMailer/src/PHPMailer.php");
	include("../PHPMailer/src/SMTP.php");
	include("../PHPMailer/src/Exception.php");

	$fullName = trim($_POST['fullName'] ?? '');
	$phone = trim($_POST['phone'] ?? '');
	$email = trim($_POST['email'] ?? '');
	$message = trim($_POST['message'] ?? '');

	if($fullName === '' || $email === '' || $message === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
		echo json_encode(["result" => "missingFields"]);
		exit;
	}

	$date_added = date("Y-m-d H:i:s");
	$ticketNo = substr(str_shuffle("0123456789abcdefghijklmnopqrstvwxyz"), 0, 30);

	$stmt = $con->prepare("INSERT INTO tbl_support(ticket_no, full_name, phone, email, message, date_added) VALUES(?, ?, ?, ?, ?, ?)");
	$stmt->bind_param("ssssss", $ticketNo, $fullName, $phone, $email, $message, $date_added);
	$stmt->execute();
	$stmt->close();

	$email_template = __DIR__ . '/../support-email-template.html';
	$mailBody = file_get_contents($email_template);
	$mailBody = str_replace('%ticketNo%', $ticketNo, $mailBody);

	$smtpDisabled = defined('GORAL_SMTP_DISABLED') && GORAL_SMTP_DISABLED;

	// Support mail goes out from the support@ mailbox (credentials from config/env)
	$mail = new PHPMailer\PHPMailer\PHPMailer();
	$mail->Timeout = 10;
	$mail->IsSMTP();
	$mail->SMTPAuth = SMTP_AUTH;
	$mail->SMTPAutoTLS = false;
	$mail->Host = SMTP_HOST;
	$mail->Port = SMTP_PORT;
	$mail->Username = SUPPORT_SMTP_USERNAME;
	$mail->Password = SUPPORT_SMTP_PASSWORD;
	$mail->SetFrom(SUPPORT_SMTP_USERNAME,'The Goral Support');
	$mail->AddReplyTo(SUPPORT_SMTP_USERNAME,"The Goral");
	$mail->Subject = "Your ticket support $ticketNo";
	$mail->MsgHTML($mailBody);
	$mail->AddAddress($email, 'TheGoral.com');
	$mail->IsHTML(true);

	if(!$smtpDisabled && !$mail->Send()) {
		error_log("[Support] Email failed for {$email}: " . $mail->ErrorInfo);
	}

	$data = array(
		"result" => "OK"
	);

	echo json_encode($data);
}
else {
	header("Location: 403");
}
?>
