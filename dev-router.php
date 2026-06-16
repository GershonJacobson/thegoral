<?php
/**
 * Development router for the PHP built-in server (NOT used in production -
 * Apache handles this via .htaccess on the live host).
 *
 *   php -S 127.0.0.1:8090 dev-router.php
 *
 * Apache runs each script with the script's own directory as cwd and at
 * global scope; the codebase relies on both (relative require("../config/...")
 * calls and `global` inside functions), so the dispatch below must stay at
 * file scope.
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = __DIR__ . $uri;
$trimmed = rtrim($uri, '/');

// Mirror the production .htaccess blocks (the built-in server ignores
// .htaccess, so without this a public tunnel would serve /.git, the DB
// schema, etc. as plain files).
if (preg_match('#^/(config|database|logs|PHPMailer|design-mockups)(/|$)#', $uri)
	|| preg_match('#/\.#', $uri) // any dot-file or dot-dir (.git, .htaccess, .DS_Store, ...)
	|| preg_match('/\.(sql|log|md|sh|ini|yml|yaml|lock|env)$/i', $uri)
	|| preg_match('#/(dev-router\.php|db\.local\.php)$#', $uri)) {
	http_response_code(404);
	exit;
}

$goral_script = null;

if ($uri !== '/' && preg_match('/\.php$/', $uri) && file_exists($path) && !is_dir($path)) {
	$goral_script = $path;
}
else if ($uri !== '/' && file_exists($path) && !is_dir($path)) {
	return false; // static asset - let the server handle it
}
else if ($trimmed !== '' && file_exists(__DIR__ . $trimmed . '.php')) {
	$goral_script = __DIR__ . $trimmed . '.php';
}
else if ($trimmed !== '' && is_dir(__DIR__ . $trimmed) && file_exists(__DIR__ . $trimmed . '/index.php')) {
	// Match Apache's DirectorySlash: /admin -> /admin/ so the page's
	// relative asset paths (css/..., js/...) resolve inside the directory.
	if (substr($uri, -1) !== '/') {
		header('Location: ' . $trimmed . '/', true, 301);
		exit;
	}
	$goral_script = __DIR__ . $trimmed . '/index.php';
}
else if ($uri === '/' || $uri === '/index.php') {
	$goral_script = __DIR__ . '/index.php';
}
else {
	$_GET['campaign'] = ltrim($trimmed, '/');
	$goral_script = __DIR__ . '/campaign.php';
}

chdir(dirname($goral_script));
require $goral_script;
return true;
?>
