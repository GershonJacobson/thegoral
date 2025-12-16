<?php
date_default_timezone_set("America/New_York");
$con = mysqli_connect("localhost:8889","root","root","thegoral");
if(mysqli_connect_errno()) {
die("Connection failed: " . mysqli_connect_error());
}
?>