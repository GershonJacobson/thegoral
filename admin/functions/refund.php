<?php
if(isset( $_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
	session_start();
	require("../../config/session.php");

	$ticketID = mysqli_real_escape_string($con, $_POST['ticketID']);
	$edtAmount = mysqli_real_escape_string($con, $_POST['edtAmount']);
	
	$qChk = mysqli_query($con, "SELECT * FROM tbl_ticket WHERE ticket_id = '" . $ticketID . "'");
	if(mysqli_num_rows($qChk) > 0) {
		$date_added = date("Y-m-d H:i:s");
		
		$qChk2 = mysqli_query($con, "SELECT * FROM tbl_refund WHERE ticketid_fk = '" . $ticketID . "'");
		if(mysqli_num_rows($qChk2) > 0) {
			$qUpdate = mysqli_query($con, "
				UPDATE tbl_refund SET amount = '" . $edtAmount . "', refund_date = '$date_added' WHERE ticketid_fk = '" . $ticketID . "'
			");
		}
		else {
			$qInsert = mysqli_query($con, "
				INSERT INTO tbl_refund(ticketid_fk, amount, refund_date) VALUES('" . $ticketID . "', '" . $edtAmount . "', '" . $date_added . "')
			");
		}
			
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