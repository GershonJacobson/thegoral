<?php
require("../config/db.php");
session_start();

unset($_SESSION["userGoral"]);
session_destroy();

// Remove the remember-me token (cookie + database row)
if(!empty($_COOKIE['goral_remember'])) {
	$parts = explode(":", $_COOKIE['goral_remember'], 2);

	if(count($parts) === 2) {
		$stmt = $con->prepare("DELETE FROM tbl_remember_token WHERE selector = ?");
		$stmt->bind_param("s", $parts[0]);
		$stmt->execute();
		$stmt->close();
	}

	setcookie("goral_remember", "", [
		'expires' => time() - 3600,
		'path' => '/',
		'secure' => isset($_SERVER['HTTPS']),
		'httponly' => true,
		'samesite' => 'Lax',
	]);
}

// Clear the legacy cookie too, in case a browser still has one
setcookie("cookielogin[user]", "", time() - 3600, "/");

header('location: /');
exit;
?>
