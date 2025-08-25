<?php
if($_GET['confirmationCode']) {
	require("config/db.php");
	
	$confirmationCode = mysqli_real_escape_string($con, $_GET['confirmationCode']);
	
	$qChkData = mysqli_query($con, "SELECT * FROM tbl_users WHERE fp_code = '" . $confirmationCode . "'");
	if(mysqli_num_rows($qChkData) > 0) {
	$dData = mysqli_fetch_array($qChkData);
	$getEmailAddress = $dData['email_address'];
	?>
		<!DOCTYPE html>
			<html lang="en">
			  <head>
				<meta charset="UTF-8" />
				<meta name="viewport" content="width=device-width, initial-scale=1.0" />
				<title>Sign in - The Goral</title>
				<link rel="icon" type="image/x-icon" href="../assets/images/favicon.svg" />

				<link
				  rel="stylesheet"
				  href="../assets/css/bootstrap/css/bootstrap.min.css"
				/>
				<link rel="stylesheet" href="../assets/css/style.css" />
				<link rel="stylesheet" href="assets/css/sweetalert.css" />
				<link rel="stylesheet" href="../assets/font/fontawesome/css/all.min.css" />

				<script src="../assets/js/jquery.min.js"></script>
				<script src="../assets/js/bootstrap/js/bootstrap.bundle.min.js"></script>
				<script src="assets/js/sweetalert.min.js"></script>
				<script src="../assets/js/reset-password.js"></script>
				<script src="../assets/font/fontawesome/js/all.min.js"></script>
			  </head>
			  <body>
				<div class="login-container">
				  <div class="signup-logo">
					<a href="/"
					  ><img src="../assets/images/logo-dark.svg" alt=""
					/></a>
				  </div>
				</div>
				<div class="form-signup">
				  <div class="row justify-content-center">
					<div class="row">
					  <div class="form-title">Reset Password</div>
					</div>
					<div class="row">
					  <div class="col-md">
						<input type="text" placeholder="Emai" id="email" value="<?php echo $getEmailAddress; ?>" readonly/>
					  </div>
					</div>
					<div class="row">
					  <div class="col-md">
						<input type="password" placeholder="Password" id="password" />
						<div class="alert minPassword" style="display: none; color: red;">Minimal password length is 7 characters!</div>
					  </div>
					</div>
					<div class="row">
					  <div class="col-md">
						<input
						  type="password"
						  placeholder="Confirm Password"
						  id="confirmPassword"
						/>
						
						<div class="alert mustSamePassword" style="display: none; color: red;">Password and Confirm password must be same!</div>
					  </div>
					</div>
					<div class="row">
					  <button type="submit" class="btnSignup" id="btnResetPassword">
						Reset Password
					  </button>
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
	<?php
	}
	else {
		header("Location: 403");
	}
}
else {
	header("Location: 403");
}
?>