<?php
// sign-up.php (Upgraded Version)

declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1'); // Good for development, turn off for production

require("config/session.php");

// If the user is already logged in, redirect them to the homepage.
if(!empty($getUserID)) {
    header("Location: /");
    exit(); // Always exit after a redirect
}

/**
 * Generates and stores a CSRF token in the session if one doesn't exist.
 * @return string The CSRF token.
 */
function getCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

$csrfToken = getCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="robots" content="index, follow">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Sign up - The Goral</title>
    <link rel="icon" type="image/x-icon" href="assets/images/favicon.svg" />

    <link rel="stylesheet" href="assets/css/bootstrap/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/style.css" />
    <link rel="stylesheet" href="assets/css/sweetalert.css" />
    <link rel="stylesheet" href="assets/font/fontawesome/css/all.min.css" />

    <script src="assets/js/jquery.min.js" defer></script>
    <script src="assets/js/bootstrap/js/bootstrap.bundle.min.js" defer></script>
    <script src="assets/js/sweetalert.min.js" defer></script>
    <script src="assets/js/sign-up.js" defer></script>
    <script src="assets/font/fontawesome/js/all.min.js" defer></script>
  </head>
  <body style="background: #fff">
    <div class="login-container">
      <div class="signup-logo">
        <a href="/"><img src="assets/images/logo-dark.svg" alt="The Goral Logo" /></a>
      </div>
      <div class="signup-menu">
        <div class="row">
          <div class="col-md col-50">
            <a class="signup menu-active" href="sign-up">Create your account</a>
          </div>
          <div class="col-md col-50">
            <a class="signin" href="sign-in">Login</a>
          </div>
        </div>
      </div>
    </div>

    <div class="form-signup">
      <div class="row justify-content-center">
        <div class="row">
          <div class="form-title">Create your account</div>
        </div>
        
        <form id="signUpForm" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <div class="row">
              <div class="col-md col-50">
                  <label for="firstName" class="form-label visually-hidden">First Name</label>
                  <input type="text" class="form-control" name="firstName" placeholder="First Name" id="firstName" autocomplete="given-name" required />
              </div>
              <div class="col-md col-50">
                  <label for="lastName" class="form-label visually-hidden">Last Name</label>
                  <input type="text" class="form-control" name="lastName" placeholder="Last Name" id="lastName" autocomplete="family-name" required />
              </div>
            </div>
            <div class="row">
              <div class="col-md">
                  <label for="email" class="form-label visually-hidden">Email</label>
                  <input type="email" class="form-control" name="emailAddress" placeholder="Email" id="email" autocomplete="email" required />
                  <div class="alert emailNotValid" style="display: none; color: red; font-size: 12px;">Email is not valid!</div>
              </div>
            </div>
            <div class="row">
              <div class="col-md">
                  <label for="phone" class="form-label visually-hidden">Phone</label>
                  <input type="tel" class="form-control" name="phone" placeholder="Phone" id="phone" autocomplete="tel" required />
                  <div class="alert phoneNotValid" style="display: none; color: red; font-size: 12px;">Phone number is not valid!</div>
              </div>
            </div>
            <div class="row">
              <div class="col-md">
                  <label for="password" class="form-label visually-hidden">Password</label>
                  <input type="password" class="form-control" name="password" placeholder="Password" id="password" autocomplete="new-password" required />
                  <div class="alert minPassword" style="display: none; color: red; font-size: 12px;">Minimal password length is 7 characters!</div>
              </div>
              <div class="col-md">
                  <label for="confirmPassword" class="form-label visually-hidden">Confirm Password</label>
                  <input type="password" class="form-control" name="confirmPassword" placeholder="Confirm Password" id="confirmPassword" autocomplete="new-password" required />
                  <div class="alert mustSamePassword" style="display: none; color: red; font-size: 12px;">Passwords must match!</div>
              </div>
            </div>
            <div class="row">
              <div class="tos">
                <input type="checkbox" name="tos" id="tos" checked required />
                <span>By clicking sign up I agree with The Goral
                  <a href="terms-and-conditions.php">Terms of Services</a>
                </span>
              </div>
            </div>
            <div class="row">
              <button type="submit" class="btnSignup" id="btnSignup">Sign up</button>
            </div>
        </form>
        </div>
    </div>
    <div class="footer">
      <div class="container">
        <div class="row">
          <div class="social-media text-center">
            <span><img src="assets/images/logo.svg" alt="The Goral Logo" /></span>
            <i class="fa-brands fa-facebook-f"></i>
            <i class="fa-brands fa-twitter"></i>
            <i class="fa-brands fa-linkedin-in"></i>
            <i class="fa-brands fa-instagram"></i>
          </div>
        </div>
        <div class="row">
          <div class="menu-footer">
            <a href="/">Home</a>
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
          <div class="copyright">© <?= date('Y') ?> The Goral</div>
        </div>
      </div>
    </div>
  </body>
</html>