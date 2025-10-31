<?php
date_default_timezone_set("America/New_York");

$con = mysqli_connect("localhost","thegoral","hJ8*76220-+1","thegoral");

if(mysqli_connect_errno()) {
	header('Location: 404');
}
?>