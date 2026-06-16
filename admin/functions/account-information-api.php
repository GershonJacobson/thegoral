<?php
/**
 * Account Information Functions
 * Handles all AJAX requests for the account information page
 * 
 * Consolidated into one file with three endpoints:
 * - get-account: Search for accounts
 * - get-payment-history: Get payment history for an account
 * - update-account: Update account information
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();
require("../../config/session.php");
require("../../config/Database.php");
require("../../config/Validator.php");

// Initialize helpers
$db = new Database($con);
$validator = new Validator();

// Verify this is an AJAX request (except for errors)
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

// Verify user is authenticated
if (!isset($getUserRole) || $getUserRole == 0) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Determine which action to perform
$action = isset($_GET['action']) ? $_GET['action'] : null;

switch ($action) {
    case 'search':
        handleSearch();
        break;
    case 'payment-history':
        handlePaymentHistory();
        break;
    case 'update':
        handleUpdate();
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        exit;
}

/**
 * Handle account search
 * Query parameter: searchVal (name, email, or phone)
 */
function handleSearch() {
    global $db, $validator;

    $searchVal = $_POST['searchVal'] ?? '';

    // Validate input
    if (!$validator->isRequired($searchVal, 'Search term')) {
        http_response_code(400);
        echo json_encode(['error' => $validator->getFirstError()]);
        exit;
    }

    // Limit search term length to prevent abuse
    if (!$validator->isLength($searchVal, 1, 100, 'Search term')) {
        http_response_code(400);
        echo json_encode(['error' => $validator->getFirstError()]);
        exit;
    }

    // Build search parameter with wildcards for LIKE query
    $searchParam = "%{$searchVal}%";

    // Query with prepared statement
    // Every buyer counts as a customer — guests AND logged-in purchasers.
    $sql = "
        SELECT DISTINCT
            first_name,
            last_name,
            email,
            phone
        FROM tbl_ticket
        WHERE (
            first_name LIKE ?
            OR last_name LIKE ?
            OR email LIKE ?
            OR phone LIKE ?
        )
        ORDER BY first_name ASC
    ";

    $result = $db->query($sql, [
        ['s', $searchParam],
        ['s', $searchParam],
        ['s', $searchParam],
        ['s', $searchParam]
    ]);

    if (!$result) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error']);
        exit;
    }

    // Build response
    $accounts = [];
    while ($row = $db->fetchRow($result)) {
        $accounts[] = [
            'firstName' => $row['first_name'],
            'lastName' => $row['last_name'],
            'emailAddress' => $row['email'],
            'phone' => $row['phone']
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($accounts);
}

/**
 * Handle payment history retrieval
 * Query parameter: email (customer email)
 */
function handlePaymentHistory() {
    global $db, $validator, $getUserRole;

    $email = $_POST['email'] ?? '';

    // Validate input
    if (!$validator->isEmail($email)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid email address']);
        exit;
    }

    // Get payment history
    $sql = "
        SELECT
            ticket_id,
            DATE_FORMAT(purchased_date, '%m/%d/%Y') AS purchased_date,
            total_price,
            card_last4 AS card_number,
            payment_method
        FROM tbl_ticket
        WHERE email = ?
        ORDER BY purchased_date DESC
    ";

    $result = $db->query($sql, [
        ['s', $email]
    ]);

    if (!$result) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error']);
        exit;
    }

    // Build response with refund information
    $payments = [];
    while ($row = $db->fetchRow($result)) {
        $ticketId = $row['ticket_id'];

        // Get refund info for this ticket
        $refundSql = "SELECT amount FROM tbl_refund WHERE ticketid_fk = ? LIMIT 1";
        $refundResult = $db->query($refundSql, [['i', $ticketId]]);
        $refundRow = $db->fetchRow($refundResult);

        // Never expose a full card number to the CRM — only the last 4 digits.
        $rawCard = preg_replace('/\D/', '', (string)$row['card_number']);
        $cardMasked = $rawCard !== '' ? ('•••• ' . substr($rawCard, -4)) : '';

        $payments[] = [
            'ticketID' => $row['ticket_id'],
            'purchased_date' => $row['purchased_date'],
            'total_price' => $row['total_price'],
            'card_number' => $cardMasked,
            'payment_method' => $row['payment_method'],
            'totalRefund' => $refundRow['amount'] ?? null,
            'userRole' => $getUserRole
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($payments);
}

/**
 * Handle account information update
 * POST parameters: firstName, lastName, email, phone
 */
function handleUpdate() {
    global $db, $validator, $getUserRole;

    // Verify user has permission to edit
    if ($getUserRole != 1 && $getUserRole != 2) {
        http_response_code(403);
        echo json_encode(['error' => 'You do not have permission to edit']);
        exit;
    }

    $firstName = $_POST['firstName'] ?? '';
    $lastName = $_POST['lastName'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';

    // Validate inputs
    $validator->clearErrors();
    
    $validator->isRequired($firstName, 'First name');
    $validator->isRequired($lastName, 'Last name');
    $validator->isRequired($email, 'Email');
    $validator->isLength($firstName, 1, 100, 'First name');
    $validator->isLength($lastName, 1, 100, 'Last name');
    $validator->isEmail($email);

    if ($validator->hasErrors()) {
        http_response_code(400);
        echo json_encode(['error' => $validator->getFirstError()]);
        exit;
    }

    // Check if email exists
    $checkSql = "SELECT ticket_id FROM tbl_ticket WHERE email = ? LIMIT 1";
    $checkResult = $db->query($checkSql, [['s', $email]]);

    if (!$checkResult || !$db->fetchRow($checkResult)) {
        http_response_code(404);
        echo json_encode(['error' => 'Email address not found']);
        exit;
    }

    // Update the account
    $updateSql = "
        UPDATE tbl_ticket 
        SET 
            first_name = ?, 
            last_name = ?, 
            phone = ? 
        WHERE email = ?
    ";

    $success = $db->execute($updateSql, [
        ['s', $firstName],
        ['s', $lastName],
        ['s', $phone],
        ['s', $email]
    ]);

    if (!$success) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update account']);
        exit;
    }

    // Return success
    header('Content-Type: application/json');
    echo json_encode(['result' => 'OK']);
}
?>