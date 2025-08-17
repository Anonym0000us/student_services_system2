<?php
require_once 'config.php';

echo "<h2>🔍 Testing Password Methods</h2>";

$password = 'admin123';

echo "<h3>Testing different password hashing methods:</h3>";

// Method 1: PASSWORD_DEFAULT (PHP 7.0+)
$hash1 = password_hash($password, PASSWORD_DEFAULT);
echo "<p><strong>PASSWORD_DEFAULT:</strong> <code>$hash1</code></p>";

// Method 2: MD5 (older systems)
$hash2 = md5($password);
echo "<p><strong>MD5:</strong> <code>$hash2</code></p>";

// Method 3: SHA1 (older systems)
$hash3 = sha1($password);
echo "<p><strong>SHA1:</strong> <code>$hash3</code></p>";

// Method 4: Plain text (for testing)
echo "<p><strong>Plain Text:</strong> <code>$password</code></p>";

echo "<hr>";

// Test verification
echo "<h3>Testing password verification:</h3>";

if (password_verify($password, $hash1)) {
    echo "<p style='color: green;'>✅ PASSWORD_DEFAULT verification works</p>";
} else {
    echo "<p style='color: red;'>❌ PASSWORD_DEFAULT verification failed</p>";
}

if (md5($password) === $hash2) {
    echo "<p style='color: green;'>✅ MD5 verification works</p>";
} else {
    echo "<p style='color: red;'>❌ MD5 verification failed</p>";
}

if (sha1($password) === $hash3) {
    echo "<p style='color: green;'>✅ SHA1 verification works</p>";
} else {
    echo "<p style='color: red;'>❌ SHA1 verification failed</p>";
}

echo "<hr>";

// Check existing users' password hashes
echo "<h3>Checking existing users' password hashes:</h3>";
$check_sql = "SELECT user_id, password_hash, LENGTH(password_hash) as hash_length FROM users LIMIT 5";
$check_result = $conn->query($check_sql);

if ($check_result && $check_result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>User ID</th><th>Password Hash</th><th>Length</th><th>Type</th>";
    echo "</tr>";
    
    while ($row = $check_result->fetch_assoc()) {
        $hash = $row['password_hash'];
        $length = $row['hash_length'];
        
        // Determine hash type
        $type = "Unknown";
        if ($length == 32) {
            $type = "MD5";
        } elseif ($length == 40) {
            $type = "SHA1";
        } elseif ($length == 60) {
            $type = "PASSWORD_DEFAULT";
        } elseif ($length < 20) {
            $type = "Plain Text";
        }
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
        echo "<td><code>" . htmlspecialchars(substr($hash, 0, 50)) . (strlen($hash) > 50 ? '...' : '') . "</code></td>";
        echo "<td>$length</td>";
        echo "<td>$type</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No users found.</p>";
}

echo "<hr>";

// Test login simulation
echo "<h3>Testing login simulation:</h3>";

// Try to find a user with role 'Student' to test login
$test_sql = "SELECT user_id, password_hash FROM users WHERE role = 'Student' LIMIT 1";
$test_result = $conn->query($test_sql);

if ($test_result && $test_result->num_rows > 0) {
    $test_user = $test_result->fetch_assoc();
    $test_hash = $test_user['password_hash'];
    $test_id = $test_user['user_id'];
    
    echo "<p><strong>Test User:</strong> $test_id</p>";
    echo "<p><strong>Password Hash:</strong> <code>" . htmlspecialchars($test_hash) . "</code></p>";
    
    // Try different verification methods
    if (password_verify('test', $test_hash)) {
        echo "<p style='color: green;'>✅ PASSWORD_DEFAULT works with 'test'</p>";
    } else {
        echo "<p style='color: red;'>❌ PASSWORD_DEFAULT failed with 'test'</p>";
    }
    
    if (md5('test') === $test_hash) {
        echo "<p style='color: green;'>✅ MD5 works with 'test'</p>";
    } else {
        echo "<p style='color: red;'>❌ MD5 failed with 'test'</p>";
    }
    
    if (sha1('test') === $test_hash) {
        echo "<p style='color: green;'>✅ SHA1 works with 'test'</p>";
    } else {
        echo "<p style='color: red;'>❌ SHA1 failed with 'test'</p>";
    }
    
    if ('test' === $test_hash) {
        echo "<p style='color: green;'>✅ Plain text works with 'test'</p>";
    } else {
        echo "<p style='color: red;'>❌ Plain text failed with 'test'</p>";
    }
} else {
    echo "<p>No test users found.</p>";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Password Methods - NEUST</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1000px;
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
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Password Method Testing</h1>
        <p>This script tests different password hashing methods to find which one works with your login system.</p>
    </div>
</body>
</html>