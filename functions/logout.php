<?php
session_start();

unset($_SESSION["userGoral"]);

$cookiePath = "/";
setcookie("cookielogin[user]","", time()-3600, $cookiePath);
unset ($_COOKIE['cookielogin']);

header('location: /');
?>