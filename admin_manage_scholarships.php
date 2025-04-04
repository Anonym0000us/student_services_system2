<?php
session_start();
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

// Fetch all scholarships
$sql = "SELECT * FROM scholarships ORDER BY deadline ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scholarship Management</title>
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

<div class="sidebar">
    <div class="sidebar-header">
        <i class="fas fa-user-graduate"></i> Admin Panel
    </div>
    <div class="sidebar-menu">
        <a href="dashboard.php" class="menu-item" id="dashboard">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="admin_manage_scholarships.php" class="menu-item" id="manage-scholarships">
            <i class="fas fa-graduation-cap"></i> Manage Scholarships
        </a>
        <a href="manage_applications.php" class="menu-item" id="applications">
            <i class="fas fa-file-alt"></i> Applications
        </a>
       
        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>

<div class="main-content">
    <div class="header">
        <h2>Scholarship Management</h2>
    </div>
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addScholarshipModal">Add Scholarship</button>

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Deadline</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= date("F j, Y", strtotime($row['deadline'])) ?></td>
                    <td><?= ucfirst($row['status']) ?></td>
                    <td>
                        <button class="btn btn-warning btn-sm edit-scholarship" 
                            data-id="<?= $row['id'] ?>" 
                            data-name="<?= htmlspecialchars($row['name']) ?>" 
                            data-description="<?= htmlspecialchars($row['description']) ?>" 
                            data-eligibility="<?= htmlspecialchars($row['eligibility']) ?>" 
                            data-deadline="<?= $row['deadline'] ?>" 
                            data-bs-toggle="modal" 
                            data-bs-target="#editScholarshipModal"><i class="fas fa-edit"></i> Edit</button>

                        <button class="btn btn-danger btn-sm delete-scholarship" 
                            data-id="<?= $row['id'] ?>"><i class="fas fa-trash"></i> Delete</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Scholarship Modal -->
<div class="modal fade" id="addScholarshipModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Scholarship</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addScholarshipForm">
                    <div class="mb-3">
                        <label class="form-label">Scholarship Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Eligibility</label>
                        <textarea class="form-control" name="eligibility" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deadline</label>
                        <input type="date" class="form-control" name="deadline" required>
                    </div>
                    <button type="submit" class="btn btn-success">Add Scholarship</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Scholarship Modal -->
<div class="modal fade" id="editScholarshipModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Scholarship</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editScholarshipForm">
                    <input type="hidden" name="id" id="editScholarshipId">
                    <div class="mb-3">
                        <label class="form-label">Scholarship Name</label>
                        <input type="text" class="form-control" name="name" id="editScholarshipName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="editScholarshipDescription" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Eligibility</label>
                        <textarea class="form-control" name="eligibility" id="editScholarshipEligibility" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deadline</label>
                        <input type="date" class="form-control" name="deadline" id="editScholarshipDeadline" required>
                    </div>
                    <button type="submit" class="btn btn-success">Update Scholarship</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){
    // Handle Add Scholarship
    $("#addScholarshipForm").submit(function(e){
        e.preventDefault();
        $.ajax({
            url: "manage_scholarships.php",
            type: "POST",
            data: $(this).serialize() + "&action=add",
            success: function(response) {
                alert(response);
                location.reload();
            }
        });
    });

    // Populate Edit Modal
    $(".edit-scholarship").click(function(){
        $("#editScholarshipId").val($(this).data("id"));
        $("#editScholarshipName").val($(this).data("name"));
        $("#editScholarshipDescription").val($(this).data("description"));
        $("#editScholarshipEligibility").val($(this).data("eligibility"));
        $("#editScholarshipDeadline").val($(this).data("deadline"));
    });

    // Handle Edit Scholarship
    $("#editScholarshipForm").submit(function(e){
        e.preventDefault();
        $.ajax({
            url: "manage_scholarships.php",
            type: "POST",
            data: $(this).serialize() + "&action=edit",
            success: function(response) {
                alert(response);
                location.reload();
            }
        });
    });

    // Handle Delete Scholarship
    $(".delete-scholarship").click(function(){
        if (confirm("Are you sure you want to delete this scholarship?")) {
            $.ajax({
                url: "manage_scholarships.php",
                type: "POST",
                data: { id: $(this).data("id"), action: "delete" },
                success: function(response) {
                    alert(response);
                    location.reload();
                }
            });
        }
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>