<?php
/**
 * Session Management
 * Handles user authentication and session/cookie validation.
 *
 * Defines: $getUserID, $getFirstName, $getLastName, $getEmailAddress,
 *          $getPhone, $getUserRole
 *
 * Remember-me uses a selector:validator token stored hashed in
 * tbl_remember_token. The old cookielogin[user] cookie (which held a raw
 * user id and could be forged) is no longer trusted.
 */

require_once(__DIR__ . "/db.php");

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

// Initialize all variables
$getUserID = "";
$getFirstName = "";
$getLastName = "";
$getEmailAddress = "";
$getPhone = "";
$getUserRole = 0;

$sessionUserID = 0;

if (!empty($_SESSION['userGoral'])) {
	$sessionUserID = (int) $_SESSION['userGoral'];
}

// Fall back to the remember-me cookie when there is no session.
if ($sessionUserID === 0 && !empty($_COOKIE['goral_remember'])) {
	$parts = explode(":", $_COOKIE['goral_remember'], 2);

	if (count($parts) === 2) {
		list($selector, $validator) = $parts;

		$stmt = $con->prepare("SELECT userid_fk, validator_hash, expires_at FROM tbl_remember_token WHERE selector = ? LIMIT 1");
		if ($stmt) {
			$stmt->bind_param("s", $selector);
			$stmt->execute();
			$result = $stmt->get_result();

			if ($result && ($token = $result->fetch_assoc())) {
				$notExpired = strtotime($token['expires_at']) > time();

				if ($notExpired && hash_equals($token['validator_hash'], hash("sha256", $validator))) {
					$sessionUserID = (int) $token['userid_fk'];
					$stmt->close();

					// Promote to an authenticated session with a fresh id
					// (prevents session fixation on the auto-login path).
					session_regenerate_id(true);
					$_SESSION['userGoral'] = $sessionUserID;

					// Rotate the validator so a leaked cookie is single-use.
					$newValidator = bin2hex(random_bytes(32));
					$newHash = hash("sha256", $newValidator);
					$rot = $con->prepare("UPDATE tbl_remember_token SET validator_hash = ? WHERE selector = ?");
					$rot->bind_param("ss", $newHash, $selector);
					$rot->execute();
					$rot->close();

					$secureFlag = (!empty($_SERVER['HTTPS']) || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'));
					setcookie("goral_remember", $selector . ":" . $newValidator, [
						'expires' => strtotime($token['expires_at']),
						'path' => '/',
						'secure' => $secureFlag,
						'httponly' => true,
						'samesite' => 'Lax',
					]);
				} else {
					$stmt->close();
				}
			} else {
				$stmt->close();
			}
		}
	}

	// Whatever happened, clear the cookie if it did not authenticate.
	if ($sessionUserID === 0) {
		setcookie("goral_remember", "", [
			'expires' => time() - 3600,
			'path' => '/',
			'secure' => isset($_SERVER['HTTPS']),
			'httponly' => true,
			'samesite' => 'Lax',
		]);
	}
}

if ($sessionUserID > 0) {
	$stmt = $con->prepare("SELECT user_id, first_name, last_name, email_address, phone, admin
	                       FROM tbl_users
	                       WHERE user_id = ?
	                       LIMIT 1");

	if ($stmt) {
		$stmt->bind_param("i", $sessionUserID);
		$stmt->execute();
		$result = $stmt->get_result();

		if ($result && $result->num_rows > 0) {
			$dSession = $result->fetch_assoc();

			$getUserID = $dSession['user_id'];
			$getFirstName = $dSession['first_name'];
			$getLastName = $dSession['last_name'];
			$getEmailAddress = $dSession['email_address'];
			$getPhone = $dSession['phone'];
			$getUserRole = (int) $dSession['admin'];
		}

		$stmt->close();
	}
}
?>
