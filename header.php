<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="assets/css/sweetalert.min.css">
<link rel="stylesheet" href="https://unpkg.com/gijgo@1.9.14/css/gijgo.min.css">

<script src="https://unpkg.com/gijgo@1.9.14/js/gijgo.min.js" defer></script>
<script src="assets/js/sweetalert.min.js" defer></script>

<?php
// ✅ Initialize session variables safely
$getUserID       = $getUserID      ?? '';
$getUserRole     = $getUserRole     ?? '';
$getFirstName    = $getFirstName    ?? '';
$getLastName     = $getLastName     ?? '';
$getEmailAddress = $getEmailAddress ?? '';
$getPhone        = $getPhone        ?? '';
$color           = $color           ?? '';

// ✅ Detect current page
$page = basename($_SERVER['PHP_SELF'], ".php");

// ✅ Helper: active state
function isActive($target, $page) {
    return $target === $page ? 'active' : '';
}

// ✅ Collapse defaults
$collapse = in_array($page, ['user-dashboard', 'wallet']) ? "collapsed" : "collapse";
$activeD  = $page === "user-dashboard" ? "active" : "";
$activeW  = $page === "wallet" ? "active" : "";
?>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const currentDate = "<?php echo date('Y-m-d'); ?>";

    const startDateEl = document.querySelector("#startDate");
    const endDateEl   = document.querySelector("#endDate");

    if (startDateEl) {
        startDateEl.addEventListener("change", () => {
            if (startDateEl.value < currentDate) startDateEl.value = "";
            if (endDateEl) endDateEl.value = "";
        });
    }

    if (endDateEl) {
        endDateEl.addEventListener("change", () => {
            const startDate = startDateEl?.value || "";
            if (endDateEl.value < startDate || endDateEl.value < currentDate) {
                endDateEl.value = "";
            }
        });
    }
});
</script>

<div class="collapse navbar-collapse justify-content-center" id="navbarNav" style="width: 100%;">
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link <?= isActive('index', $page) ?>" href="/" <?= $color ?>>Home</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= isActive('drawing', $page) ?>" href="drawing" <?= $color ?>>Drawing Page</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= isActive('contact', $page) ?>" href="contact" <?= $color ?>>Contact Us</a>
        </li>

        <?php if ($getUserID): ?>
            <li class="nav-item logout">
                <a class="nav-link <?= $collapse ?>"
                   data-bs-toggle="collapse"
                   data-bs-target="#collapseProfile"
                   aria-controls="collapseProfile"
                   aria-expanded="false"
                   <?= $color ?>>My Profile</a>

                <div class="<?= $collapse ?>" id="collapseProfile">
                    <ul>
                        <li><a class="nav-link <?= $activeD ?>" href="user-dashboard">Dashboard</a></li>
                        <li><a class="nav-link <?= $activeW ?>" href="wallet" <?= $color ?>>Wallet</a></li>
                    </ul>
                </div>
            </li>
        <?php endif; ?>

        <?php if ($getUserRole != 0): ?>
            <li class="nav-item">
                <a class="nav-link" href="admin" <?= $color ?>>Admin Page</a>
            </li>
        <?php endif; ?>

        <?php if ($getUserID): ?>
            <li class="nav-item logout">
                <a class="nav-link logout-btn" href="functions/logout" style="color:#fff;">Logout</a>
            </li>
        <?php else: ?>
            <li class="nav-item" style="padding:0 15px;">
                <div class="box-hide">
                    <a class="nav-link menu-hide" href="sign-up">Sign Up</a>
                </div>
            </li>
            <li class="nav-item" style="padding:0 15px;">
                <div class="box-hide-x">
                    <a class="nav-link menu-hide" href="sign-in">Login</a>
                </div>
            </li>
        <?php endif; ?>
    </ul>
</div>

<div class="collapse navbar-collapse justify-content-end">
    <ul class="navbar-nav">
        <li>
            <div class="dropdown">
                <a aria-expanded="false" data-bs-toggle="dropdown" href="#" role="button">
                    <img alt="" src="assets/images/user-icon.png">
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <?php if ($getUserID): ?>
                        <li style="list-style:none;display:inline;">
                            <div class="acc" style="padding:0 10px 10px;border-bottom:1px solid #e7e7e7;">
                                <img alt="" src="assets/images/user-icon.png"> <span><?= $getFirstName ?></span>
                            </div>
                        </li>
                        <li><a class="dropdown-item" href="user-dashboard">My Profile</a></li>
                        <li><a class="dropdown-item" href="wallet">Wallet</a></li>
                        <li><a class="dropdown-item" href="functions/logout">Logout</a></li>
                    <?php else: ?>
                        <li style="list-style:none;display:inline;">
                            <div class="acc" style="padding:0 10px 10px;">
                                <img alt="" src="assets/images/user-icon.png"> <span>Account</span>
                            </div>
                            <a class="btn-login" href="sign-in" style="display:block;">Login</a>
                        </li>
                        <li><a class="dropdown-item" href="sign-up">Sign Up</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </li>
    </ul>
</div>
