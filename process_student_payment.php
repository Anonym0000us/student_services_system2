<?php
session_start();
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');

// Security: Check user session and status
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.', 'status' => 'error']);
    exit;
}
$stmt = $conn->prepare("SELECT role, status FROM users WHERE user_id = ?");
$stmt->bind_param('s', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if (!$result->num_rows) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'User not found.', 'status' => 'error']);
    exit;
}
$user = $result->fetch_assoc();
if ($user['status'] !== 'Active') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Account is inactive.', 'status' => 'error']);
    exit;
}
if ($user['role'] !== 'Dormitory Admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied. Dormitory Admin role required.', 'status' => 'error']);
    exit;
}

// Security: CSRF validation
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.', 'status' => 'error']);
    exit;
}

// Security: Rate limiting
if (!isset($_SESSION['last_action_time'])) $_SESSION['last_action_time'] = 0;
$now = time();
if ($now - $_SESSION['last_action_time'] < 1) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many requests. Please wait.', 'status' => 'error']);
    exit;
}
$_SESSION['last_action_time'] = $now;

// Constants
const VALID_ACTIONS = ['verify', 'reject'];
const PAYMENT_TYPE = 'student';
const MAX_REMARKS_LENGTH = 500;
const MAX_RECEIPT_NUMBER_LENGTH = 100;
const MAX_PAYMENT_TYPE_LENGTH = 20;
const MAX_ACTION_LENGTH = 20;

// Validate inputs
$action = $_POST['action'] ?? '';
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$remarks = isset($_POST['remarks']) ? trim($_POST['remarks']) : '';
$receiptNumber = isset($_POST['receipt_number']) ? trim($_POST['receipt_number']) : null;
$datePaid = isset($_POST['date_paid']) ? trim($_POST['date_paid']) : null;

if ($id <= 0 || !in_array($action, VALID_ACTIONS)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid payment ID or action.', 'status' => 'error']);
    exit;
}

if ($action === 'reject' && empty($remarks)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Remarks are required for rejection.', 'status' => 'error']);
    exit;
}

if (strlen($remarks) > MAX_REMARKS_LENGTH) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Remarks exceed maximum length of ' . MAX_REMARKS_LENGTH . ' characters.', 'status' => 'error']);
    exit;
}

if ($receiptNumber && strlen($receiptNumber) > MAX_RECEIPT_NUMBER_LENGTH) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Receipt number exceeds maximum length of ' . MAX_RECEIPT_NUMBER_LENGTH . ' characters.', 'status' => 'error']);
    exit;
}

if ($datePaid && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $datePaid)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid date format for date paid (YYYY-MM-DD required).', 'status' => 'error']);
    exit;
}

if (strlen(PAYMENT_TYPE) > MAX_PAYMENT_TYPE_LENGTH || strlen($action) > MAX_ACTION_LENGTH) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid payment type or action length.', 'status' => 'error']);
    exit;
}

$status = $action === 'verify' ? 'Verified' : 'Rejected';

try {
    $conn->begin_transaction();

    // Check payment and validate student_id, room_id
    $checkStmt = $conn->prepare("SELECT status, student_id, room_id FROM payments WHERE id = ?");
    $checkStmt->bind_param('i', $id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    if (!$checkResult->num_rows) {
        $conn->rollback();
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Payment not found.', 'status' => 'error']);
        exit;
    }
    $row = $checkResult->fetch_assoc();
    $currentStatus = $row['status'];
    if ($currentStatus !== 'Pending') {
        $conn->rollback();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Payment is already $currentStatus.", 'status' => 'error']);
        exit;
    }

    // Validate student_id
    $studentCheck = $conn->prepare("SELECT user_id FROM users WHERE user_id = ? AND status = 'Active'");
    $studentCheck->bind_param('s', $row['student_id']);
    $studentCheck->execute();
    if (!$studentCheck->get_result()->num_rows) {
        $conn->rollback();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid or inactive student ID.', 'status' => 'error']);
        exit;
    }

    // Validate room_id
    $roomCheck = $conn->prepare("SELECT id FROM rooms WHERE id = ?");
    $roomCheck->bind_param('i', $row['room_id']);
    $roomCheck->execute();
    if (!$roomCheck->get_result()->num_rows) {
        $conn->rollback();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid room ID.', 'status' => 'error']);
        exit;
    }

    // Update payment
    if ($action === 'verify') {
        $stmt = $conn->prepare("UPDATE payments SET status = ?, verified_by = ?, verified_at = NOW(), remarks = ?, receipt_number = ?, date_paid = ? WHERE id = ?");
        $stmt->bind_param('sssssi', $status, $_SESSION['user_id'], $remarks, $receiptNumber, $datePaid, $id);
    } else {
        $stmt = $conn->prepare("UPDATE payments SET status = ?, verified_by = ?, verified_at = NOW(), remarks = ? WHERE id = ?");
        $stmt->bind_param('sssi', $status, $_SESSION['user_id'], $remarks, $id);
    }
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Log action
        $log = $conn->prepare("INSERT INTO payment_audit_logs (payment_type, payment_id, action, old_status, new_status, admin_id, remarks, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $log->bind_param('sisssss', PAYMENT_TYPE, $id, $action, $currentStatus, $status, $_SESSION['user_id'], $remarks);
        $log->execute();

        $conn->commit();
        echo json_encode([
            'success' => true,
            'message' => "Payment $status successfully.",
            'status' => 'success',
            'new_status' => $status
        ]);
    } else {
        $conn->rollback();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to update payment.', 'status' => 'error']);
    }
} catch (mysqli_sql_exception $e) {
    $conn->rollback();
    $errorMessage = $e->getMessage();
    error_log("Payment processing error: $errorMessage | ID: $id | Action: $action | POST: " . json_encode($_POST) . " | File: " . __FILE__ . " | Line: " . __LINE__);

    // Specific error messages
    if (stripos($errorMessage, 'unknown column') !== false) {
        $message = 'Database schema error: Missing column in table.';
    } elseif (stripos($errorMessage, 'duplicate entry') !== false) {
        $message = 'Database error: Duplicate entry detected.';
    } elseif (stripos($errorMessage, 'foreign key') !== false) {
        $message = 'Database error: Invalid user or room ID.';
    } elseif (stripos($errorMessage, 'cannot be null') !== false) {
        $message = 'Database error: Required field is missing.';
    } else {
        $message = 'An error occurred while processing the payment. Please try again.';
    }

    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $message, 'status' => 'error']);
} catch (Exception $e) {
    $conn->rollback();
    error_log("Unexpected error: " . $e->getMessage() . " | ID: $id | Action: $action | POST: " . json_encode($_POST) . " | File: " . __FILE__ . " | Line: " . __LINE__);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred.', 'status' => 'error']);
}
?>