<?php
/**
 * Participants of one campaign, grouped per person (by email),
 * alphabetical, with every purchase they made (when, how many
 * tickets, ticket number, amount). Admin only.
 */
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
	session_start();
	require("../../config/session.php");

	if((int)$getUserRole === 0) {
		http_response_code(403);
		echo json_encode(["result" => "forbidden"]);
		exit;
	}

	$campaignID = trim($_POST['campaignID'] ?? '');

	$stmt = $con->prepare("
		SELECT first_name, last_name, email, phone, ticket_no, total_ticket, total_price,
		       DATE_FORMAT(purchased_date, '%m/%d/%Y %H:%i') AS purchased_at
		FROM tbl_ticket
		WHERE campaignid_fk = ? AND ticket_no IS NOT NULL
		ORDER BY first_name ASC, last_name ASC, purchased_date ASC
	");
	$stmt->bind_param("s", $campaignID);
	$stmt->execute();
	$result = $stmt->get_result();

	$participants = [];
	while($row = $result->fetch_assoc()) {
		$key = strtolower(trim($row['email'] ?? ''));
		if($key === '') { $key = 'no-email-' . $row['ticket_no']; }

		if(!isset($participants[$key])) {
			$participants[$key] = [
				"firstName" => $row['first_name'],
				"lastName"  => $row['last_name'],
				"email"     => $row['email'],
				"phone"     => $row['phone'],
				"tickets"   => 0,
				"spent"     => 0,
				"purchases" => []
			];
		}

		$participants[$key]["tickets"] += (int)$row['total_ticket'];
		$participants[$key]["spent"] += (float)$row['total_price'];
		$participants[$key]["purchases"][] = [
			"ticketNo" => (int)$row['ticket_no'],
			"qty"      => (int)$row['total_ticket'],
			"price"    => number_format((float)$row['total_price'], 2),
			"at"       => $row['purchased_at']
		];
	}
	$stmt->close();

	echo json_encode(["result" => "OK", "participants" => array_values($participants)]);
}
else {
	header("HTTP/1.1 403 Forbidden");
	exit;
}
?>
