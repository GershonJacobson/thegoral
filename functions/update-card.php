<?php
/**
 * Update a saved card.
 *
 * Editing card details requires re-tokenizing through PayArc (raw card data
 * never touches this server). That ships with the wallet/token phase; until
 * then we report unavailable. Removing a card still works (delete-card.php).
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

	echo json_encode([
		"result"  => "notAvailable",
		"message" => "Editing saved cards is coming soon. You can remove a card and add it again at checkout."
	]);
} else {
	header("Location: 403");
}
?>
