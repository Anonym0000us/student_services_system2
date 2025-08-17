<?php
include 'config.php';
session_start();



// Fetch guidance requests for the logged-in admin user
$user_id = $_SESSION['user_id'];
$query = "SELECT appointments.*, students.first_name AS student_first_name, students.last_name AS student_last_name
          FROM appointments
          JOIN users AS students ON appointments.student_id = students.user_id
          WHERE appointments.user_id = ?"; // Filter by the logged-in admin's ID
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $user_id); // Bind the admin ID to the query
$stmt->execute();
$result = $stmt->get_result();

// Check if query execution was successful
if ($result === false) {
    die("Error executing query: " . $stmt->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guidance Requests</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            display: flex;
        }

        .main-content {
            margin-left: 260px;
            padding: 20px;
            width: calc(100% - 260px);
            min-height: 100vh;
            background: white;
        }

        h2 {
            text-align: center;
            color: #333333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #dddddd;
            text-align: left;
        }
        th {
            background-color: #f4f4f9;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .success-message {
            color: green;
            text-align: center;
            margin-bottom: 15px;
        }
        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
        .action-link, .delete-link {
            color: white;
            text-decoration: none;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 4px;
            margin-right: 5px;
            display: inline-block;
        }
        .action-link {
            background-color: #007bff;
        }
        .action-link:hover {
            background-color: #0056b3;
        }
        .delete-link {
            background-color: #d9534f;
        }
        .delete-link:hover {
            background-color: #c9302c;
        }
        .update-form, .delete-form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .update-form select, .update-form button, .delete-form button {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #dddddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .update-form button, .delete-form button {
            background-color: #007bff;
            color: #ffffff;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .update-form button:hover, .delete-form button:hover {
            background-color: #0056b3;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background-color: white;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 8px;
            text-align: center;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <?php include 'guidance_admin_header.php'; ?>
    <div class="main-content">
        <h2>Guidance Requests</h2>

        <?php if (isset($_GET['success'])): ?>
            <p class="success-message"><?= htmlspecialchars($_GET['success']) ?></p>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <p class="error-message"><?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student Name</th>
                    <th>Appointment Date</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Admin Message</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['student_first_name'] . ' ' . $row['student_last_name']) ?></td>
                            <td><?= htmlspecialchars($row['appointment_date']) ?></td>
                            <td><?= htmlspecialchars($row['reason']) ?></td>
                            <td><?= htmlspecialchars($row['status']) ?></td>
                            <td><?= htmlspecialchars($row['admin_message']) ?></td>
                            <td>
                                <button class="action-link" onclick="openUpdateModal('<?= htmlspecialchars($row['id']) ?>', '<?= htmlspecialchars($row['status']) ?>')">Update Status</button>
                                <button class="delete-link" onclick="openDeleteModal('<?= htmlspecialchars($row['id']) ?>')">Delete</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No guidance requests found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Update Modal -->
        <div id="updateModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('updateModal')">&times;</span>
                <h2>Update Guidance Request Status</h2>
                <form method="POST" class="update-form" action="update_guidance_status.php">
                    <input type="hidden" id="update_request_id" name="request_id">
                    <label for="status">Status:</label>
                    <select id="status" name="status" required>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="completed">Completed</option>
                    </select>
                    <label for="admin_message">Admin Message:</label>
                    <textarea id="admin_message" name="admin_message"></textarea>
                    <button type="submit" name="update_status">Update Status</button>
                </form>
            </div>
        </div>

        <!-- Delete Modal -->
        <div id="deleteModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('deleteModal')">&times;</span>
                <h2>Delete Guidance Request</h2>
                <form method="POST" class="delete-form" action="delete_guidance_request.php">
                    <input type="hidden" id="delete_request_id" name="request_id">
                    <p>Are you sure you want to delete this request?</p>
                    <button type="submit" name="delete_request">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openUpdateModal(request_id, currentStatus) {
            document.getElementById('update_request_id').value = request_id;
            document.getElementById('status').value = currentStatus;
            document.getElementById('updateModal').style.display = "flex";
        }

        function openDeleteModal(request_id) {
            document.getElementById('delete_request_id').value = request_id;
            document.getElementById('deleteModal').style.display = "flex";
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == document.getElementById('updateModal')) {
                closeModal('updateModal');
            } else if (event.target == document.getElementById('deleteModal')) {
                closeModal('deleteModal');
            }
        }
    </script>
</body>
</html>