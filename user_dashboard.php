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

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Error: User not logged in.");
}

$user_id = $_SESSION['user_id'];

// Fetch user grievances
$result = $conn->query("SELECT * FROM grievances WHERE user_id = '$user_id' ORDER BY submission_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        /* General Styles */
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .table th, .table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .table th {
            background-color: #003366;
            color: white;
        }

        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 14px;
            color: white;
        }

        .badge-success {
            background-color: green;
        }

        .badge-danger {
            background-color: red;
        }

        .badge-warning {
            background-color: orange;
        }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar">
    <div class="logo">NEUST Gabaldon</div>
    <ul class="nav-links">
        <li><a href="student_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="student_announcement.php"><i class="fas fa-bullhorn"></i> Announcements</a></li>

        <!-- Services Dropdown -->
        <li class="dropdown">
            <a href="#"><i class="fas fa-list"></i> Services</a>
            <div class="dropdown-content">
                <div class="sub-dropdown">
                    <a href="#">🏠 Dormitory</a>
                    <div class="sub-dropdown-content">
                        <a href="rooms.php">🏠 Apply</a>
                        <a href="check_applications_status.php">✅ Check Status</a>
                        <a href="dormitory_rules.php">📜 Rules</a>
                    </div>
                </div>

                <div class="sub-dropdown">
                    <a href="#">🎓 Scholarship</a>
                    <div class="sub-dropdown-content">
                        <a href="scholarships.php">📝 Apply</a>
                        <a href="track_applications.php">📊 Status</a>
                        <a href="scholarship_resources.php">📚 Resources</a>
                    </div>
                </div>

                <div class="sub-dropdown">
                    <a href="#">🗣️ Guidance</a>
                    <div class="sub-dropdown-content">
                        <a href="guidance_request.php">📅 Book Appointment</a>
                        <a href="student_status_appoinments.php">📋 Appointment Status</a>
                        <a href="guidance_counseling.php">🗣️ Counseling</a>
                        <a href="guidance_resources.php">📖 Resources</a>
                    </div>
                </div>

                <div class="sub-dropdown">
                    <a href="#">📜 Registrar</a>
                    <div class="sub-dropdown-content">
                        <a href="create_student_tor_request.php">📄 TOR Request</a>
                        <a href="tor_list_student.php">📊 Track TOR Status</a>
                        <a href="student_profile.php">👨‍🎓 Student Profiling</a>
                    </div>
                </div>

                <div class="sub-dropdown">
                    <a href="#">⚖️ Grievance</a>
                    <div class="sub-dropdown-content">
                        <a href="grievance_filing.php">📢 File Complaint</a>
                        <a href="grievance_appointment.php">📅 Set Appointment</a>
                    </div>
                </div>
            </div>
        </li>

        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</nav>

<div class="container">