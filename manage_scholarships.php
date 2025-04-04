<?php
session_start();

$host = "localhost";
$user = "root";
$password = "";
$dbname = "student_services_db";

// Create connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if action and id are set
if (isset($_POST['action']) && isset($_POST['id'])) {
    $action = $_POST['action'];
    $id = intval($_POST['id']);

    if ($action == 'delete') {
        // Delete the application
        $sql = "DELETE FROM scholarship_applications WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                echo "Application deleted successfully.";
            } else {
                echo "Error deleting application: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    } elseif ($action == 'approve') {
        // Approve the application
        $sql = "UPDATE scholarship_applications SET status = 'approved' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                echo "Application approved successfully.";
            } else {
                echo "Error approving application: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    } elseif ($action == 'reject') {
        // Reject the application
        $sql = "UPDATE scholarship_applications SET status = 'rejected' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                echo "Application rejected successfully.";
            } else {
                echo "Error rejecting application: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    } else {
        echo "Invalid action.";
    }
} else {
    echo "Invalid request.";
}

$conn->close();
?>