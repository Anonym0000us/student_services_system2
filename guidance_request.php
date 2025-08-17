<?php
include 'config.php';
session_start();

// If form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_SESSION['user_id']; // Use user_id as student_id
    $appointment_date = $_POST['appointment_date'];
    $reason = $_POST['reason'];
    $guidance_admin_id = 'Guidance01'; // Assuming the guidance admin's user_id is 'Guidance01'

    // Insert query
    $insertQuery = "INSERT INTO appointments (student_id, user_id, appointment_date, reason) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("ssss", $student_id, $guidance_admin_id, $appointment_date, $reason);

    if ($stmt->execute()) {
        $success_message = "Guidance request submitted successfully";
    } else {
        $error_message = "Submission failed. Try again!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Guidance Request</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
        }

        .main-content {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - 56px); /* Adjusting for the navbar height */
        }

        .container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            text-align: center;
        }

        h2 {
            margin-bottom: 20px;
            color: #333333;
        }

        .success-message {
            color: green;
            margin-bottom: 15px;
        }

        .error-message {
            color: red;
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            text-align: left;
        }

        input, textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #dddddd;
            border-radius: 4px;
            font-size: 16px;
        }

        button {
            padding: 10px 20px;
            background-color: #007bff;
            color: #ffffff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <?php include 'student_header.php'; ?>
    <div class="main-content">
        <div class="container">
            <h2>Submit Guidance Request</h2>

            <?php if (isset($success_message)): ?>
                <p class="success-message"><?= htmlspecialchars($success_message) ?></p>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <p class="error-message"><?= htmlspecialchars($error_message) ?></p>
            <?php endif; ?>

            <form method="POST">
                <label for="appointment_date">Appointment Date:</label>
                <input type="datetime-local" id="appointment_date" name="appointment_date" required>
                <label for="reason">Reason:</label>
                <textarea id="reason" name="reason" required></textarea>
                <button type="submit">Submit Request</button>
            </form>
        </div>
    </div>
</body>
</html>