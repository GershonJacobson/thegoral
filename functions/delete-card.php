<?php
if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
	http_response_code(403);
	exit;
}

if(!empty($_POST['cardID'])) {
	require("../config/db.php");
	session_start();
	require("../config/session.php");

	if($getUserID == "") {
		http_response_code(403);
		echo json_encode(["result" => "notLoggedIn"]);
		exit;
	}

	$cardID = intval($_POST['cardID']);

	// The card must belong to the logged-in user
	$stmt = $con->prepare("SELECT card_id FROM tbl_card WHERE card_id = ? AND userid_fk = ? LIMIT 1");
	$stmt->bind_param("ii", $cardID, $getUserID);
	$stmt->execute();

	if($stmt->get_result()->num_rows > 0) {
		$delete = $con->prepare("DELETE FROM tbl_card WHERE card_id = ? AND userid_fk = ?");
		$delete->bind_param("ii", $cardID, $getUserID);
		$delete->execute();
		$delete->close();

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
