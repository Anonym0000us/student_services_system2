<?php
session_start();

$host = "localhost";
$user = "root";
$password = "";
$dbname = "student_services_db";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch scholarship statistics
$totalApplications = $conn->query("SELECT COUNT(*) AS total FROM scholarships")->fetch_assoc()['total'];
$approvedApplications = $conn->query("SELECT COUNT(*) AS total FROM scholarships WHERE status = 'approved'")->fetch_assoc()['total'];
$pendingApplications = $conn->query("SELECT COUNT(*) AS total FROM scholarships WHERE status = 'pending'")->fetch_assoc()['total'];
$rejectedApplications = $conn->query("SELECT COUNT(*) AS total FROM scholarships WHERE status = 'rejected'")->fetch_assoc()['total'];

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scholarship Admin Dashboard</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .main-content {
            margin-left: 270px;
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
        .stats {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            flex: 1 1 calc(25% - 20px);
            border-radius: 5px;
            text-align: center;
            box-shadow: 2px 2px 10px rgba(0,0,0,0.1);
        }
        .stat-card i {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .stat-card span {
            display: block;
            font-size: 2rem;
            font-weight: bold;
        }
        .approved {
            background-color: #28a745;
            color: white;
        }
        .pending {
            background-color: #ffc107;
            color: white;
        }
        .rejected {
            background-color: #dc3545;
            color: white;
        }
        .quick-actions {
            display: flex;
            gap: 10px;
        }
        .quick-actions button {
            flex: 1;
            padding: 15px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            border-radius: 5px;
            background: #00509E;
            color: white;
            transition: background 0.3s, color 0.3s;
        }
        .quick-actions button:hover {
            background: gold;
            color: black;
        }
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 10px;
            }
            .stat-card {
                flex: 1 1 calc(50% - 20px);
            }
            .quick-actions button {
                font-size: 14px;
                padding: 10px;
            }
        }
        @media (max-width: 480px) {
            .stat-card {
                flex: 1 1 100%;
            }
        }
    </style>
</head>
<body>
    <?php include 'admin_scholarship_header.php'; ?>
    <div class="main-content">
        <div class="header">
            <h2>Scholarship Dashboard</h2>
        </div>
        <section class="stats">
            <div class="stat-card">
                <i class="fas fa-file-alt"></i>
                Total Applications
                <span><?php echo $totalApplications; ?></span>
            </div>
            <div class="stat-card approved">
                <i class="fas fa-check-circle"></i>
                Approved
                <span><?php echo $approvedApplications; ?></span>
            </div>
            <div class="stat-card pending">
                <i class="fas fa-clock"></i>
                Pending
                <span><?php echo $pendingApplications; ?></span>
            </div>
            <div class="stat-card rejected">
                <i class="fas fa-times-circle"></i>
                Rejected
                <span><?php echo $rejectedApplications; ?></span>
            </div>
        </section>
        <section class="quick-actions">
            <button onclick="location.href='admin_manage_scholarships.php'"><i class="fas fa-tasks"></i> Manage Applications</button>
            <button onclick="location.href='approved_scholars.php'"><i class="fas fa-user-check"></i> View Approved Scholars</button>
        </section>
    </div>
</body>
</html>