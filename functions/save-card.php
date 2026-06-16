<?php
/**
 * Save a card to the wallet.
 *
 * The card is tokenized in the browser by PayArc's hosted fields (wallet.php)
 * — this endpoint only ever receives the token + display info (last4/brand).
 * Raw card numbers/CVV never reach this server.
 *
 * NB: PayArc tokens are single-use, so the stored token is a placeholder for
 * the future vault phase; the saved card is display info for now.
 */
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
	require("../config/db.php");
	session_start();
	require("../config/session.php");

	if($getUserID == "") {
		http_response_code(403);
		echo json_encode(["result" => "notLoggedIn"]);
		exit;
	}

	$userID = (int) $getUserID;
	$cardName = substr(trim($_POST['cardHolderName'] ?? ''), 0, 160);
	$token = substr(trim($_POST['paymentToken'] ?? ''), 0, 120);
	$cardLast4 = substr(preg_replace('/\D/', '', $_POST['cardLast4'] ?? ''), -4);
	$cardBrand = substr(trim($_POST['cardBrand'] ?? ''), 0, 40);

	if($cardName === '' || $token === '' || strlen($cardLast4) !== 4) {
		echo json_encode(["result" => "error", "message" => "Please complete your card details."]);
		exit;
	}

	// One row per card per user (same dedupe as the checkout save).
	$stmt = $con->prepare("SELECT card_id FROM tbl_card WHERE userid_fk = ? AND card_last4 = ? AND card_brand <=> ? LIMIT 1");
	$stmt->bind_param("iss", $userID, $cardLast4, $cardBrand);
	$stmt->execute();

	if($stmt->get_result()->num_rows > 0) {
		$stmt->close();
		echo json_encode(["result" => "duplicate"]);
		exit;
	}
	$stmt->close();

	$zip = '';
	$insert = $con->prepare("INSERT INTO tbl_card
	                         (card_name, card_last4, card_brand, zip, payarc_card_id, email_address, userid_fk)
	                         VALUES (?, ?, ?, ?, ?, ?, ?)");
	$insert->bind_param("ssssssi", $cardName, $cardLast4, $cardBrand, $zip, $token, $getEmailAddress, $userID);

	if($insert->execute()) {
		echo json_encode(["result" => "OK", "id" => $con->insert_id]);
	}
	else {
		error_log("[Save Card] insert failed: " . $insert->error);
		echo json_encode(["result" => "error", "message" => "Could not save the card. Please try again."]);
	}
	$insert->close();
}
else {
	header("HTTP/1.1 403 Forbidden");
	exit;
}
?>
