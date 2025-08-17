<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$user = "root";
$password = "";
$dbname = "student_services_db";

// Create connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch scholarship applications along with user details
$sql = "
    SELECT 
        scholarships.name AS scholarship_name, 
        users.first_name, 
        users.middle_name, 
        users.last_name, 
        users.email, 
        users.phone, 
        users.course, 
        scholarship_applications.application_date, 
        scholarship_applications.status,
        scholarship_applications.id AS application_id
    FROM 
        scholarship_applications 
    JOIN 
        scholarships 
    ON 
        scholarship_applications.scholarship_id = scholarships.id 
    JOIN 
        users 
    ON 
        scholarship_applications.user_id = users.user_id 
    WHERE 
        scholarship_applications.status = 'pending'
    ORDER BY 
        scholarship_applications.application_date DESC
";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scholarship Applications</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
        }
        .sidebar {
            width: 260px;
            height: 100vh;
            background-color: #003366;
            color: white;
            position: fixed;
            padding-top: 20px;
            transition: all 0.3s ease;
        }
        .sidebar-header {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background-color: #002855;
            font-size: 1.5rem;
            font-weight: 700;
            text-transform: uppercase;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            color: #FFD700;
        }
        .sidebar-header i {
            margin-right: 10px;
            color: #FFD700;
        }
        .sidebar-menu {
            margin-top: 20px;
        }
        .menu-item {
            text-decoration: none;
            color: white;
            display: block;
            padding: 15px 20px;
            font-size: 1.1rem;
            margin: 8px 15px;
            background-color: #004080;
            transition: all 0.3s ease;
            border-radius: 8px;
        }
        .menu-item:hover,
        .menu-item.active {
            background-color: #FFD700;
            color: #003366;
            transform: scale(1.05);
            font-weight: bold;
        }
        .logout-btn {
            text-decoration: none;
            color: white;
            display: block;
            padding: 15px;
            font-size: 1.1rem;
            background-color: #d9534f;
            margin: 30px 15px;
            border-radius: 8px;
            text-align: center;
            transition: all 0.3s ease;
        }
        .logout-btn:hover {
            background-color: #c9302c;
            transform: scale(1.05);
        }
        .main-content {
            margin-left: 260px;
            padding: 20px;
            width: calc(100% - 260px);
            transition: all 0.3s ease;
        }
        .header {
            background: #00509E;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .table-responsive {
            background: white;
            border-radius: 5px;
            overflow: auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        th, td {
            text-align: center;
            vertical-align: middle;
            white-space: nowrap;
        }
        th {
            background: #004080;
            color: white;
        }
        .btn-primary {
            background: #00509E;
            border: none;
        }
        .btn-primary:hover {
            background: #003366;
        }
        .btn-warning, .btn-danger {
            margin-left: 5px;
        }
        .modal-header {
            background: #00509E;
            color: white;
        }
        .btn-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #000;
            opacity: 0.6;
        }
        .btn-close:hover {
            color: #000;
            opacity: 1;
        }
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            .sidebar {
                height: auto;
                position: static;
                width: 100%;
            }
        }
    </style>
</head>
<body>

<?php include 'admin_scholarship_header.php'; ?>

<div class="main-content">
    <div class="header">
        <h2>Scholarship Applications</h2>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Scholarship Name</th>
                    <th>Student Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Course</th>
                    <th>Application Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['scholarship_name']) ?></td>
                    <td><?= htmlspecialchars($row['first_name'] . ' ' . ($row['middle_name'] ? $row['middle_name'] . ' ' : '') . $row['last_name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['phone']) ?></td>
                    <td><?= htmlspecialchars($row['course']) ?></td>
                    <td><?= date("F j, Y", strtotime($row['application_date'])) ?></td>
                    <td><?= ucfirst($row['status']) ?></td>
                    <td>
                        <?php if ($row['status'] == 'pending'): ?>
                            <button class="btn btn-success btn-sm approve-application" 
                                data-id="<?= $row['application_id'] ?>"><i class="fas fa-check"></i> Approve</button>
                            <button class="btn btn-warning btn-sm reject-application" 
                                data-id="<?= $row['application_id'] ?>"><i class="fas fa-times"></i> Reject</button>
                        <?php endif; ?>
                        <button class="btn btn-danger btn-sm delete-application" 
                            data-id="<?= $row['application_id'] ?>"><i class="fas fa-trash"></i> Delete</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){
    // Handle Approve Application
    $(".approve-application").click(function(){
        if (confirm("Are you sure you want to approve this application?")) {
            var button = $(this);
            $.ajax({
                url: "update_application.php",
                type: "POST",
                data: { id: button.data("id"), action: "approve" },
                success: function(response) {
                    alert(response);
                    button.closest('td').find('.approve-application, .reject-application').hide();
                    location.reload();
                }
            });
        }
    });

    // Handle Reject Application
    $(".reject-application").click(function(){
        if (confirm("Are you sure you want to reject this application?")) {
            var button = $(this);
            $.ajax({
                url: "update_application.php",
                type: "POST",
                data: { id: button.data("id"), action: "reject" },
                success: function(response) {
                    alert(response);
                    button.closest('td').find('.approve-application, .reject-application').hide();
                    location.reload();
                }
            });
        }
    });

    // Handle Delete Application
    $(".delete-application").click(function(){
        if (confirm("Are you sure you want to delete this application?")) {
            var button = $(this);
            $.ajax({
                url: "update_application.php",
                type: "POST",
                data: { id: button.data("id"), action: "delete" },
                success: function(response) {
                    alert(response);
                    button.closest('tr').remove();
                }
            });
        }
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>