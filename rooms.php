<?php
include 'config.php';
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "You must be logged in to apply for a room."]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle room application
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['apply_room'])) {
    $room_id = isset($_POST['room_id']) ? intval($_POST['room_id']) : 0;
    $message = isset($_POST['message']) ? mysqli_real_escape_string($conn, $_POST['message']) : '';
    
    // Validate room existence
    $roomQuery = "SELECT * FROM rooms WHERE id = ?";
    $stmt = $conn->prepare($roomQuery);
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "Selected room does not exist."]);
        exit;
    }

    // Check if user already applied for the room
    $checkQuery = "SELECT * FROM student_room_applications WHERE user_id = ? AND room_id = ? AND status = 'Pending'";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("si", $user_id, $room_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $insertQuery = "INSERT INTO student_room_applications (user_id, room_id, message, status, applied_at) VALUES (?, ?, ?, 'Pending', NOW())";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("sis", $user_id, $room_id, $message);
        
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Application submitted successfully!"]);
        } else {
            echo json_encode(["success" => false, "message" => "Error submitting application."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "You already have a pending application for this room."]);
    }
    exit;
}

$query = "SELECT * FROM rooms";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Rooms</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .room-card { transition: transform 0.3s ease-in-out; }
        .room-card:hover { transform: scale(1.03); }
        .room-img { width: 100%; height: 180px; object-fit: cover; }
    </style>
</head>
<body>
    <?php include 'student_header.php'  // Include the header file ?>
    <div class="container mt-4">
        <h2 class="text-center">Available Rooms</h2>
        <div class="row">
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <div class="col-md-4 mb-4">
                    <div class="card shadow-lg room-card">
                        <img src="<?= htmlspecialchars($row['image']) ?>" class="room-img" alt="Room Image">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($row['name']) ?></h5>
                            <p>Total Beds: <?= htmlspecialchars($row['total_beds']) ?></p>
                            <p>Occupied Beds: <?= htmlspecialchars($row['occupied_beds']) ?></p>
                            <p>Available Beds: <?= htmlspecialchars($row['total_beds'] - $row['occupied_beds']) ?></p>
                            <button class="btn btn-success w-100 apply-room-btn" data-room-id="<?= $row['id'] ?>">Apply for Room</button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="applyRoomModal" tabindex="-1" aria-labelledby="applyRoomModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Apply for Room</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="applyRoomForm">
                        <input type="hidden" name="room_id" id="room_id">
                        <label for="message" class="form-label">Optional Message:</label>
                        <textarea name="message" class="form-control" rows="3"></textarea>
                        <div class="modal-footer mt-3">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Submit Application</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let applyRoomModal = new bootstrap.Modal(document.getElementById("applyRoomModal"));
            
            document.querySelectorAll(".apply-room-btn").forEach(button => {
                button.addEventListener("click", function() {
                    document.getElementById("room_id").value = this.getAttribute("data-room-id");
                    applyRoomModal.show();
                });
            });

            document.getElementById("applyRoomForm").addEventListener("submit", function(event) {
                event.preventDefault(); 
                let formData = new FormData(this);
                formData.append("apply_room", true);
                
                fetch("rooms.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    applyRoomModal.hide();
                    alert(data.message);
                    if (data.success) location.reload();
                })
                .catch(error => console.error("Error:", error));
            });
        });
    </script>
</body>
</html>
