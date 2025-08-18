<?php
session_start();
require_once 'config.php';

// Security: Check user session and status
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    http_response_code(403);
    include 'error_page.php';
    exit;
}
$stmt = $conn->prepare("SELECT status FROM users WHERE user_id = ?");
$stmt->bind_param('s', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if (!$result->num_rows || $result->fetch_assoc()['status'] !== 'Active') {
    http_response_code(403);
    include 'error_page.php';
    exit;
}

// Security: CSRF validation
if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    include 'error_page.php';
    exit;
}

// Security: Rate limiting
if (!isset($_SESSION['last_file_access'])) $_SESSION['last_file_access'] = 0;
$now = time();
if ($now - $_SESSION['last_file_access'] < 1) {
    http_response_code(429);
    include 'error_page.php';
    exit;
}
$_SESSION['last_file_access'] = $now;

// Constants
const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'application/pdf'];
const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'pdf'];
const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB

// Validate payment ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    include 'error_page.php';
    exit;
}

try {
    // Fetch payment details
    $stmt = $conn->prepare("SELECT student_id, receipt_path, amount, submitted_at, status FROM payments WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if (!$row) {
        http_response_code(404);
        include 'error_page.php';
        exit;
    }

    // Security: Access control
    $canView = ($_SESSION['user_id'] === $row['student_id']) || ($_SESSION['role'] === 'Dormitory Admin');
    if (!$canView) {
        http_response_code(403);
        include 'error_page.php';
        exit;
    }

    // Validate file
    $filePath = UPLOAD_DIR . '/' . basename($row['receipt_path']);
    if (!is_file($filePath)) {
        http_response_code(404);
        include 'error_page.php';
        exit;
    }

    // Check file size
    if (filesize($filePath) > MAX_FILE_SIZE) {
        http_response_code(400);
        include 'error_page.php';
        exit;
    }

    // Validate MIME type and extension
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($filePath);
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    if (!in_array($mime, ALLOWED_MIME_TYPES, true) || !in_array($extension, ALLOWED_EXTENSIONS, true)) {
        http_response_code(400);
        include 'error_page.php';
        exit;
    }

    // Verify file hash
    if ($row['file_hash'] && hash_file('sha256', $filePath) !== $row['file_hash']) {
        http_response_code(400);
        include 'error_page.php';
        exit;
    }

    // Log file access
    logAction($conn, $_SESSION['user_id'], 'view_receipt', $id);

    // Serve file
    if (isset($_GET['download']) && $_GET['download'] === '1') {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="payment-' . $id . '.' . $extension . '"');
    } else {
        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="payment-' . $id . '.' . $extension . '"');
        if ($mime === 'application/pdf' || in_array($mime, ['image/jpeg', 'image/png'])) {
            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Payment Receipt #<?php echo $id; ?> | Dormitory Admin</title>
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
                <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
                <style>
                    body { font-family: 'Roboto', sans-serif; background-color: #f8f9fa; }
                    .card { border-radius: 0.5rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); }
                    .preview-container { max-height: 70vh; overflow: auto; }
                    .btn:focus { outline: 2px solid #007bff; outline-offset: 2px; }
                    @media (max-width: 576px) { .preview-container { max-height: 50vh; } }
                </style>
            </head>
            <body>
            <div class="container my-4">
                <div class="card p-4">
                    <h2 class="mb-3">Payment Receipt #<?php echo $id; ?></h2>
                    <div class="mb-3">
                        <p class="text-muted mb-1"><strong>Student ID:</strong> <?php echo htmlspecialchars($row['student_id']); ?></p>
                        <p class="text-muted mb-1"><strong>Amount:</strong> ₱<?php echo htmlspecialchars(number_format((float)$row['amount'], 2)); ?></p>
                        <p class="text-muted mb-1"><strong>Submitted:</strong> <?php echo htmlspecialchars($row['submitted_at']); ?></p>
                        <p class="text-muted mb-1"><strong>Status:</strong> <span class="badge bg-<?php echo $row['status'] === 'Verified' ? 'success' : ($row['status'] === 'Rejected' ? 'danger' : 'warning'); ?>"><?php echo htmlspecialchars($row['status']); ?></span></p>
                    </div>
                    <div class="preview-container mb-3">
                        <?php if ($mime === 'application/pdf'): ?>
                        <object data="data:<?php echo $mime; ?>;base64,<?php echo base64_encode(file_get_contents($filePath)); ?>" type="<?php echo $mime; ?>" width="100%" height="600px">
                            <p class="text-center">Unable to preview PDF. <a href="?id=<?php echo $id; ?>&csrf_token=<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>&download=1" class="text-primary">Download instead</a>.</p>
                        </object>
                        <?php else: ?>
                        <img src="data:<?php echo $mime; ?>;base64,<?php echo base64_encode(file_get_contents($filePath)); ?>" class="img-fluid" alt="Payment receipt">
                        <?php endif; ?>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="?id=<?php echo $id; ?>&csrf_token=<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>&download=1" class="btn btn-primary">Download</a>
                        <a href="index.php" class="btn btn-secondary">Back to Payments</a>
                    </div>
                </div>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
            </body>
            </html>
            <?php
            exit;
        }
    }

    // Add caching headers
    header('Cache-Control: public, max-age=3600');
    header('ETag: "' . ($row['file_hash'] ?: md5_file($filePath)) . '"');

    // Stream file
    readfile($filePath);
    exit;
} catch (Exception $e) {
    error_log("File access error: " . $e->getMessage() . " | ID: $id | File: " . __FILE__ . " | Line: " . __LINE__);
    http_response_code(500);
    include 'error_page.php';
    exit;
}
?>