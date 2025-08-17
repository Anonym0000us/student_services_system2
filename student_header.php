<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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
    <title>Student Dashboard</title>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <style>
        /* General Styles */
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        /* Navigation Bar */
        .navbar {
            background-color: #003366;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0px 3px 10px rgba(0, 0, 0, 0.2);
        }

        .logo {
            font-size: 22px;
            font-weight: bold;
            color: gold;
        }

        .nav-links {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
        }

        .nav-links li {
            position: relative;
            margin: 0 15px;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 16px;
            padding: 10px 15px;
            display: block;
            transition: 0.3s ease-in-out;
            border-radius: 5px;
        }

        .nav-links a:hover {
            background-color: gold;
            color: #003366;
        }

        /* Dropdown Menu */
        .dropdown {
            position: relative;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: white;
            width: 220px;
            left: 0;
            top: 40px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            padding: 10px 0;
            z-index: 1000;
        }

        .dropdown-content a {
            display: block;
            color: black;
            padding: 10px 20px;
            text-decoration: none;
            font-size: 15px;
            transition: 0.3s ease-in-out;
        }

        .dropdown-content a:hover {
            background-color: #003366;
            color: white;
        }

        /* Show dropdown on click */
        .dropdown.active .dropdown-content {
            display: block;
        }

        /* Sub-dropdown (Second Level) */
        .sub-dropdown {
            position: relative;
        }

        .sub-dropdown-content {
            display: none;
            position: absolute;
            background-color: white;
            width: 220px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            padding: 10px 0;
            z-index: 1000;
            transition: transform 0.3s ease-in-out;
        }

        /* Normal dropdown placement */
        .sub-dropdown-content {
            left: 100%;
            top: 0;
        }

        /* Adjust if near right edge */
        @media (min-width: 768px) {
            .sub-dropdown-content {
                left: auto;
                right: -100%;
            }

            .sub-dropdown:hover .sub-dropdown-content {
                transform: translateX(-100%);
            }
        }

        .sub-dropdown.active > .sub-dropdown-content {
            display: block;
        }

        /* Dropdown Arrow */
        .nav-links li a i {
            margin-left: 5px;
        }

        /* Arrows for dropdowns */
        .dropdown > a::after,
        .sub-dropdown > a::after {
            content: ' ▼';
            font-size: 0.8em;
        }

        /* Active dropdown indicator */
        .dropdown.active > a::after,
        .sub-dropdown.active > a::after {
            content: ' ▲';
        }

        /* Responsive Menu */
        @media (max-width: 768px) {
            .nav-links {
                flex-direction: column;
                background: #003366;
                position: absolute;
                width: 100%;
                left: 0;
                top: 60px;
                display: none;
                padding: 15px;
            }

            .nav-links li {
                text-align: center;
                margin-bottom: 10px;
            }

            .nav-links.active {
                display: block;
            }

            .dropdown-content {
                width: 100%;
                position: relative;
                left: 0;
                top: 0;
            }

            .sub-dropdown-content {
                position: relative;
                left: 0;
                width: 100%;
            }
        }

        /* New Additions */
        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .notifications {
            position: relative;
            cursor: pointer;
        }

        .notifications i {
            color: white;
            font-size: 18px;
            padding: 10px;
            border-radius: 50%;
            transition: 0.3s ease;
        }

        .notifications i:hover {
            background-color: gold;
            color: #003366;
        }

        .notification-badge {
            position: absolute;
            top: 5px;
            right: 5px;
            background-color: #ff4444;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 8px 15px;
            border-radius: 25px;
            transition: 0.3s ease;
        }

        .user-profile:hover {
            background-color: gold;
            color: #003366;
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            background-color: gold;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #003366;
            font-weight: bold;
            font-size: 14px;
        }

        .user-info {
            color: white;
            font-size: 14px;
        }

        .user-info .user-name {
            font-weight: bold;
            margin-bottom: 2px;
        }

        .user-info .user-role {
            font-size: 12px;
            opacity: 0.8;
        }

        .profile-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            min-width: 200px;
            display: none;
            z-index: 1000;
            margin-top: 5px;
        }

        .profile-dropdown.active {
            display: block;
        }

        .profile-dropdown a {
            color: #333;
            padding: 12px 20px;
            display: block;
            text-decoration: none;
            border-bottom: 1px solid #eee;
            transition: 0.3s ease;
        }

        .profile-dropdown a:last-child {
            border-bottom: none;
        }

        .profile-dropdown a:hover {
            background-color: #f8f9fa;
            color: #003366;
        }

        .profile-dropdown .dropdown-header {
            background-color: #003366;
            color: white;
            padding: 15px 20px;
            border-radius: 8px 8px 0 0;
            font-weight: bold;
        }

        .quick-actions {
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            min-width: 250px;
            display: none;
            z-index: 1000;
            margin-top: 5px;
        }

        .quick-actions.active {
            display: block;
        }

        .quick-actions .action-item {
            padding: 12px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #333;
            text-decoration: none;
            transition: 0.3s ease;
        }

        .quick-actions .action-item:last-child {
            border-bottom: none;
        }

        .quick-actions .action-item:hover {
            background-color: #f8f9fa;
            color: #003366;
        }

        .quick-actions .action-header {
            background-color: #003366;
            color: white;
            padding: 15px 20px;
            border-radius: 8px 8px 0 0;
            font-weight: bold;
            text-align: center;
        }

        @media (max-width: 768px) {
            .header-right {
                flex-direction: column;
                gap: 10px;
                margin-top: 15px;
            }
            
            .user-profile {
                justify-content: center;
            }
            
            .profile-dropdown,
            .quick-actions {
                position: relative;
                top: auto;
                left: auto;
                transform: none;
                margin-top: 10px;
            }
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
                        <a href="student_payments.php">💳 Dormitory Payments</a>
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
                        <a href="student_status_appointments.php">📋 Appointment Status</a>
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

        <li><a href="login.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>

    <!-- Header Right Section -->
    <div class="header-right">
        <!-- Notifications -->
        <div class="notifications" id="notifications">
            <i class="fas fa-bell"></i>
            <?php if (isset($notification_count) && $notification_count > 0): ?>
                <span class="notification-badge"><?= $notification_count ?></span>
            <?php endif; ?>
        </div>

        <!-- User Profile -->
        <div class="user-profile" id="userProfile">
            <div class="user-avatar">
                <?php
                $user_initials = '';
                if (isset($_SESSION['first_name']) && isset($_SESSION['last_name'])) {
                    $user_initials = strtoupper(substr($_SESSION['first_name'], 0, 1) . substr($_SESSION['last_name'], 0, 1));
                } else {
                    $user_initials = 'S';
                }
                echo $user_initials;
                ?>
            </div>
            <div class="user-info">
                <div class="user-name"><?= $_SESSION['first_name'] ?? 'Student' ?></div>
                <div class="user-role">Student</div>
            </div>
        </div>
    </div>
</nav>

<!-- Profile Dropdown -->
<div class="profile-dropdown" id="profileDropdown">
    <div class="dropdown-header">
        <i class="fas fa-user"></i> Profile Menu
    </div>
    <a href="student_profile.php"><i class="fas fa-user-edit"></i> Edit Profile</a>
    <a href="change_password.php"><i class="fas fa-key"></i> Change Password</a>
    <a href="student_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="login.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<!-- Quick Actions Panel -->
<div class="quick-actions" id="quickActions">
    <div class="action-header">
        <i class="fas fa-bolt"></i> Quick Actions
    </div>
    <a href="scholarships.php" class="action-item">
        <i class="fas fa-graduation-cap"></i> Apply for Scholarship
    </a>
    <a href="rooms.php" class="action-item">
        <i class="fas fa-bed"></i> Apply for Dormitory
    </a>
    <a href="guidance_request.php" class="action-item">
        <i class="fas fa-calendar"></i> Book Guidance Appointment
    </a>
    <a href="create_student_tor_request.php" class="action-item">
        <i class="fas fa-file-alt"></i> Request TOR
    </a>
</div>

<script>
    // Mobile Menu Toggle
    document.addEventListener("DOMContentLoaded", function () {
        const menuToggle = document.querySelector(".navbar");
        const navLinks = document.querySelector(".nav-links");

        menuToggle.addEventListener("click", function () {
            navLinks.classList.toggle("active");
        });

        // Toggle Dropdown on Click for Desktop and Mobile
        const dropdowns = document.querySelectorAll(".dropdown > a");
        dropdowns.forEach(dropdown => {
            dropdown.addEventListener("click", function (event) {
                event.preventDefault();
                const dropdownContent = this.nextElementSibling;
                // Hide other dropdowns
                const allDropdowns = document.querySelectorAll('.dropdown');
                allDropdowns.forEach(d => {
                    if (d !== this.parentElement) {
                        d.classList.remove("active");
                        d.querySelector(".dropdown-content").style.display = "none";
                    }
                });
                // Toggle the current dropdown
                this.parentElement.classList.toggle("active");
                dropdownContent.style.display = dropdownContent.style.display === "block" ? "none" : "block";
            });
        });

        // Toggle Sub-dropdown on Click for Mobile
        const subDropdowns = document.querySelectorAll(".sub-dropdown > a");
        subDropdowns.forEach(subDropdown => {
            subDropdown.addEventListener("click", function (event) {
                event.preventDefault();
                const subDropdownContent = this.nextElementSibling;
                // Hide other sub-dropdowns
                const allSubDropdowns = document.querySelectorAll('.sub-dropdown');
                allSubDropdowns.forEach(sd => {
                    if (sd !== this.parentElement) {
                        sd.classList.remove("active");
                        sd.querySelector(".sub-dropdown-content").style.display = "none";
                    }
                });
                // Toggle the current sub-dropdown
                this.parentElement.classList.toggle("active");
                subDropdownContent.style.display = subDropdownContent.style.display === "block" ? "none" : "block";
            });
        });

        // New Header Features Functionality
        const userProfile = document.getElementById('userProfile');
        const profileDropdown = document.getElementById('profileDropdown');
        const notifications = document.getElementById('notifications');
        const quickActions = document.getElementById('quickActions');

        // Profile Dropdown Toggle
        userProfile.addEventListener('click', function(e) {
            e.stopPropagation();
            profileDropdown.classList.toggle('active');
            quickActions.classList.remove('active');
        });

        // Notifications Click
        notifications.addEventListener('click', function(e) {
            e.stopPropagation();
            // You can add notification functionality here
            alert('Notifications feature coming soon!');
        });

        // Quick Actions Toggle (can be triggered by a button or other element)
        // For now, let's add it to the logo click
        const logo = document.querySelector('.logo');
        logo.addEventListener('click', function(e) {
            e.stopPropagation();
            quickActions.classList.toggle('active');
            profileDropdown.classList.remove('active');
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!userProfile.contains(e.target) && !profileDropdown.contains(e.target)) {
                profileDropdown.classList.remove('active');
            }
            if (!logo.contains(e.target) && !quickActions.contains(e.target)) {
                quickActions.classList.remove('active');
            }
        });

        // Close dropdowns on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                profileDropdown.classList.remove('active');
                quickActions.classList.remove('active');
            }
        });
    });
</script>

</body>
</html>
