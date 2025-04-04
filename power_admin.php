<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Power Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to your CSS file -->
</head>
<body>

    <!-- Power Admin Dashboard Header -->
    <header>
        <h1>Welcome, Power Admin</h1>
        <nav>
            <ul>
                <li><a href="admin_list.php">Manage Admins</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <!-- Main Content -->
    <section>
        <h2>Manage Admins</h2>
        <button onclick="window.location.href='add_admin.php'">Add New Admin</button>

        <!-- Admins Table -->
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Unit</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Example admin list (You’ll fetch this dynamically from the database) -->
                <tr>
                    <td>John Doe</td>
                    <td>Guidance Admin</td>
                    <td>Guidance</td>
                    <td><a href="edit_admin.php?id=1">Edit</a> | <a href="delete_admin.php?id=1">Delete</a></td>
                </tr>
                <tr>
                    <td>Jane Smith</td>
                    <td>Dormitory Admin</td>
                    <td>Dormitory</td>
                    <td><a href="edit_admin.php?id=2">Edit</a> | <a href="delete_admin.php?id=2">Delete</a></td>
                </tr>
            </tbody>
        </table>
    </section>

</body>
</html>
