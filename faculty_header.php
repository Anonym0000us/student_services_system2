<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard</title>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

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

        .dropdown.active .dropdown-content {
            display: block;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="logo">NEUST Gabaldon - Faculty</div>
    <ul class="nav-links">
        <li><a href="faculty_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="faculty_announcement.php"><i class="fas fa-bullhorn"></i> Announcements</a></li>

        <li class="dropdown">
            <a href="#"><i class="fas fa-book"></i> Academic</a>
            <div class="dropdown-content">
                <a href="faculty_courses.php">📚 My Courses</a>
                <a href="faculty_schedules.php">📅 Class Schedules</a>
                <a href="faculty_reports.php">📄 Reports</a>
            </div>
        </li>

        <li class="dropdown">
            <a href="#"><i class="fas fa-users"></i> Student Management</a>
            <div class="dropdown-content">
                <a href="student_list.php">👨‍🎓 Student List</a>
                <a href="grade_submission.php">📝 Submit Grades</a>
                <a href="advising_appointments.php">📅 Advising</a>
            </div>
        </li>
        
        <li><a href="login.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
    </ul>
</nav>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const dropdowns = document.querySelectorAll(".dropdown > a");
        dropdowns.forEach(dropdown => {
            dropdown.addEventListener("click", function (event) {
                event.preventDefault();
                const dropdownContent = this.nextElementSibling;
                const allDropdowns = document.querySelectorAll('.dropdown');
                allDropdowns.forEach(d => {
                    if (d !== this.parentElement) {
                        d.classList.remove("active");
                        d.querySelector(".dropdown-content").style.display = "none";
                    }
                });
                this.parentElement.classList.toggle("active");
                dropdownContent.style.display = dropdownContent.style.display === "block" ? "none" : "block";
            });
        });
    });
</script>

</body>
</html>
