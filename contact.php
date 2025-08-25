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
    <script src="assets/js/contact.js"></script>
    <script src="assets/font/fontawesome/js/all.min.js"></script>
  </head>
  <body style="background: #fff">
    <div class="header-ac-bg">
      <div class="container">
        <nav class="navbar navbar-expand-lg bg-body-tertiary static-top">
          <div class="container-fluid">
            <a class="navbar-brand" href="/">
              <img class="logo" src="assets/images/logo.svg" alt="logo" />
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
          <div class="col-md col-50">
            <input
              type="text"
              placeholder="Name"
              id="fullName"
              autocomplete="off"
            />
			
			<div class="alert fullNameCantBeEmpty" style="display: none; color: red;">Fullname can't be empty!</div>
          </div>
          <div class="col-md col-50">
            <input
              type="text"
              placeholder="Phone"
              id="phoneC"
              autocomplete="off"
            />
          </div>
        </div>
        <div class="row">
          <div class="col-md">
            <input type="email" placeholder="Email" id="emailC" />
			
			<div class="alert emailNotValid" style="display: none; color: red;">Email is not valid!</div>
          </div>
        </div>
        <div class="row">
          <div class="col-md">
            <textarea
              name="textarea"
              id="message"
              cols="30"
              rows="10"
              placeholder="Message"
            ></textarea>
			
			<div class="alert messageCantBeEmpty" style="display: none; color: red;">Message can't be empty!</div>
          </div>
        </div>
        <div class="row">
          <button type="button" class="btnSignup" id="btnSubmit">Submit</button>
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
            <a href="/live-campaign.html">Live Campaigns</a>
            <a href="/all-campaign.html">All Campaigns</a>
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
