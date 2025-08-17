<?php
require_once 'config.php';

echo "<h2>🔍 Checking Admin Accounts</h2>";

// Check all admin accounts
echo "<h3>📋 All Admin Accounts Found:</h3>";
$check_sql = "SELECT user_id, first_name, last_name, email, role, status, password_hash, LENGTH(password_hash) as hash_length FROM users WHERE role LIKE '%Admin%' OR role LIKE '%admin%' ORDER BY role";
$check_result = $conn->query($check_sql);

if ($check_result && $check_result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>User ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Password Hash</th><th>Hash Length</th>";
    echo "</tr>";
    
    while ($row = $check_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td><strong>" . htmlspecialchars($row['user_id']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td><span style='background: #007bff; color: white; padding: 2px 8px; border-radius: 3px;'>" . htmlspecialchars($row['role']) . "</span></td>";
        echo "<td><span style='background: " . ($row['status'] == 'Active' ? '#28a745' : '#dc3545') . "; color: white; padding: 2px 8px; border-radius: 3px;'>" . htmlspecialchars($row['status']) . "</span></td>";
        echo "<td><code>" . htmlspecialchars(substr($row['password_hash'], 0, 30)) . (strlen($row['password_hash']) > 30 ? '...' : '') . "</code></td>";
        echo "<td>" . $row['hash_length'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No admin accounts found.</p>";
}

echo "<hr>";

// Check specifically for Power Admin
echo "<h3>⚡ Power Admin Account Details:</h3>";
$power_admin_sql = "SELECT * FROM users WHERE role = 'Power Admin' OR role LIKE '%Power%' OR role LIKE '%power%'";
$power_admin_result = $conn->query($power_admin_sql);

if ($power_admin_result && $power_admin_result->num_rows > 0) {
    while ($power_admin = $power_admin_result->fetch_assoc()) {
        echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #ffc107;'>";
        echo "<h4 style='color: #856404; margin: 0 0 15px 0;'>⚡ Power Admin Found!</h4>";
        echo "<p><strong>User ID:</strong> <code style='background: #f8f9fa; padding: 4px 8px; border-radius: 3px;'>" . htmlspecialchars($power_admin['user_id']) . "</code></p>";
        echo "<p><strong>Name:</strong> " . htmlspecialchars($power_admin['first_name'] . ' ' . $power_admin['last_name']) . "</p>";
        echo "<p><strong>Email:</strong> " . htmlspecialchars($power_admin['email']) . "</p>";
        echo "<p><strong>Role:</strong> " . htmlspecialchars($power_admin['role']) . "</p>";
        echo "<p><strong>Status:</strong> " . htmlspecialchars($power_admin['status']) . "</p>";
        echo "<p><strong>Password Hash:</strong> <code style='background: #f8f9fa; padding: 4px 8px; border-radius: 3px;'>" . htmlspecialchars($power_admin['password_hash']) . "</code></p>";
        echo "<p><strong>Hash Length:</strong> " . strlen($power_admin['password_hash']) . " characters</p>";
        echo "</div>";
    }
} else {
    echo "<p style='color: orange;'>⚠️ No Power Admin account found.</p>";
}

echo "<hr>";

// Check for admin01 account specifically
echo "<h3>🔑 Admin01 Account Details:</h3>";
$admin01_sql = "SELECT * FROM users WHERE user_id = 'admin01'";
$admin01_result = $conn->query($admin01_sql);

if ($admin01_result && $admin01_result->num_rows > 0) {
    $admin01 = $admin01_result->fetch_assoc();
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #28a745;'>";
    echo "<h4 style='color: #155724; margin: 0 0 15px 0;'>🔑 Admin01 Found!</h4>";
    echo "<p><strong>User ID:</strong> <code style='background: #f8f9fa; padding: 4px 8px; border-radius: 3px;'>" . htmlspecialchars($admin01['user_id']) . "</code></p>";
    echo "<p><strong>Name:</strong> " . htmlspecialchars($admin01['first_name'] . ' ' . $admin01['last_name']) . "</p>";
    echo "<p><strong>Email:</strong> " . htmlspecialchars($admin01['email']) . "</p>";
    echo "<p><strong>Role:</strong> " . htmlspecialchars($admin01['role']) . "</p>";
    echo "<p><strong>Status:</strong> " . htmlspecialchars($admin01['status']) . "</p>";
    echo "<p><strong>Password Hash:</strong> <code style='background: #f8f9fa; padding: 4px 8px; border-radius: 3px;'>" . htmlspecialchars($admin01['password_hash']) . "</code></p>";
    echo "<p><strong>Hash Length:</strong> " . strlen($admin01['password_hash']) . " characters</p>";
    echo "</div>";
} else {
    echo "<p style='color: orange;'>⚠️ No Admin01 account found.</p>";
}

echo "<hr>";

// Show all users with their roles for reference
echo "<h3>👥 All Users and Their Roles:</h3>";
$all_users_sql = "SELECT user_id, first_name, last_name, role, status FROM users ORDER BY role, user_id LIMIT 20";
$all_users_result = $conn->query($all_users_sql);

if ($all_users_result && $all_users_result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>User ID</th><th>Name</th><th>Role</th><th>Status</th>";
    echo "</tr>";
    
    while ($row = $all_users_result->fetch_assoc()) {
        $role_color = '#6c757d'; // Default gray
        if (strpos(strtolower($row['role']), 'admin') !== false) {
            $role_color = '#007bff'; // Blue for admin
        } elseif (strpos(strtolower($row['role']), 'student') !== false) {
            $role_color = '#28a745'; // Green for student
        }
        
        echo "<tr>";
        echo "<td><strong>" . htmlspecialchars($row['user_id']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
        echo "<td><span style='background: $role_color; color: white; padding: 2px 8px; border-radius: 3px;'>" . htmlspecialchars($row['role']) . "</span></td>";
        echo "<td><span style='background: " . ($row['status'] == 'Active' ? '#28a745' : '#dc3545') . "; color: white; padding: 2px 8px; border-radius: 3px;'>" . htmlspecialchars($row['status']) . "</span></td>";
        echo "</tr>";
    }
    echo "</table>";
    
    if ($all_users_result->num_rows >= 20) {
        echo "<p style='color: #6c757d; font-style: italic;'>Showing first 20 users. There are more users in the database.</p>";
    }
} else {
    echo "<p>No users found.</p>";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Admin Accounts - NEUST</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
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
        code {
            background: #f8f9fa;
            padding: 2px 4px;
            border-radius: 3px;
            font-family: monospace;
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
        <h1>🔍 Admin Account Checker</h1>
        <p>This script shows all admin accounts in your database, including the Power Admin account.</p>
        
        <div style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #007bff;'>
            <strong>💡 Tip:</strong> Look for the Power Admin account in the results above. The User ID and Password Hash will be displayed.
        </div>
        
        <a href="login.php" class="btn">🔐 Go to Login Page</a>
    </div>
</body>
</html>