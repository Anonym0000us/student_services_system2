<?php
include 'config.php'; 
session_start();


// If form is submitted to delete a request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_request'])) {
    $request_id = $_POST['request_id'];

    // Validate received data
    if (!empty($request_id)) {
        // Delete query
        $deleteQuery = "DELETE FROM appointments WHERE id = ?";
        $stmt = $conn->prepare($deleteQuery);
        $stmt->bind_param("i", $request_id);

        if ($stmt->execute()) {
            header("Location: guidance_list_admin.php?success=Guidance request deleted successfully");
            exit();
        } else {
            $error_message = "Delete failed. Try again!";
        }
    } else {
        $error_message = "Invalid data. Please try again.";
    }
}
?>