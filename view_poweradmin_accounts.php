<?php
// SCRIPT TO VIEW POWERADMIN ACCOUNTS
// This script only displays information, it doesn't change anything

include 'config.php';

// Get PowerAdmin accounts
$query = "SELECT user_id, first_name, last_name, email, phone, unit, status, date_registered FROM users WHERE role = 'Power Admin'";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PowerAdmin Accounts</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f4f4f4; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #007bff; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 30px; }
        .info-box { background-color: #e9ecef; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 PowerAdmin Accounts</h1>
            <p>View your PowerAdmin account information</p>
        </div>
        
        <div class="info-box">
            <strong>Note:</strong> This script only displays information. To reset passwords, use the reset script or access the database directly.
        </div>
        
        <?php if ($result->num_rows > 0): ?>
            <h3>Found <?php echo $result->num_rows; ?> PowerAdmin account(s):</h3>
            <table>
                <tr>
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Unit</th>
                    <th>Status</th>
                    <th>Date Registered</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($row['user_id']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone'] ?: 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($row['unit'] ?: 'N/A'); ?></td>
                        <td style="color: <?php echo ($row['status'] == 'Active') ? 'green' : 'red'; ?>">
                            <?php echo htmlspecialchars($row['status']); ?>
                        </td>
                        <td><?php echo $row['date_registered']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No PowerAdmin accounts found in the database.</p>
        <?php endif; ?>
        
        <hr style="margin: 30px 0;">
        <h3>Next Steps:</h3>
        <ol>
            <li><strong>Remember your User ID</strong> from the table above</li>
            <li>Use the reset script to change your password</li>
            <li>Or access the database directly to update the password</li>
            <li>Delete these temporary scripts after use</li>
        </ol>
        
        <div style="margin-top: 20px; text-align: center;">
            <a href="reset_poweradmin_password.php" style="background-color: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">Go to Password Reset</a>
        </div>
    </div>
</body>
</html>