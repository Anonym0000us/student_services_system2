<?php
session_start();

// Database connection
$host = "localhost";
$user = "root";
$password = "";
$dbname = "student_services_db";
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// ✅ FIX: Corrected `REQUEST_METHOD`
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id']) && isset($_POST['status'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];
    $remarks = trim($_POST['remarks'] ?? '');

    // ✅ FIX: Ensure ID is a valid integer
    if (!filter_var($id, FILTER_VALIDATE_INT)) {
        die("Invalid application ID.");
    }

    // ✅ FIX: Secure Prepared Statement
    $sql = "UPDATE scholarship_applications SET status = ?, remarks = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $status, $remarks, $id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Application updated successfully!";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['message'] = "Error updating record: " . $stmt->error;
        $_SESSION['msg_type'] = "danger";
    }

    $stmt->close();
    $conn->close();

    // ✅ Redirect back with a success/error message
    header("Location: manage_applications.php");
    exit();
} else {
    die("Invalid request.");
}
?>
