<?php
/**
 * Database connection.
 *
 * Production credentials live below. For local development create a
 * config/db.local.php that defines GORAL_DB_* constants before this
 * file falls through to the defaults (db.local.php is gitignored).
 */

date_default_timezone_set("America/New_York");

/*
 * PHP 8.1+ defaults mysqli to THROW exceptions on error. This codebase is
 * written for the classic "return false / check the return value" contract
 * (e.g. `if (!$stmt->execute())`, and the duplicate-key retry in buy-ticket).
 * Force that contract so error handling behaves as the code expects.
 */
mysqli_report(MYSQLI_REPORT_OFF);

if (file_exists(__DIR__ . "/db.local.php")) {
	require_once(__DIR__ . "/db.local.php");
}

// Production defaults (cPanel hosting) - edit these when deploying.
if (!defined("GORAL_DB_HOST")) define("GORAL_DB_HOST", "localhost");
if (!defined("GORAL_DB_USER")) define("GORAL_DB_USER", "root");
if (!defined("GORAL_DB_PASS")) define("GORAL_DB_PASS", "");
if (!defined("GORAL_DB_NAME")) define("GORAL_DB_NAME", "thegoral");
if (!defined("GORAL_DB_PORT")) define("GORAL_DB_PORT", 3306);

if (!isset($con) || !($con instanceof mysqli)) {
	$con = mysqli_connect(GORAL_DB_HOST, GORAL_DB_USER, GORAL_DB_PASS, GORAL_DB_NAME, GORAL_DB_PORT);

	if (mysqli_connect_errno()) {
		error_log("[DB] Connection failed: " . mysqli_connect_error());
		die("Database connection failed. Please try again later.");
	}

	mysqli_set_charset($con, "utf8mb4");
}
?>
