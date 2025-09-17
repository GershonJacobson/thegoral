<?php
// functions/register.php (Upgraded Version)

declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1'); // Good for development, turn off for production

require("../config/session.php");
require("../config/db.php");
// Note: Email sending is often problematic on local servers without configuration.
// It is commented out by default to ensure registration works.
// require("../PHPMailer/src/PHPMailer.php");
// require("../PHPMailer/src/SMTP.php");

// --- 1. CSRF Token Validation (Critical Security Step) ---
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['result' => 'error', 'message' => 'Invalid security token.']);
    exit();
}

// --- 2. Safely Get and Validate Input ---
$firstName = $_POST['firstName'] ?? '';
$lastName = $_POST['lastName'] ?? '';
$emailAddress = $_POST['emailAddress'] ?? '';
$phone = $_POST['phone'] ?? '';
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';

$json = ['result' => ''];

if(empty($firstName) || empty($lastName) || empty($emailAddress) || empty($phone) || empty($password)) {
    $json['result'] = "blank";
    $json['message'] = "All fields are required.";
} elseif ($password !== $confirmPassword) {
    $json['result'] = "password_mismatch";
    $json['message'] = "Passwords do not match.";
} elseif (strlen($password) < 7) {
    $json['result'] = "password_short";
    $json['message'] = "Password must be at least 7 characters.";
} elseif (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
    $json['result'] = "invalid_email";
    $json['message'] = "Please enter a valid email address.";
} else {
    // --- 3. Check if Email Already Exists (Securely) ---
    $checkSql = "SELECT userid_pk FROM tbl_user WHERE email = ?";
    $checkStmt = $con->prepare($checkSql);
    $checkStmt->bind_param("s", $emailAddress);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if($checkResult->num_rows > 0) {
        $json['result'] = "existed";
        $json['message'] = "An account with this email already exists.";
    } else {
        // --- 4. Insert New User into Database (Securely) ---
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $status = 1; // Set user to active by default for local testing
        $role = 0;   // Standard user role
        $token = md5($emailAddress . time()); // Stronger token

        $insertSql = "INSERT INTO tbl_user (firstname, lastname, email, phone, password, role, status, token) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $insertStmt = $con->prepare($insertSql);
        $insertStmt->bind_param("sssssiis", $firstName, $lastName, $emailAddress, $phone, $hashedPassword, $role, $status, $token);

        if($insertStmt->execute()) {
            $json['result'] = "OK";
            
            // Unset CSRF token after successful registration
            unset($_SESSION['csrf_token']);
            
            // --- Email Sending Logic (Optional) ---
            /*
            $mail = new PHPMailer\PHPMailer\PHPMailer();
            // ... (configure and send email) ...
            */

        } else {
            $json['result'] = "error";
            $json['message'] = "A database error occurred while creating your account.";
        }
        $insertStmt->close();
    }
    $checkStmt->close();
}

// --- 5. Send Proper JSON Response ---
header('Content-Type: application/json');
echo json_encode($json);
?>