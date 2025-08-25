<?php
	session_start();
	require("../../config/session.php");

	$email = $_POST['email'];
	
	$list = [];
	
	if($email != "") {
		$qSearch = mysqli_query($con, "
			SELECT ticket_id, DATE_FORMAT(purchased_date,'%m/%d/%Y') AS purchased_date, total_price, card_number, payment_method FROM tbl_ticket WHERE purchased_by = 0 AND email = '$email' ORDER BY purchased_date DESC
		");
		while($dSearch = mysqli_fetch_array($qSearch)) {
			$ticketID = $dSearch['ticket_id'];
			
			$qRefund = mysqli_query($con, "SELECT * FROM tbl_refund WHERE ticketid_fk = '$ticketID'");
			$dRefund = mysqli_fetch_array($qRefund);
			$totalRefund = $dRefund['amount'];
			
			array_push($list, (object)[
				"ticketID" => $dSearch['ticket_id'],
				"purchased_date" => $dSearch['purchased_date'],
				"total_price" => $dSearch['total_price'],
				"card_number" => $dSearch['card_number'],
				"payment_method" => $dSearch['payment_method'],
				"totalRefund" => $totalRefund,
				"userRole" => $getUserRole
			]);
		}
		
		echo json_encode($list);
	}
	else {
		header("Location: 403");
	}
?>