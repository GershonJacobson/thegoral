<?php
/**
 * Reversible encryption helper.
 *
 * The original used mcrypt (removed in PHP 7.2) with an empty key.
 * Rewritten with openssl AES-256-CBC. Set a strong random key below
 * (or define GORAL_CRYPT_KEY in config/db.local.php) before relying
 * on this for anything sensitive.
 */

if (!defined('GORAL_CRYPT_KEY')) {
	define('GORAL_CRYPT_KEY', '***REMOVED***');
}

function encrypt_decrypt($action, $string)
{
	$output = false;
	$method = 'AES-256-CBC';
	$key = hash('sha256', GORAL_CRYPT_KEY, true);

	if ($action == 'encrypt') {
		$iv = openssl_random_pseudo_bytes(16);
		$encrypted = openssl_encrypt($string, $method, $key, OPENSSL_RAW_DATA, $iv);
		if ($encrypted !== false) {
			$output = base64_encode($iv . $encrypted);
		}
	} else if ($action == 'decrypt') {
		$decoded = base64_decode($string, true);
		if ($decoded !== false && strlen($decoded) > 16) {
			$iv = substr($decoded, 0, 16);
			$ciphertext = substr($decoded, 16);
			$output = openssl_decrypt($ciphertext, $method, $key, OPENSSL_RAW_DATA, $iv);
		}
	}

	return $output;
}
?>
