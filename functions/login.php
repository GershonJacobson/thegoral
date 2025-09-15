<?php
require("../config/session.php");
require("../config/db.php");

$emailAddress = $_POST['emailAddress'];
$password = $_POST['password'];

$json = array(
    'result' => ""
);

if($emailAddress == "" || $password == "") {
    $json['result'] = "blank";
} else {
    // Use prepared statements to prevent SQL injection
    $sql = "SELECT userid_pk, firstname, lastname, email, password, role, status FROM tbl_user WHERE email = ? AND status = 1";
    $stmt = mysqli_prepare($con, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $emailAddress);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if(mysqli_num_rows($result) > 0) {
            $data = mysqli_fetch_array($result);
            
            // Securely verify the hashed password
            if (password_verify($password, $data['password'])) {
                $_SESSION['goral_user_id'] = $data['userid_pk'];
                $_SESSION['goral_firstname'] = $data['firstname'];
                $_SESSION['goral_lastname'] = $data['lastname'];
                $_SESSION['goral_email'] = $data['email'];
                $_SESSION['goral_role'] = $data['role'];

                $json['result'] = "OK";
            } else {
                // Password does not match
                $json['result'] = "NOTOK";
            }
        } else {
            // No user found with that email or user is not active
            $json['result'] = "NOTOK";
        }
        mysqli_stmt_close($stmt);
    } else {
        // SQL statement preparation failed
        $json['result'] = "error"; 
    }
}

echo json_encode($json);
?>