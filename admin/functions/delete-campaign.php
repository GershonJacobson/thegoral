<?php
if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
	http_response_code(403);
	exit;
}

if(!empty($_POST['campaignID'])) {
	require("../../config/db.php");
	session_start();
	require("../../config/session.php");

	if(!in_array((int)$getUserRole, [1, 2], true)) {
		http_response_code(403);
		echo json_encode(["result" => "forbidden"]);
		exit;
	}

	$campaignID = trim($_POST['campaignID']);

	$stmt = $con->prepare("SELECT campaign_id FROM tbl_campaign WHERE campaign_id = ? LIMIT 1");
	$stmt->bind_param("s", $campaignID);
	$stmt->execute();

	if($stmt->get_result()->num_rows > 0) {
		$delete = $con->prepare("DELETE FROM tbl_campaign WHERE campaign_id = ?");
		$delete->bind_param("s", $campaignID);
		$delete->execute();
		$delete->close();

		$prices = $con->prepare("DELETE FROM tbl_ticket_price WHERE campaignid_fk = ?");
		$prices->bind_param("s", $campaignID);
		$prices->execute();
		$prices->close();

		$data = array(
			"result" => "OK"
		);
	}
	else {
		$data = array(
			"result" => "notExisted"
		);
	}
	$stmt->close();

	echo json_encode($data);
}
else {
	header("Location: 403");
}
?>
