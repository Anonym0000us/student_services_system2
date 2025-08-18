<?php
// TEMPORARY SCRIPT TO RESET POWERADMIN PASSWORD
// DELETE THIS FILE AFTER USE FOR SECURITY

include 'config.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if ($newPassword === $confirmPassword) {
        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Update PowerAdmin password
        $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE role = 'Power Admin'");
        $stmt->bind_param("s", $hashedPassword);
        
        if ($stmt->execute()) {
            $successMessage = "PowerAdmin password updated successfully! New password: " . $newPassword;
        } else {
            $errorMessage = "Error updating password: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $errorMessage = "Passwords do not match!";
    }
}

// Get current PowerAdmin info
$query = "SELECT user_id, first_name, last_name, email FROM users WHERE role = 'Power Admin'";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset PowerAdmin Password</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .alert { padding: 15px; margin: 20px 0; border-radius: 5px; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="password"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        button { background-color: #007bff; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        button:hover { background-color: #0056b3; }
        .user-info { background-color: #e9ecef; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .warning { background-color: #fff3cd; color: #856404; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Reset PowerAdmin Password</h1>
        
        <div class="warning">
            <strong>⚠️ SECURITY WARNING:</strong> This is a temporary script. Delete this file immediately after use!
        </div>
        
        <?php if (isset($successMessage)): ?>
            <div class="alert alert-success"><?php echo $successMessage; ?></div>
        <?php endif; ?>
        
        <?php if (isset($errorMessage)): ?>
            <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
        <?php endif; ?>
        
        <h3>Current PowerAdmin Users:</h3>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="user-info">
                    <strong>User ID:</strong> <?php echo htmlspecialchars($row['user_id']); ?><br>
                    <strong>Name:</strong> <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?><br>
                    <strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No PowerAdmin users found.</p>
        <?php endif; ?>
        
        <h3>Reset Password:</h3>
        <form method="POST">
            <div class="form-group">
                <label for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit">Reset PowerAdmin Password</button>
        </form>
        
        <hr style="margin: 30px 0;">
        <p><strong>Instructions:</strong></p>
        <ol>
            <li>Enter a new password above</li>
            <li>Click "Reset PowerAdmin Password"</li>
            <li>Use the new password to login</li>
            <li><strong>DELETE THIS FILE IMMEDIATELY</strong></li>
        </ol>
    </div>
</body>
</html>