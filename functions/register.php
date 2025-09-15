<?php
require("../config/session.php");
require("../config/db.php");
require("../PHPMailer/src/PHPMailer.php");
require("../PHPMailer/src/SMTP.php");

$firstName = $_POST['firstName'];
$lastName = $_POST['lastName'];
$emailAddress = $_POST['emailAddress'];
$password = $_POST['password'];
$confirmPassword = $_POST['confirmPassword'];

$json = array(
    'result' => ""
);

if($firstName == "" || $lastName == "" || $emailAddress == "" || $password == "" || $confirmPassword == "") {
    $json['result'] = "blank";
} else {
    // Check if email already exists using prepared statements
    $checkSql = "SELECT userid_pk FROM tbl_user WHERE email = ?";
    $checkStmt = mysqli_prepare($con, $checkSql);
    mysqli_stmt_bind_param($checkStmt, "s", $emailAddress);
    mysqli_stmt_execute($checkStmt);
    $checkResult = mysqli_stmt_get_result($checkStmt);

    if(mysqli_num_rows($checkResult) > 0) {
        $json['result'] = "existed";
    } else {
        // Hash the password for secure storage
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $status = 0; // Inactive until confirmed
        $role = 0; // Default user role
        $token = md5($emailAddress);

        // Insert new user with prepared statements
        $insertSql = "INSERT INTO tbl_user (firstname, lastname, email, password, role, status, token) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $insertStmt = mysqli_prepare($con, $insertSql);
        mysqli_stmt_bind_param($insertStmt, "ssssiis", $firstName, $lastName, $emailAddress, $hashedPassword, $role, $status, $token);

        if(mysqli_stmt_execute($insertStmt)) {
            // --- Email Sending Logic ---
            $mail = new PHPMailer\PHPMailer\PHPMailer();
            $mail->IsSMTP();
            $mail->SMTPDebug = 0;
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'ssl';
            $mail->Host = "mail.thegoral.com";
            $mail->Port = 465;
            $mail->IsHTML(true);
            $mail->Username = "support@thegoral.com";
            $mail->Password = "***REMOVED***";
            $mail->SetFrom("support@thegoral.com", "The Goral");
            $mail->Subject = "Confirmation Email";
            $mailContent = file_get_contents('../confirmation-email-template.html');
            $mailContent = str_replace("{{TOKEN}}", $token, $mailContent);
            $mail->MsgHTML($mailContent);
            $mail->AddAddress($emailAddress);

            if(!$mail->Send()) {
                // Optional: Log email error
            }

            $json['result'] = "OK";
        } else {
            $json['result'] = "error";
        }
        mysqli_stmt_close($insertStmt);
    }
    mysqli_stmt_close($checkStmt);
}

echo json_encode($json);
?>