<?php
/**
 * Session Management
 * Handles user authentication and session/cookie validation
 */

// Initialize all variables
$getUserID = "";
$getFirstName = "";
$getLastName = "";
$getEmailAddress = "";
$getPhone = "";
$getUserRole = 0;

// Check if user has active session or cookie
$userSession = isset($_SESSION['userGoral']) ? $_SESSION['userGoral'] : '';
$userCookie = isset($_COOKIE['cookielogin']['user']) ? $_COOKIE['cookielogin']['user'] : '';

// Only proceed if there's a session or cookie to check
if (!empty($userSession) || !empty($userCookie)) {
    
    // Prepare the query with parameters
    $sql = "SELECT user_id, first_name, last_name, email_address, phone, admin 
            FROM tbl_users 
            WHERE user_id = ? OR user_id = ? 
            LIMIT 1";
    
    // Create prepared statement
    $stmt = $con->prepare($sql);
    
    if ($stmt) {
        // Bind parameters (both are strings/integers depending on user_id type)
        // Assuming user_id is an integer - change 'i' to 's' if it's a string UUID
        $stmt->bind_param("ii", $userSession, $userCookie);
        
        // Execute the query
        $stmt->execute();
        
        // Get the result
        $result = $stmt->get_result();
        
        // Fetch user data if found
        if ($result && $result->num_rows > 0) {
            $dSession = $result->fetch_assoc();
            
            // Set all user variables from database
            $getUserID = $dSession['user_id'];
            $getFirstName = $dSession['first_name'];
            $getLastName = $dSession['last_name'];
            $getEmailAddress = $dSession['email_address'];
            $getPhone = $dSession['phone'];
            $getUserRole = $dSession['admin'];
        }
        
        // Close the statement
        $stmt->close();
    }
}
?>