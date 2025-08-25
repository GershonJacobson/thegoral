<?php
if(isset( $_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
	session_start();
	require("../config/session.php");
	
	include("../PHPMailer/src/PHPMailer.php");
	include("../PHPMailer/src/SMTP.php");
	include("../PHPMailer/src/Exception.php");

	$campaignID = mysqli_real_escape_string($con, $_POST['campaignID']);
	$firstName = mysqli_real_escape_string($con, $_POST['firstName']);
	$lastname = mysqli_real_escape_string($con, $_POST['lastname']);
	$email = mysqli_real_escape_string($con, $_POST['email']);
	$phone = mysqli_real_escape_string($con, $_POST['phone']);
	$cardHolderName = mysqli_real_escape_string($con, $_POST['cardHolderName']);
	$cardNumber = mysqli_real_escape_string($con, $_POST['cardNumber']);
	$expiry = mysqli_real_escape_string($con, $_POST['expiry']);
	$cvv = mysqli_real_escape_string($con, $_POST['cvv']);
	$zip = mysqli_real_escape_string($con, $_POST['zip']);
	$inputPurchase = mysqli_real_escape_string($con, $_POST['inputPurchase']);
	$inputPrice = mysqli_real_escape_string($con, $_POST['inputPrice']);
	$campaignName = mysqli_real_escape_string($con, $_POST['campaignName']);
	$defaultPaymentMethod = mysqli_real_escape_string($con, $_POST['defaultPaymentMethod']);
	$saveCard = $_POST['saveCard'];
	
	$qChkData = mysqli_query($con, "SELECT * FROM tbl_campaign WHERE campaign_id = '" . $campaignID . "' AND status = 'open'");
	if(mysqli_num_rows($qChkData) > 0) {
		$date_added = date("Y-m-d H:i:s");
		
		$qTicketData = mysqli_query($con, "SELECT * FROM tbl_ticket WHERE campaignid_fk = '" . $campaignID . "' ORDER BY ticket_no DESC LIMIT 1");
		if(mysqli_num_rows($qTicketData) > 0) {
			$dTicketData = mysqli_fetch_array($qTicketData);
			
			$ticketNo = $dTicketData['ticket_no'] + 1;
		}
		else {
			$ticketNo = 1;
		}
		
		$qSave = mysqli_query($con, "INSERT INTO tbl_ticket(ticket_no, campaignid_fk, first_name, last_name, email, phone, card_holder_name, card_number, expiry, cvv, zip, total_ticket, total_price, purchased_by, purchased_date, payment_method) VALUES('" . $ticketNo . "', '" . $campaignID . "', '" . $firstName . "', '" . $lastname . "', '" . $email . "', '" . $phone . "', '" . $cardHolderName . "', '" . $cardNumber . "', '" . $expiry . "', '" . $cvv . "', '" . $zip . "', '" . $inputPurchase . "', '" . $inputPrice . "', '$getUserID', '$date_added', '" . $defaultPaymentMethod . "' )");
		
		if($saveCard == "Y") {
			$qCard = mysqli_query($con, "SELECT * FROM tbl_card WHERE userid_fk = '" . $getUserID . "' AND card_number = '" . $cardNumber . "'");
			if(mysqli_num_rows($qCard) > 0) {}
			else {
				$qInsertCard = mysqli_query($con, "INSERT INTO tbl_card(card_number, card_name, expired, cvv, zip, email_address, userid_fk) VALUES('" . $cardNumber . "', '" . $cardHolderName . "', '" . $expiry . "', '" . $cvv . "', '" . $zip . "', '" . $email . "', '" . $getUserID . "' )");
			}
		}
		
		$qCampaign = mysqli_query($con, "SELECT * FROM tbl_campaign WHERE campaign_id = '" . $campaignID . "'");
		$dCampaign = mysqli_fetch_array($qCampaign);
		
		define('SMTP_HOST','relay-hosting.secureserver.net');
		define('SMTP_PORT',25);
		define('SMTP_USERNAME','noreply@thegoral.com');
		define('SMTP_PASSWORD','7!mM0*g&21');
		define('SMTP_AUTH',false);

		$firstName = 'The Goral';
		
		$email_template = '../purchase-email-template.html';
		$message = file_get_contents($email_template);
		$message = str_replace('%ticketNo%', $ticketNo, $message);
		$message = str_replace('%pageURL%', $dCampaign['page_url'], $message);
		$message = str_replace('%campaignName%', $campaignName, $message);

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
		$mail->Subject = "Your ticket order with The Goral";
		$mail->MsgHTML($message);
		$mail->AddAddress($email, 'TheGoral.com');
		$mail->IsHTML(true);
		$mail->Send();
		
		$data = array(
			"ticketNo" => $ticketNo,
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