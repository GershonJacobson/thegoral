<?php
if(isset( $_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
	session_start();
	require("../../config/session.php");

	if(!in_array((int)$getUserRole, [1, 2], true)) {
		http_response_code(403);
		echo json_encode(["result" => "forbidden"]);
		exit;
	}

	$campaignID = trim($_POST['campaignID'] ?? '');
	$campaignName = trim($_POST['campaignName'] ?? '');
	$category = trim($_POST['category'] ?? '');
	$ticketPrice1 = is_numeric($_POST['ticketPrice1'] ?? null) ? (float)$_POST['ticketPrice1'] : 2.0;
	$ticketPrice2 = is_numeric($_POST['ticketPrice2'] ?? null) ? (float)$_POST['ticketPrice2'] : 3.0;

	// The campaign already started — its start date is history, never editable.
	$endTs = strtotime($_POST['endDate'] ?? '');

	if($endTs === false) {
		echo json_encode(["result" => "badEndDate"]);
		exit;
	}
	if($endTs <= time()) {
		echo json_encode(["result" => "endInPast"]);
		exit;
	}
	$endDateC = date("Y-m-d H:i", $endTs);

	$publicOrPrivate = (($_POST['publicOrPrivate'] ?? '') === "true") ? 1 : 0;

	$stmt = $con->prepare("SELECT campaign_id FROM tbl_campaign WHERE campaign_id = ? LIMIT 1");
	$stmt->bind_param("s", $campaignID);
	$stmt->execute();
	$exists = $stmt->get_result()->num_rows > 0;
	$stmt->close();

	if($exists) {
		// Weekly campaigns keep their public flag; others can toggle it.
		if($category == "weekly") {
			$stmt = $con->prepare("UPDATE tbl_campaign SET end_date = ?, campaign_name = ? WHERE campaign_id = ?");
			$stmt->bind_param("sss", $endDateC, $campaignName, $campaignID);
		}
		else {
			$stmt = $con->prepare("UPDATE tbl_campaign SET end_date = ?, campaign_name = ?, public = ? WHERE campaign_id = ?");
			$stmt->bind_param("ssis", $endDateC, $campaignName, $publicOrPrivate, $campaignID);
		}
		$stmt->execute();
		$stmt->close();

		// Upsert ticket prices (note the backticked column names — they start with a digit)
		$stmt = $con->prepare("SELECT ticket_price_id FROM tbl_ticket_price WHERE campaignid_fk = ? LIMIT 1");
		$stmt->bind_param("s", $campaignID);
		$stmt->execute();
		$hasPrice = $stmt->get_result()->num_rows > 0;
		$stmt->close();

		if($hasPrice) {
			$stmt = $con->prepare("UPDATE tbl_ticket_price SET `1ticket_price` = ?, `2ticket_price` = ? WHERE campaignid_fk = ?");
			$stmt->bind_param("dds", $ticketPrice1, $ticketPrice2, $campaignID);
		}
		else {
			$stmt = $con->prepare("INSERT INTO tbl_ticket_price(campaignid_fk, `1ticket_price`, `2ticket_price`) VALUES(?, ?, ?)");
			$stmt->bind_param("sdd", $campaignID, $ticketPrice1, $ticketPrice2);
		}
		$stmt->execute();
		$stmt->close();

		$data = array(
			"result" => "OK",
			"ticketPrice1" => $ticketPrice1,
			"ticketPrice2" => $ticketPrice2
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
