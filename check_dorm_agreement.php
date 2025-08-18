<?php
session_start();
require_once 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Not authenticated."]);
    exit;
}

require_once __DIR__ . '/includes/DormAgreementService.php';
$service = new DormAgreementService($conn);
$active = $service->getActiveAgreement();

if (!$active) {
    echo json_encode(["success" => true, "hasActive" => false, "accepted" => true]);
    exit;
}

$accepted = $service->hasUserAccepted($_SESSION['user_id'], (int)$active['id']);
if ($accepted) {
    echo json_encode(["success" => true, "hasActive" => true, "accepted" => true]);
    exit;
}

echo json_encode([
    "success" => true,
    "hasActive" => true,
    "accepted" => false,
    "agreement" => [
        "id" => (int)$active['id'],
        "title" => $active['title'],
        "content" => $active['content']
    ]
]);
?>