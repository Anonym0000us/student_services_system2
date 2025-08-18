<?php
session_start();
require_once 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Student') {
    echo json_encode(["success" => false, "message" => "Unauthorized."]);
    exit;
}

$studentId = $_SESSION['user_id'];
$roomId = isset($_POST['room_id']) ? intval($_POST['room_id']) : 0;
$amount = isset($_POST['amount']) ? trim($_POST['amount']) : '';

// Validation
if ($roomId <= 0) { 
    echo json_encode(["success" => false, "message" => "No active room assignment found."]); 
    exit; 
}

if ($amount === '' || !is_numeric($amount) || (float)$amount <= 0) { 
    echo json_encode(["success" => false, "message" => "Invalid amount."]); 
    exit; 
}

if (!isset($_FILES['receipt']) || $_FILES['receipt']['error'] !== UPLOAD_ERR_OK) { 
    echo json_encode(["success" => false, "message" => "Invalid file upload."]); 
    exit; 
}

$maxSize = 5 * 1024 * 1024; // 5MB
if ($_FILES['receipt']['size'] > $maxSize) { 
    echo json_encode(["success" => false, "message" => "File too large. Max 5MB."]); 
    exit; 
}

// Validate file type
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($_FILES['receipt']['tmp_name']);
$allowed = ['image/jpeg'=>'jpg','image/png'=>'png','application/pdf'=>'pdf'];

if (!array_key_exists($mime, $allowed)) { 
    echo json_encode(["success" => false, "message" => "Invalid file type. Only JPG, PNG, and PDF are allowed."]); 
    exit; 
}

$ext = $allowed[$mime];

// Create uploads directory if it doesn't exist
$uploadDir = __DIR__ . '/uploads/payments';
if (!is_dir($uploadDir)) { 
    if (!mkdir($uploadDir, 0755, true)) {
        echo json_encode(["success" => false, "message" => "Failed to create upload directory."]); 
        exit;
    }
}

// Check if directory is writable
if (!is_writable($uploadDir)) {
    echo json_encode(["success" => false, "message" => "Upload directory is not writable."]); 
    exit;
}

// Generate unique filename
$timestamp = date('Ymd_His');
$sanitizedId = preg_replace('/[^A-Za-z0-9_-]/','', $studentId);
$filename = $sanitizedId . '_' . $timestamp . '.' . $ext;
$dest = $uploadDir . '/' . $filename;

// Move uploaded file
if (!move_uploaded_file($_FILES['receipt']['tmp_name'], $dest)) {
    echo json_encode(["success" => false, "message" => "Failed to save file."]); 
    exit; 
}

try {
    // Calculate file hash for security
    $fileHash = hash_file('sha256', $dest);
    
    // Insert payment record into the existing payments table
    $stmt = $conn->prepare("INSERT INTO payments (student_id, room_id, amount, receipt_path, status, submitted_at, file_hash) VALUES (?, ?, ?, ?, 'Pending', NOW(), ?)");
    
    if (!$stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }
    
    $stmt->bind_param('sids', $studentId, $roomId, $amount, $filename, $fileHash);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    echo json_encode(["success" => true, "message" => "Payment uploaded successfully! Awaiting verification."]);
    
} catch (Exception $e) {
    // Clean up uploaded file if database insert fails
    @unlink($dest);
    
    // Log the error for debugging
    error_log("Payment upload error: " . $e->getMessage());
    
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]); 
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
}
?>
