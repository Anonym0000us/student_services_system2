<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Database connection for stats
$host = "localhost";
$user = "root";
$password = "";
$dbname = "student_services_db";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    $pending_count = 0;
    $scholarships_count = 0;
} else {
    // Get pending applications count
    $pending_count = 0;
    $count_sql = "SELECT COUNT(*) as count FROM scholarship_applications WHERE status = 'pending'";
    $count_result = $conn->query($count_sql);
    if ($count_result) {
        $pending_count = $count_result->fetch_assoc()['count'];
    }
    
    // Get total scholarships count
    $scholarships_count = 0;
    $count_sql = "SELECT COUNT(*) as count FROM scholarships WHERE status = 'active'";
    $count_result = $conn->query($count_sql);
    if ($count_result) {
        $scholarships_count = $count_result->fetch_assoc()['count'];
    }
    
    $conn->close();
}
?>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Roboto', sans-serif;
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

    @media (max-width: 768px) {
        .sidebar {
            width: 100%;
            height: auto;
            position: relative;
        }
        .sidebar-header {
            justify-content: flex-start;
            padding-left: 15px;
        }
        .sidebar-menu {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        .menu-item {
            width: 100%;
            text-align: left;
        }
        .logout-btn {
            width: 100%;
            text-align: left;
        }
    }

    /* Simple Admin Header Styles */
    .admin-header {
        background: linear-gradient(135deg, #003366, #004080);
        color: white;
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
    }

    .admin-info h3 {
        margin: 0;
        color: #FFD700;
        font-size: 1.4rem;
    }

    .admin-info p {
        margin: 5px 0 0 0;
        opacity: 0.9;
    }

    .admin-stats {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }

    .stat-item {
        text-align: center;
        background: rgba(255, 255, 255, 0.1);
        padding: 15px 20px;
        border-radius: 8px;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .stat-number {
        display: block;
        font-size: 1.8rem;
        font-weight: bold;
        color: #FFD700;
        margin-bottom: 5px;
    }

    .stat-label {
        font-size: 0.8rem;
        opacity: 0.9;
    }

    @media (max-width: 768px) {
        .admin-header {
            flex-direction: column;
            text-align: center;
        }
        
        .admin-stats {
            justify-content: center;
        }
    }
</style>

<!-- Simple Admin Header -->
<div class="admin-header">
    <div class="admin-info">
        <h3>Welcome, <?= $_SESSION['first_name'] ?? 'Administrator' ?>!</h3>
        <p>Scholarship Office Dashboard</p>
    </div>
    <div class="admin-stats">
        <div class="stat-item">
            <span class="stat-number"><?= $pending_count ?></span>
            <span class="stat-label">Pending Applications</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?= $scholarships_count ?></span>
            <span class="stat-label">Active Scholarships</span>
        </div>
    </div>
</div>

<div class="sidebar">
    <div class="sidebar-header">
        <i class="fa fa-graduation-cap"></i>
        <span>Admin Panel</span>
    </div>
    <div class="sidebar-menu">
        <a href="scholarship_admin_dashboard.php" class="menu-item" id="dashboard">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="admin_manage_scholarships.php" class="menu-item" id="manage-scholars">
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

<script>
    // Highlight the active menu item
    document.addEventListener('DOMContentLoaded', function() {
        const currentLocation = window.location.pathname.split('/').pop();
        const menuItems = document.querySelectorAll('.menu-item');
        menuItems.forEach(item => {
            if (item.getAttribute('href') === currentLocation) {
                item.classList.add('active');
            }
        });
    });
</script>