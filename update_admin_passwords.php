<?php
// SCRIPT TO UPDATE ADMIN PASSWORDS
// This script will update PowerAdmin and Scholarship Admin passwords

include 'config.php';

$successMessage = "";
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Update PowerAdmin password
        $powerAdminPassword = "PowerAdmin0405";
        $powerAdminHash = password_hash($powerAdminPassword, PASSWORD_DEFAULT);
        
        $stmt1 = $conn->prepare("UPDATE users SET password_hash = ? WHERE user_id = 'admin01' AND role = 'Power Admin'");
        $stmt1->bind_param("s", $powerAdminHash);
        $stmt1->execute();
        
        // Update Scholarship Admin password
        $scholarshipPassword = "Scholarship0405";
        $scholarshipHash = password_hash($scholarshipPassword, PASSWORD_DEFAULT);
        
        $stmt2 = $conn->prepare("UPDATE users SET password_hash = ? WHERE user_id = 'Scholarship01' AND role = 'Scholarship Admin'");
        $stmt2->bind_param("s", $scholarshipHash);
        $stmt2->execute();
        
        if ($stmt1->affected_rows > 0 && $stmt2->affected_rows > 0) {
            $successMessage = "Both admin passwords updated successfully!";
        } else {
            $errorMessage = "Some passwords may not have been updated. Check the database.";
        }
        
        $stmt1->close();
        $stmt2->close();
        
    } catch (Exception $e) {
        $errorMessage = "Error: " . $e->getMessage();
    }
}

// Get current admin info
$query = "SELECT user_id, first_name, last_name, email, role FROM users WHERE role IN ('Power Admin', 'Scholarship Admin') ORDER BY role";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Admin Passwords</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f4f4f4; }
        .container { max-width: 700px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .alert { padding: 15px; margin: 20px 0; border-radius: 5px; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .admin-info { background-color: #e9ecef; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .password-box { background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 10px 0; }
        button { background-color: #007bff; color: white; padding: 12px 24px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin: 10px 5px; }
        button:hover { background-color: #0056b3; }
        .warning { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Update Admin Passwords</h1>
        
        <div class="warning">
            <strong>⚠️ IMPORTANT:</strong> This script will update both PowerAdmin and Scholarship Admin passwords. Delete this file after use!
        </div>
        
        <?php if ($successMessage): ?>
            <div class="alert alert-success">
                <h3>✅ Success!</h3>
                <?php echo $successMessage; ?>
                <div class="password-box">
                    <strong>New Passwords:</strong><br>
                    <strong>PowerAdmin (admin01):</strong> PowerAdmin0405<br>
                    <strong>Scholarship Admin (Scholarship01):</strong> Scholarship0405
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($errorMessage): ?>
            <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
        <?php endif; ?>
        
        <h3>Current Admin Accounts:</h3>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="admin-info">
                    <strong>User ID:</strong> <?php echo htmlspecialchars($row['user_id']); ?><br>
                    <strong>Name:</strong> <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?><br>
                    <strong>Email:</strong> <?php echo htmlspecialchars($row['email']); ?><br>
                    <strong>Role:</strong> <?php echo htmlspecialchars($row['role']); ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No admin accounts found.</p>
        <?php endif; ?>
        
        <h3>Update Passwords:</h3>
        <p>This will set the following passwords:</p>
        <ul>
            <li><strong>PowerAdmin (admin01):</strong> PowerAdmin0405</li>
            <li><strong>Scholarship Admin (Scholarship01):</strong> Scholarship0405</li>
        </ul>
        
        <form method="POST" onsubmit="return confirm('Are you sure you want to update both admin passwords?');">
            <button type="submit">Update Admin Passwords</button>
        </form>
        
        <hr style="margin: 30px 0;">
        <h3>After Update:</h3>
        <ol>
            <li>Login to PowerAdmin using: <strong>admin01</strong> / <strong>PowerAdmin0405</strong></li>
            <li>Login to Scholarship Admin using: <strong>Scholarship01</strong> / <strong>Scholarship0405</strong></li>
            <li><strong>DELETE THIS FILE IMMEDIATELY</strong></li>
        </ol>
        
        <div style="margin-top: 20px; text-align: center;">
            <a href="view_poweradmin_accounts.php" style="background-color: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px;">View Accounts</a>
            <a href="reset_poweradmin_password.php" style="background-color: #ffc107; color: black; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px;">Reset Script</a>
        </div>
    </div>
</body>
</html>