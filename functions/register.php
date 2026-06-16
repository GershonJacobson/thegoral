<?php
/**
 * User Registration Handler
 */

if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    require("../config/db.php");

    // Get input
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim(strtolower($_POST['email'] ?? ''));
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validate inputs
    if(empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        echo json_encode(["result" => "missingFields"]);
        exit;
    }

    // Names must look like names (letters/spaces/hyphens/apostrophes only) —
    // winners are announced publicly by name.
    $namePattern = "/^\p{L}[\p{L} .'-]{0,48}\p{L}$/u";
    if(!preg_match($namePattern, $firstName) || !preg_match($namePattern, $lastName)) {
        echo json_encode(["result" => "invalidName"]);
        exit;
    }

    if(filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        echo json_encode(["result" => "invalidEmail"]);
        exit;
    }

    if(strlen($password) < 6) {
        echo json_encode(["result" => "weakPassword"]);
        exit;
    }

    // Check if email exists
    $stmt = $con->prepare("SELECT user_id FROM tbl_users WHERE email_address = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if($stmt->get_result()->num_rows > 0) {
        echo json_encode(["result" => "userExisted"]);
        exit;
    }
    $stmt->close();

    // Hash password
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    $dateJoined = date("Y-m-d H:i:s");

    $stmt = $con->prepare("INSERT INTO tbl_users (first_name, last_name, email_address, phone, password, active, admin, date_joined)
                           VALUES (?, ?, ?, ?, ?, 1, 0, ?)");
    $stmt->bind_param("ssssss", $firstName, $lastName, $email, $phone, $passwordHash, $dateJoined);

    if($stmt->execute()) {
        echo json_encode(["result" => "OK"]);
    } else {
        error_log("[Register] Insert failed: " . $stmt->error);
        echo json_encode(["result" => "error", "message" => "Registration failed. Please try again."]);
    }
    $stmt->close();
} else {
    header("HTTP/1.1 403 Forbidden");
    exit;
}
?>
