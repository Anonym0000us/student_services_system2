<?php
require_once 'config.php';

echo "<h2>🔄 Resetting Scholarship Admin Account</h2>";

// Step 1: Check current admin accounts
echo "<h3>📋 Current Admin Accounts:</h3>";
$check_sql = "SELECT user_id, username, first_name, last_name, email, role, status FROM users WHERE role = 'admin'";
$check_result = $conn->query($check_sql);

if ($check_result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>User ID</th><th>Username</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th>";
    echo "</tr>";
    
    while ($row = $check_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
        echo "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['role']) . "</td>";
        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No admin accounts found.</p>";
}

// Step 2: Drop existing scholarship admin accounts
echo "<h3>🗑️ Dropping Existing Scholarship Admin Accounts:</h3>";

// Delete admin accounts with scholarship-related usernames or emails
$delete_sql = "DELETE FROM users WHERE role = 'admin' AND (username LIKE '%scholarship%' OR email LIKE '%scholarship%' OR username = 'scholarship_admin')";
$delete_result = $conn->query($delete_sql);

if ($delete_result) {
    $affected_rows = $conn->affected_rows;
    echo "<p style='color: green;'>✅ Successfully deleted $affected_rows scholarship admin account(s)</p>";
} else {
    echo "<p style='color: red;'>❌ Error deleting accounts: " . $conn->error . "</p>";
}

// Step 3: Create new scholarship admin account
echo "<h3>➕ Creating New Scholarship Admin Account:</h3>";

$new_admin_data = [
    'user_id' => 'ADMIN001',
    'username' => 'scholarship_admin',
    'password' => password_hash('admin123', PASSWORD_DEFAULT),
    'first_name' => 'Scholarship',
    'last_name' => 'Administrator',
    'email' => 'scholarship@neust.edu.ph',
    'role' => 'admin',
    'status' => 'active',
    'created_at' => date('Y-m-d H:i:s')
];

// Check if user_id already exists
$check_id_sql = "SELECT user_id FROM users WHERE user_id = ?";
$check_stmt = $conn->prepare($check_id_sql);
$check_stmt->bind_param("s", $new_admin_data['user_id']);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    // Update existing user_id
    $update_sql = "UPDATE users SET 
                    username = ?, 
                    password = ?, 
                    first_name = ?, 
                    last_name = ?, 
                    email = ?, 
                    role = ?, 
                    status = ?, 
                    updated_at = ? 
                    WHERE user_id = ?";
    
    $stmt = $conn->prepare($update_sql);
    if ($stmt) {
        $stmt->bind_param("sssssssss", 
            $new_admin_data['username'],
            $new_admin_data['password'],
            $new_admin_data['first_name'],
            $new_admin_data['last_name'],
            $new_admin_data['email'],
            $new_admin_data['role'],
            $new_admin_data['status'],
            date('Y-m-d H:i:s'),
            $new_admin_data['user_id']
        );
        
        if ($stmt->execute()) {
            echo "<p style='color: green;'>✅ Successfully updated existing user ID to scholarship admin</p>";
        } else {
            echo "<p style='color: red;'>❌ Error updating account: " . $stmt->error . "</p>";
        }
        $stmt->close();
    }
} else {
    // Insert new account
    $insert_sql = "INSERT INTO users (user_id, username, password, first_name, last_name, email, role, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($insert_sql);
    if ($stmt) {
        $stmt->bind_param("sssssssss", 
            $new_admin_data['user_id'],
            $new_admin_data['username'],
            $new_admin_data['password'],
            $new_admin_data['first_name'],
            $new_admin_data['last_name'],
            $new_admin_data['email'],
            $new_admin_data['role'],
            $new_admin_data['status'],
            $new_admin_data['created_at']
        );
        
        if ($stmt->execute()) {
            echo "<p style='color: green;'>✅ Successfully created new scholarship admin account</p>";
        } else {
            echo "<p style='color: red;'>❌ Error creating account: " . $stmt->error . "</p>";
        }
        $stmt->close();
    } else {
        echo "<p style='color: red;'>❌ Error preparing insert statement: " . $conn->error . "</p>";
    }
}

$check_stmt->close();

// Step 4: Verify the new account
echo "<h3>✅ Verification - New Admin Account:</h3>";
$verify_sql = "SELECT user_id, username, first_name, last_name, email, role, status FROM users WHERE username = 'scholarship_admin'";
$verify_result = $conn->query($verify_sql);

if ($verify_result && $verify_result->num_rows > 0) {
    $admin = $verify_result->fetch_assoc();
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<h4 style='color: #155724; margin: 0 0 10px 0;'>🎉 New Scholarship Admin Account Created Successfully!</h4>";
    echo "<p><strong>User ID:</strong> " . htmlspecialchars($admin['user_id']) . "</p>";
    echo "<p><strong>Username:</strong> " . htmlspecialchars($admin['username']) . "</p>";
    echo "<p><strong>Name:</strong> " . htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']) . "</p>";
    echo "<p><strong>Email:</strong> " . htmlspecialchars($admin['email']) . "</p>";
    echo "<p><strong>Role:</strong> " . htmlspecialchars($admin['role']) . "</p>";
    echo "<p><strong>Status:</strong> " . htmlspecialchars($admin['status']) . "</p>";
    echo "</div>";
    
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "<h4 style='color: #856404; margin: 0 0 10px 0;'>🔐 Login Credentials:</h4>";
    echo "<p><strong>Username:</strong> <code>scholarship_admin</code></p>";
    echo "<p><strong>Password:</strong> <code>admin123</code></p>";
    echo "<p><strong>⚠️ Important:</strong> Change the password after first login!</p>";
    echo "</div>";
    
    echo "<a href='login.php' style='display: inline-block; background: #003366; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin-top: 15px;'>🔐 Go to Login Page</a>";
} else {
    echo "<p style='color: red;'>❌ Error: Could not verify the new admin account</p>";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Admin Account - NEUST</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        h2, h3 {
            color: #003366;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin: 10px 0;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .success {
            color: #28a745;
            background: #d4edda;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .error {
            color: #dc3545;
            background: #f8d7da;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .warning {
            color: #856404;
            background: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .btn {
            display: inline-block;
            background: #003366;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
        }
        .btn:hover {
            background: #004080;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎓 NEUST Scholarship Admin Reset</h1>
        <p>This script will reset the scholarship administrator account.</p>
        
        <div class="warning">
            <strong>⚠️ Warning:</strong> This will delete existing scholarship admin accounts and create a new one!
        </div>
        
        <div class="success">
            <strong>✅ New Admin Credentials:</strong><br>
            Username: <code>scholarship_admin</code><br>
            Password: <code>admin123</code>
        </div>
    </div>
</body>
</html>