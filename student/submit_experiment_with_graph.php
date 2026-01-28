<?php
session_start();
include 'db_connect.php';

error_log("POST data: " . print_r($_POST, true));

if (!isset($_SESSION['student_id'])) {
    die("You must log in first.");
}

$student_id = $_SESSION['student_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['experiment_id'], $_POST['employee_id'], $_POST['submission_data'])) {
        die("Missing required fields.");
    }

    $experiment_id = intval($_POST['experiment_id']);
    $employee_id = intval($_POST['employee_id']);
    $submission_data = $conn->real_escape_string($_POST['submission_data']);
    
    // Handle graph data
    $graph_data = null;
    $has_graph = 0;
    
    if (isset($_POST['graph_data']) && !empty($_POST['graph_data'])) {
        $graph_data = $conn->real_escape_string($_POST['graph_data']);
        $has_graph = 1;
    }

    // Insert with graph data if available
    if ($has_graph) {
        $sql = "INSERT INTO submissions (experiment_id, student_id, employee_id, submission_data, graph_data, has_graph, verification_status)
                VALUES (?, ?, ?, ?, ?, ?, 'Pending')";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            die("Database error.");
        }
        
        $stmt->bind_param("iiissi", $experiment_id, $student_id, $employee_id, $submission_data, $graph_data, $has_graph);
    } else {
        $sql = "INSERT INTO submissions (experiment_id, student_id, employee_id, submission_data, has_graph, verification_status)
                VALUES (?, ?, ?, ?, ?, 'Pending')";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            die("Database error.");
        }
        
        $stmt->bind_param("iiisi", $experiment_id, $student_id, $employee_id, $submission_data, $has_graph);
    }

    if ($stmt->execute()) {
        echo "Experiment submitted successfully and sent for verification.";
    } else {
        echo "Error submitting experiment: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Invalid request method.";
}

$conn->close();
?>