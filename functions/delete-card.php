<?php
if($_POST['cardID']) {
	require("../config/db.php");
	
	$cardID = mysqli_real_escape_string($con, $_POST['cardID']);
	
	$qChkData = mysqli_query($con, "SELECT * FROM tbl_card WHERE card_id = '" . $cardID . "'");
	if(mysqli_num_rows($qChkData) > 0) {
		$qSave = mysqli_query($con, "DELETE FROM tbl_card WHERE card_id = '" . $cardID . "'");
		
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