<?php
session_start();
require_once 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Dormitory Admin') {
    echo json_encode(["success" => false, "message" => "Access denied."]);
    exit;
}

$action = $_POST['action'] ?? '';
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$remarks = trim($_POST['remarks'] ?? '');

if ($id <= 0 || ($action !== 'verify' && $action !== 'reject')) {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
    exit;
}

if ($action === 'verify') {
    $stmt = $conn->prepare("UPDATE dormitory_payments SET status = 'Verified', verified_by = ?, verified_at = NOW(), remarks = ? WHERE id = ?");
    $stmt->bind_param('ssi', $_SESSION['user_id'], $remarks, $id);
} else {
    $stmt = $conn->prepare("UPDATE dormitory_payments SET status = 'Rejected', verified_by = ?, verified_at = NOW(), remarks = ? WHERE id = ?");
    $stmt->bind_param('ssi', $_SESSION['user_id'], $remarks, $id);
}

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Payment updated."]);
} else {
    echo json_encode(["success" => false, "message" => "Database error."]);
}
