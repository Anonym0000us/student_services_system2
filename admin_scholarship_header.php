<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}
?>
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

    /* New Admin Header Additions */
    .admin-header {
        background: linear-gradient(135deg, #003366, #004080);
        color: white;
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .admin-header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
    }

    .admin-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .admin-avatar {
        width: 50px;
        height: 50px;
        background: #FFD700;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #003366;
        font-weight: bold;
        font-size: 18px;
    }

    .admin-details h3 {
        margin: 0;
        font-size: 1.3rem;
        color: #FFD700;
    }

    .admin-details p {
        margin: 5px 0 0 0;
        opacity: 0.9;
        font-size: 0.9rem;
    }

    .quick-stats {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
    }

    .stat-card {
        background: rgba(255, 255, 255, 0.1);
        padding: 15px 20px;
        border-radius: 8px;
        text-align: center;
        min-width: 120px;
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .stat-number {
        font-size: 1.8rem;
        font-weight: bold;
        color: #FFD700;
        margin-bottom: 5px;
    }

    .stat-label {
        font-size: 0.8rem;
        opacity: 0.9;
    }

    .admin-actions {
        display: flex;
        gap: 15px;
        align-items: center;
    }

    .action-btn {
        background: #FFD700;
        color: #003366;
        border: none;
        padding: 10px 15px;
        border-radius: 20px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .action-btn:hover {
        background: #FFA500;
        transform: scale(1.05);
    }

    .notifications-btn {
        position: relative;
        background: transparent;
        border: 2px solid #FFD700;
        color: #FFD700;
    }

    .notifications-btn:hover {
        background: #FFD700;
        color: #003366;
    }

    .notification-badge {
        position: absolute;
        top: -8px;
        right: -8px;
        background: #ff4444;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        font-size: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }

    .dropdown-panel {
        position: absolute;
        top: 100%;
        right: 0;
        background: white;
        border-radius: 8px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        min-width: 250px;
        display: none;
        z-index: 1000;
        margin-top: 5px;
    }

    .dropdown-panel.active {
        display: block;
    }

    .dropdown-header {
        background: #003366;
        color: white;
        padding: 15px 20px;
        border-radius: 8px 8px 0 0;
        font-weight: bold;
        text-align: center;
    }

    .dropdown-item {
        padding: 12px 20px;
        border-bottom: 1px solid #eee;
        display: flex;
        align-items: center;
        gap: 10px;
        color: #333;
        text-decoration: none;
        transition: 0.3s ease;
    }

    .dropdown-item:last-child {
        border-bottom: none;
    }

    .dropdown-item:hover {
        background: #f8f9fa;
        color: #003366;
    }

    @media (max-width: 768px) {
        .admin-header-content {
            flex-direction: column;
            text-align: center;
        }
        
        .admin-info {
            justify-content: center;
        }
        
        .quick-stats {
            justify-content: center;
        }
        
        .admin-actions {
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .dropdown-panel {
            position: relative;
            top: auto;
            right: auto;
            margin-top: 10px;
        }
    }
</style>

<!-- Admin Header Section -->
<div class="admin-header">
    <div class="admin-header-content">
        <div class="admin-info">
            <div class="admin-avatar">
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
            <div class="admin-details">
                <h3><?= $_SESSION['first_name'] ?? 'Administrator' ?> <?= $_SESSION['last_name'] ?? '' ?></h3>
                <p><i class="fas fa-shield-alt"></i> <?= ucfirst($_SESSION['role'] ?? 'Admin') ?> - Scholarship Office</p>
            </div>
        </div>

        <div class="quick-stats">
            <div class="stat-card">
                <div class="stat-number"><?= $pending_count ?? 0 ?></div>
                <div class="stat-label">Pending Apps</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $scholarships_count ?? 0 ?></div>
                <div class="stat-label">Active Scholarships</div>
            </div>
        </div>

        <div class="admin-actions">
            <button class="action-btn notifications-btn" id="notificationsBtn">
                <i class="fas fa-bell"></i> Notifications
                <?php if (isset($admin_notification_count) && $admin_notification_count > 0): ?>
                    <span class="notification-badge"><?= $admin_notification_count ?></span>
                <?php endif; ?>
            </button>
            <a href="enhanced_admin_scholarships.php" class="action-btn">
                <i class="fas fa-plus"></i> Add Scholarship
            </a>
            <a href="enhanced_scholarship_reports.php" class="action-btn">
                <i class="fas fa-chart-bar"></i> Reports
            </a>
        </div>
    </div>
</div>

<!-- Notifications Panel -->
<div class="dropdown-panel" id="notificationsPanel">
    <div class="dropdown-header">
        <i class="fas fa-bell"></i> System Notifications
    </div>
    <?php if (isset($admin_notification_count) && $admin_notification_count > 0): ?>
        <a href="view_admin_notifications.php" class="dropdown-item">
            <i class="fas fa-envelope"></i> View All (<?= $admin_notification_count ?> new)
        </a>
    <?php else: ?>
        <div class="dropdown-item">
            <i class="fas fa-check-circle"></i> No new notifications
        </div>
    <?php endif; ?>
    <a href="system_logs.php" class="dropdown-item">
        <i class="fas fa-list"></i> System Logs
    </a>
    <a href="audit_trail.php" class="dropdown-item">
        <i class="fas fa-history"></i> Audit Trail
    </a>
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

        // New Admin Header Features
        const notificationsBtn = document.getElementById('notificationsBtn');
        const notificationsPanel = document.getElementById('notificationsPanel');

        // Notifications Panel Toggle
        notificationsBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationsPanel.classList.toggle('active');
        });

        // Close notifications panel when clicking outside
        document.addEventListener('click', function(e) {
            if (!notificationsBtn.contains(e.target) && !notificationsPanel.contains(e.target)) {
                notificationsPanel.classList.remove('active');
            }
        });

        // Close notifications panel on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                notificationsPanel.classList.remove('active');
            }
        });

        // Auto-refresh stats every 30 seconds
        setInterval(function() {
            // You can add AJAX call here to refresh stats
            // For now, just a placeholder
        }, 30000);
    });
</script>