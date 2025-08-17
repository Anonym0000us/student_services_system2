<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - NEUST Gabaldon</title>
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
        
        .admin-info {
            color: var(--neust-white);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .admin-avatar {
            width: 40px;
            height: 40px;
            background: var(--neust-gold);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--neust-blue);
            font-weight: 600;
            font-size: 1.1rem;
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
            min-width: 250px;
        }
        
        .dropdown-item {
            color: var(--neust-blue);
            transition: all 0.3s ease;
            border-radius: 8px;
            margin: 2px 8px;
            padding: 10px 15px;
        }
        
        .dropdown-item:hover {
            background: var(--neust-gold);
            color: var(--neust-blue);
            transform: scale(1.02);
        }
        
        .dropdown-header {
            color: var(--neust-blue);
            font-weight: 600;
            font-size: 1rem;
        }
        
        .navbar-toggler {
            border: none;
            color: var(--neust-white);
        }
        
        .navbar-toggler:focus {
            box-shadow: none;
        }
        
        .quick-stats {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 15px;
            margin: 10px 0;
        }
        
        .quick-stats .stat-item {
            text-align: center;
            color: var(--neust-white);
        }
        
        .quick-stats .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--neust-gold);
        }
        
        .quick-stats .stat-label {
            font-size: 0.8rem;
            opacity: 0.9;
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
            
            .admin-info {
                justify-content: center;
                margin-top: 15px;
                flex-direction: column;
            }
            
            .quick-stats {
                margin: 15px 0;
            }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <!-- Brand -->
        <a class="navbar-brand" href="scholarship_admin_dashboard.php">
            <i class="fas fa-graduation-cap"></i>
            NEUST Admin Panel
        </a>
        
        <!-- Mobile Toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Navigation Menu -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'scholarship_admin_dashboard.php') ? 'active' : '' ?>" 
                       href="scholarship_admin_dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'enhanced_admin_scholarships.php') ? 'active' : '' ?>" 
                       href="enhanced_admin_scholarships.php">
                        <i class="fas fa-graduation-cap"></i> Manage Scholarships
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'enhanced_manage_applications.php') ? 'active' : '' ?>" 
                       href="enhanced_manage_applications.php">
                        <i class="fas fa-file-alt"></i> Applications
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'approved_scholars.php') ? 'active' : '' ?>" 
                       href="approved_scholars.php">
                        <i class="fas fa-check-circle"></i> Approved Scholars
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'enhanced_scholarship_reports.php') ? 'active' : '' ?>" 
                       href="enhanced_scholarship_reports.php">
                        <i class="fas fa-chart-bar"></i> Reports
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php') ? 'active' : '' ?>" 
                       href="admin_dashboard.php">
                        <i class="fas fa-cogs"></i> System Admin
                    </a>
                </li>
            </ul>
            
            <!-- Quick Stats (Visible on larger screens) -->
            <div class="quick-stats d-none d-lg-block">
                <div class="row">
                    <div class="col-3">
                        <div class="stat-item">
                            <?php
                            // Get pending applications count
                            $pending_count = 0;
                            $host = "localhost";
                            $user = "root";
                            $password = "";
                            $dbname = "student_services_db";
                            
                            $conn = new mysqli($host, $user, $password, $dbname);
                            if (!$conn->connect_error) {
                                $count_sql = "SELECT COUNT(*) as count FROM scholarship_applications WHERE status = 'pending'";
                                $count_result = $conn->query($count_sql);
                                if ($count_result) {
                                    $pending_count = $count_result->fetch_assoc()['count'];
                                }
                                $conn->close();
                            }
                            ?>
                            <div class="stat-number"><?= $pending_count ?></div>
                            <div class="stat-label">Pending</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="stat-item">
                            <?php
                            // Get total scholarships count
                            $scholarships_count = 0;
                            $conn = new mysqli($host, $user, $password, $dbname);
                            if (!$conn->connect_error) {
                                $count_sql = "SELECT COUNT(*) as count FROM scholarships WHERE status = 'active'";
                                $count_result = $conn->query($count_sql);
                                if ($count_result) {
                                    $scholarships_count = $count_result->fetch_assoc()['count'];
                                }
                                $conn->close();
                            }
                            ?>
                            <div class="stat-number"><?= $scholarships_count ?></div>
                            <div class="stat-label">Active</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Admin Info & Notifications -->
            <div class="admin-info">
                <!-- Notifications Dropdown -->
                <div class="dropdown position-relative">
                    <a class="nav-link" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-bell"></i>
                        <?php
                        // Get unread notifications count for admin
                        $admin_notification_count = 0;
                        $conn = new mysqli($host, $user, $password, $dbname);
                        if (!$conn->connect_error) {
                            $count_sql = "SELECT COUNT(*) as count FROM scholarship_notifications WHERE is_read = FALSE";
                            $count_result = $conn->query($count_sql);
                            if ($count_result) {
                                $admin_notification_count = $count_result->fetch_assoc()['count'];
                            }
                            $conn->close();
                        }
                        ?>
                        <?php if ($admin_notification_count > 0): ?>
                            <span class="notification-badge"><?= $admin_notification_count ?></span>
                        <?php endif; ?>
                    </a>
                    
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header">System Notifications</h6></li>
                        <?php if ($admin_notification_count > 0): ?>
                            <li><a class="dropdown-item" href="view_admin_notifications.php">View All (<?= $admin_notification_count ?> new)</a></li>
                        <?php else: ?>
                            <li><span class="dropdown-item text-muted">No new notifications</span></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="system_logs.php"><i class="fas fa-list"></i> System Logs</a></li>
                        <li><a class="dropdown-item" href="audit_trail.php"><i class="fas fa-history"></i> Audit Trail</a></li>
                    </ul>
                </div>
                
                <!-- Admin Profile Dropdown -->
                <div class="dropdown">
                    <div class="admin-avatar" data-bs-toggle="dropdown">
                        <?php
                        $admin_initials = '';
                        if (isset($_SESSION['first_name']) && isset($_SESSION['last_name'])) {
                            $admin_initials = strtoupper(substr($_SESSION['first_name'], 0, 1) . substr($_SESSION['last_name'], 0, 1));
                        } else {
                            $admin_initials = 'A';
                        }
                        echo $admin_initials;
                        ?>
                    </div>
                    
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header">Welcome, <?= $_SESSION['first_name'] ?? 'Administrator' ?>!</h6></li>
                        <li><span class="dropdown-item text-muted"><i class="fas fa-shield-alt"></i> Role: <?= ucfirst($_SESSION['role'] ?? 'Admin') ?></span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="admin_profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                        <li><a class="dropdown-item" href="change_admin_password.php"><i class="fas fa-key"></i> Change Password</a></li>
                        <li><a class="dropdown-item" href="system_settings.php"><i class="fas fa-cog"></i> System Settings</a></li>
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