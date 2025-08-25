<?php
session_start();

require("config/session.php");

if($getUserID == "") {
	header("Location: /");
	die();
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Campaign - The Goral</title>
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.svg" />

    <link
      rel="stylesheet"
      href="assets/css/bootstrap/css/bootstrap.min.css"
    />
    <link rel="stylesheet" href="assets/css/style.css" />
    <link rel="stylesheet" href="assets/font/fontawesome/css/all.min.css" />

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/index.js"></script>
    <script src="assets/font/fontawesome/js/all.min.js"></script>
  </head>
  <body>
    <div class="section-one">
      <div class="container">
        <nav class="navbar navbar-expand-lg bg-body-tertiary static-top">
          <div class="container-fluid">
            <a class="navbar-brand" href="/">
              <img class="logo" src="assets/images/logo.png" alt="logo" />
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

        <div class="row">
          <div class="weeks-pot">Weeks Pot</div>
        </div>
        <div class="row">
          <h1>$109,054.00</h1>
        </div>
        <div style="border: 0.568125px solid #707070"></div>
        <div class="row">
          <div class="col-md">
            <h3>Low risk, high <br />reward</h3>
            <br />
            <div class="box-cd"></div>
          </div>
          <div class="col-md" style="margin-bottom: 70px">
            <div class="illus-1">
              <img src="assets/images/illu-1.png" alt="" />
            </div>
          </div>
        </div>
        <div class="row">
          <button type="submit" class="btnBuyNow" id="btnBuyNow">
            Buy Now
          </button>
        </div>
      </div>
    </div>
    <div class="banner">
      <img src="assets/images/banner-1.png" alt="" />
    </div>
    <div class="content-mc">
      <div class="container">
        <div class="row">
          <div class="col-md">
            <div class="card-mc">
              <span class="mc-right">3 Ticket</span>
              <span class="mc-left"
                >Diego Carlos
                <p>20 Min Ago</p>
              </span>
              <br />
            </div>
          </div>
          <div class="col-md">
            <div class="card-mc">
              <span class="mc-right">3 Ticket</span>
              <span class="mc-left"
                >Diego Carlos
                <p>20 Min Ago</p>
              </span>
              <br />
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md">
            <div class="card-mc">
              <span class="mc-right">3 Ticket</span>
              <span class="mc-left"
                >Diego Carlos
                <p>20 Min Ago</p>
              </span>
              <br />
            </div>
          </div>
          <div class="col-md">
            <div class="card-mc">
              <span class="mc-right">3 Ticket</span>
              <span class="mc-left"
                >Diego Carlos
                <p>20 Min Ago</p>
              </span>
              <br />
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md">
            <div class="card-mc">
              <span class="mc-right">3 Ticket</span>
              <span class="mc-left"
                >Diego Carlos
                <p>20 Min Ago</p>
              </span>
              <br />
            </div>
          </div>
          <div class="col-md">
            <div class="card-mc">
              <span class="mc-right">3 Ticket</span>
              <span class="mc-left"
                >Diego Carlos
                <p>20 Min Ago</p>
              </span>
              <br />
            </div>
          </div>
        </div>
        <div class="text-center">
          <div class="title">Make your own campaign</div>
          <button class="btn-get-started">Get Started</button>
        </div>
      </div>
    </div>
    <div class="footer">
      <div class="container">
        <div class="row">
          <div class="social-media text-center">
            <span><img src="assets/images/logo.png" alt="" /></span>
            <i class="fa-brands fa-facebook-f"></i>
            <i class="fa-brands fa-twitter"></i>
            <i class="fa-brands fa-linkedin-in"></i>
            <i class="fa-brands fa-instagram"></i>
          </div>
        </div>
        <div class="row">
          <div class="menu-footer">
            <a href="my-campaign">Home</a>
            <a href="live-campaign">Live Campaigns</a>
            <a href="all-campaign">All Campaigns</a>
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
