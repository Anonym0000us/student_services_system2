<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            display: flex;
        }

        .sidebar {
            width: 260px;
            height: 100vh;
            background-color: #003366;
            color: white;
            position: fixed;
            padding-top: 20px;
            transition: all 0.3s ease;
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background-color: #002855;
            font-size: 1.5rem;
            font-weight: 700;
            text-transform: uppercase;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            color: #FFD700;
        }

        .sidebar-header i {
            margin-right: 10px;
            color: #FFD700;
        }

        .sidebar-menu a {
            text-decoration: none;
            color: white;
            display: block;
            padding: 15px 20px;
            font-size: 1.1rem;
            margin: 8px 15px;
            background-color: #004080;
            transition: all 0.3s ease;
            border-radius: 8px;
        }

        .sidebar-menu a:hover {
            background-color: #FFD700;
            color: #003366;
            transform: scale(1.05);
            font-weight: bold;
        }

        .logout-btn {
            text-decoration: none;
            color: white;
            display: block;
            padding: 15px;
            font-size: 1.1rem;
            background-color: #d9534f;
            margin: 30px 15px;
            border-radius: 8px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background-color: #c9302c;
            transform: scale(1.05);
        }

        .main-content {
            margin-left: 260px;
            padding: 20px;
            width: calc(100% - 260px);
            min-height: 100vh;
            background: white;  
            box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <i class="fa fa-user-shield"></i>
            <span>Admin Panel</span>
        </div>
        <div class="sidebar-menu">
            <a href="registrar_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="registrar_tor_list.php"><i class="fas fa-file-alt"></i> TOR Requests</a>
          
          
        </div>
        <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="main-content">