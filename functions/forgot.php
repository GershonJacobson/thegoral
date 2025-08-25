<?php
if(isset( $_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
	require("../config/db.php");
	require("../config/decrypt.php");
	
	include("../PHPMailer/src/PHPMailer.php");
	include("../PHPMailer/src/SMTP.php");
	include("../PHPMailer/src/Exception.php");

	$email = mysqli_real_escape_string($con, $_POST['email']);
	
	$qChkData = mysqli_query($con, "SELECT * FROM tbl_users WHERE email_address = '" . $email . "'");
	if(mysqli_num_rows($qChkData) > 0) {
		$date_added = date("Y-m-d H:i:s");
		
		$confirmationCode = substr(str_shuffle("0123456789abcdefghijklmnopqrstvwxyz"), 0, 60);
		
		$qUpdate = mysqli_query($con, "UPDATE tbl_users SET fp_code = '" . $confirmationCode . "' WHERE email_address = '" . $email . "'");
		
		define('SMTP_HOST','relay-hosting.secureserver.net');
		define('SMTP_PORT',25);
		define('SMTP_USERNAME','noreply@thegoral.com');
		define('SMTP_PASSWORD','7!mM0*g&21');
		define('SMTP_AUTH',false);

		$firstName = 'The Goral';
		
		$email_template = '../reset-email-template.html';
		$message = file_get_contents($email_template);
		$message = str_replace('%confirmationCode%', $confirmationCode, $message);
		$message = str_replace('%email%', $email, $message);

		$mail = new PHPMailer\PHPMailer\PHPMailer();
		$mail->IsSMTP();                
		$mail->SMTPAuth = false;
		$mail->SMTPAutoTLS = false; 		
		$mail->Host = 'localhost';
		$mail->Port = 25;
		$mail->Username = SMTP_USERNAME;
		$mail->Password = SMTP_PASSWORD;
		$mail->SetFrom(SMTP_USERNAME,'The Goral');
		$mail->AddReplyTo(SMTP_USERNAME,"The Goral");
		$mail->Subject = "Account Recovery - TheGoral";
		$mail->MsgHTML($message);
		$mail->AddAddress($email, 'TheGoral.com');
		$mail->IsHTML(true);
		$mail->Send();
		
		$data = array(
			"result" => "OK"
		);
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