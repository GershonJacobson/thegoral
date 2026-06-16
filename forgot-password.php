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
    <!-- Utility-pages layer (overrides style.css) -->
    <link rel="stylesheet" href="assets/css/site.css" />

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap/js/bootstrap.bundle.min.js"></script>
	<script src="assets/js/sweetalert.min.js"></script>
    <script src="assets/js/forgot-password.js"></script>
    <script src="assets/font/fontawesome/js/all.min.js"></script>
  </head>
  <body class="page-auth">
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
            <a href="sign-up">Create an Account</a>
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
            <a href="/">Home</a>
            <a href="/drawing">Drawing Page</a>
            <a href="/contact">Contact Us</a>
          </div>
        </div>
        <div class="row">
          <div class="text-desc">
            The Goral is a weekly community split-the-pot drawing. One ticket can win the pot &mdash; and a share of every pot goes to tzedakah. Winners are drawn and posted publicly each week.
          </div>
        </div>
        <div class="row">
          <div class="copyright">© <?php echo date('Y'); ?> The Goral</div>
        </div>
      </div>
    </div>
  </body>
</html>
