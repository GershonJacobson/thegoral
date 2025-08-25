<?php
if($_POST['campaignID']) {
	require("../config/db.php");
	
	$campaignID = mysqli_real_escape_string($con, $_POST['campaignID']);
	
	$qChkData = mysqli_query($con, "SELECT * FROM tbl_campaign WHERE campaign_id = '" . $campaignID . "'");
	if(mysqli_num_rows($qChkData) > 0) {
		$data = mysqli_fetch_array($qChkData);
		
		$status = $data['status'];
		
		if($status == "open") {
			$data = array(
				"result" => "stillOpen"
			);
		}
		else {
			$qSave = mysqli_query($con, "DELETE FROM tbl_campaign WHERE campaign_id = '" . $campaignID . "'");
		
			$data = array(
				"result" => "OK"
			);
		}
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