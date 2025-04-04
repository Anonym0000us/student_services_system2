<?php
include 'config.php'; 
session_start();

// If form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $request_type = 'Transcript of Records'; // Assuming this is the type for TOR

    // Validate student_id
    $query = "SELECT * FROM users WHERE user_id = ? AND role = 'student'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $student_result = $stmt->get_result();

    if ($student_result->num_rows > 0) {
        // Insert into requests table
        $insertQuery = "INSERT INTO requests (student_id, request_type, status) VALUES (?, ?, 'Pending')";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("ss", $student_id, $request_type);

        if ($stmt->execute()) {
            header("Location: tor_list_student.php?success=TOR request created successfully");
            exit();
        } else {
            $error_message = "Request creation failed. Try again!";
        }
    } else {
        $error_message = "Invalid Student ID";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create TOR Request</title>
    <link rel="stylesheet" href="styles.css">
    <style>
       
        .container {
            margin-left:500px;
            margin-top: 150px;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            width: 400px;
        }
        h2 {
            text-align: center;
            color: #333333;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-bottom: 5px;
            color: #555555;
        }
        input, select, button {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #dddddd;
            border-radius: 4px;
            font-size: 16px;
        }
        button {
            background-color: #007bff;
            color: #ffffff;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #0056b3;
        }
        .error-message {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
        .cancel-link {
            text-align: center;
            display: block;
            margin-top: 10px;
            color: #007bff;
            text-decoration: none;
        }
        .cancel-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <!-- Include the header outside the container -->
    <?php include 'student_header.php'; ?>

    <div class="container">
        <h2>Create TOR Request</h2>

        <?php if (isset($error_message)): ?>
            <p class="error-message"><?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>

        <form method="POST">
            <label for="student_id">Student ID:</label>
            <input type="text" name="student_id" required>

            <button type="submit">Create Request</button>
            <a class="cancel-link" href="tor_list.php">Cancel</a>
        </form>
    </div>

</body>
</html>