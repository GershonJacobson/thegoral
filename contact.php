<?php
session_start();

require("config/session.php");
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
	<meta name="robots" content="index, follow">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Contact Us - The Goral</title>
    <link rel="icon" type="image/x-icon" href="../assets/images/favicon.svg" />

    <link
      rel="stylesheet"
      href="../assets/css/bootstrap/css/bootstrap.min.css"
    />
    <link rel="stylesheet" href="../assets/css/style.css" />
	<link rel="stylesheet" href="assets/css/sweetalert.css" />
    <link rel="stylesheet" href="../assets/font/fontawesome/css/all.min.css" />
    <!-- Utility-pages layer (overrides style.css) -->
    <link rel="stylesheet" href="../assets/css/site.css" />

    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap/js/bootstrap.bundle.min.js"></script>
	<script src="assets/js/sweetalert.min.js"></script>
    <script src="../assets/js/contact.js"></script>
    <script src="../assets/font/fontawesome/js/all.min.js"></script>
  </head>
  <body class="page-form">
    <div class="header-ac-bg">
      <div class="container">
        <nav class="navbar navbar-expand-lg bg-body-tertiary static-top">
          <div class="container-fluid">
            <a class="navbar-brand" href="/">
              <img class="logo" src="../assets/images/logo.svg" alt="logo" />
            </a>
            <button
              class="navbar-toggler collapsed"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#navbarNav"
              aria-controls="navbarNav"
              aria-expanded="false"
              aria-label="Toggle navigation"
            >
              <span class="icon-bar top-bar"></span>
              <span class="icon-bar middle-bar"></span>
              <span class="icon-bar bottom-bar"></span>
            </button>
            
			<?php require("header.php"); ?>
          </div>
        </nav>
      </div>
    </div>
    <div class="form-signup">
      <div class="row justify-content-center">
        <div class="row">
          <div class="form-title">Contact Us</div>
        </div>
        <div class="row">
          <div class="form-subtitle">Questions about the pot, a ticket, or a payment? Send us a message &mdash; we usually reply within a day.</div>
        </div>
        <div class="row">
          <div class="col-md col-50">
            <input
              type="text"
              placeholder="Your name"
              id="fullName"
              autocomplete="off"
            />

			<div class="alert fullNameCantBeEmpty" style="display: none;">Please enter your name.</div>
          </div>
          <div class="col-md col-50">
            <input
              type="text"
              placeholder="Phone (optional)"
              id="phoneC"
              autocomplete="off"
            />
          </div>
        </div>
        <div class="row">
          <div class="col-md">
            <input type="email" placeholder="you@example.com" id="emailC" />

			<div class="alert emailNotValid" style="display: none;">Please enter a valid email address.</div>
          </div>
        </div>
        <div class="row">
          <div class="col-md">
            <textarea
              name="textarea"
              id="message"
              cols="30"
              rows="7"
              placeholder="How can we help?"
            ></textarea>

			<div class="alert messageCantBeEmpty" style="display: none;">Please enter a message.</div>
          </div>
        </div>
        <div class="row">
          <button type="button" class="btnSignup" id="btnSubmit">Send Message</button>
        </div>
        <div class="row">
          <div class="contact-alt">or email us directly at <a href="mailto:support@thegoral.com">support@thegoral.com</a></div>
        </div>
      </div>
    </div>
    <div class="footer">
      <div class="container">
        <div class="row">
          <div class="social-media text-center">
            <span><img src="../assets/images/logo.svg" alt="" /></span>
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
            <a class="active" href="/contact">Contact Us</a>
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
