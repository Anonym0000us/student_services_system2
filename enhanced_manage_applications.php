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
            case 'approve':
                $application_id = intval($_POST['application_id']);
                $review_notes = trim($_POST['review_notes'] ?? '');
                
                $stmt = $conn->prepare("UPDATE scholarship_applications SET status = 'approved', review_notes = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ?");
                $stmt->bind_param("ssi", $review_notes, $_SESSION['user_id'], $application_id);
                
                if ($stmt->execute()) {
                    // Create notification for student
                    $notification_sql = "INSERT INTO scholarship_notifications (user_id, title, message, type, created_at) VALUES ((SELECT user_id FROM scholarship_applications WHERE id = ?), ?, ?, 'success', NOW())";
                    $notification_title = 'Application Approved';
                    $notification_message = 'Congratulations! Your scholarship application has been approved.';
                    
                    $notification_stmt = $conn->prepare($notification_sql);
                    $notification_stmt->bind_param("iss", $application_id, $notification_title, $notification_message);
                    $notification_stmt->execute();
                    $notification_stmt->close();
                    
                    $response = ['status' => 'success', 'message' => 'Application approved successfully!'];
                } else {
                    throw new Exception('Error approving application: ' . $stmt->error);
                }
                $stmt->close();
                break;

            case 'reject':
                $application_id = intval($_POST['application_id']);
                $rejection_reason = trim($_POST['rejection_reason']);
                $review_notes = trim($_POST['review_notes'] ?? '');
                
                if (empty($rejection_reason)) {
                    throw new Exception('Rejection reason is required.');
                }
                
                $stmt = $conn->prepare("UPDATE scholarship_applications SET status = 'rejected', rejection_reason = ?, review_notes = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ?");
                $stmt->bind_param("sssi", $rejection_reason, $review_notes, $_SESSION['user_id'], $application_id);
                
                if ($stmt->execute()) {
                    // Create notification for student
                    $notification_sql = "INSERT INTO scholarship_notifications (user_id, title, message, type, created_at) VALUES ((SELECT user_id FROM scholarship_applications WHERE id = ?), ?, ?, 'error', NOW())";
                    $notification_title = 'Application Status Update';
                    $notification_message = 'Your scholarship application has been reviewed. Please check your application status for details.';
                    
                    $notification_stmt = $conn->prepare($notification_sql);
                    $notification_stmt->bind_param("iss", $application_id, $notification_title, $notification_message);
                    $notification_stmt->execute();
                    $notification_stmt->close();
                    
                    $response = ['status' => 'success', 'message' => 'Application rejected successfully!'];
                } else {
                    throw new Exception('Error rejecting application: ' . $stmt->error);
                }
                $stmt->close();
                break;

            case 'request_documents':
                $application_id = intval($_POST['application_id']);
                $additional_requirements = trim($_POST['additional_requirements']);
                
                if (empty($additional_requirements)) {
                    throw new Exception('Additional requirements description is required.');
                }
                
                $stmt = $conn->prepare("UPDATE scholarship_applications SET status = 'under_review', review_notes = CONCAT(COALESCE(review_notes, ''), '\n\nAdditional documents requested: ', ?), reviewed_by = ?, reviewed_at = NOW() WHERE id = ?");
                $stmt->bind_param("ssi", $additional_requirements, $_SESSION['user_id'], $application_id);
                
                if ($stmt->execute()) {
                    // Create notification for student
                    $notification_sql = "INSERT INTO scholarship_notifications (user_id, title, message, type, created_at) VALUES ((SELECT user_id FROM scholarship_applications WHERE id = ?), ?, ?, 'warning', NOW())";
                    $notification_title = 'Additional Documents Required';
                    $notification_message = 'Additional documents are required for your scholarship application. Please check the requirements and submit them.';
                    
                    $notification_stmt = $conn->prepare($notification_sql);
                    $notification_stmt->bind_param("iss", $application_id, $notification_title, $notification_message);
                    $notification_stmt->execute();
                    $notification_stmt->close();
                    
                    $response = ['status' => 'success', 'message' => 'Additional documents requested successfully!'];
                } else {
                    throw new Exception('Error updating application: ' . $stmt->error);
                }
                $stmt->close();
                break;

            case 'mark_under_review':
                $application_id = intval($_POST['application_id']);
                $review_notes = trim($_POST['review_notes'] ?? '');
                
                $stmt = $conn->prepare("UPDATE scholarship_applications SET status = 'under_review', review_notes = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ?");
                $stmt->bind_param("ssi", $review_notes, $_SESSION['user_id'], $application_id);
                
                if ($stmt->execute()) {
                    $response = ['status' => 'success', 'message' => 'Application marked as under review!'];
                } else {
                    throw new Exception('Error updating application: ' . $stmt->error);
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

// Fetch scholarship applications with enhanced data
$sql = "SELECT 
            sa.*,
            s.name AS scholarship_name,
            s.type AS scholarship_type,
            s.amount AS scholarship_amount,
            u.first_name,
            u.middle_name,
            u.last_name,
            u.email,
            u.phone,
            u.course,
            u.year_level
        FROM 
            scholarship_applications sa
        JOIN 
            scholarships s ON sa.scholarship_id = s.id
        JOIN 
            users u ON sa.user_id = u.user_id
        ORDER BY 
            sa.application_date DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced Application Management - NEUST Gabaldon</title>
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
        
        .application-card {
            background: var(--neust-white);
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            transition: all 0.3s ease;
            border: none;
            overflow: hidden;
        }
        
        .application-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.15);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--neust-blue), var(--neust-light-blue));
            color: var(--neust-white);
            padding: 20px;
            border: none;
        }
        
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-under-review {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-approved {
            background: #d4edda;
            color: #155724;
        }
        
        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        
        .btn-primary {
            background: var(--neust-blue);
            border: none;
            padding: 8px 16px;
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
        
        .btn-info {
            background: #17a2b8;
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
        
        .document-preview {
            max-width: 100%;
            max-height: 400px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .application-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
        }
        
        .application-info h6 {
            color: var(--neust-blue);
            margin-bottom: 10px;
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
        <a href="enhanced_admin_scholarships.php" class="menu-item">
            <i class="fas fa-graduation-cap"></i> Manage Scholarships
        </a>
        <a href="enhanced_manage_applications.php" class="menu-item active">
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
        <h1><i class="fas fa-file-alt"></i> Enhanced Application Management</h1>
        <p>Comprehensive review and management of all scholarship applications</p>
    </div>
    
    <!-- Statistics Row -->
    <div class="row stats-row">
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number">
                    <?php
                    $total_sql = "SELECT COUNT(*) as count FROM scholarship_applications";
                    $total_result = $conn->query($total_sql);
                    $total_count = $total_result->fetch_assoc()['count'];
                    echo $total_count;
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
                <div class="stats-label">Pending Review</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number">
                    <?php
                    $approved_sql = "SELECT COUNT(*) as count FROM scholarship_applications WHERE status = 'approved'";
                    $approved_result = $conn->query($approved_sql);
                    $approved_count = $approved_result->fetch_assoc()['count'];
                    echo $approved_count;
                    ?>
                </div>
                <div class="stats-label">Approved</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-number">
                    <?php
                    $rejected_sql = "SELECT COUNT(*) as count FROM scholarship_applications WHERE status = 'rejected'";
                    $rejected_result = $conn->query($rejected_sql);
                    $rejected_count = $rejected_result->fetch_assoc()['count'];
                    echo $rejected_count;
                    ?>
                </div>
                <div class="stats-label">Rejected</div>
            </div>
        </div>
    </div>
    
    <!-- Filters Section -->
    <div class="filters-section">
        <div class="row">
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="searchInput"><i class="fas fa-search"></i> Search Applications</label>
                    <input type="text" id="searchInput" class="form-control search-box" placeholder="Search by student name or scholarship...">
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="statusFilter"><i class="fas fa-filter"></i> Filter by Status</label>
                    <select id="statusFilter" class="form-control">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="under_review">Under Review</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="scholarshipFilter"><i class="fas fa-graduation-cap"></i> Filter by Scholarship</label>
                    <select id="scholarshipFilter" class="form-control">
                        <option value="">All Scholarships</option>
                        <?php
                        $scholarships_sql = "SELECT DISTINCT id, name FROM scholarships ORDER BY name";
                        $scholarships_result = $conn->query($scholarships_sql);
                        while ($scholarship = $scholarships_result->fetch_assoc()) {
                            echo '<option value="' . $scholarship['id'] . '">' . htmlspecialchars($scholarship['name']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label>&nbsp;</label>
                    <button id="resetFilters" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Applications Grid -->
    <div id="applicationsGrid">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="application-item" 
                     data-name="<?= strtolower($row['first_name'] . ' ' . $row['last_name']) ?>"
                     data-scholarship="<?= strtolower($row['scholarship_name']) ?>"
                     data-status="<?= $row['status'] ?>"
                     data-scholarship-id="<?= $row['scholarship_id'] ?>">
                    
                    <div class="application-card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="mb-2"><?= htmlspecialchars($row['scholarship_name']) ?></h5>
                                    <p class="mb-1">
                                        <strong>Student:</strong> <?= htmlspecialchars($row['first_name'] . ' ' . ($row['middle_name'] ? $row['middle_name'] . ' ' : '') . $row['last_name']) ?>
                                    </p>
                                    <p class="mb-0">
                                        <strong>Course:</strong> <?= htmlspecialchars($row['course'] ?? 'Not specified') ?> | 
                                        <strong>Year:</strong> <?= htmlspecialchars($row['year_level'] ?? 'Not specified') ?>
                                    </p>
                                </div>
                                <div class="text-end">
                                    <span class="status-badge status-<?= $row['status'] ?>">
                                        <?= ucfirst(str_replace('_', ' ', $row['status'])) ?>
                                    </span>
                                    <br>
                                    <small class="d-block mt-1">ID: <?= $row['id'] ?></small>
                                    <small class="d-block">Applied: <?= date("M j, Y", strtotime($row['application_date'])) ?></small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="application-info">
                                        <h6><i class="fas fa-user"></i> Student Information</h6>
                                        <p><strong>Email:</strong> <?= htmlspecialchars($row['email']) ?></p>
                                        <p><strong>Phone:</strong> <?= htmlspecialchars($row['phone'] ?? 'Not specified') ?></p>
                                        <p><strong>GPA:</strong> <?= htmlspecialchars($row['gpa'] ?? 'Not specified') ?></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="application-info">
                                        <h6><i class="fas fa-graduation-cap"></i> Scholarship Details</h6>
                                        <p><strong>Type:</strong> <?= htmlspecialchars($row['scholarship_type']) ?></p>
                                        <p><strong>Amount:</strong> ₱<?= number_format($row['scholarship_amount'], 2) ?></p>
                                        <p><strong>Application Date:</strong> <?= date("F j, Y", strtotime($row['application_date'])) ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (!empty($row['review_notes'])): ?>
                                <div class="application-info">
                                    <h6><i class="fas fa-sticky-note"></i> Review Notes</h6>
                                    <p><?= nl2br(htmlspecialchars($row['review_notes'])) ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($row['status'] === 'rejected' && !empty($row['rejection_reason'])): ?>
                                <div class="application-info" style="background: #f8d7da; border-left: 4px solid #dc3545;">
                                    <h6><i class="fas fa-times-circle"></i> Rejection Reason</h6>
                                    <p><?= nl2br(htmlspecialchars($row['rejection_reason'])) ?></p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div>
                                    <button class="btn btn-info btn-sm view-documents" 
                                            data-application-id="<?= $row['id'] ?>"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#viewDocumentsModal">
                                        <i class="fas fa-file-alt"></i> View Documents
                                    </button>
                                    
                                    <button class="btn btn-info btn-sm view-application" 
                                            data-application-id="<?= $row['id'] ?>"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#viewApplicationModal">
                                        <i class="fas fa-eye"></i> View Details
                                    </button>
                                </div>
                                
                                <div class="btn-group" role="group">
                                    <?php if ($row['status'] === 'pending'): ?>
                                        <button class="btn btn-warning btn-sm mark-under-review" 
                                                data-application-id="<?= $row['id'] ?>"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#markUnderReviewModal">
                                            <i class="fas fa-clock"></i> Mark Under Review
                                        </button>
                                        
                                        <button class="btn btn-info btn-sm request-documents" 
                                                data-application-id="<?= $row['id'] ?>"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#requestDocumentsModal">
                                            <i class="fas fa-file-upload"></i> Request Documents
                                        </button>
                                        
                                        <button class="btn btn-success btn-sm approve-application" 
                                                data-application-id="<?= $row['id'] ?>"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#approveApplicationModal">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                        
                                        <button class="btn btn-danger btn-sm reject-application" 
                                                data-application-id="<?= $row['id'] ?>"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#rejectApplicationModal">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    <?php elseif ($row['status'] === 'under_review'): ?>
                                        <button class="btn btn-success btn-sm approve-application" 
                                                data-application-id="<?= $row['id'] ?>"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#approveApplicationModal">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                        
                                        <button class="btn btn-danger btn-sm reject-application" 
                                                data-application-id="<?= $row['id'] ?>"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#rejectApplicationModal">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">No applications found</h4>
                <p class="text-muted">Applications will appear here once students start applying for scholarships.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- View Documents Modal -->
<div class="modal fade" id="viewDocumentsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-alt"></i> Application Documents</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="documentsModalBody">
                <!-- Documents will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- View Application Modal -->
<div class="modal fade" id="viewApplicationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-eye"></i> Application Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="applicationModalBody">
                <!-- Application details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Mark Under Review Modal -->
<div class="modal fade" id="markUnderReviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-clock"></i> Mark Application Under Review</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="markUnderReviewForm">
                    <input type="hidden" name="application_id" id="markUnderReviewApplicationId">
                    <div class="mb-3">
                        <label class="form-label">Review Notes (Optional)</label>
                        <textarea class="form-control" name="review_notes" rows="3" placeholder="Add any notes about this application..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="markUnderReviewForm" class="btn btn-warning">
                    <i class="fas fa-clock"></i> Mark Under Review
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Request Documents Modal -->
<div class="modal fade" id="requestDocumentsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-upload"></i> Request Additional Documents</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="requestDocumentsForm">
                    <input type="hidden" name="application_id" id="requestDocumentsApplicationId">
                    <div class="mb-3">
                        <label class="form-label">Additional Documents Required <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="additional_requirements" rows="4" required placeholder="Describe what additional documents are needed..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="requestDocumentsForm" class="btn btn-info">
                    <i class="fas fa-file-upload"></i> Request Documents
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Approve Application Modal -->
<div class="modal fade" id="approveApplicationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-check"></i> Approve Application</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="approveApplicationForm">
                    <input type="hidden" name="application_id" id="approveApplicationId">
                    <div class="alert alert-success">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> This will approve the student's scholarship application. 
                        The student will be notified of the approval.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Review Notes (Optional)</label>
                        <textarea class="form-control" name="review_notes" rows="3" placeholder="Add any notes about this approval..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="approveApplicationForm" class="btn btn-success">
                    <i class="fas fa-check"></i> Approve Application
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Reject Application Modal -->
<div class="modal fade" id="rejectApplicationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-times"></i> Reject Application</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="rejectApplicationForm">
                    <input type="hidden" name="application_id" id="rejectApplicationId">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning:</strong> This action cannot be undone. The student will be notified of the rejection.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="rejection_reason" rows="4" required placeholder="Please provide a clear reason for rejection..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Review Notes (Optional)</label>
                        <textarea class="form-control" name="review_notes" rows="3" placeholder="Add any additional notes..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="rejectApplicationForm" class="btn btn-danger">
                    <i class="fas fa-times"></i> Reject Application
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function(){
    // Search and Filter functionality
    function filterApplications() {
        const searchTerm = $('#searchInput').val().toLowerCase();
        const statusFilter = $('#statusFilter').val();
        const scholarshipFilter = $('#scholarshipFilter').val();
        
        $('.application-item').each(function() {
            const $item = $(this);
            const name = $item.data('name');
            const scholarship = $item.data('scholarship');
            const status = $item.data('status');
            const scholarshipId = $item.data('scholarship-id');
            
            let show = true;
            
            // Search filter
            if (searchTerm && !name.includes(searchTerm) && !scholarship.includes(searchTerm)) {
                show = false;
            }
            
            // Status filter
            if (statusFilter && status !== statusFilter) {
                show = false;
            }
            
            // Scholarship filter
            if (scholarshipFilter && scholarshipId != scholarshipFilter) {
                show = false;
            }
            
            $item.toggle(show);
        });
    }
    
    // Event listeners for filters
    $('#searchInput, #statusFilter, #scholarshipFilter').on('change keyup', filterApplications);
    
    $('#resetFilters').click(function() {
        $('#searchInput').val('');
        $('#statusFilter').val('');
        $('#scholarshipFilter').val('');
        filterApplications();
    });
    
    // View Documents
    $('.view-documents').click(function() {
        const applicationId = $(this).data('application-id');
        
        $.ajax({
            url: 'get_application_documents.php',
            type: 'GET',
            data: { application_id: applicationId },
            success: function(response) {
                $('#documentsModalBody').html(response);
            },
            error: function() {
                $('#documentsModalBody').html('<div class="alert alert-danger">Error loading documents.</div>');
            }
        });
    });
    
    // View Application Details
    $('.view-application').click(function() {
        const applicationId = $(this).data('application-id');
        
        $.ajax({
            url: 'get_application_details.php',
            type: 'GET',
            data: { application_id: applicationId },
            success: function(response) {
                $('#applicationModalBody').html(response);
            },
            error: function() {
                $('#applicationModalBody').html('<div class="alert alert-danger">Error loading application details.</div>');
            }
        });
    });
    
    // Set application ID for modals
    $('.mark-under-review').click(function() {
        $('#markUnderReviewApplicationId').val($(this).data('application-id'));
    });
    
    $('.request-documents').click(function() {
        $('#requestDocumentsApplicationId').val($(this).data('application-id'));
    });
    
    $('.approve-application').click(function() {
        $('#approveApplicationId').val($(this).data('application-id'));
    });
    
    $('.reject-application').click(function() {
        $('#rejectApplicationId').val($(this).data('application-id'));
    });
    
    // Handle Mark Under Review
    $("#markUnderReviewForm").submit(function(e){
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'mark_under_review');
        
        $.ajax({
            url: 'enhanced_manage_applications.php',
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
    
    // Handle Request Documents
    $("#requestDocumentsForm").submit(function(e){
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'request_documents');
        
        $.ajax({
            url: 'enhanced_manage_applications.php',
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
    
    // Handle Approve Application
    $("#approveApplicationForm").submit(function(e){
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'approve');
        
        $.ajax({
            url: 'enhanced_manage_applications.php',
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
    
    // Handle Reject Application
    $("#rejectApplicationForm").submit(function(e){
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'reject');
        
        $.ajax({
            url: 'enhanced_manage_applications.php',
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
});
</script>

</body>
</html>