<?php
require("db.php");

if(!empty($_SESSION['userGoral']) || !empty($_COOKIE['cookielogin']['user'])) {
	$userSession = $_SESSION['userGoral'];
	$userCookie = $_COOKIE['cookielogin']['user'];

	$qSession = mysqli_query($con, "SELECT * FROM tbl_users WHERE user_id = '" . $userSession . "' OR user_id = '" . $userCookie . "'");
	$dSession = mysqli_fetch_array($qSession);
	
	$getUserID = $dSession['user_id'];
	$getFirstName = $dSession['first_name'];
	$getLastName = $dSession['last_name'];
	$getEmailAddress = $dSession['email_address'];
	$getPhone = $dSession['phone'];
	$getUserRole = $dSession['admin'];
}
?>