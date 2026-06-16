<?php
// Initialize variables to prevent undefined warnings
$color = isset($color) ? $color : '';
$addActiveHM = isset($addActiveHM) ? $addActiveHM : '';
$addActiveDr = isset($addActiveDr) ? $addActiveDr : '';
$addActiveCt = isset($addActiveCt) ? $addActiveCt : '';
?>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<link rel="stylesheet" href="assets/css/sweetalert.css" />
<script src="assets/js/sweetalert.min.js"></script>

<?php
$link = $_SERVER['PHP_SELF'];
$link_array = explode('/',$link);
$page = basename($link,".php");

if($page == "user-dashboard") {
	$collapse = "collapsed";
	$activeD = "active";
	$activeW = "";
}
else if($page == "wallet") {
	$collapse = "collapsed";
	$activeD = "";
	$activeW = "active";
}
else {
	$collapse = "collapse";
	$activeD = "";
	$activeW = "";
}
?>

<div class="collapse navbar-collapse justify-content-center" id="navbarNav" style="width: 100%;">
	<ul class="navbar-nav">
		<li class="nav-item">
			<?php
			if($page == "index") {
				$addActiveHM = "active";
			}
			?>

			<a class="nav-link <?php echo $addActiveHM; ?>" href="/" <?php echo $color; ?>>Home</a>
		</li>

		<li class="nav-item">
			<?php
			if($page == "drawing") {
				$addActiveDr = "active";
			}
			?>
			<a class="nav-link <?php echo $addActiveDr; ?>" href="drawing" <?php echo $color; ?>>Drawing Page</a>
		</li>

		<li class="nav-item">
			<?php
			if($page == "contact") {
				$addActiveCt = "active";
			}
			?>
			<a class="nav-link <?php echo $addActiveCt; ?>" href="contact" <?php echo $color; ?>>Contact Us</a>
		</li>

		<?php
		if($getUserID != "") {
		?>
			<li class="nav-item logout">
				<a class="nav-link collapsed"
            data-bs-toggle="collapse"
            data-bs-target="#collapseProfile"
            aria-controls="collapseProfile"
            aria-expanded="false"
            aria-label="Toggle navigation" <?php echo $color; ?>>My Profile</a>

				<div class="<?php echo $collapse; ?>" id="collapseProfile">
					<ul>
						<li><a class="nav-link <?php echo $activeD; ?>" href="user-dashboard" >Dashboard</a></li>
						<li><a class="nav-link <?php echo $activeW; ?>" href="wallet" <?php echo $color; ?>>Wallet</a></li>
					</ul>
				</div>
			</li>
		<?php
		}

		if($getUserRole != 0) {
		?>
			<li class="nav-item">
				<a class="nav-link" href="admin" <?php echo $color; ?>>Admin Page</a>
			</li>
		<?php
		}

		if($getUserID != "") {
		?>
			<li class="nav-item logout">
				<a class="nav-link logout-btn" href="functions/logout" style="color: #ffffff;">Logout</a>
			</li>
		<?php
		}

		if($getUserID != "") {
		}
		else {
		?>
			<li style="padding-left: 15px; padding-right: 15px;">
				<div class="box-hide">
					<a class="nav-link box-hide menu-hide" href="sign-up">Sign Up</a>
				</div>
			</li>
			<li style="padding-left: 15px; padding-right: 15px;">
				<div class="box-hide-x">
					<a class="nav-link menu-hide" href="sign-in">Login</a>
				</div>
			</li>
		<?php
		}
		?>
	</ul>
</div>

<div class="collapse navbar-collapse justify-content-end">
	<ul class="navbar-nav">
		<li>
			<div class="dropdown">
				<?php
				$goralInitial = "";
				if($getUserID != "") {
					$goralInitial = strtoupper(function_exists('mb_substr') ? mb_substr($getFirstName, 0, 1, 'UTF-8') : substr($getFirstName, 0, 1));
					$goralInitial = htmlspecialchars($goralInitial, ENT_QUOTES, 'UTF-8');
				}
				?>
				<a class="avatar-trigger" aria-expanded="false" data-bs-toggle="dropdown" href="#" role="button">
					<span class="avatar-chip"><?php echo $getUserID != "" ? $goralInitial : '<i class="fa-solid fa-user"></i>'; ?></span>
				</a>
				<ul class="dropdown-menu dropdown-menu-end goral-menu">
					<?php
					if($getUserID != "") {
					?>
						<li class="goral-menu-head">
							<span class="avatar-chip avatar-chip-sm"><?php echo $goralInitial; ?></span>
							<div class="goral-menu-id">
								<span class="goral-menu-name"><?php echo htmlspecialchars(trim($getFirstName . " " . $getLastName), ENT_QUOTES, 'UTF-8'); ?></span>
								<span class="goral-menu-mail"><?php echo htmlspecialchars($getEmailAddress, ENT_QUOTES, 'UTF-8'); ?></span>
							</div>
						</li>
						<li><hr class="dropdown-divider"></li>
						<li><a class="dropdown-item" href="user-dashboard"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
						<li><a class="dropdown-item" href="wallet"><i class="fa-solid fa-credit-card"></i> Wallet</a></li>
						<li><hr class="dropdown-divider"></li>
						<li><a class="dropdown-item goral-menu-logout" href="functions/logout"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a></li>
					<?php
					}
					else {
					?>
						<li><a class="dropdown-item" href="sign-in"><i class="fa-solid fa-arrow-right-to-bracket"></i> Login</a></li>
						<li><a class="dropdown-item" href="sign-up"><i class="fa-solid fa-user-plus"></i> Sign Up</a></li>
					<?php
					}
					?>
				</ul>
			</div>
		</li>
	</ul>
</div>
