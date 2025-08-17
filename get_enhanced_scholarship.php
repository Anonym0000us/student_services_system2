<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'User not authenticated']);
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Enhanced query with all scholarship details
    $sql = "SELECT s.*, 
            (SELECT COUNT(*) FROM scholarship_applications sa WHERE sa.scholarship_id = s.id) as current_applicants,
            (SELECT COUNT(*) FROM scholarship_applications sa WHERE sa.scholarship_id = s.id AND sa.status = 'approved') as approved_applicants
            FROM scholarships s 
            WHERE s.id = ? AND s.status = 'active'";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Database error: ' . $conn->error]);
        exit();
    }
    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Check if user has already applied
        $user_id = $_SESSION['user_id'];
        $check_sql = "SELECT status FROM scholarship_applications WHERE scholarship_id = ? AND user_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("is", $id, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $application_status = $check_result->fetch_assoc()['status'];
            $row['user_application_status'] = $application_status;
            $row['can_apply'] = false;
            $row['application_message'] = 'You have already applied for this scholarship.';
        } else {
            $row['user_application_status'] = null;
            $row['can_apply'] = true;
            $row['application_message'] = 'You can apply for this scholarship.';
        }
        $check_stmt->close();
        
        // Check if deadline has passed
        $deadline = strtotime($row['deadline']);
        $current_time = time();
        $row['deadline_passed'] = $deadline < $current_time;
        
        if ($row['deadline_passed']) {
            $row['can_apply'] = false;
            $row['application_message'] = 'Application deadline has passed.';
        }
        
        // Check if max applicants reached
        if ($row['max_applicants'] > 0 && $row['current_applicants'] >= $row['max_applicants']) {
            $row['can_apply'] = false;
            $row['application_message'] = 'Maximum number of applicants reached.';
        }
        
        // Format documents required
        if (!empty($row['documents_required'])) {
            $documents = json_decode($row['documents_required'], true);
            if (is_array($documents)) {
                $row['documents_list'] = $documents;
            } else {
                $row['documents_list'] = [$row['documents_required']];
            }
        } else {
            $row['documents_list'] = [];
        }
        
        // Calculate days until deadline
        $days_until_deadline = ceil(($deadline - $current_time) / (60 * 60 * 24));
        $row['days_until_deadline'] = $days_until_deadline;
        
        // Format amount
        $row['amount_formatted'] = number_format($row['amount'], 2);
        
        // Add eligibility check for current user
        $user_sql = "SELECT * FROM users WHERE user_id = ?";
        $user_stmt = $conn->prepare($user_sql);
        $user_stmt->bind_param("s", $user_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        $user_data = $user_result->fetch_assoc();
        $user_stmt->close();
        
        if ($user_data) {
            $row['user_eligibility'] = [
                'gpa' => $user_data['gpa'] ?? null,
                'course' => $user_data['course'] ?? null,
                'year_level' => $user_data['year_level'] ?? null,
                'has_financial_need' => $user_data['family_income'] ?? null
            ];
        }
        
        header('Content-Type: application/json');
        echo json_encode($row);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Scholarship not found or inactive']);
    }
    $stmt->close();
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No scholarship ID provided']);
}

$conn->close();
?>