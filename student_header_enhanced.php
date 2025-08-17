<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Services - NEUST Gabaldon</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --neust-blue: #003366;
            --neust-light-blue: #00509E;
            --neust-gold: #FFD700;
            --neust-white: #FFFFFF;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--neust-blue), var(--neust-light-blue)) !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            color: var(--neust-gold) !important;
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .navbar-nav .nav-link {
            color: var(--neust-white) !important;
            font-weight: 500;
            transition: all 0.3s ease;
            margin: 0 5px;
            border-radius: 20px;
            padding: 8px 16px;
        }
        
        .navbar-nav .nav-link:hover {
            background: var(--neust-gold);
            color: var(--neust-blue) !important;
            transform: scale(1.05);
        }
        
        .navbar-nav .nav-link.active {
            background: var(--neust-gold);
            color: var(--neust-blue) !important;
            font-weight: 600;
        }
        
        .user-info {
            color: var(--neust-white);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-avatar {
            width: 35px;
            height: 35px;
            background: var(--neust-gold);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--neust-blue);
            font-weight: 600;
        }
        
        .notification-badge {
            background: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.7rem;
            position: absolute;
            top: -5px;
            right: -5px;
        }
        
        .dropdown-menu {
            background: var(--neust-white);
            border: none;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border-radius: 15px;
        }
        
        .dropdown-item {
            color: var(--neust-blue);
            transition: all 0.3s ease;
            border-radius: 8px;
            margin: 2px 8px;
        }
        
        .dropdown-item:hover {
            background: var(--neust-gold);
            color: var(--neust-blue);
            transform: scale(1.02);
        }
        
        .navbar-toggler {
            border: none;
            color: var(--neust-white);
        }
        
        .navbar-toggler:focus {
            box-shadow: none;
        }
        
        @media (max-width: 768px) {
            .navbar-nav {
                text-align: center;
                padding: 20px 0;
            }
            
            .navbar-nav .nav-link {
                margin: 5px 0;
                padding: 12px 20px;
            }
            
            .user-info {
                justify-content: center;
                margin-top: 15px;
            }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <!-- Brand -->
        <a class="navbar-brand" href="student_dashboard.php">
            <i class="fas fa-graduation-cap"></i>
            NEUST Gabaldon
        </a>
        
        <!-- Mobile Toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Navigation Menu -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'student_dashboard.php') ? 'active' : '' ?>" 
                       href="student_dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'scholarships.php') ? 'active' : '' ?>" 
                       href="scholarships.php">
                        <i class="fas fa-graduation-cap"></i> Scholarships
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'track_applications.php') ? 'active' : '' ?>" 
                       href="track_applications.php">
                        <i class="fas fa-file-alt"></i> My Applications
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'student_profile.php') ? 'active' : '' ?>" 
                       href="student_profile.php">
                        <i class="fas fa-user"></i> Profile
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'student_payments.php') ? 'active' : '' ?>" 
                       href="student_payments.php">
                        <i class="fas fa-credit-card"></i> Payments
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'student_announcement.php') ? 'active' : '' ?>" 
                       href="student_announcement.php">
                        <i class="fas fa-bullhorn"></i> Announcements
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'guidance_request.php') ? 'active' : '' ?>" 
                       href="guidance_request.php">
                        <i class="fas fa-hands-helping"></i> Guidance
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'submit_grievance.php') ? 'active' : '' ?>" 
                       href="submit_grievance.php">
                        <i class="fas fa-exclamation-triangle"></i> Grievances
                    </a>
                </li>
            </ul>
            
            <!-- User Info & Notifications -->
            <div class="user-info">
                <!-- Notifications Dropdown -->
                <div class="dropdown position-relative">
                    <a class="nav-link" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-bell"></i>
                        <?php
                        // Get unread notifications count
                        $notification_count = 0;
                        if (isset($_SESSION['user_id'])) {
                            $host = "localhost";
                            $user = "root";
                            $password = "";
                            $dbname = "student_services_db";
                            
                            $conn = new mysqli($host, $user, $password, $dbname);
                            if (!$conn->connect_error) {
                                $user_id = $_SESSION['user_id'];
                                $count_sql = "SELECT COUNT(*) as count FROM scholarship_notifications WHERE user_id = ? AND is_read = FALSE";
                                $count_stmt = $conn->prepare($count_sql);
                                if ($count_stmt) {
                                    $count_stmt->bind_param("s", $user_id);
                                    $count_stmt->execute();
                                    $count_result = $count_stmt->get_result();
                                    $notification_count = $count_result->fetch_assoc()['count'];
                                    $count_stmt->close();
                                }
                                $conn->close();
                            }
                        }
                        ?>
                        <?php if ($notification_count > 0): ?>
                            <span class="notification-badge"><?= $notification_count ?></span>
                        <?php endif; ?>
                    </a>
                    
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header">Notifications</h6></li>
                        <?php if ($notification_count > 0): ?>
                            <li><a class="dropdown-item" href="view_notifications.php">View All (<?= $notification_count ?> new)</a></li>
                        <?php else: ?>
                            <li><span class="dropdown-item text-muted">No new notifications</span></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <!-- User Profile Dropdown -->
                <div class="dropdown">
                    <div class="user-avatar" data-bs-toggle="dropdown">
                        <?php
                        $user_initials = '';
                        if (isset($_SESSION['first_name']) && isset($_SESSION['last_name'])) {
                            $user_initials = strtoupper(substr($_SESSION['first_name'], 0, 1) . substr($_SESSION['last_name'], 0, 1));
                        } else {
                            $user_initials = 'U';
                        }
                        echo $user_initials;
                        ?>
                    </div>
                    
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header">Welcome, <?= $_SESSION['first_name'] ?? 'Student' ?>!</h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="student_profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                        <li><a class="dropdown-item" href="change_password.php"><i class="fas fa-key"></i> Change Password</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- Page Content Container -->
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <!-- Page content will be inserted here -->