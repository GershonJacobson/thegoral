<?php
error_reporting(0);
session_start();

require("config/session.php");

if($getUserID != "") {
	header("Location: /");
	die();
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Sign in - The Goral</title>
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.svg" />

    <link
      rel="stylesheet"
      href="assets/css/bootstrap/css/bootstrap.min.css"
    />
    <link rel="stylesheet" href="assets/css/style.css" />
	<link rel="stylesheet" href="assets/css/sweetalert.css" />
    <link rel="stylesheet" href="assets/font/fontawesome/css/all.min.css" />

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap/js/bootstrap.bundle.min.js"></script>
	<script src="assets/js/sweetalert.min.js"></script>
    <script src="assets/js/forgot-password.js"></script>
    <script src="assets/font/fontawesome/js/all.min.js"></script>
  </head>
  <body>
    <div class="login-container">
      <div class="signup-logo">
        <a href="/"
          ><img src="assets/images/logo-dark.svg" alt=""
        /></a>
      </div>
    </div>
    <div class="form-signup">
      <div class="row justify-content-center">
        <div class="row">
          <div class="form-title">Forgot Password</div>
        </div>
        <div class="row">
          <div class="form-subtitle">
            New Here?
            <a href="sign-up.html">Create an Account</a>
          </div>
        </div>
        <div class="row">
          <div class="col-md">
            <input type="email" placeholder="Email" id="email" />
          </div>
        </div>
        <div class="row">
          <button type="submit" class="btnSignup" id="btnForgotPassword">
            Submit
          </button>
        </div>
        <div class="row">
          <div class="forgot">
            <a href="sign-in">Back to Login</a>
          </div>
        </div>
      </div>
    </div>
    <div class="footer">
      <div class="container">
        <div class="row">
          <div class="social-media text-center">
            <span><img src="assets/images/logo.svg" alt="" /></span>
            <i class="fa-brands fa-facebook-f"></i>
            <i class="fa-brands fa-twitter"></i>
            <i class="fa-brands fa-linkedin-in"></i>
            <i class="fa-brands fa-instagram"></i>
          </div>
        </div>
        <div class="row">
          <div class="menu-footer">
            <a href="http://thegoral.com">Home</a>
            <a href="live-campaign.html">Live Campaigns</a>
            <a href="all-campaign.html">All Campaigns</a>
          </div>
        </div>
        <div class="row">
          <div class="text-desc">
            Lörem ipsum od ohet dilogi. Bell trabel, samuligt, ohöbel utom
            diska. Jinesade bel när feras redorade i belogi. FAR paratyp <br />
            i muvåning, och pesask vyfisat. Viktiga poddradio har un mad och
            inde.
          </div>
        </div>
        <div class="row">
          <div class="copyright">© 2022 The Goral</div>
        </div>
      </div>
    </div>
  </body>
</html>
