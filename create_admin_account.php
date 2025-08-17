<?php
require_once 'config.php';

// Check if admin already exists
$check_sql = "SELECT * FROM users WHERE role = 'admin' LIMIT 1";
$check_result = $conn->query($check_sql);

if ($check_result->num_rows > 0) {
    echo "Admin account already exists!<br>";
    $admin = $check_result->fetch_assoc();
    echo "Username: " . $admin['username'] . "<br>";
    echo "Role: " . $admin['role'] . "<br>";
    echo "Status: " . $admin['status'] . "<br>";
} else {
    // Create admin account
    $admin_data = [
        'user_id' => 'ADMIN001',
        'username' => 'scholarship_admin',
        'password' => password_hash('admin123', PASSWORD_DEFAULT), // Default password: admin123
        'first_name' => 'Scholarship',
        'last_name' => 'Administrator',
        'email' => 'scholarship@neust.edu.ph',
        'role' => 'admin',
        'status' => 'active',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $insert_sql = "INSERT INTO users (user_id, username, password, first_name, last_name, email, role, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($insert_sql);
    if ($stmt) {
        $stmt->bind_param("sssssssss", 
            $admin_data['user_id'],
            $admin_data['username'],
            $admin_data['password'],
            $admin_data['first_name'],
            $admin_data['last_name'],
            $admin_data['email'],
            $admin_data['role'],
            $admin_data['status'],
            $admin_data['created_at']
        );
        
        if ($stmt->execute()) {
            echo "✅ Scholarship Admin Account Created Successfully!<br><br>";
            echo "📋 <strong>Login Credentials:</strong><br>";
            echo "Username: <strong>scholarship_admin</strong><br>";
            echo "Password: <strong>admin123</strong><br>";
            echo "Role: <strong>admin</strong><br><br>";
            echo "⚠️ <strong>Important:</strong> Change the password after first login!<br>";
            echo "🔗 <a href='login.php'>Go to Login Page</a>";
        } else {
            echo "❌ Error creating admin account: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "❌ Error preparing statement: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin Account - NEUST</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .success {
            color: #28a745;
            background: #d4edda;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .info {
            color: #17a2b8;
            background: #d1ecf1;
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
        <h1>🎓 NEUST Scholarship Admin Setup</h1>
        <p>This script will create the scholarship administrator account.</p>
        
        <div class="info">
            <strong>Default Admin Credentials:</strong><br>
            Username: <code>scholarship_admin</code><br>
            Password: <code>admin123</code>
        </div>
        
        <div class="warning">
            <strong>Security Note:</strong> Change the default password immediately after first login!
        </div>
        
        <a href="login.php" class="btn">🔐 Go to Login Page</a>
    </div>
</body>
</html>