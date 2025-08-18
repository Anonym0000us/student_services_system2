<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Student') {
    header('Location: login.php');
    exit;
}

$studentId = $_SESSION['user_id'];

// Fetch full name
$stmt = $conn->prepare("SELECT TRIM(CONCAT(first_name,' ',COALESCE(NULLIF(middle_name,''),''),' ',last_name)) AS full_name FROM users WHERE user_id = ?");
$stmt->bind_param('s', $studentId);
$stmt->execute();
$fullName = ($stmt->get_result()->fetch_assoc()['full_name']) ?? '';

// Fetch current dorm room assignment (if any)
$roomId = null; $roomName = '';
$stmt = $conn->prepare("SELECT r.id, r.name FROM student_room_assignments a JOIN rooms r ON a.room_id = r.id WHERE a.user_id = ? AND a.status = 'Active' ORDER BY a.assigned_at DESC LIMIT 1");
$stmt->bind_param('s', $studentId);
$stmt->execute();
$room = $stmt->get_result()->fetch_assoc();
if ($room) { $roomId = (int)$room['id']; $roomName = $room['name']; }

// Fallback: use latest approved application if no active assignment record
if (!$roomId) {
    $stmt = $conn->prepare("SELECT r.id, r.name FROM student_room_applications sa JOIN rooms r ON sa.room_id = r.id WHERE sa.user_id = ? AND sa.status = 'Approved' ORDER BY sa.applied_at DESC LIMIT 1");
    $stmt->bind_param('s', $studentId);
    $stmt->execute();
    $appRoom = $stmt->get_result()->fetch_assoc();
    if ($appRoom) { $roomId = (int)$appRoom['id']; $roomName = $appRoom['name']; }
}

// History - Updated to use the correct payments table with correct column names
$rows = [];
$stmt = $conn->prepare("SELECT id, room_id, amount, receipt_path, status, submitted_at FROM payments WHERE student_id = ? ORDER BY submitted_at DESC");
$stmt->bind_param('s', $studentId);
$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) { $rows[] = $r; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dormitory Payments</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .progress { height: 8px; }
    </style>
</head>
<body>
<?php include 'student_header.php'; ?>
<div class="container mt-4">
    <h2>Dormitory Payments</h2>
    <div class="card mb-4">
        <div class="card-body">
            <form id="uploadForm" method="post" enctype="multipart/form-data" action="upload_payment.php">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Student ID</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($studentId) ?>" disabled>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($fullName) ?>" disabled>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Dormitory Room</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($roomName ?: 'Not Assigned') ?>" disabled>
                        <input type="hidden" name="room_id" value="<?= (int)($roomId ?: 0) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Payment Amount</label>
                        <input type="number" step="0.01" min="0" name="amount" class="form-control" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Receipt (JPG, PNG, PDF)</label>
                        <input type="file" name="receipt" id="receipt" class="form-control" accept="image/jpeg,image/png,application/pdf" required>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="progress d-none" id="progressBar"><div class="progress-bar" role="progressbar" style="width: 0%"></div></div>
                    <button class="btn btn-primary mt-2" type="submit">Upload</button>
                </div>
            </form>
        </div>
    </div>

    <h4>Payment Status</h4>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Room</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Receipt</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['submitted_at']) ?></td>
                    <td><?= htmlspecialchars($p['room_id']) ?></td>
                    <td>₱<?= htmlspecialchars(number_format((float)$p['amount'],2)) ?></td>
                    <td><span class="badge bg-<?= $p['status']==='Verified'?'success':($p['status']==='Rejected'?'danger':'warning') ?>"><?= htmlspecialchars($p['status']) ?></span></td>
                    <td><a class="btn btn-sm btn-outline-secondary" target="_blank" href="view_payment_file.php?id=<?= (int)$p['id'] ?>">View</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
document.getElementById('uploadForm').addEventListener('submit', function(e){
    e.preventDefault();
    const form = this;
    const file = document.getElementById('receipt').files[0];
    if (!file) { alert('Please select a file'); return; }
    if (!['image/jpeg','image/png','application/pdf'].includes(file.type)) { alert('Invalid file type'); return; }
    if (file.size > 5 * 1024 * 1024) { alert('Max file size is 5MB'); return; }

    const bar = document.getElementById('progressBar');
    bar.classList.remove('d-none');
    const xhr = new XMLHttpRequest();
    xhr.open('POST', form.action, true);
    xhr.upload.onprogress = function(evt){ if (evt.lengthComputable) { const p = Math.floor((evt.loaded/evt.total)*100); bar.firstElementChild.style.width = p + '%'; } };
    xhr.onload = function(){
        try { 
            const res = JSON.parse(xhr.responseText); 
            if (res.success) {
                alert(res.message || 'Payment uploaded successfully!');
                location.reload();
            } else {
                alert(res.message || 'Upload failed');
            }
        }
        catch(e){ 
            console.error('Server response error:', e);
            alert('Server error occurred. Please try again.'); 
        }
    };
    xhr.onerror = function() {
        alert('Network error occurred. Please check your connection and try again.');
    };
    const data = new FormData(form);
    xhr.send(data);
});
</script>
</body>
</html>
