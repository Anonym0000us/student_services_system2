<?php
session_start();
require_once 'config.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Handle POST request actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $response = ['status' => 'error', 'message' => 'An unexpected error occurred.'];

    try {
        switch ($action) {
            case 'add':
                $name = trim($_POST['name']);
                $type = trim($_POST['type']);
                $description = trim($_POST['description']);
                $eligibility = trim($_POST['eligibility']);
                $amount = floatval($_POST['amount']);
                $requirements = trim($_POST['requirements']);
                $documents_required = json_encode($_POST['documents_required']);
                $max_applicants = intval($_POST['max_applicants']);
                $deadline = $_POST['deadline'];
                $status = 'active';

                if (empty($name) || empty($description) || empty($eligibility)) {
                    throw new Exception('Please fill in all required fields.');
                }

                $stmt = $conn->prepare("INSERT INTO scholarships (name, type, description, eligibility, amount, requirements, documents_required, max_applicants, deadline, status, created_by, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
                $stmt->bind_param("ssssdssis", $name, $type, $description, $eligibility, $amount, $requirements, $documents_required, $max_applicants, $deadline, $status, $_SESSION['user_id']);
                
                if ($stmt->execute()) {
                    $response = ['status' => 'success', 'message' => 'Scholarship added successfully!'];
                } else {
                    throw new Exception('Error adding scholarship: ' . $stmt->error);
                }
                $stmt->close();
                break;

            case 'edit':
                $id = intval($_POST['id']);
                $name = trim($_POST['name']);
                $type = trim($_POST['type']);
                $description = trim($_POST['description']);
                $eligibility = trim($_POST['eligibility']);
                $amount = floatval($_POST['amount']);
                $requirements = trim($_POST['requirements']);
                $documents_required = json_encode($_POST['documents_required']);
                $max_applicants = intval($_POST['max_applicants']);
                $deadline = $_POST['deadline'];

                if (empty($name) || empty($description) || empty($eligibility)) {
                    throw new Exception('Please fill in all required fields.');
                }

                $stmt = $conn->prepare("UPDATE scholarships SET name = ?, type = ?, description = ?, eligibility = ?, amount = ?, requirements = ?, documents_required = ?, max_applicants = ?, deadline = ?, updated_at = NOW() WHERE id = ?");
                $stmt->bind_param("ssssdssi", $name, $type, $description, $eligibility, $amount, $requirements, $documents_required, $max_applicants, $deadline, $id);
                
                if ($stmt->execute()) {
                    $response = ['status' => 'success', 'message' => 'Scholarship updated successfully!'];
                } else {
                    throw new Exception('Error updating scholarship: ' . $stmt->error);
                }
                $stmt->close();
                break;

            case 'delete':
                $id = intval($_POST['id']);

                // Check if there are applications for this scholarship
                $check_sql = "SELECT COUNT(*) as count FROM scholarship_applications WHERE scholarship_id = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("i", $id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                $applications_count = $check_result->fetch_assoc()['count'];
                $check_stmt->close();

                if ($applications_count > 0) {
                    throw new Exception('Cannot delete scholarship with existing applications. Please deactivate it instead.');
                }

                $stmt = $conn->prepare("DELETE FROM scholarships WHERE id = ?");
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    $response = ['status' => 'success', 'message' => 'Scholarship deleted successfully.'];
                } else {
                    throw new Exception('Error deleting scholarship: ' . $stmt->error);
                }
                $stmt->close();
                break;

            case 'toggle_status':
                $id = intval($_POST['id']);
                $new_status = $_POST['status'] === 'active' ? 'inactive' : 'active';

                $stmt = $conn->prepare("UPDATE scholarships SET status = ?, updated_at = NOW() WHERE id = ?");
                $stmt->bind_param("si", $new_status, $id);
                
                if ($stmt->execute()) {
                    $response = ['status' => 'success', 'message' => 'Scholarship status updated successfully.'];
                } else {
                    throw new Exception('Error updating scholarship status: ' . $stmt->error);
                }
                $stmt->close();
                break;

            default:
                throw new Exception('Invalid action.');
        }
    } catch (Exception $e) {
        $response = ['status' => 'error', 'message' => $e->getMessage()];
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Fetch all scholarships with enhanced data
$sql = "SELECT s.*, 
        (SELECT COUNT(*) FROM scholarship_applications sa WHERE sa.scholarship_id = s.id) as applicant_count,
        (SELECT COUNT(*) FROM scholarship_applications sa WHERE sa.scholarship_id = s.id AND sa.status = 'approved') as approved_count
        FROM scholarships s 
        ORDER BY s.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced Scholarship Management - NEUST Gabaldon</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        :root {
            --neust-blue: #003366;
            --neust-light-blue: #00509E;
            --neust-gold: #FFD700;
            --neust-white: #FFFFFF;
            --neust-gray: #F8F9FA;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--neust-gray);
        }
        
        .sidebar {
            width: 260px;
            height: 100vh;
            background-color: var(--neust-blue);
            color: var(--neust-white);
            position: fixed;
            padding-top: 20px;
            z-index: 1000;
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
            color: var(--neust-gold);
        }
        
        .sidebar-menu {
            margin-top: 20px;
        }
        
        .menu-item {
            text-decoration: none;
            color: var(--neust-white);
            display: block;
            padding: 15px 20px;
            font-size: 1.1rem;
            background-color: #004080;
            border-radius: 8px;
            margin: 8px 15px;
            transition: all 0.3s ease;
        }
        
        .menu-item:hover,
        .menu-item.active {
            background-color: var(--neust-gold);
            color: var(--neust-blue);
            transform: scale(1.02);
        }
        
        .main-content {
            margin-left: 260px;
            padding: 20px;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--neust-blue), var(--neust-light-blue));
            color: var(--neust-white);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .stats-row {
            margin-bottom: 30px;
        }
        
        .stats-card {
            background: var(--neust-white);
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--neust-blue);
            margin-bottom: 10px;
        }
        
        .stats-label {
            color: #666;
            font-size: 1rem;
            font-weight: 600;
        }
        
        .filters-section {
            background: var(--neust-white);
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        
        .scholarship-card {
            background: var(--neust-white);
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            transition: all 0.3s ease;
            border: none;
            overflow: hidden;
        }
        
        .scholarship-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.15);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--neust-blue), var(--neust-light-blue));
            color: var(--neust-white);
            padding: 20px;
            border: none;
        }
        
        .scholarship-type {
            background: var(--neust-gold);
            color: var(--neust-blue);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 10px;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .btn-primary {
            background: var(--neust-blue);
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: var(--neust-light-blue);
            transform: scale(1.02);
        }
        
        .btn-success {
            background: #28a745;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-warning {
            background: #ffc107;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-danger {
            background: #dc3545;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .modal-header {
            background: var(--neust-blue);
            color: var(--neust-white);
        }
        
        .form-control:focus {
            border-color: var(--neust-blue);
            box-shadow: 0 0 0 0.2rem rgba(0, 83, 158, 0.25);
        }
        
        .search-box {
            border: 2px solid #e9ecef;
            border-radius: 25px;
            padding: 12px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .search-box:focus {
            border-color: var(--neust-blue);
            box-shadow: 0 0 0 0.2rem rgba(0, 83, 158, 0.25);
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
            
            .sidebar {
                display: none;
            }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <i class="fa fa-graduation-cap"></i>
        <span>Admin Panel</span>
    </div>
    <div class="sidebar-menu">
        <a href="scholarship_admin_dashboard.php" class="menu-item">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a href="enhanced_admin_scholarships.php" class="menu-item active">
            <i class="fas fa-graduation-cap"></i> Manage Scholarships
        </a>
        <a href="enhanced_manage_applications.php" class="menu-item">
            <i class="fas fa-file-alt"></i> Applications
        </a>
        <a href="approved_scholars.php" class="menu-item">
            <i class="fas fa-check-circle"></i> Approved Scholars
        </a>
        <a href="enhanced_scholarship_reports.php" class="menu-item">
            <i class="fas fa-chart-bar"></i> Reports
        </a>
        <a href="login.php" class="menu-item" style="background-color: #d9534f; margin-top: 30px;">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</div>

<div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
        <h1><i class="fas fa-graduation-cap"></i> Enhanced Scholarship Management</h1>
        <p>Comprehensive management of all scholarship programs and applications</p>
    </div>
    
    <!-- Statistics Row -->
    <div class="row stats-row">
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number"><?= $result->num_rows ?></div>
                <div class="stats-label">Total Scholarships</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number">
                    <?php
                    $active_sql = "SELECT COUNT(*) as count FROM scholarships WHERE status = 'active'";
                    $active_result = $conn->query($active_sql);
                    $active_count = $active_result->fetch_assoc()['count'];
                    echo $active_count;
                    ?>
                </div>
                <div class="stats-label">Active Scholarships</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number">
                    <?php
                    $total_applications_sql = "SELECT COUNT(*) as count FROM scholarship_applications";
                    $total_applications_result = $conn->query($total_applications_sql);
                    $total_applications = $total_applications_result->fetch_assoc()['count'];
                    echo $total_applications;
                    ?>
                </div>
                <div class="stats-label">Total Applications</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number">
                    <?php
                    $pending_sql = "SELECT COUNT(*) as count FROM scholarship_applications WHERE status = 'pending'";
                    $pending_result = $conn->query($pending_sql);
                    $pending_count = $pending_result->fetch_assoc()['count'];
                    echo $pending_count;
                    ?>
                </div>
                <div class="stats-label">Pending Applications</div>
            </div>
        </div>
    </div>
    
    <!-- Filters Section -->
    <div class="filters-section">
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="searchInput"><i class="fas fa-search"></i> Search Scholarships</label>
                    <input type="text" id="searchInput" class="form-control search-box" placeholder="Search by name, type, or description...">
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="typeFilter"><i class="fas fa-filter"></i> Filter by Type</label>
                    <select id="typeFilter" class="form-control">
                        <option value="">All Types</option>
                        <option value="Academic">Academic</option>
                        <option value="Leadership">Leadership</option>
                        <option value="Need-based">Need-based</option>
                        <option value="Sports">Sports</option>
                        <option value="Arts">Arts</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="statusFilter"><i class="fas fa-toggle-on"></i> Filter by Status</label>
                    <select id="statusFilter" class="form-control">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="mb-3">
                    <label>&nbsp;</label>
                    <button id="resetFilters" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-list"></i> Scholarship Programs</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addScholarshipModal">
            <i class="fas fa-plus"></i> Add New Scholarship
        </button>
    </div>
    
    <!-- Scholarships Grid -->
    <div id="scholarshipsGrid" class="row">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-lg-6 col-xl-4 scholarship-item" 
                     data-name="<?= strtolower($row['name']) ?>"
                     data-type="<?= strtolower($row['type']) ?>"
                     data-status="<?= $row['status'] ?>">
                    
                    <div class="scholarship-card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="scholarship-type"><?= htmlspecialchars($row['type']) ?></div>
                                    <h5 class="mb-2"><?= htmlspecialchars($row['name']) ?></h5>
                                    <span class="status-badge status-<?= $row['status'] ?>">
                                        <?= ucfirst($row['status']) ?>
                                    </span>
                                </div>
                                <div class="text-end">
                                    <small class="d-block">ID: <?= $row['id'] ?></small>
                                    <small class="d-block">Created: <?= date("M j, Y", strtotime($row['created_at'])) ?></small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <p class="text-muted mb-3"><?= htmlspecialchars(substr($row['description'], 0, 120)) ?>...</p>
                            
                            <div class="row mb-3">
                                <div class="col-6">
                                    <strong>Amount:</strong><br>
                                    <span class="text-success">₱<?= number_format($row['amount'], 2) ?></span>
                                </div>
                                <div class="col-6">
                                    <strong>Deadline:</strong><br>
                                    <span class="text-danger"><?= date("M j, Y", strtotime($row['deadline'])) ?></span>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-6">
                                    <strong>Applicants:</strong><br>
                                    <span class="text-primary"><?= $row['applicant_count'] ?></span>
                                    <?php if ($row['max_applicants'] > 0): ?>
                                        / <?= $row['max_applicants'] ?>
                                    <?php endif; ?>
                                </div>
                                <div class="col-6">
                                    <strong>Approved:</strong><br>
                                    <span class="text-success"><?= $row['approved_count'] ?></span>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button class="btn btn-warning btn-sm edit-scholarship" 
                                        data-id="<?= $row['id'] ?>" 
                                        data-name="<?= htmlspecialchars($row['name']) ?>" 
                                        data-type="<?= htmlspecialchars($row['type']) ?>"
                                        data-description="<?= htmlspecialchars($row['description']) ?>" 
                                        data-eligibility="<?= htmlspecialchars($row['eligibility']) ?>" 
                                        data-amount="<?= $row['amount'] ?>"
                                        data-requirements="<?= htmlspecialchars($row['requirements'] ?? '') ?>"
                                        data-documents="<?= htmlspecialchars($row['documents_required'] ?? '[]') ?>"
                                        data-max-applicants="<?= $row['max_applicants'] ?>"
                                        data-deadline="<?= $row['deadline'] ?>"
                                        data-status="<?= $row['status'] ?>"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editScholarshipModal">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                
                                <div class="btn-group" role="group">
                                    <button class="btn btn-success btn-sm toggle-status" 
                                            data-id="<?= $row['id'] ?>" 
                                            data-current-status="<?= $row['status'] ?>">
                                        <i class="fas fa-toggle-on"></i> 
                                        <?= $row['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                                    </button>
                                    
                                    <button class="btn btn-danger btn-sm delete-scholarship" 
                                            data-id="<?= $row['id'] ?>"
                                            data-name="<?= htmlspecialchars($row['name']) ?>">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-graduation-cap fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No scholarships found</h4>
                    <p class="text-muted">Start by adding your first scholarship program.</p>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addScholarshipModal">
                        <i class="fas fa-plus"></i> Add Scholarship
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Scholarship Modal -->
<div class="modal fade" id="addScholarshipModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus"></i> Add New Scholarship</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addScholarshipForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Scholarship Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                <select class="form-control" name="type" required>
                                    <option value="">Select Type</option>
                                    <option value="Academic">Academic</option>
                                    <option value="Leadership">Leadership</option>
                                    <option value="Need-based">Need-based</option>
                                    <option value="Sports">Sports</option>
                                    <option value="Arts">Arts</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="description" rows="3" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Eligibility Requirements <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="eligibility" rows="3" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Amount (₱)</label>
                                <input type="number" class="form-control" name="amount" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Deadline <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="deadline" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Scholarship Requirements</label>
                        <textarea class="form-control" name="requirements" rows="3" placeholder="What students must maintain or do to keep the scholarship..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Required Documents</label>
                        <div id="documentsContainer">
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" name="documents_required[]" placeholder="Document name (e.g., Transcript of Records)">
                                <button type="button" class="btn btn-outline-secondary add-document">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Maximum Applicants (0 = Unlimited)</label>
                        <input type="number" class="form-control" name="max_applicants" min="0" value="0">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="addScholarshipForm" class="btn btn-success">
                    <i class="fas fa-save"></i> Add Scholarship
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Scholarship Modal -->
<div class="modal fade" id="editScholarshipModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Scholarship</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editScholarshipForm">
                    <input type="hidden" name="id" id="editScholarshipId">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Scholarship Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" id="editScholarshipName" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                <select class="form-control" name="type" id="editScholarshipType" required>
                                    <option value="">Select Type</option>
                                    <option value="Academic">Academic</option>
                                    <option value="Leadership">Leadership</option>
                                    <option value="Need-based">Need-based</option>
                                    <option value="Sports">Sports</option>
                                    <option value="Arts">Arts</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="description" id="editScholarshipDescription" rows="3" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Eligibility Requirements <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="eligibility" id="editScholarshipEligibility" rows="3" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Amount (₱)</label>
                                <input type="number" class="form-control" name="amount" id="editScholarshipAmount" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Deadline <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="deadline" id="editScholarshipDeadline" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Scholarship Requirements</label>
                        <textarea class="form-control" name="requirements" id="editScholarshipRequirements" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Required Documents</label>
                        <div id="editDocumentsContainer">
                            <!-- Documents will be populated here -->
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Maximum Applicants (0 = Unlimited)</label>
                        <input type="number" class="form-control" name="max_applicants" id="editScholarshipMaxApplicants" min="0">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="editScholarshipForm" class="btn btn-success">
                    <i class="fas fa-save"></i> Update Scholarship
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function(){
    // Search and Filter functionality
    function filterScholarships() {
        const searchTerm = $('#searchInput').val().toLowerCase();
        const typeFilter = $('#typeFilter').val().toLowerCase();
        const statusFilter = $('#statusFilter').val().toLowerCase();
        
        $('.scholarship-item').each(function() {
            const $item = $(this);
            const name = $item.data('name');
            const type = $item.data('type');
            const status = $item.data('status');
            
            let show = true;
            
            // Search filter
            if (searchTerm && !name.includes(searchTerm)) {
                show = false;
            }
            
            // Type filter
            if (typeFilter && type !== typeFilter) {
                show = false;
            }
            
            // Status filter
            if (statusFilter && status !== statusFilter) {
                show = false;
            }
            
            $item.toggle(show);
        });
    }
    
    // Event listeners for filters
    $('#searchInput, #typeFilter, #statusFilter').on('change keyup', filterScholarships);
    
    $('#resetFilters').click(function() {
        $('#searchInput').val('');
        $('#typeFilter').val('');
        $('#statusFilter').val('');
        filterScholarships();
    });
    
    // Document management
    $('.add-document').click(function() {
        const newDoc = `
            <div class="input-group mb-2">
                <input type="text" class="form-control" name="documents_required[]" placeholder="Document name">
                <button type="button" class="btn btn-outline-danger remove-document">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        `;
        $('#documentsContainer').append(newDoc);
    });
    
    $(document).on('click', '.remove-document', function() {
        $(this).closest('.input-group').remove();
    });
    
    // Handle Add Scholarship
    $("#addScholarshipForm").submit(function(e){
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'add');
        
        $.ajax({
            url: 'enhanced_admin_scholarships.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status === 'success') {
                    alert(response.message);
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            }
        });
    });
    
    // Populate Edit Modal
    $(".edit-scholarship").click(function(){
        const data = $(this).data();
        
        $("#editScholarshipId").val(data.id);
        $("#editScholarshipName").val(data.name);
        $("#editScholarshipType").val(data.type);
        $("#editScholarshipDescription").val(data.description);
        $("#editScholarshipEligibility").val(data.eligibility);
        $("#editScholarshipAmount").val(data.amount);
        $("#editScholarshipRequirements").val(data.requirements);
        $("#editScholarshipDeadline").val(data.deadline);
        $("#editScholarshipMaxApplicants").val(data.maxApplicants);
        
        // Populate documents
        let documents = [];
        try {
            documents = JSON.parse(data.documents);
        } catch (e) {
            documents = [];
        }
        
        let documentsHtml = '';
        documents.forEach((doc, index) => {
            documentsHtml += `
                <div class="input-group mb-2">
                    <input type="text" class="form-control" name="documents_required[]" value="${doc}" placeholder="Document name">
                    ${index === 0 ? '' : '<button type="button" class="btn btn-outline-danger remove-document"><i class="fas fa-minus"></i></button>'}
                </div>
            `;
        });
        
        if (documents.length === 0) {
            documentsHtml = `
                <div class="input-group mb-2">
                    <input type="text" class="form-control" name="documents_required[]" placeholder="Document name">
                </div>
            `;
        }
        
        $("#editDocumentsContainer").html(documentsHtml);
    });
    
    // Handle Edit Scholarship
    $("#editScholarshipForm").submit(function(e){
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'edit');
        
        $.ajax({
            url: 'enhanced_admin_scholarships.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status === 'success') {
                    alert(response.message);
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            }
        });
    });
    
    // Handle Toggle Status
    $(".toggle-status").click(function(){
        const button = $(this);
        const id = button.data('id');
        const currentStatus = button.data('current-status');
        const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
        
        if (confirm(`Are you sure you want to ${newStatus === 'active' ? 'activate' : 'deactivate'} this scholarship?`)) {
            $.ajax({
                url: 'enhanced_admin_scholarships.php',
                type: 'POST',
                data: { 
                    action: 'toggle_status',
                    id: id,
                    status: currentStatus
                },
                success: function(response) {
                    if (response.status === 'success') {
                        alert(response.message);
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                }
            });
        }
    });
    
    // Handle Delete Scholarship
    $(".delete-scholarship").click(function(){
        const id = $(this).data('id');
        const name = $(this).data('name');
        
        if (confirm(`Are you sure you want to delete the scholarship "${name}"? This action cannot be undone.`)) {
            $.ajax({
                url: 'enhanced_admin_scholarships.php',
                type: 'POST',
                data: { 
                    id: id, 
                    action: 'delete' 
                },
                success: function(response) {
                    if (response.status === 'success') {
                        alert(response.message);
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                }
            });
        }
    });
});
</script>

</body>
</html>