<?php
/**
 * User Login Handler
 */

if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    require("../config/db.php");
    session_start();

    $email = trim(strtolower($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $rememberMe = intval($_POST['rememberMe'] ?? 0);

    if(empty($email) || empty($password)) {
        echo json_encode(["result" => "missingFields"]);
        exit;
    }

    $stmt = $con->prepare("SELECT user_id, password, active FROM tbl_users WHERE email_address = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $qUser = $stmt->get_result();

    if($qUser->num_rows > 0) {
        $user = $qUser->fetch_assoc();

        if(password_verify($password, $user['password'])) {
            if((int)$user['active'] !== 1) {
                echo json_encode(["result" => "notActive"]);
                exit;
            }

            session_regenerate_id(true);
            $_SESSION['userGoral'] = (int)$user['user_id'];

            if($rememberMe === 1) {
                $selector = bin2hex(random_bytes(12));
                $validator = bin2hex(random_bytes(32));
                $validatorHash = hash("sha256", $validator);
                $expiresAt = date("Y-m-d H:i:s", time() + (60 * 60 * 24 * 7));
                $createdAt = date("Y-m-d H:i:s");

                $tokenStmt = $con->prepare("INSERT INTO tbl_remember_token (selector, validator_hash, userid_fk, expires_at, created_at) VALUES (?, ?, ?, ?, ?)");
                $tokenStmt->bind_param("ssiss", $selector, $validatorHash, $user['user_id'], $expiresAt, $createdAt);
                $tokenStmt->execute();
                $tokenStmt->close();

                setcookie("goral_remember", $selector . ":" . $validator, [
                    'expires' => time() + (60 * 60 * 24 * 7),
                    'path' => '/',
                    'secure' => isset($_SERVER['HTTPS']),
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);
            }

            echo json_encode(["result" => "OK"]);
        } else {
            echo json_encode(["result" => "wrongPassword"]);
        }
    } else {
        echo json_encode(["result" => "emailNotFound"]);
    }
    $stmt->close();
} else {
    header("HTTP/1.1 403 Forbidden");
    exit;
}
?>
