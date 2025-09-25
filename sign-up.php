<?php
// Enhanced sign-up.php
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1'); // For development only

require("config/session.php");

// Redirect if already logged in
if(!empty($getUserID)) {
    header("Location: /");
    exit();
}

// Generate CSRF token for security
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
    <meta charset="UTF-8">
    <meta name="robots" content="index, follow">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Sign up - The Goral</title>
    <link rel="icon" type="image/svg+xml" href="assets/images/favicon.svg">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="assets/css/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/sweetalert.css">
    <link rel="stylesheet" href="assets/font/fontawesome/css/all.min.css">
    
    <!-- Custom styles for enhanced design -->
    <style>
        /* Enhanced form styling */
        .form-signup {
            max-width: 600px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .form-title {
            font-size: 32px;
            font-weight: bold;
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        .form-control {
            height: 50px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            padding: 12px 15px;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .form-control:focus {
            border-color: #FFA500;
            box-shadow: 0 0 0 0.2rem rgba(255, 165, 0, 0.1);
            outline: none;
        }
        
        .form-control.is-invalid {
            border-color: #dc3545;
            padding-right: 40px;
        }
        
        .form-control.is-valid {
            border-color: #28a745;
            padding-right: 40px;
        }
        
        /* Validation icons */
        .form-control.is-valid::after {
            content: "✓";
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #28a745;
            font-weight: bold;
        }
        
        /* Enhanced button */
        .btnSignup {
            width: 100%;
            height: 55px;
            background: linear-gradient(135deg, #FFA500 0%, #FF8C00 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
            position: relative;
            overflow: hidden;
        }
        
        .btnSignup:hover {
            background: linear-gradient(135deg, #FF8C00 0%, #FF7700 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(255, 165, 0, 0.3);
        }
        
        .btnSignup:disabled {
            background: #cccccc;
            cursor: not-allowed;
            transform: none;
        }
        
        /* Password strength indicator */
        .password-strength-bar {
            height: 4px;
            border-radius: 2px;
            margin-top: 5px;
            transition: all 0.3s ease;
        }
        
        .strength-weak { background: #dc3545; width: 33%; }
        .strength-medium { background: #ffc107; width: 66%; }
        .strength-strong { background: #28a745; width: 100%; }
        
        /* Terms checkbox styling */
        .tos-container {
            display: flex;
            align-items: flex-start;
            margin: 20px 0;
            font-size: 14px;
            color: #666;
        }
        
        .tos-container input[type="checkbox"] {
            width: 20px;
            height: 20px;
            margin-right: 10px;
            margin-top: 2px;
            cursor: pointer;
        }
        
        .tos-container a {
            color: #FFA500;
            text-decoration: none;
            font-weight: 500;
        }
        
        .tos-container a:hover {
            text-decoration: underline;
        }
        
        /* Error/success messages */
        .invalid-feedback, .valid-feedback {
            display: none;
            font-size: 13px;
            margin-top: 5px;
        }
        
        .invalid-feedback.d-block, .valid-feedback.d-block {
            display: block;
        }
        
        .invalid-feedback {
            color: #dc3545;
        }
        
        .valid-feedback {
            color: #28a745;
        }
        
        /* Social login section */
        .divider-text {
            text-align: center;
            margin: 30px 0;
            position: relative;
            color: #999;
        }
        
        .divider-text::before {
            content: "";
            position: absolute;
            left: 0;
            top: 50%;
            width: 45%;
            height: 1px;
            background: #e0e0e0;
        }
        
        .divider-text::after {
            content: "";
            position: absolute;
            right: 0;
            top: 50%;
            width: 45%;
            height: 1px;
            background: #e0e0e0;
        }
        
        .social-signup {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .social-btn {
            flex: 1;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
            color: #333;
            font-weight: 500;
        }
        
        .social-btn:hover {
            border-color: #FFA500;
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        /* Responsive design */
        @media (max-width: 576px) {
            .col-50 {
                width: 100%;
                margin-bottom: 15px;
            }
            
            .social-signup {
                flex-direction: column;
            }
        }
        
        /* Loading spinner */
        .spinner-border {
            width: 20px;
            height: 20px;
            border-width: 2px;
            margin-right: 8px;
        }
    </style>
    
    <!-- Scripts -->
    <script src="assets/js/jquery.min.js" defer></script>
    <script src="assets/js/bootstrap/js/bootstrap.bundle.min.js" defer></script>
    <script src="assets/js/sweetalert.min.js" defer></script>
    <script src="assets/js/sign-up.js" defer></script>
    <script src="assets/font/fontawesome/js/all.min.js" defer></script>
</head>
<body style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh;">
    
    <!-- Header with logo -->
    <div class="login-container">
        <div class="signup-logo text-center py-4">
            <a href="/">
                <img src="assets/images/logo-dark.svg" alt="The Goral Logo" style="max-height: 60px;">
            </a>
        </div>
        
        <!-- Tab navigation -->
        <div class="signup-menu">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="d-flex" style="background: white; border-radius: 50px; padding: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                        <a class="flex-fill text-center py-2 px-4" 
                           style="background: #FFA500; color: white; border-radius: 50px; text-decoration: none; font-weight: 600;"
                           href="sign-up">Create Account</a>
                        <a class="flex-fill text-center py-2 px-4" 
                           style="color: #666; text-decoration: none; font-weight: 600;"
                           href="sign-in">Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main form container -->
    <div class="form-signup">
        <div class="bg-white p-5 rounded-3 shadow-lg">
            <h1 class="form-title">Create your account</h1>
            
            <!-- Social signup buttons (optional) -->
            <div class="social-signup">
                <button type="button" class="social-btn">
                    <i class="fab fa-google" style="color: #4285F4;"></i>
                    Google
                </button>
                <button type="button" class="social-btn">
                    <i class="fab fa-facebook-f" style="color: #1877F2;"></i>
                    Facebook
                </button>
            </div>
            
            <div class="divider-text">or sign up with email</div>
            
            <!-- Registration form -->
            <form id="signupForm" novalidate>
                <!-- CSRF Token (CRITICAL for security) -->
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                
                <!-- Name fields -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="firstName" class="form-label">First Name *</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="firstName" 
                                   name="firstName"
                                   placeholder="John" 
                                   autocomplete="given-name"
                                   required>
                            <div class="invalid-feedback"></div>
                            <div class="valid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="lastName" class="form-label">Last Name *</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="lastName"
                                   name="lastName" 
                                   placeholder="Doe" 
                                   autocomplete="family-name"
                                   required>
                            <div class="invalid-feedback"></div>
                            <div class="valid-feedback"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Email field -->
                <div class="form-group">
                    <label for="email" class="form-label">Email Address *</label>
                    <input type="email" 
                           class="form-control" 
                           id="email"
                           name="email" 
                           placeholder="john.doe@example.com" 
                           autocomplete="email"
                           required>
                    <div class="invalid-feedback"></div>
                    <div class="valid-feedback"></div>
                </div>
                
                <!-- Phone field -->
                <div class="form-group">
                    <label for="phone" class="form-label">Phone Number *</label>
                    <input type="tel" 
                           class="form-control" 
                           id="phone"
                           name="phone" 
                           placeholder="(555) 123-4567" 
                           autocomplete="tel"
                           required>
                    <div class="invalid-feedback"></div>
                    <div class="valid-feedback"></div>
                </div>
                
                <!-- Password fields -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password" class="form-label">Password *</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password"
                                   name="password" 
                                   placeholder="Enter password" 
                                   autocomplete="new-password"
                                   required>
                            <div class="password-strength-bar"></div>
                            <div class="invalid-feedback"></div>
                            <div class="valid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="confirmPassword" class="form-label">Confirm Password *</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="confirmPassword"
                                   name="confirmPassword" 
                                   placeholder="Confirm password" 
                                   autocomplete="new-password"
                                   required>
                            <div class="invalid-feedback"></div>
                            <div class="valid-feedback"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Terms of service -->
                <div class="tos-container">
                    <input type="checkbox" name="tos" id="tos" checked required>
                    <label for="tos">
                        I agree to The Goral's 
                        <a href="terms-and-conditions" target="_blank">Terms of Service</a> 
                        and 
                        <a href="privacy-policy" target="_blank">Privacy Policy</a>
                    </label>
                </div>
                
                <!-- Submit button -->
                <button type="submit" class="btnSignup" id="btnSignup">
                    Create Account
                </button>
                
                <!-- Sign in link -->
                <div class="text-center mt-4">
                    <span class="text-muted">Already have an account?</span> 
                    <a href="sign-in" style="color: #FFA500; font-weight: 600; text-decoration: none;">Sign in</a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="footer mt-5">
        <div class="container">
            <div class="row">
                <div class="col text-center">
                    <div class="social-media mb-3">
                        <a href="#" class="text-muted mx-2"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-muted mx-2"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-muted mx-2"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="text-muted mx-2"><i class="fab fa-instagram"></i></a>
                    </div>
                    <div class="copyright text-muted">
                        &copy; <?= date('Y') ?> The Goral. All rights reserved.
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
