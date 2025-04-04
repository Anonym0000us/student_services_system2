<?php
session_start();
include 'admin_dormitory_header.php'; // Include the header for the dormitory admin
include 'config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch all pending applications
$query = "SELECT sa.id, sa.user_id, sa.room_id, sa.status, sa.message, 
                 TRIM(CONCAT(u.first_name, ' ', COALESCE(NULLIF(u.middle_name, ''), ''), ' ', u.last_name)) AS full_name, 
                 r.name AS room_name
          FROM student_room_applications sa
          JOIN users u ON sa.user_id = u.user_id
          JOIN rooms r ON sa.room_id = r.id
          WHERE sa.status = 'Pending'
          ORDER BY sa.applied_at DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Applications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .email-icon {
            color: #007bff;
            cursor: pointer;
            transition: color 0.3s ease;
            font-size: 1.2rem;
        }
        .email-icon:hover {
            color: #0056b3;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h2 class="text-center">Pending Room Applications</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Student Name</th>
                <th>Room Applied</th>
                <th>Message</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                    <td><?= htmlspecialchars($row['room_name']) ?></td>
                    <td>
                        <i class="fa-solid fa-envelope email-icon" data-bs-toggle="modal" data-bs-target="#messageModal" 
                            data-message="<?= htmlspecialchars(nl2br(trim($row['message'] ?: 'No message'))) ?>">
                        </i>
                    </td>
                    <td><span class="badge bg-warning">Pending</span></td>
                    <td>
                        <button class="btn btn-success approve-btn" data-id="<?= intval($row['id']) ?>">Approve</button>
                        <button class="btn btn-danger reject-btn" data-id="<?= intval($row['id']) ?>">Reject</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Message Modal -->
<div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="messageModalLabel">Applicant Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="messageContent">
                <!-- Message will be injected here -->
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".email-icon").forEach(icon => {
        icon.addEventListener("click", function () {
            const message = this.getAttribute("data-message").replace(/\n/g, "<br>");
            document.getElementById("messageContent").innerHTML = message; // Use innerHTML to render <br>
        });
    });
    
    document.querySelectorAll(".approve-btn, .reject-btn").forEach(button => {
        button.addEventListener("click", function () {
            const applicationId = this.getAttribute("data-id");
            const action = this.classList.contains("approve-btn") ? "approve" : "reject";

            if (confirm(`Are you sure you want to ${action} this application?`)) {
                fetch("dormitory_process_applications.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: new URLSearchParams({ application_id: applicationId, action: action })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error("Network response was not ok");
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    alert("An error occurred while processing the request.");
                });
            }
        });
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>