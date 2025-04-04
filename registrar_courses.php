<?php
include 'config.php'; 
session_start();



// Fetch all courses
$query = "SELECT * FROM courses";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses</title>
    <style>
        h2 {
            text-align: center;
            color: #333333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #dddddd;
            text-align: left;
        }
        th {
            background-color: #f4f4f9;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <?php include 'registrar_admin_header.php'; ?>
    <div class="main-content">
        <h2>Courses</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Course Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['course_id']) ?></td>
                        <td><?= htmlspecialchars($row['course_name']) ?></td>
                        <td><?= htmlspecialchars($row['description']) ?></td>
                        <td>
                            <a class="action-link" href="view_course.php?id=<?= htmlspecialchars($row['course_id']) ?>">View</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>