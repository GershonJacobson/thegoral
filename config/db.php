<?php
// Set the default timezone for date/time functions
date_default_timezone_set("America/New_York");

// --- Database Credentials ---
$db_host = "localhost";
$db_user = "root";
$db_pass = ""; // Default XAMPP password is an empty string
$db_name = "thegoral"; // Make sure this EXACTLY matches your database name in phpMyAdmin

// --- Create Connection ---
$con = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// --- Check Connection ---
// This is the most important part. If it fails, it will stop everything
// and show a clear error message explaining exactly what is wrong.
if (mysqli_connect_errno()) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>