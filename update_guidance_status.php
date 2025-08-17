<?php
include 'config.php'; 
session_start();


// If form is submitted to update status
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $request_id = $_POST['request_id'];
    $status = $_POST['status'];
    $admin_message = $_POST['admin_message'];

    // Validate received data
    if (!empty($request_id) && !empty($status)) {
        // Update query
        $updateQuery = "UPDATE appointments SET status = ?, admin_message = ? WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("ssi", $status, $admin_message, $request_id);

        if ($stmt->execute()) {
            header("Location: guidance_list_admin.php?success=Guidance request updated successfully");
            exit();
        } else {
            $error_message = "Update failed. Try again!";
        }
    } else {
        $error_message = "Invalid data. Please try again.";
    }
}
?>