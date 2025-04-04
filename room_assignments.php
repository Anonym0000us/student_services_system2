<?php
include_once "admin_dormitory_header.php";
session_start();

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "student_services_db"; // Correct database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure uploads directory exists
if (!is_dir('uploads')) {
    mkdir('uploads', 0777, true);
}

// Fetch rooms from the database
$sql = "SELECT * FROM rooms";
$result = $conn->query($sql);
$rooms = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $rooms[] = $row;
    }
}

// Handle room actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_room'])) {
        if (!empty($_POST['room_name']) && !empty($_POST['total_beds'])) {
            $imagePath = "dorm.jpg";
            if (!empty($_FILES['room_image']['name'])) {
                $targetDir = "uploads/";
                $targetFile = $targetDir . basename($_FILES['room_image']['name']);
                if (move_uploaded_file($_FILES['room_image']['tmp_name'], $targetFile)) {
                    $imagePath = $targetFile;
                } else {
                    error_log("Failed to upload image.");
                }
            }

            $roomName = $_POST['room_name'];
            $totalBeds = intval($_POST['total_beds']);
            $occupiedBeds = 0; // Define occupied_beds separately

            $stmt = $conn->prepare("INSERT INTO rooms (name, total_beds, occupied_beds, image) VALUES (?, ?, ?, ?)");
            if (!$stmt) {
                error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
            }

            if (!$stmt->bind_param("siis", $roomName, $totalBeds, $occupiedBeds, $imagePath)) {
                error_log("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
            }

            if (!$stmt->execute()) {
                error_log("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
            } else {
                error_log("Room added successfully.");
            }

            $stmt->close();
        }
    } elseif (isset($_POST['update_room'])) {
        foreach ($rooms as &$room) {
            if ($room['id'] == $_POST['room_id']) {
                $room['name'] = $_POST['room_name'];
                $room['total_beds'] = intval($_POST['total_beds']);
                $room['occupied_beds'] = min(intval($_POST['occupied_beds']), $room['total_beds']);

                if (!empty($_FILES['room_image']['name'])) {
                    $targetDir = "uploads/";
                    $targetFile = $targetDir . basename($_FILES['room_image']['name']);
                    if (move_uploaded_file($_FILES['room_image']['tmp_name'], $targetFile)) {
                        $room['image'] = $targetFile;
                    } else {
                        error_log("Failed to upload image.");
                    }
                }

                $stmt = $conn->prepare("UPDATE rooms SET name = ?, total_beds = ?, occupied_beds = ?, image = ? WHERE id = ?");
                if (!$stmt) {
                    error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
                }

                if (!$stmt->bind_param("siisi", $room['name'], $room['total_beds'], $room['occupied_beds'], $room['image'], $room['id'])) {
                    error_log("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
                }

                if (!$stmt->execute()) {
                    error_log("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
                } else {
                    error_log("Room updated successfully.");
                }

                $stmt->close();
                break;
            }
        }
    } elseif (isset($_POST['delete_room'])) {
        $stmt = $conn->prepare("DELETE FROM rooms WHERE id = ?");
        if (!$stmt) {
            error_log("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        }

        if (!$stmt->bind_param("i", $_POST['room_id'])) {
            error_log("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error);
        }

        if (!$stmt->execute()) {
            error_log("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
        } else {
            error_log("Room deleted successfully.");
        }

        $stmt->close();
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dormitory Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h1 class="text-center">Dormitory Management</h1>
        <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addRoomModal">Add Room</button>
        <div class="row">
            <?php foreach ($rooms as $room): ?>
                <div class="col-md-4">
                    <div class="card mb-4">
                        <img src="<?php echo $room['image']; ?>" class="card-img-top" alt="Room Image">
                        <div class="card-body">
                            <h5 class="card-title"> <?php echo $room['name']; ?> </h5>
                            <p class="card-text"> Occupied: <?php echo $room['occupied_beds'] . '/' . $room['total_beds']; ?> </p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editRoomModal<?php echo $room['id']; ?>">Edit</button>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                                <button type="submit" name="delete_room" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal fade" id="editRoomModal<?php echo $room['id']; ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Room</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                                    <div class="mb-3">
                                        <label class="form-label">Room Name</label>
                                        <input type="text" name="room_name" value="<?php echo $room['name']; ?>" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Total Beds</label>
                                        <input type="number" name="total_beds" value="<?php echo $room['total_beds']; ?>" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Occupied Beds</label>
                                        <input type="number" name="occupied_beds" value="<?php echo $room['occupied_beds']; ?>" class="form-control" required min="0">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Room Image</label>
                                        <input type="file" name="room_image" class="form-control">
                                    </div>
                                    <button type="submit" name="update_room" class="btn btn-primary">Update</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="modal fade" id="addRoomModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Room</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Room Name</label>
                                <input type="text" name="room_name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Total Beds</label>
                                <input type="number" name="total_beds" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Room Image</label>
                                <input type="file" name="room_image" class="form-control">
                            </div>
                            <button type="submit" name="add_room" class="btn btn-success">Add Room</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>