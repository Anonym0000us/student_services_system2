<?php
session_start();
$host = "localhost";
$user = "root";
$password = "";
$dbname = "student_services_db";

// Connect to the database
$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
}

$response = ["status" => "error", "message" => "An unexpected error occurred."];

if (isset($_POST['scholarship_id']) && isset($_SESSION['user_id'])) {
    $scholarship_id = intval($_POST['scholarship_id']);
    $user_id = $_SESSION['user_id'];

    // Check if the user_id exists in the users table
    $user_check_sql = "SELECT * FROM users WHERE user_id = ?";
    $user_check_stmt = $conn->prepare($user_check_sql);
    if (!$user_check_stmt) {
        $response["message"] = "User check prepare failed: (" . $conn->errno . ") " . $conn->error;
        error_log($response["message"]);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    $user_check_stmt->bind_param("s", $user_id);
    if (!$user_check_stmt->execute()) {
        $response["message"] = "User check execute failed: (" . $user_check_stmt->errno . ") " . $user_check_stmt->error;
        error_log($response["message"]);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    $user_check_result = $user_check_stmt->get_result();
    if ($user_check_result->num_rows == 0) {
        $response["message"] = "User ID does not exist.";
        error_log($response["message"]);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    $user_check_stmt->close();

    // Check if the user has already applied for the scholarship
    $check_sql = "SELECT * FROM scholarship_applications WHERE scholarship_id = ? AND user_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    if (!$check_stmt) {
        $response["message"] = "Prepare failed: (" . $conn->errno . ") " . $conn->error;
        error_log($response["message"]);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    $check_stmt->bind_param("is", $scholarship_id, $user_id);
    if (!$check_stmt->execute()) {
        $response["message"] = "Execute failed: (" . $check_stmt->errno . ") " . $check_stmt->error;
        error_log($response["message"]);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $response["message"] = "You have already applied for this scholarship.";
    } else {
        // Insert new application with application_date and status
        $sql = "INSERT INTO scholarship_applications (scholarship_id, user_id, application_date, status) VALUES (?, ?, NOW(), 'pending')";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $response["message"] = "Prepare failed: (" . $conn->errno . ") " . $conn->error;
            error_log($response["message"]);
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
        $stmt->bind_param("is", $scholarship_id, $user_id);

        if ($stmt->execute()) {
            $response["status"] = "success";
            $response["message"] = "Your application has been submitted successfully.";
        } else {
            $response["message"] = "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
            error_log($response["message"]);
        }
        $stmt->close();
    }
    $check_stmt->close();
}

$conn->close();
header('Content-Type: application/json');
echo json_encode($response);
?>