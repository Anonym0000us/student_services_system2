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

// CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF validate
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = "Invalid request. Please refresh and try again.";
    } else {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($title === '' || strlen($title) < 5 || $description === '' || strlen($description) < 20) {
            $message = "Please provide a valid title (min 5 chars) and description (min 20 chars).";
        } else {
            $stmt = $conn->prepare("INSERT INTO grievances (user_id, title, description, submission_date, status) VALUES (?, ?, ?, NOW(), 'pending')");
            $stmt->bind_param("sss", $user_id, $title, $description);

            if ($stmt->execute()) {
                $message = "Grievance submitted successfully.";
            } else {
                $message = "Error submitting grievance: " . $stmt->error;
            }

            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Grievance</title>
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
            max-width: 900px;
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

        .form-label {
            font-weight: bold;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            margin: 10px 0 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        .btn-primary {
            background-color: #003366;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-primary:hover {
            background-color: gold;
            color: #003366;
        }

        .alert {
            padding: 15px;
            background-color: #2196F3;
            color: white;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align:center;
        }
        .alert.error { background-color: #f44336; }
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
    <h2 class="text-center mt-4">Submit Grievance</h2>
    <?php if (isset($message)): ?>
        <div class="alert<?= (strpos($message,'Error')!==false || strpos($message,'Invalid')!==false)?' error':'' ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="POST" action="submit_grievance.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" class="form-control" id="title" name="title" required minlength="5" maxlength="120" placeholder="Brief title">
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="5" required minlength="20" placeholder="Describe your grievance..."></textarea>
        </div>
        <button type="submit" class="btn-primary">Submit</button>
    </form>
</div>

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
</style>

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
});
</script>

</body>
</html>