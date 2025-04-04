<?php 
session_start(); // Start the session

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student_services_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$errorMessage = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId = trim($_POST['user_id']);
    $password = $_POST['password'];
    $rememberMe = isset($_POST['remember_me']);

    // SQL query to fetch user information
    $stmt = $conn->prepare("SELECT password_hash, role FROM users WHERE user_id = ?");
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($passwordHash, $role);
        $stmt->fetch();

        if (password_verify($password, $passwordHash)) {
            $_SESSION['user_id'] = $userId;
            $_SESSION['role'] = $role;

            if ($rememberMe) {
                setcookie("user_id", $userId, time() + (86400 * 30), "/"); // Save user for 30 days
            }

            // Redirect based on user role
            switch ($role) {
                case 'Power Admin':
                    header("Location: admin_dashboard.php");
                    break;
                case 'Student':
                    header("Location: student_dashboard.php");
                    break;
                case 'Faculty':
                    header("Location: faculty_dashboard.php");
                    break;
                case 'Scholarship Admin':
                    header("Location: scholarship_admin_dashboard.php");
                    break;
                case 'Guidance Admin':
                    header("Location: guidance_admin_dashboard.php");
                    break;
                case 'Dormitory Admin':
                    header("Location: admin_dormitory_dashboard.php");
                    break;
                case 'Registrar Admin':
                    header("Location: registrar_dashboard.php");
                    break;
                default:
                    $errorMessage = "Unauthorized role.";
                    session_destroy();
                    exit();
            }
            exit();
        } else {
            $errorMessage = "Invalid password.";
        }
    } else {
        $errorMessage = "User ID does not exist.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(135deg, #007bff, #6610f2);
            transition: background 0.3s ease-in-out;
        }
        .dark-mode {
            background: linear-gradient(135deg, #222, #444);
            color: white;
        }
        .container {
            width: 400px;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            text-align: center;
            transition: all 0.3s;
        }
        .dark-mode .container {
            background: #333;
            color: white;
        }
        .container:hover {
            transform: scale(1.02);
        }
        .icon {
            font-size: 60px;
            color: #007bff;
            margin-bottom: 20px;
        }
        .dark-mode .icon {
            color: #ffcc00;
        }
        .input-group {
            position: relative;
            width: 100%;
        }
        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }
        .dark-mode input {
            background: #555;
            color: white;
            border-color: #777;
        }
        input:focus {
            border-color: #007bff;
            outline: none;
        }
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
        }
        .toggle-password:hover {
            color: #007bff;
        }
        .dark-mode .toggle-password {
            color: #ffcc00;
        }
        .error {
            color: red;
            font-size: 14px;
            margin-top: 10px;
        }
        .remember-me {
            display: flex;
            align-items: center;
            margin: 10px 0;
        }
        .remember-me input {
            width: auto;
            margin-right: 10px;
        }
        button {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            border: none;
            background: #007bff;
            color: white;
            font-size: 16px;
            cursor: pointer;
            border-radius: 8px;
            transition: background 0.3s;
        }
        button:hover {
            background: #0056b3;
        }
        .dark-mode button {
            background: #ffcc00;
            color: black;
        }
        .dark-mode button:hover {
            background: #e6b800;
        }
        .dark-mode-toggle {
            position: absolute;
            top: 20px;
            right: 20px;
            cursor: pointer;
            font-size: 20px;
            color: white;
        }
    </style>
</head>
<body>
    <i class="fas fa-moon dark-mode-toggle" id="darkModeToggle"></i>
    <div class="container">
        <i class="fas fa-user-circle icon"></i>
        <h2>Login</h2>
        <form method="POST" action="">
            <div class="input-group">
                <input type="text" name="user_id" placeholder="User ID" value="<?php echo isset($_COOKIE['user_id']) ? $_COOKIE['user_id'] : ''; ?>" required>
            </div>
            <div class="input-group">
                <input type="password" id="password" name="password" placeholder="Password" required>
                <i class="fas fa-eye toggle-password" id="togglePassword"></i>
            </div>
            <div class="remember-me">
                <input type="checkbox" name="remember_me" id="remember_me">
                <label for="remember_me">Remember Me</label>
            </div>
            <span class="error"><?php echo $errorMessage; ?></span>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
    <script>
        document.getElementById("togglePassword").addEventListener("click", function () {
            let passwordInput = document.getElementById("password");
            this.classList.toggle("fa-eye-slash");
            passwordInput.type = passwordInput.type === "password" ? "text" : "password";
        });
        document.getElementById("darkModeToggle").addEventListener("click", function () {
            document.body.classList.toggle("dark-mode");
        });
    </script>
</body>
</html>