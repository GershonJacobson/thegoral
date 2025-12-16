<?php
/**
 * User Login Handler
 */

if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    require("../config/db.php");
    session_start();
    
    $email = trim(strtolower(mysqli_real_escape_string($con, $_POST['email'] ?? '')));
    $password = $_POST['password'] ?? '';
    $rememberMe = intval($_POST['rememberMe'] ?? 0);
    
    if(empty($email) || empty($password)) {
        echo json_encode(["result" => "missingFields"]);
        exit;
    }
    
    // Query user
    $qUser = mysqli_query($con, "SELECT user_id, password FROM tbl_users WHERE email_address = '$email'");
    
    if(mysqli_num_rows($qUser) > 0) {
        $user = mysqli_fetch_array($qUser);
        
        if(password_verify($password, $user['password'])) {
            $_SESSION['userGoral'] = $user['user_id'];
            
            if($rememberMe === 1) {
                $cookieExpiry = time() + (60 * 60 * 24 * 7);
                setcookie("cookielogin[user]", $user['user_id'], $cookieExpiry, "/", "", false, true);
            }
            
            echo json_encode(["result" => "OK"]);
        } else {
            echo json_encode(["result" => "wrongPassword"]);
        }
    } else {
        echo json_encode(["result" => "emailNotFound"]);
    }
} else {
    header("HTTP/1.1 403 Forbidden");
    exit;
}
?>