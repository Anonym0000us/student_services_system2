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

// Handle POST request actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action == 'add') {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $eligibility = $_POST['eligibility'];
        $deadline = $_POST['deadline'];
        $status = 'active';

        $stmt = $conn->prepare("INSERT INTO scholarships (name, description, eligibility, deadline, status) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sssss", $name, $description, $eligibility, $deadline, $status);
            if ($stmt->execute()) {
                echo "Scholarship added successfully!";
            } else {
                echo "Error adding scholarship: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    } elseif ($action == 'edit') {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        $eligibility = $_POST['eligibility'];
        $deadline = $_POST['deadline'];

        $stmt = $conn->prepare("UPDATE scholarships SET name = ?, description = ?, eligibility = ?, deadline = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("ssssi", $name, $description, $eligibility, $deadline, $id);
            if ($stmt->execute()) {
                echo "Scholarship updated successfully!";
            } else {
                echo "Error updating scholarship: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    } elseif ($action == 'delete') {
        $id = $_POST['id'];

        $stmt = $conn->prepare("DELETE FROM scholarships WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo "Scholarship deleted successfully.";
                } else {
                    echo "Error: Scholarship not found or already deleted.";
                }
            } else {
                echo "Error deleting scholarship: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    } else {
        echo "Invalid action.";
    }
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
        }
        .sidebar {
            width: 260px;
            height: 100vh;
            background-color: #003366;
            color: white;
            position: fixed;
            padding-top: 20px;
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
            background-color: #004080;
            border-radius: 8px;
            margin: 8px 15px;
            transition: all 0.3s ease;
        }
        .menu-item:hover,
        .menu-item.active {
            background-color: #FFD700;
            color: #003366;
        }
        .logout-btn {
            text-decoration: none;
            color: white;
            display: block;
            padding: 15px;
            font-size: 1.1rem;
            background-color: #d9534f;
            border-radius: 8px;
            text-align: center;
            margin: 30px 15px;
            transition: all 0.3s ease;
        }
        .logout-btn:hover {
            background-color: #c9302c;
        }
        .main-content {
            margin-left: 260px;
            padding: 20px;
        }
        .header {
            background: #00509E;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .card-header {
            background: #00509E;
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
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <i class="fa fa-graduation-cap"></i>
        <span>Admin Panel</span>
    </div>
    <div class="sidebar-menu">
        <a href="scholarship_admin_dashboard.php" class="menu-item" id="dashboard">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="admin_manage_scholarships.php" class="menu-item active" id="manage-scholars">
            <i class="fas fa-graduation-cap"></i> Manage Scholarships
        </a>
        <a href="manage_applications.php" class="menu-item" id="applications">
            <i class="fas fa-file-alt"></i> Applications
        </a>
        <a href="approved_scholars.php" class="menu-item" id="approved-scholars">
            <i class="fas fa-check-circle"></i> Approved Scholars
        </a>
        <a href="login.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>

<div class="main-content">
    <div class="header">
        <h2>Scholarship Management</h2>
    </div>
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addScholarshipModal">Add Scholarship</button>

    <div class="row">
        <?php while ($row = $result->fetch_assoc()): ?>
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <?= htmlspecialchars($row['name']) ?>
                </div>
                <div class="card-body">
                    <p><strong>Deadline:</strong> <?= date("F j, Y", strtotime($row['deadline'])) ?></p>
                    <p><strong>Status:</strong> <?= ucfirst($row['status']) ?></p>
                    <p><strong>Description:</strong> <?= htmlspecialchars($row['description']) ?></p>
                    <p><strong>Eligibility:</strong> <?= htmlspecialchars($row['eligibility']) ?></p>
                </div>
                <div class="card-footer">
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
                </div>
            </div>
        </div>
        <?php endwhile; ?>
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