<?php
date_default_timezone_set("America/New_York");

$con = mysqli_connect("localhost","root","","thegoral");

if(mysqli_connect_errno()) {
	header('Location: 404');
}
?>