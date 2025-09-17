<?php
// functions/login.php (Upgraded and Corrected)

declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1'); // Good for development, turn off for production

// Start the session to access session variables
require("../config/session.php");

// --- 1. CSRF Token Validation (Critical Security Step) ---
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['result' => 'error', 'message' => 'Invalid security token.']);
    exit();
}

// --- 2. Safely Get Input ---
require("../config/db.php");
$emailAddress = $_POST['emailAddress'] ?? '';
$password = $_POST['password'] ?? '';

$json = ['result' => ''];

if(empty($emailAddress) || empty($password)) {
    $json['result'] = "blank";
    $json['message'] = "Email and password cannot be empty.";
} else {
    // --- 3. Use Prepared Statements to Find User (CORRECTED SQL) ---
    $sql = "SELECT userid_pk, firstname, lastname, email, pass, role FROM tbl_user WHERE email = ? AND status = 1";
    $stmt = $con->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("s", $emailAddress);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($user = $result->fetch_assoc()) {
            // --- 4. Securely Verify Password and Set Session (CORRECTED KEY) ---
            if (password_verify($password, $user['pass'])) {
                // Password is correct, create the session
                $_SESSION['goral_user_id'] = $user['userid_pk'];
                $_SESSION['goral_firstname'] = $user['firstname'];
                $_SESSION['goral_lastname'] = $user['lastname'];
                $_SESSION['goral_email'] = $user['email'];
                $_SESSION['goral_role'] = $user['role'];

                // Unset the CSRF token after successful login for security
                unset($_SESSION['csrf_token']);

                $json['result'] = "OK";
            } else {
                // Password does not match
                $json['result'] = "NOTOK";
                $json['message'] = "Invalid email or password.";
            }
        } else {
            // No user found with that email or user is not active
            $json['result'] = "NOTOK";
            $json['message'] = "Invalid email or password.";
        }
        $stmt->close();
    } else {
        // SQL statement preparation failed
        $json['result'] = "error";
        $json['message'] = "A database error occurred.";
    }
}

// --- 5. Send Proper JSON Response ---
header('Content-Type: application/json');
echo json_encode($json);
?>