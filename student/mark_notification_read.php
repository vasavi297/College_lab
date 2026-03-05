<?php
session_start();
require_once '../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['student_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_SESSION['student_id'];
    $notification_id = isset($_POST['notification_id']) ? intval($_POST['notification_id']) : null;

    if ($notification_id) {
        $sql = "UPDATE student_notifications SET is_read = 1 WHERE student_id = ? AND notification_id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ii", $student_id, $notification_id);
        }
    } else {
        $sql = "UPDATE student_notifications SET is_read = 1 WHERE student_id = ? AND is_read = 0";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $student_id);
        }
    }

    if ($stmt) {
        if ($stmt->execute()) {
            // Return remaining unread count
            $count_sql = "SELECT COUNT(*) as count FROM student_notifications WHERE student_id = ? AND is_read = 0";
            $count_stmt = $conn->prepare($count_sql);
            $count_stmt->bind_param("i", $student_id);
            $count_stmt->execute();
            $count_result = $count_stmt->get_result();
            $count_row = $count_result->fetch_assoc();
            $remaining = (int)($count_row['count'] ?? 0);
            $count_stmt->close();

            echo json_encode(['success' => true, 'remaining' => $remaining]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update notifications']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
}

$conn->close();
?>