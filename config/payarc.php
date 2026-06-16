<?php
/**
 * PayArc payment gateway client.
 *
 * Architecture (PCI-safe): the card is tokenized in the browser by PayArc's
 * hosted-fields iframe (testportal/portal .payarc.net/js/iframeprocess.js).
 * Raw PAN/CVV never reach this server — we only receive a token and charge it.
 *
 * Credentials (config/db.local.php for local, env vars in production):
 *   GORAL_PAYARC_ENV          'sandbox' | 'production'  (default sandbox)
 *   GORAL_PAYARC_CLIENT_ID    public Client ID (used by the front-end iframe)
 *   GORAL_PAYARC_BEARER       secret API Bearer Token (server only)
 *
 * Sandbox keys come from testdashboard.payarc.net -> API (eye icon).
 * Production keys from dashboard.payarc.net -> API.
 */

if (!defined('GORAL_PAYARC_ENV')) {
	define('GORAL_PAYARC_ENV', getenv('GORAL_PAYARC_ENV') ?: 'sandbox');
}
if (!defined('GORAL_PAYARC_CLIENT_ID')) {
	define('GORAL_PAYARC_CLIENT_ID', getenv('GORAL_PAYARC_CLIENT_ID') ?: '');
}
if (!defined('GORAL_PAYARC_BEARER')) {
	define('GORAL_PAYARC_BEARER', getenv('GORAL_PAYARC_BEARER') ?: '');
}

define('GORAL_PAYARC_API_BASE', GORAL_PAYARC_ENV === 'production'
	? 'https://api.payarc.net'
	: 'https://testapi.payarc.net');
define('GORAL_PAYARC_IFRAME_JS', GORAL_PAYARC_ENV === 'production'
	? 'https://portal.payarc.net/js/iframeprocess.js'
	: 'https://testportal.payarc.net/js/iframeprocess.js');

/**
 * Charge a PayArc token. Amount in cents.
 * @return array ['ok'=>bool,'charge_id'=>?string,'last4'=>?string,'brand'=>?string,'error'=>?string]
 */
function goral_payarc_charge($tokenId, $amountCents, array $meta = []) {
	if (GORAL_PAYARC_BEARER === '') {
		return ['ok' => false, 'error' => 'Payment gateway is not configured.'];
	}
	if ($tokenId === '' || $amountCents <= 0) {
		return ['ok' => false, 'error' => 'Invalid payment request.'];
	}

	// PayArc's REST API expects token_id at the TOP LEVEL (the PHP SDK nests it
	// under "source" but flattens before sending). Confirmed against testapi.
	$payload = [
		'amount'   => (int) $amountCents,
		'currency' => 'usd',
		'token_id' => $tokenId,
	];
	if (!empty($meta['email'])) $payload['email'] = $meta['email'];
	if (!empty($meta['name']))  $payload['name'] = $meta['name'];
	if (!empty($meta['description'])) $payload['description'] = $meta['description'];

	$res = goral_payarc_request('POST', '/v1/charges', $payload);
	if (!$res['ok']) {
		// 'unreachable' = no HTTP response (timeout/network). The charge MAY have
		// gone through at PayArc — the caller must record it for reconciliation
		// rather than treat it as a clean decline.
		return ['ok' => false, 'error' => $res['error'], 'unreachable' => ($res['status'] === 0)];
	}

	$data = $res['body']['data'] ?? $res['body'];
	return [
		'ok'        => true,
		'charge_id' => $data['id'] ?? ($data['object_id'] ?? null),
		'last4'     => $data['last4'] ?? ($data['card']['last4'] ?? null),
		'brand'     => $data['brand'] ?? ($data['card']['brand'] ?? null),
	];
}

/**
 * Update a tbl_charge_log row's status (and optionally its charge id).
 * Best-effort: failures here must never block the customer response.
 */
function goral_charge_log_update($con, $logId, $status, $note = null, $chargeId = null) {
	if (!$logId) return;
	$note = $note !== null ? substr((string) $note, 0, 255) : null;
	if ($chargeId !== null) {
		$stmt = $con->prepare("UPDATE tbl_charge_log SET status = ?, note = ?, payarc_charge_id = ?, updated_at = NOW() WHERE charge_log_id = ?");
		$stmt->bind_param("sssi", $status, $note, $chargeId, $logId);
	} else {
		$stmt = $con->prepare("UPDATE tbl_charge_log SET status = ?, note = ?, updated_at = NOW() WHERE charge_log_id = ?");
		$stmt->bind_param("ssi", $status, $note, $logId);
	}
	if ($stmt) { $stmt->execute(); $stmt->close(); }
}

function goral_payarc_refund($chargeId, $amountCents = null) {
	if (GORAL_PAYARC_BEARER === '' || $chargeId === '') {
		return ['ok' => false, 'error' => 'Refund not configured.'];
	}
	$payload = [];
	if ($amountCents !== null) $payload['amount'] = (int) $amountCents;
	$res = goral_payarc_request('POST', '/v1/charges/' . rawurlencode($chargeId) . '/refunds', $payload);
	return $res['ok'] ? ['ok' => true] : ['ok' => false, 'error' => $res['error']];
}

function goral_payarc_request($method, $path, array $payload = []) {
	$ch = curl_init(GORAL_PAYARC_API_BASE . $path);
	curl_setopt_array($ch, [
		CURLOPT_CUSTOMREQUEST  => $method,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT        => 30,
		CURLOPT_CONNECTTIMEOUT => 10,
		CURLOPT_HTTPHEADER     => [
			'Authorization: Bearer ' . GORAL_PAYARC_BEARER,
			'Accept: application/json',
			'Content-Type: application/x-www-form-urlencoded',
		],
		CURLOPT_POSTFIELDS     => http_build_query($payload),
	]);
	$raw = curl_exec($ch);
	$status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
	$curlErr = curl_error($ch);

	if ($raw === false) {
		error_log("[PayArc] cURL error: " . $curlErr);
		return ['ok' => false, 'status' => 0, 'body' => [], 'error' => 'Could not reach the payment gateway.'];
	}
	$body = json_decode($raw, true);
	if (!is_array($body)) $body = [];
	if ($status >= 200 && $status < 300) {
		return ['ok' => true, 'status' => $status, 'body' => $body, 'error' => null];
	}
	error_log("[PayArc] {$method} {$path} -> {$status}: " . substr($raw, 0, 500));
	return ['ok' => false, 'status' => $status, 'body' => $body, 'error' => $body['message'] ?? ($body['error'] ?? 'Payment failed.')];
}
?>
