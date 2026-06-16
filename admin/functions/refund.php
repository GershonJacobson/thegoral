<?php
/**
 * Admin refund. Actually reverses the money at PayArc, then records it.
 * Previously this only wrote a tbl_refund row and never called the gateway —
 * customers were marked refunded but never got their money back.
 */
if(isset( $_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')) {
	require("../../config/db.php");
	session_start();
	require("../../config/session.php");
	require("../../config/payarc.php");

	if(!in_array((int)$getUserRole, [1, 2], true)) {
		http_response_code(403);
		echo json_encode(["result" => "forbidden"]);
		exit;
	}

	$ticketID = intval($_POST['ticketID'] ?? 0);
	$edtAmount = is_numeric($_POST['edtAmount'] ?? null) ? (float)$_POST['edtAmount'] : null;

	if($ticketID <= 0 || $edtAmount === null || $edtAmount <= 0) {
		echo json_encode(["result" => "invalid"]);
		exit;
	}

	// Need the original charge + the paid amount to refund against.
	$stmt = $con->prepare("SELECT total_price, payarc_charge_id FROM tbl_ticket WHERE ticket_id = ? LIMIT 1");
	$stmt->bind_param("i", $ticketID);
	$stmt->execute();
	$ticket = $stmt->get_result()->fetch_assoc();
	$stmt->close();

	if(!$ticket) {
		echo json_encode(["result" => "notExisted"]);
		exit;
	}

	$chargeId = $ticket['payarc_charge_id'] ?? '';
	$paid = (float) $ticket['total_price'];

	if($chargeId === '' || $chargeId === null) {
		// No gateway charge on record — cannot refund money.
		echo json_encode(["result" => "noCharge", "message" => "This ticket has no recorded payment to refund."]);
		exit;
	}

	// Never refund more than was paid.
	if($edtAmount > $paid) {
		echo json_encode(["result" => "tooMuch", "message" => "Refund cannot exceed the amount paid ($" . number_format($paid, 2) . ")."]);
		exit;
	}

	// Reverse the money at PayArc FIRST. Only record it if the gateway confirms.
	$refund = goral_payarc_refund($chargeId, (int) round($edtAmount * 100));
	if(!$refund['ok']) {
		error_log("[Refund] gateway refund failed ticket={$ticketID} charge={$chargeId}: " . ($refund['error'] ?? '?'));
		echo json_encode(["result" => "gatewayError", "message" => $refund['error'] ?? "The refund could not be processed by the gateway."]);
		exit;
	}

	$date_added = date("Y-m-d H:i:s");
	$stmt = $con->prepare("SELECT refund_id FROM tbl_refund WHERE ticketid_fk = ? LIMIT 1");
	$stmt->bind_param("i", $ticketID);
	$stmt->execute();
	$hasRefund = $stmt->get_result()->num_rows > 0;
	$stmt->close();

	if($hasRefund) {
		$stmt = $con->prepare("UPDATE tbl_refund SET amount = ?, refund_date = ? WHERE ticketid_fk = ?");
		$stmt->bind_param("dsi", $edtAmount, $date_added, $ticketID);
	} else {
		$stmt = $con->prepare("INSERT INTO tbl_refund(ticketid_fk, amount, refund_date) VALUES(?, ?, ?)");
		$stmt->bind_param("ids", $ticketID, $edtAmount, $date_added);
	}
	$stmt->execute();
	$stmt->close();

	echo json_encode(["result" => "OK"]);
}
else {
	header("Location: 403");
}
?>
