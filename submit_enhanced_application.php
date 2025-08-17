<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'User not authenticated']);
    exit();
}

$response = ['status' => 'error', 'message' => 'An unexpected error occurred.'];

try {
    // Validate required fields
    if (!isset($_POST['scholarship_id']) || !isset($_POST['gpa']) || !isset($_POST['family_income'])) {
        throw new Exception('Missing required application information.');
    }

    $scholarship_id = intval($_POST['scholarship_id']);
    $user_id = $_SESSION['user_id'];
    $gpa = floatval($_POST['gpa']);
    $family_income = floatval($_POST['family_income']);

    // Validate GPA
    if ($gpa < 1.0 || $gpa > 4.0) {
        throw new Exception('Invalid GPA value. Must be between 1.0 and 4.0.');
    }

    // Check if scholarship exists and is active
    $scholarship_sql = "SELECT * FROM scholarships WHERE id = ? AND status = 'active'";
    $scholarship_stmt = $conn->prepare($scholarship_sql);
    $scholarship_stmt->bind_param("i", $scholarship_id);
    $scholarship_stmt->execute();
    $scholarship_result = $scholarship_stmt->get_result();
    
    if ($scholarship_result->num_rows === 0) {
        throw new Exception('Scholarship not found or inactive.');
    }
    
    $scholarship = $scholarship_result->fetch_assoc();
    $scholarship_stmt->close();

    // Check if deadline has passed
    $deadline = strtotime($scholarship['deadline']);
    if ($deadline < time()) {
        throw new Exception('Application deadline has passed.');
    }

    // Check if user has already applied
    $check_sql = "SELECT id FROM scholarship_applications WHERE scholarship_id = ? AND user_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("is", $scholarship_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        throw new Exception('You have already applied for this scholarship.');
    }
    $check_stmt->close();

    // Check if max applicants reached
    if ($scholarship['max_applicants'] > 0) {
        $current_applicants_sql = "SELECT COUNT(*) as count FROM scholarship_applications WHERE scholarship_id = ?";
        $current_applicants_stmt = $conn->prepare($current_applicants_sql);
        $current_applicants_stmt->bind_param("i", $scholarship_id);
        $current_applicants_stmt->execute();
        $current_applicants_result = $current_applicants_stmt->get_result();
        $current_applicants = $current_applicants_result->fetch_assoc()['count'];
        $current_applicants_stmt->close();
        
        if ($current_applicants >= $scholarship['max_applicants']) {
            throw new Exception('Maximum number of applicants reached for this scholarship.');
        }
    }

    // Get user details
    $user_sql = "SELECT * FROM users WHERE user_id = ?";
    $user_stmt = $conn->prepare($user_sql);
    $user_stmt->bind_param("s", $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user = $user_result->fetch_assoc();
    $user_stmt->close();

    if (!$user) {
        throw new Exception('User information not found.');
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert application
        $insert_sql = "INSERT INTO scholarship_applications (
            scholarship_id, user_id, application_date, status, gpa, course, year_level, 
            documents_submitted, created_at, updated_at
        ) VALUES (?, ?, NOW(), 'pending', ?, ?, ?, ?, NOW(), NOW())";
        
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("isds", $scholarship_id, $user_id, $gpa, $user['course'], $user['year_level'], '[]');
        
        if (!$insert_stmt->execute()) {
            throw new Exception('Failed to create application: ' . $insert_stmt->error);
        }
        
        $application_id = $conn->insert_id;
        $insert_stmt->close();

        // Process document uploads
        $uploaded_documents = [];
        $documents_dir = 'uploads/scholarship_documents/';
        
        // Create directory if it doesn't exist
        if (!is_dir($documents_dir)) {
            mkdir($documents_dir, 0755, true);
        }

        if (isset($_FILES['documents']) && is_array($_FILES['documents']['name'])) {
            $document_types = $_POST['document_types'] ?? [];
            
            foreach ($_FILES['documents']['name'] as $index => $filename) {
                if ($_FILES['documents']['error'][$index] === UPLOAD_ERR_OK) {
                    $file_tmp = $_FILES['documents']['tmp_name'][$index];
                    $file_size = $_FILES['documents']['size'][$index];
                    $file_type = $_FILES['documents']['type'][$index];
                    $document_type = $document_types[$index] ?? 'Unknown';
                    
                    // Validate file type
                    $allowed_types = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
                    if (!in_array($file_type, $allowed_types)) {
                        throw new Exception('Invalid file type for document: ' . $document_type);
                    }
                    
                    // Validate file size (5MB)
                    if ($file_size > 5 * 1024 * 1024) {
                        throw new Exception('File size too large for document: ' . $document_type);
                    }
                    
                    // Generate unique filename
                    $file_extension = pathinfo($filename, PATHINFO_EXTENSION);
                    $unique_filename = $application_id . '_' . $index . '_' . time() . '.' . $file_extension;
                    $file_path = $documents_dir . $unique_filename;
                    
                    // Move uploaded file
                    if (!move_uploaded_file($file_tmp, $file_path)) {
                        throw new Exception('Failed to save document: ' . $document_type);
                    }
                    
                    // Insert document record
                    $doc_insert_sql = "INSERT INTO scholarship_documents (
                        application_id, document_type, file_name, file_path, file_size, mime_type, uploaded_at
                    ) VALUES (?, ?, ?, ?, ?, ?, NOW())";
                    
                    $doc_insert_stmt = $conn->prepare($doc_insert_sql);
                    $doc_insert_stmt->bind_param("isssis", $application_id, $document_type, $filename, $file_path, $file_size, $file_type);
                    
                    if (!$doc_insert_stmt->execute()) {
                        throw new Exception('Failed to save document record: ' . $doc_insert_stmt->error);
                    }
                    $doc_insert_stmt->close();
                    
                    $uploaded_documents[] = [
                        'type' => $document_type,
                        'filename' => $filename,
                        'path' => $file_path
                    ];
                }
            }
        }

        // Update application with documents info
        $documents_json = json_encode($uploaded_documents);
        $update_docs_sql = "UPDATE scholarship_applications SET documents_submitted = ? WHERE id = ?";
        $update_docs_stmt = $conn->prepare($update_docs_sql);
        $update_docs_stmt->bind_param("si", $documents_json, $application_id);
        $update_docs_stmt->execute();
        $update_docs_stmt->close();

        // Update scholarship current applicants count
        $update_count_sql = "UPDATE scholarships SET current_applicants = current_applicants + 1 WHERE id = ?";
        $update_count_stmt = $conn->prepare($update_count_sql);
        $update_count_stmt->bind_param("i", $scholarship_id);
        $update_count_stmt->execute();
        $update_count_stmt->close();

        // Create notification
        $notification_sql = "INSERT INTO scholarship_notifications (
            user_id, title, message, type, created_at
        ) VALUES (?, ?, ?, 'success', NOW())";
        
        $notification_title = 'Application Submitted Successfully';
        $notification_message = 'Your application for ' . $scholarship['name'] . ' has been submitted successfully. Application ID: ' . $application_id;
        
        $notification_stmt = $conn->prepare($notification_sql);
        $notification_stmt->bind_param("sss", $user_id, $notification_title, $notification_message);
        $notification_stmt->execute();
        $notification_stmt->close();

        // Create audit log
        $audit_sql = "INSERT INTO scholarship_audit_log (
            action, table_name, record_id, new_values, user_id, ip_address, user_agent, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $action = 'CREATE';
        $table_name = 'scholarship_applications';
        $new_values = json_encode([
            'scholarship_id' => $scholarship_id,
            'user_id' => $user_id,
            'gpa' => $gpa,
            'documents_count' => count($uploaded_documents)
        ]);
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        $audit_stmt = $conn->prepare($audit_sql);
        $audit_stmt->bind_param("ssisss", $action, $table_name, $application_id, $new_values, $user_id, $ip_address, $user_agent);
        $audit_stmt->execute();
        $audit_stmt->close();

        // Commit transaction
        $conn->commit();

        $response = [
            'status' => 'success',
            'message' => 'Your scholarship application has been submitted successfully! Application ID: ' . $application_id,
            'application_id' => $application_id,
            'documents_uploaded' => count($uploaded_documents)
        ];

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    $response = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
    
    // Log error
    error_log('Scholarship application error: ' . $e->getMessage());
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>