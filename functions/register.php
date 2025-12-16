<?php
/**
 * User Registration Handler
 */

if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    require("../config/db.php");
    
    // Get input
    $firstName = trim(mysqli_real_escape_string($con, $_POST['firstName'] ?? ''));
    $lastName = trim(mysqli_real_escape_string($con, $_POST['lastName'] ?? ''));
    $email = trim(strtolower(mysqli_real_escape_string($con, $_POST['email'] ?? '')));
    $phone = trim(mysqli_real_escape_string($con, $_POST['phone'] ?? ''));
    $password = $_POST['password'] ?? '';
    
    // Validate inputs
    if(empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        echo json_encode(["result" => "missingFields"]);
        exit;
    }
    
    // Check if email exists
    $qCheck = mysqli_query($con, "SELECT user_id FROM tbl_users WHERE email_address = '$email'");
    if(mysqli_num_rows($qCheck) > 0) {
        echo json_encode(["result" => "userExisted"]);
        exit;
    }
    
    // Hash password
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    $dateJoined = date("Y-m-d H:i:s");
    
    // Insert user - use backticks around column names
    $query = "INSERT INTO `tbl_users` (`first_name`, `last_name`, `email_address`, `phone`, `password`, `active`, `admin`, `date_joined`) 
              VALUES ('$firstName', '$lastName', '$email', '$phone', '$passwordHash', 1, 0, '$dateJoined')";
    
    $qInsert = mysqli_query($con, $query);
    
    if($qInsert) {
        echo json_encode(["result" => "OK"]);
    } else {
        echo json_encode(["result" => "error", "message" => mysqli_error($con)]);
    }
} else {
    header("HTTP/1.1 403 Forbidden");
    exit;
}
?>
