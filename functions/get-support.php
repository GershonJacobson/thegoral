<?php
if(isset( $_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
	require("../config/db.php");
	require("../config/decrypt.php");
	
	include("../PHPMailer/src/PHPMailer.php");
	include("../PHPMailer/src/SMTP.php");
	include("../PHPMailer/src/Exception.php");

	$fullName = mysqli_real_escape_string($con, $_POST['fullName']);
	$phone = mysqli_real_escape_string($con, $_POST['phone']);
	$email = mysqli_real_escape_string($con, $_POST['email']);
	$message = mysqli_real_escape_string($con, $_POST['message']);
	
	$date_added = date("Y-m-d H:i:s");
	
	$ticketNo = substr(str_shuffle("0123456789abcdefghijklmnopqrstvwxyz"), 0, 30);
	
	$qSave = mysqli_query($con, "INSERT INTO tbl_support(ticket_no, full_name, phone, email, message, date_added) VALUES('" . $ticketNo . "', '" . $fullName . "', '" . $phone . "', '" . $email . "', '" . $message . "', '$date_added')");
	
	define('SMTP_HOST','relay-hosting.secureserver.net');
	define('SMTP_PORT',25);
	define('SMTP_USERNAME','support@thegoral.com');
	define('SMTP_PASSWORD','kW33#_042M<.');
	define('SMTP_AUTH',false);

	$firstName = 'The Goral';
	
	$email_template = '../support-email-template.html';
	$message = file_get_contents($email_template);
	$message = str_replace('%ticketNo%', $ticketNo, $message);

	$mail = new PHPMailer\PHPMailer\PHPMailer();
	$mail->IsSMTP();                
	$mail->SMTPAuth = false;
	$mail->SMTPAutoTLS = false; 		
	$mail->Host = 'localhost';
	$mail->Port = 25;
	$mail->Username = SMTP_USERNAME;
	$mail->Password = SMTP_PASSWORD;
	$mail->SetFrom(SMTP_USERNAME,'The Goral Support');
	$mail->AddReplyTo(SMTP_USERNAME,"The Goral");
	$mail->Subject = "Your ticket support $ticketNo";
	$mail->MsgHTML($message);
	$mail->AddAddress($email, 'TheGoral.com');
	$mail->IsHTML(true);
	$mail->Send();
	
	$data = array(
		"result" => "OK"
	);
	
	echo json_encode($data);
}
else {
	header("Location: 403");
}
?>