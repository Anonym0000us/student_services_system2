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

// Fetch approved scholarship applications along with user details
$sql = "
    SELECT 
        scholarships.name AS scholarship_name, 
        users.first_name, 
        users.middle_name, 
        users.last_name, 
        users.email, 
        users.phone, 
        users.course, 
        users.year, 
        users.section, 
        scholarship_applications.application_date, 
        scholarship_applications.approval_date, 
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
        scholarship_applications.status = 'approved'
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
    <title>Approved Scholars</title>
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
        <h2>Approved Scholars</h2>
    </div>

    <div class="mb-3">
        <input type="text" id="search-bar" class="form-control" placeholder="Search..." style="width: 40%;">
    </div>

    <div class="table-responsive">
        <table class="table table-bordered" id="scholars-table">
            <thead>
                <tr>
                    <th>Scholarship Name</th>
                    <th>Student Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Course</th>
                    <th>Year</th>
                    <th>Section</th>
                    <th>Application Date</th>
                    <th>Date Approved</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['scholarship_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars(($row['first_name'] ?? '') . ' ' . (($row['middle_name'] ?? '') ? ($row['middle_name'] . ' ') : '') . ($row['last_name'] ?? '')) ?></td>
                        <td><?= htmlspecialchars($row['email'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['phone'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['course'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['year'] ?? '') ?></td>
                        <td><?= htmlspecialchars($row['section'] ?? '') ?></td>
                        <td><?= date("F j, Y", strtotime($row['application_date'] ?? '')) ?></td>
                        <td><?= date("F j, Y", strtotime($row['approval_date'] ?? '')) ?></td>
                        <td><button class="btn btn-danger delete-btn" data-id="<?= $row['application_id'] ?>">Delete</button></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10">No approved scholars found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        // Search functionality
        $("#search-bar").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#scholars-table tbody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });

        // Delete functionality
        $(".delete-btn").on("click", function() {
            if (confirm("Are you sure you want to delete this record?")) {
                var id = $(this).data("id");
                // Make an AJAX request to delete the record
                $.ajax({
                    url: 'delete_application.php',
                    type: 'POST',
                    data: { id: id },
                    success: function(response) {
                        if (response === 'success') {
                            location.reload();
                        } else {
                            alert("Error deleting the record.");
                        }
                    }
                });
            }
        });
    });
</script>
</body>
</html>