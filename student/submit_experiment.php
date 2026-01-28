<?php
// ========== CLEAN VERSION - NO DEBUG OUTPUT ==========
// Turn off error display
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// Clear output buffers
while (ob_get_level() > 0) {
    ob_end_clean();
}

// Set JSON header
header('Content-Type: application/json; charset=utf-8');

session_start();

// Check session
if (!isset($_SESSION['student_id'])) {
    echo json_encode(['success' => false, 'message' => 'You must log in first.']);
    exit;
}

// Include database
include '../db_connect.php';

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

$student_id = $_SESSION['student_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if this is a retake submission
    $is_retake = isset($_POST['is_retake']) && $_POST['is_retake'] == '1';
    $retake_id = isset($_POST['retake_id']) ? intval($_POST['retake_id']) : 0;
    
    // Check required fields
    if (!isset($_POST['subject'], $_POST['experiment_number'], $_POST['employee_id'], $_POST['submission_data'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
        exit;
    }

    $subject = $_POST['subject'];
    $experiment_number = intval($_POST['experiment_number']);
    $employee_id = intval($_POST['employee_id']);
    $submission_data = $_POST['submission_data'];
    $retake_count = isset($_POST['retake_count']) ? intval($_POST['retake_count']) : 0;
    
    // Get experiment_id
    $get_exp_sql = "SELECT id, experiment_name FROM experiments 
                    WHERE subject = ? AND experiment_number = ? AND is_active = 1";
    $get_exp_stmt = $conn->prepare($get_exp_sql);
    
    if (!$get_exp_stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
        exit;
    }
    
    $get_exp_stmt->bind_param("si", $subject, $experiment_number);
    
    if (!$get_exp_stmt->execute()) {
        $get_exp_stmt->close();
        echo json_encode(['success' => false, 'message' => 'Error retrieving experiment.']);
        exit;
    }
    
    $exp_result = $get_exp_stmt->get_result();
    
    if ($exp_row = $exp_result->fetch_assoc()) {
        $experiment_id = $exp_row['id'];
        $experiment_name = $exp_row['experiment_name'];
    } else {
        $get_exp_stmt->close();
        echo json_encode(['success' => false, 'message' => 'Experiment not found.']);
        exit;
    }
    $get_exp_stmt->close();
    
    if ($is_retake && $retake_id > 0) {
        // ========== RETAKE SUBMISSION ==========
        // Verify this retake belongs to the current student
        $verify_retake_sql = "SELECT submission_id FROM submissions 
                              WHERE submission_id = ? AND student_id = ? 
                              AND verification_status = 'Retake' 
                              AND can_retake_again = 1";
        $verify_stmt = $conn->prepare($verify_retake_sql);
        
        if (!$verify_stmt) {
            echo json_encode(['success' => false, 'message' => 'Database error.']);
            exit;
        }
        
        $verify_stmt->bind_param("ii", $retake_id, $student_id);
        $verify_stmt->execute();
        $verify_result = $verify_stmt->get_result();
        
        if ($verify_result->num_rows === 0) {
            $verify_stmt->close();
            echo json_encode([
                'success' => false, 
                'message' => 'Retake submission not found or not allowed.'
            ]);
            exit;
        }
        $verify_stmt->close();
        
        // Update the existing submission
        $update_sql = "UPDATE submissions 
                      SET submission_data = ?,
                          submitted_date = NOW(),
                          verification_status = 'Pending',
                          verification_date = NULL,
                          marks_obtained = NULL,
                          feedback = NULL,
                          can_retake_again = 0,
                          retake_count = retake_count + 1,
                          last_retake_date = NOW()
                      WHERE submission_id = ? AND student_id = ?";
        
        $stmt = $conn->prepare($update_sql);
        
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Database error.']);
            exit;
        }
        
        // Clean the submission data to prevent JSON issues
        $clean_submission_data = $submission_data;
        
        $stmt->bind_param("sii", $clean_submission_data, $retake_id, $student_id);
        
        if ($stmt->execute()) {
            $new_attempt_number = $retake_count + 1;
            
            echo json_encode([
                'success' => true, 
                'message' => "Retake #{$new_attempt_number} for experiment '{$experiment_name}' submitted successfully!",
                'submission_id' => $retake_id,
                'subject' => $subject,
                'experiment_name' => $experiment_name,
                'is_retake' => true,
                'attempt_number' => $new_attempt_number
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error submitting retake.']);
        }
        
        $stmt->close();
        
    } else {
        // ========== NEW SUBMISSION ==========
        $sql = "INSERT INTO submissions (experiment_id, student_id, employee_id, submission_data, experiment_subject, verification_status, submitted_date)
                VALUES (?, ?, ?, ?, ?, 'Pending', NOW())";
        
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Database error.']);
            exit;
        }
        
        $stmt->bind_param("iiiss", $experiment_id, $student_id, $employee_id, $submission_data, $subject);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true, 
                'message' => "Experiment '{$experiment_name}' submitted successfully and sent for verification.",
                'submission_id' => $stmt->insert_id,
                'subject' => $subject,
                'experiment_name' => $experiment_name
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error submitting experiment.']);
        }
        
        $stmt->close();
    }
    
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?> 