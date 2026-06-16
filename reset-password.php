<?php
if(!empty($_GET['confirmationCode'])) {
	require("config/db.php");

	$confirmationCode = trim($_GET['confirmationCode']);

	$stmt = $con->prepare("SELECT email_address FROM tbl_users WHERE fp_code = ? AND fp_code != '' AND fp_code_expires IS NOT NULL AND fp_code_expires > NOW() LIMIT 1");
	$stmt->bind_param("s", $confirmationCode);
	$stmt->execute();
	$qChkData = $stmt->get_result();
	if($qChkData->num_rows > 0) {
	$dData = $qChkData->fetch_assoc();
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
				<!-- Utility-pages layer (overrides style.css) -->
				<link rel="stylesheet" href="../assets/css/site.css" />

				<script src="../assets/js/jquery.min.js"></script>
				<script src="../assets/js/bootstrap/js/bootstrap.bundle.min.js"></script>
				<script src="assets/js/sweetalert.min.js"></script>
				<script src="../assets/js/reset-password.js"></script>
				<script src="../assets/font/fontawesome/js/all.min.js"></script>
			  </head>
			  <body class="page-auth">
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
						<input type="text" placeholder="Email" id="email" value="<?php echo htmlspecialchars($getEmailAddress, ENT_QUOTES, 'UTF-8'); ?>" readonly/>
						<input type="hidden" id="fpCode" value="<?php echo htmlspecialchars($confirmationCode, ENT_QUOTES, 'UTF-8'); ?>"/>
					  </div>
					</div>
					<div class="row">
					  <div class="col-md">
						<input type="password" placeholder="Password" id="password" />
						<div class="alert minPassword" style="display: none;">Password must be at least 6 characters.</div>
					  </div>
					</div>
					<div class="row">
					  <div class="col-md">
						<input
						  type="password"
						  placeholder="Confirm Password"
						  id="confirmPassword"
						/>
						
						<div class="alert mustSamePassword" style="display: none;">Passwords don&rsquo;t match.</div>
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