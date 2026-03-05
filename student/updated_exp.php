<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['name']) || !isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

function ensureTargetStudentColumn($conn) {
    $check = $conn->query("SHOW COLUMNS FROM weekly_experiments LIKE 'target_students'");
    if ($check && $check->num_rows === 0) {
        $conn->query("ALTER TABLE weekly_experiments ADD COLUMN target_students TEXT NULL AFTER target_section");
    }
}

ensureTargetStudentColumn($conn);

$student_id = $_SESSION['student_id'];
$roll_number = $_SESSION['roll_number'] ?? '';
$student_roll_code = strtoupper(trim($roll_number));
$student_branch = $_SESSION['branch'];
$student_username = $_SESSION['username'] ?? '';
$student_section = isset($_SESSION['section']) ? $_SESSION['section'] : '';
$subject = isset($_GET['subject']) ? htmlspecialchars($_GET['subject']) : '';
$can_download = 0;

$download_stmt = $conn->prepare("SELECT can_download FROM students WHERE student_id = ?");
if ($download_stmt) {
    $download_stmt->bind_param("i", $student_id);
    $download_stmt->execute();
    $download_result = $download_stmt->get_result();
    if ($download_result && $download_row = $download_result->fetch_assoc()) {
        $can_download = (int)($download_row['can_download'] ?? 0);
    }
    $download_stmt->close();
}
$notification_count = 0;
$notifications = [];
$announcement_count = 0;
$announcements = [];

$ann_stmt = $conn->query("SELECT COUNT(*) AS count FROM announcements");
if ($ann_stmt) {
    if ($row = $ann_stmt->fetch_assoc()) {
        $announcement_count = (int)($row['count'] ?? 0);
    }
    $ann_stmt->free();
}

$ann_list = $conn->query("SELECT id, title, description, created_at FROM announcements ORDER BY created_at DESC LIMIT 10");
if ($ann_list) {
    while ($row = $ann_list->fetch_assoc()) {
        $announcements[] = $row;
    }
    $ann_list->free();
}

$dedupe_sql = "DELETE n1 FROM student_notifications n1
               INNER JOIN student_notifications n2
                   ON n1.student_id = n2.student_id
                  AND n1.title = n2.title
                  AND n1.message = n2.message
                  AND n1.notification_type = n2.notification_type
                  AND n1.notification_id > n2.notification_id
               WHERE n1.student_id = ?";
$dedupe_stmt = $conn->prepare($dedupe_sql);
if ($dedupe_stmt) {
    $dedupe_stmt->bind_param("i", $student_id);
    $dedupe_stmt->execute();
    $dedupe_stmt->close();
}

$notif_sql = "SELECT * FROM student_notifications 
              WHERE student_id = ? 
              ORDER BY created_at DESC 
              LIMIT 10";
$notif_stmt = $conn->prepare($notif_sql);
if ($notif_stmt) {
    $notif_stmt->bind_param("i", $student_id);
    $notif_stmt->execute();
    $notif_result = $notif_stmt->get_result();
    $notifications = $notif_result->fetch_all(MYSQLI_ASSOC);
    
    $count_sql = "SELECT COUNT(*) as count FROM student_notifications WHERE student_id = ? AND is_read = 0";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param("i", $student_id);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $count_row = $count_result->fetch_assoc();
    $notification_count = $count_row['count'] ?? 0;
    
    $count_stmt->close();
    $notif_stmt->close();
}

// Get current week
$current_week = date('W');
$current_year = date('Y');

// ============================================
// HELPER FUNCTION: Convert Roman to Number
// ============================================
function romanToNumber($roman) {
    $romans = [
        'I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4,
        'V' => 5, 'VI' => 6, 'VII' => 7, 'VIII' => 8
    ];
    return $romans[$roman] ?? 1;
}

// ============================================
// GET STUDENT'S SEMESTER
// ============================================
$semester_query = "SELECT semester FROM students WHERE student_id = ?";
$stmt_sem = $conn->prepare($semester_query);
$stmt_sem->bind_param("i", $student_id);
$stmt_sem->execute();
$semester_result = $stmt_sem->get_result();
$student_semester = 'I';
if ($row = $semester_result->fetch_assoc()) {
    $student_semester = $row['semester'];
}

$semester_number = romanToNumber($student_semester);

$experiments = [];
$submission_status = [];

// ============================================
// GET AVAILABLE SUBJECTS FROM semester_subject_assignments TABLE
// ============================================
$available_subjects = [];
$subject_types = [];

// Fetch subjects from semester_subject_assignments table
$subjects_query = "SELECT subject_name, subject_type 
                   FROM semester_subject_assignments 
                   WHERE semester = ? AND branch = ? 
                   ORDER BY subject_type, subject_name";
$stmt_subjects = $conn->prepare($subjects_query);
$stmt_subjects->bind_param("ss", $student_semester, $student_branch);
$stmt_subjects->execute();
$subjects_result = $stmt_subjects->get_result();

while ($row = $subjects_result->fetch_assoc()) {
    $available_subjects[] = $row['subject_name'];
    // Keep UI types limited to BSH / PROFESSIONAL
    $subject_types[$row['subject_name']] = ($row['subject_type'] === 'BSH') ? 'BSH' : 'PROFESSIONAL';
}
$stmt_subjects->close();

// ============================================
// GET EXPERIMENTS IF SUBJECT IS SELECTED
// ============================================
if (!empty($subject) && in_array($subject, $available_subjects)) {
    // Get experiments enabled for current week
        $query = "SELECT e.Id as experiment_id, e.experiment_number, e.experiment_name, e.file_path,
                                         we.enabled_until, we.enabled_date, we.target_branch, we.target_section, we.target_students,
                                         CASE 
                                            WHEN we.experiment_id IS NOT NULL 
                                                 AND (we.enabled_by IS NULL OR es.id IS NOT NULL)
                                            THEN 'enabled'
                                                 ELSE 'disabled'
                                         END as status
                            FROM experiments e
                            LEFT JOIN weekly_experiments we ON e.Id = we.experiment_id
                                AND we.is_active = 1
                                AND (we.enabled_date IS NULL OR we.enabled_date <= CURDATE())
                                AND (we.enabled_until IS NULL OR we.enabled_until >= CURDATE())
                                AND (we.target_branch IS NULL OR we.target_branch = '' OR FIND_IN_SET(?, we.target_branch) > 0)
                                AND (we.target_section IS NULL OR we.target_section = '' OR FIND_IN_SET(?, we.target_section) > 0)
                                AND (
                                    we.target_students IS NULL OR we.target_students = ''
                                    OR FIND_IN_SET(?, we.target_students) > 0
                                    OR FIND_IN_SET(?, we.target_students) > 0
                                )
                            LEFT JOIN employees emp ON we.enabled_by = emp.employee_id
                            LEFT JOIN employee_subjects es ON emp.username = es.employee_username
                                AND es.branch = ?
                                AND es.section = ?
                                AND LOWER(REPLACE(REPLACE(TRIM(es.subject), ' ', ''), '_', '')) = LOWER(REPLACE(REPLACE(TRIM(e.subject), ' ', ''), '_', ''))
                            WHERE e.subject = ? AND e.is_active = 1
                            ORDER BY e.experiment_number";
    
        $stmt = $conn->prepare($query);
        $student_id_str = (string)$student_id;
        $stmt->bind_param("sssssss", $student_branch, $student_section, $student_roll_code, $student_id_str, $student_branch, $student_section, $subject);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $experiments[] = $row;
    }
    $stmt->close();
    
    // Get submission status
    if (!empty($experiments)) {
        $experiment_ids = array_column($experiments, 'experiment_id');
        $placeholders = implode(',', array_fill(0, count($experiment_ids), '?'));
        
        $submission_query = "SELECT s1.experiment_id, s1.verification_status, s1.can_retake_again
                             FROM submissions s1
                             INNER JOIN (
                                 SELECT experiment_id, MAX(submitted_date) as latest_date
                                 FROM submissions
                                 WHERE student_id = ? AND experiment_id IN ($placeholders)
                                 GROUP BY experiment_id
                             ) s2 ON s1.experiment_id = s2.experiment_id 
                                AND s1.submitted_date = s2.latest_date
                                AND s1.student_id = ?";
        
        $stmt2 = $conn->prepare($submission_query);
        $params = array_merge([$student_id], $experiment_ids, [$student_id]);
        $types = str_repeat('i', count($params));
        $stmt2->bind_param($types, ...$params);
        $stmt2->execute();
        $submission_result = $stmt2->get_result();
        
        while ($sub_row = $submission_result->fetch_assoc()) {
            $submission_status[$sub_row['experiment_id']] = [
                'verification_status' => $sub_row['verification_status'],
                'can_retake_again' => $sub_row['can_retake_again']
            ];
        }
        $stmt2->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Updated Experiments | SVEC</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../css/notifications.css">
<link rel="stylesheet" href="../css/announcements.css">

<style>
    :root {
        --primary-color: #1e3a8a;
        --primary-light: #eff6ff;
        --accent-color: #2563eb;
        --secondary-color: #dc2626;
        --text-dark: #0f172a;
        --text-gray: #64748b;
        --bg-body: #f1f5f9;
        --white: #ffffff;
        
        --sidebar-width: 290px;
        --header-height: 74px;
        
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow-card: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        --shadow-hover: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        
        --radius-lg: 16px;
        --radius-md: 12px;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
    
    html, body {
        height: 100%;
        width: 100%;
        overflow: hidden;
        background-color: var(--bg-body);
        color: var(--text-dark);
    }
    
    a { text-decoration: none; color: inherit; }

    /* SIDEBAR */
    .sidebar {
        width: var(--sidebar-width);
        background: var(--white);
        height: 100vh;
        position: fixed;
        left: 0; top: 0; z-index: 100;
        border-right: 1px solid #e2e8f0;
        display: flex; flex-direction: column;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 10px 0 30px rgba(0,0,0,0.03);
    }
    .sidebar.closed { transform: translateX(-100%); }

    .sidebar-brand { height: 90px; display: flex; align-items: center; justify-content: space-between; padding: 0 24px; border-bottom: 1px solid #f1f5f9; flex-shrink: 0; }
    .brand-wrapper { display: flex; align-items: center; gap: 12px; }
    .brand-wrapper img { height: 52px; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1)); }
    .brand-text { display: flex; flex-direction: column; line-height: 1.1; }
    .brand-title { font-size: 24px; font-weight: 800; color: var(--primary-color); letter-spacing: -0.5px; }
    .brand-subtitle { font-size: 10px; text-transform: uppercase; color: var(--text-gray); font-weight: 600; letter-spacing: 1px; }

    .close-btn { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-gray); transition: 0.2s; }
    .close-btn:hover { background: #f1f5f9; color: var(--secondary-color); }

    .sidebar-user { padding: 24px; text-align: center; background: linear-gradient(to bottom, #ffffff, #f8fafc); border-bottom: 1px solid #e2e8f0; flex-shrink: 0; }
    .sidebar-user img { width: 70px; height: 70px; border-radius: 50%; object-fit: cover; border: 4px solid var(--white); box-shadow: var(--shadow-card); margin-bottom: 10px; }

    .sidebar-menu { padding: 20px 16px; flex: 1; overflow-y: auto; }
    .menu-item { display: flex; align-items: center; padding: 14px 18px; margin-bottom: 6px; border-radius: var(--radius-md); color: var(--text-gray); font-weight: 500; font-size: 14px; transition: all 0.2s; border: 1px solid transparent; }
    .menu-item i { width: 26px; font-size: 18px; margin-right: 12px; color: #94a3b8; transition: 0.2s; }
    .menu-item:hover { background-color: #f8fafc; color: var(--primary-color); border-color: #e2e8f0; }
    .menu-item:hover i { color: var(--primary-color); }
    .menu-item.active { background: linear-gradient(45deg, var(--primary-color), #2563eb); color: var(--white); box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3); }
    .menu-item.active i { color: var(--white); }

    .logout-container { padding: 20px; border-top: 1px solid #f1f5f9; flex-shrink: 0; }
    .logout-btn { display: flex; justify-content: center; align-items: center; width: 100%; padding: 12px; border-radius: var(--radius-md); background-color: #fef2f2; color: var(--secondary-color); font-weight: 600; font-size: 14px; transition: 0.2s; border: 1px solid #fee2e2; }
    .logout-btn:hover { background-color: #fee2e2; transform: translateY(-1px); }

    /* MAIN CONTENT */
    .main-content { 
        margin-left: var(--sidebar-width);
        width: calc(100% - var(--sidebar-width));
        height: 100vh;
        overflow-y: auto;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .main-content.full-width { margin-left: 0; width: 100%; }

    .top-header { 
        height: var(--header-height); 
        background: rgba(255, 255, 255, 0.9); 
        backdrop-filter: blur(10px); 
        display: flex; justify-content: space-between; align-items: center; 
        padding: 0 32px; 
        position: sticky; top: 0; z-index: 90; 
        border-bottom: 1px solid #e2e8f0; 
    }
    .header-branding h1 { font-size: 18px; font-weight: 800; color: var(--primary-color); }
    .header-branding p { font-size: 11px; color: var(--text-gray); font-weight: 600; letter-spacing: 0.5px; }
    .toggle-btn { font-size: 20px; cursor: pointer; padding: 8px; border-radius: 8px; border: none; background: transparent; margin-right: 15px; color: var(--text-dark); }
    
    .header-right { display: flex; align-items: center; gap: 15px; }
    .header-icons { display: flex; align-items: center; gap: 12px; }
    .header-icon { width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; background: #f1f5f9; border-radius: 50%; cursor: pointer; transition: all 0.2s; position: relative; color: var(--primary-color); }
    .header-icon:hover { background: #e2e8f0; transform: translateY(-1px); }
    .header-icon .badge { position: absolute; top: 2px; right: 2px; min-width: 18px; height: 18px; padding: 0 5px; background: var(--secondary-color); color: #fff; border-radius: 10px; border: 2px solid #fff; font-size: 11px; font-weight: 700; display: flex; align-items: center; justify-content: center; }
    
    .profile-section { display: flex; align-items: center; gap: 12px; }
    .profile-section img { width: 42px; height: 42px; border-radius: 50%; border: 2px solid #e2e8f0; object-fit: cover; }
    .profile-section .user-info { text-align: right; }
    .profile-section .user-name { font-size: 14px; font-weight: 700; color: var(--text-dark); }
    .profile-section .user-role { font-size: 11px; color: var(--text-gray); font-weight: 600; }
    
    .header-logout-btn { display: flex; align-items: center; gap: 6px; padding: 8px 14px; background: #fef2f2; color: var(--secondary-color); border: 1px solid #fecaca; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none; transition: all 0.2s; }
    .header-logout-btn:hover { background: #fee2e2; transform: translateY(-1px); }
    
    .dashboard-container { 
        padding: 24px; 
        height: calc(100vh - var(--header-height));
        overflow-y: auto;
    }

    .overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.4); backdrop-filter: blur(2px); z-index: 95; opacity: 0; visibility: hidden; transition: 0.3s; }

    /* PAGE TITLE */
    .page-title {
        font-size: 24px;
        font-weight: 800;
        color: var(--text-dark);
        margin-bottom: 24px;
    }

    /* WEEK INFO */
    .week-info {
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        color: white;
        padding: 16px 24px;
        border-radius: var(--radius-md);
        margin-bottom: 24px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .week-info i {
        font-size: 20px;
    }

    /* SEMESTER INFO */
    .semester-info {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        padding: 12px 20px;
        border-radius: var(--radius-md);
        margin-bottom: 16px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
    }

    /* SUBJECT CARDS */
    .subject-selection {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
    }
    .subject-card {
        background: var(--white);
        border: 1px solid #e2e8f0;
        border-radius: var(--radius-lg);
        padding: 30px;
        cursor: pointer;
        text-align: center;
        transition: all 0.3s;
        box-shadow: var(--shadow-sm);
        position: relative;
    }
    .subject-card:hover {
        border-color: var(--accent-color);
        transform: translateY(-4px);
        box-shadow: var(--shadow-hover);
    }
    .subject-card h3 {
        font-size: 18px;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 8px;
    }
    .subject-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
    }
    .badge-bsh {
        background: #dcfce7;
        color: #15803d;
    }
    .badge-professional {
        background: #dbeafe;
        color: #1d4ed8;
    }

    /* EXPERIMENT LIST */
    .experiment-card {
        background: var(--white);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-card);
        border: 1px solid #e2e8f0;
        overflow: hidden;
        margin-bottom: 16px;
    }
    .experiment-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 24px;
        border-bottom: 1px solid #f1f5f9;
        transition: 0.2s;
    }
    .experiment-row:last-child {
        border-bottom: none;
    }
    .experiment-row:hover {
        background: #f8fafc;
    }
    .experiment-info {
        display: flex;
        align-items: center;
        gap: 16px;
        flex: 1;
    }
    .experiment-number {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: var(--primary-light);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        color: var(--accent-color);
        font-size: 16px;
        flex-shrink: 0;
    }
    .experiment-details h4 {
        font-size: 15px;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 4px;
    }
    .experiment-status {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
    }
    .status-enabled {
        background: #dcfce7;
        color: #15803d;
    }
    .status-disabled {
        background: #f1f5f9;
        color: #64748b;
    }

    /* SUBMISSION STATUS BADGES */
    .submission-status {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        display: inline-block;
    }
    .submission-verified {
        background: #dcfce7;
        color: #15803d;
    }
    .submission-pending {
        background: #fef08a;
        color: #854d0e;
    }
    .submission-retake {
        background: #fed7aa;
        color: #92400e;
    }

    /* BUTTON STATES */
    .start-btn {
        background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 10px;
        cursor: pointer;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.2s;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }
    .start-btn:hover:not(:disabled):not(.completed):not(.pending) {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4);
    }
    .start-btn.disabled {
        background: #e2e8f0;
        color: #94a3b8;
        cursor: not-allowed;
        box-shadow: none;
    }
    .start-btn.disabled:hover {
        transform: none;
    }
    .start-btn.completed {
        background: linear-gradient(45deg, #10b981, #059669);
        color: white;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        cursor: not-allowed;
    }
    .start-btn.completed:hover {
        transform: none;
    }
    .start-btn.pending {
        background: linear-gradient(45deg, #f59e0b, #d97706);
        color: white;
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        cursor: not-allowed;
    }
    .start-btn.pending:hover {
        transform: none;
    }
    .start-btn.retake {
        background: linear-gradient(45deg, #ef4444, #dc2626);
        color: white;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    }
    .start-btn.retake:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(239, 68, 68, 0.4);
    }
    .back-btn {
        background: var(--white);
        color: var(--text-dark);
        border: 1px solid #e2e8f0;
        padding: 12px 24px;
        border-radius: 10px;
        cursor: pointer;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.2s;
        margin-top: 20px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .back-btn:hover {
        background: #f8fafc;
        border-color: var(--accent-color);
    }

    .no-data {
        text-align: center;
        padding: 60px 20px;
        color: var(--text-gray);
    }
    .no-data i {
        font-size: 48px;
        margin-bottom: 16px;
        color: #cbd5e1;
    }
    .no-data p {
        font-size: 16px;
        font-weight: 500;
    } .modal-close { position: absolute; top: 15px; right: 20px; font-size: 1.8rem; cursor: pointer; color: var(--text-gray); transition: 0.2s; border: none; background: none; }
    .modal-close:hover { color: var(--secondary-color); }
    .modal-title { text-align: center; color: var(--primary-color); margin-bottom: 1.8rem; font-size: 1.4rem; font-weight: 700; }
    

    @media (max-width: 992px) {
        .sidebar { transform: translateX(-100%); width: 280px; }
        .sidebar.active { transform: translateX(0); }
        .main-content { margin-left: 0; width: 100%; }
        .overlay.active { opacity: 1; visibility: visible; }
    }

    @media (max-width: 768px) {
        .subject-selection { grid-template-columns: 1fr; }
        .experiment-row { flex-direction: column; align-items: flex-start; gap: 16px; }
        .start-btn { width: 100%; text-align: center; }
        .top-header { padding: 0 16px; }
        .dashboard-container { padding: 16px; }
    }

  @media (max-width: 600px) {
        .header-branding { display: flex; flex-direction: column; }
        .header-branding h1 { font-size: 13px; line-height: 1.1; }
        .header-right .info-text { display: none; }
        .header-logout-btn span { display: none; }
        .header-logout-btn { padding: 8px; }
} 
  

   
</style>
</head>
<body>

    <div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

    <!-- SIDEBAR -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-wrapper">
                <img src="../images/vasavi.png" alt="Logo">
                <div class="brand-text">
                    <span class="brand-title">SVEC</span>
                    <span class="brand-subtitle">Autonomous</span>
                </div>
            </div>
            <div class="close-btn" onclick="toggleSidebar()"><i class="fa-solid fa-xmark"></i></div>
        </div>
        <div class="sidebar-user">
            <img src="student.jpg" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['name']); ?>&background=1e3a8a&color=fff'">
            <div style="font-weight:700; color:var(--text-dark);">Welcome <?php echo htmlspecialchars($_SESSION['name']); ?> !!</div>
            <div style="font-size:12px; color:var(--text-gray);">ID: <?php echo htmlspecialchars($roll_number); ?></div>
           
            <?php if ($semester_number <= 2): ?>
                <div style="font-size:11px; color:#15803d; background:#dcfce7; padding:3px 8px; border-radius:10px; margin-top:8px; display:inline-block;">
                    BSH Phase
                </div>
            <?php else: ?>
                <div style="font-size:11px; color:#1d4ed8; background:#dbeafe; padding:3px 8px; border-radius:10px; margin-top:8px; display:inline-block;">
                    Professional Phase
                </div>
            <?php endif; ?>
        </div>
        <div class="sidebar-menu">
            <a href="updated_exp.php" class="menu-item active"> Dashboard</a>
            <a href="profile.php" class="menu-item"> My Profile</a>
            <a href="completed_exp.php" class="menu-item"> Completed Experiments</a>
            <a href="retake_exp.php" class="menu-item"> Retake Experiments</a>
            <a href="timetable.php" class="menu-item"> Timetable</a>
        </div>
        <div class="logout-container">
            <a href="logout.php" class="logout-btn"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
        </div>
    </nav>

    <!-- MAIN CONTENT -->
    <main class="main-content" id="mainContent">
        <header class="top-header">
            <div style="display:flex; align-items:center;">
                <button class="toggle-btn" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
                <div class="header-branding">
                    <h1>SRI VASAVI ENGINEERING COLLEGE</h1>
                    
                </div>
            </div>
            <div class="header-right" style="display:flex; align-items:center; gap:15px;">
                <div class="header-icons">
                    <div class="header-icon" title="Messages" onclick="toggleAnnouncementSidebar()"><i class="fa-regular fa-message"></i><?php if ($announcement_count > 0): ?><span class="badge"><?php echo $announcement_count > 9 ? '9+' : $announcement_count; ?></span><?php endif; ?></div>
                    <div class="header-icon" title="Notifications" onclick="toggleNotificationSidebar()" style="cursor: pointer; position: relative;">
                        <i class="fa-regular fa-bell"></i>
                        <?php if ($notification_count > 0): ?>
                            <span class="notification-badge"><?php echo $notification_count > 9 ? '9+' : $notification_count; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="profile-section">
                    <div class="user-info info-text">
                        <div class="user-name"><?php echo htmlspecialchars($_SESSION['name']); ?></div>
                        <div class="user-role">Student - Sem <?php echo $student_semester; ?></div>
                    </div>
                    <img src="student.jpg" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['name']); ?>'">
                </div>
                <a href="logout.php" class="header-logout-btn"><i class="fa-solid fa-arrow-right-from-bracket"></i><span> LOG OUT</span></a>
            </div>
        </header>

        <div class="dashboard-container">

       
            
            <?php if (empty($subject)): ?>
            
            <h1 class="page-title">Select Subject</h1>
            
            <div class="week-info">
                <i class="fa-regular fa-calendar"></i>
                Current Week: <?php echo $current_week; ?> | Year: <?php echo $current_year; ?>
            </div>
            
            <div class="semester-info">
                <i class="fa-solid fa-graduation-cap"></i>
                Semester <?php echo $student_semester; ?> | 
                <i class="fa-solid fa-code-branch"></i> <?php echo htmlspecialchars($student_branch); ?>
                <?php if ($semester_number <= 2): ?>
                    <span style="font-size:12px; opacity:0.9; margin-left:10px; background:rgba(255,255,255,0.2); padding:2px 8px; border-radius:10px;">
                        BSH Phase
                    </span>
                <?php else: ?>
                    <span style="font-size:12px; opacity:0.9; margin-left:10px; background:rgba(255,255,255,0.2); padding:2px 8px; border-radius:10px;">
                        Professional Phase
                    </span>
                <?php endif; ?>
            </div>
            
            <div class="subject-selection">
                <?php if (!empty($available_subjects)): ?>
                    <?php foreach ($available_subjects as $subj): ?>
                        <?php
                            // Get badge type from database
                            $badge_type = $subject_types[$subj] ?? 'PROFESSIONAL';
                            $badge_classes = [
                                'BSH' => ['class' => 'badge-bsh', 'text' => 'BSH', 'desc' => 'Basic Science & Humanities'],
                                'PROFESSIONAL' => ['class' => 'badge-professional', 'text' => 'PROFESSIONAL', 'desc' => 'Professional Subject']
                            ];
                            $badge = $badge_classes[$badge_type] ?? $badge_classes['PROFESSIONAL'];
                        ?>
                        <div class="subject-card" onclick="selectSubject('<?php echo htmlspecialchars($subj); ?>')">
                            <span class="subject-badge <?php echo $badge['class']; ?>">
                                <?php echo $badge['text']; ?>
                            </span>
                            <h3><?php echo htmlspecialchars($subj); ?></h3>
                            <p style="font-size:12px; color:var(--text-gray); margin-top:5px;">
                                <?php echo $badge['desc']; ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                        <i class="fa-regular fa-folder-open" style="font-size:48px; color:#cbd5e1; margin-bottom:16px;"></i>
                        <p style="color: #64748b; font-size: 16px;">No subjects available for Semester <?php echo $student_semester; ?>.</p>
                        <p style="color: #94a3b8; font-size: 14px; margin-top: 10px;">
                            Subjects will be assigned based on your semester and branch.
                        </p>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php else: ?>
            
            <h1 class="page-title">Experiments - <?php echo htmlspecialchars($subject); ?></h1>
            
            <div class="week-info">
                <i class="fa-regular fa-calendar"></i>
                Week: <?php echo $current_week; ?> | Year: <?php echo $current_year; ?>
            </div>
            
            <?php 
                // Get subject type for the selected subject
                $selected_subject_type = isset($subject_types[$subject]) ? $subject_types[$subject] : 'PROFESSIONAL';
                $subject_type_badge = [
                    'BSH' => ['class' => 'badge-bsh', 'text' => 'BSH'],
                    'PROFESSIONAL' => ['class' => 'badge-professional', 'text' => 'PROFESSIONAL']
                ][$selected_subject_type] ?? ['class' => 'badge-professional', 'text' => 'PROFESSIONAL'];
            ?>
            
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:20px;">
                <span class="subject-badge <?php echo $subject_type_badge['class']; ?>" style="position:static;">
                    <?php echo $subject_type_badge['text']; ?> SUBJECT
                </span>
                <span style="color:var(--text-gray); font-size:14px;">
                    <i class="fa-solid fa-graduation-cap"></i> Semester <?php echo $student_semester; ?>
                    | <i class="fa-solid fa-code-branch"></i> <?php echo htmlspecialchars($student_branch); ?>
                </span>
            </div>

            <div style="display:flex; justify-content:center; margin-bottom:25px;">
                <a href="<?php echo $can_download ? 'download_subject_pdf.php?subject=' . urlencode($subject) : '#'; ?>"
                   style="display:inline-flex; align-items:center; justify-content:center; gap:10px; padding:16px 30px; border-radius:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:#fff; background:linear-gradient(45deg, #1e3a8a, #2563eb); box-shadow:0 12px 25px rgba(30,58,138,0.2); min-width:260px; text-decoration:none; transition:all 0.3s; <?php echo !$can_download ? 'cursor:not-allowed; opacity:0.6;' : 'transform:translateY(0);'; ?>"
                   <?php echo $can_download ? 'target="_blank" rel="noopener"' : 'onclick="return false;"'; ?>>
                    <i class="fa-solid fa-file-pdf"></i>
                    Download <?php echo htmlspecialchars($subject); ?> PDF
                </a>
            </div>

            <?php if (!$can_download): ?>
                <div style="margin-bottom:25px; padding:12px 16px; border-radius:10px; border:1px solid #fecaca; background:#fef2f2; color:#b91c1c; font-size:13px;">
                    <i class="fa-solid fa-lock" style="margin-right:6px;"></i>
                    PDF downloads are currently disabled by the administrator. Please contact your department to enable this feature.
                </div>
            <?php endif; ?>
            
            <?php if (empty($experiments)): ?>
                <div class="no-data">
                    <i class="fa-regular fa-folder-open"></i>
                    <p>No experiments available for this subject.</p>
                    <p style="font-size:14px; color:#94a3b8; margin-top:8px;">
                        Experiments will be enabled by your instructor.
                    </p>
                </div>
                <button class="back-btn" onclick="goBack()">
                    <i class="fa-solid fa-arrow-left"></i> Back to Subjects
                </button>
            <?php else: ?>
                <div class="experiment-card">
                    <?php foreach ($experiments as $exp): ?>
                        <?php
                            $exp_id = $exp['experiment_id'];
                            $has_submission = isset($submission_status[$exp_id]);
                            $verification_status = $has_submission ? $submission_status[$exp_id]['verification_status'] : null;
                            $can_retake_again = $has_submission ? $submission_status[$exp_id]['can_retake_again'] : 0;
                            
                            // Determine button state
                            $button_text = 'Start';
                            $button_class = '';
                            $button_disabled = false;
                            $button_onclick = "startExperiment('" . htmlspecialchars($exp['file_path']) . "')";
                            
                            if ($exp['status'] != 'enabled') {
                                $button_text = 'Not Available';
                                $button_class = 'disabled';
                                $button_disabled = true;
                            } 
                            elseif ($has_submission) {
                                if ($verification_status == 'Verified') {
                                    $button_text = '✓ Completed';
                                    $button_class = 'completed';
                                    $button_disabled = true;
                                } 
                                elseif ($verification_status == 'Pending') {
                                    $button_text = '⏳ Pending';
                                    $button_class = 'pending';
                                    $button_disabled = true;
                                } 
                                elseif ($verification_status == 'Retake') {
                                    if ($can_retake_again == 1) {
                                        $button_text = '↻ Retake';
                                        $button_class = 'retake';
                                        $button_disabled = false;
                                        $button_onclick = "startRetake(" . $exp_id . ")";
                                    } else {
                                        $button_text = '⏳ Submitted';
                                        $button_class = 'pending';
                                        $button_disabled = true;
                                    }
                                }
                            }
                        ?>
                        <div class="experiment-row">
                            <div class="experiment-info">
                                <div class="experiment-number"><?php echo $exp['experiment_number']; ?></div>
                                <div class="experiment-details">
                                    <h4><?php echo htmlspecialchars($exp['experiment_name']); ?></h4>
                                    <span class="experiment-status <?php echo $exp['status']=='enabled'?'status-enabled':'status-disabled'; ?>">
                                        <?php echo $exp['status']=='enabled'?'Available':'Not Available'; ?>
                                    </span>
                                    <?php if ($has_submission): ?>
                                        <span class="submission-status submission-<?php echo strtolower($verification_status); ?>" style="margin-left: 8px;">
                                            <?php echo htmlspecialchars($verification_status); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <button class="start-btn <?php echo $button_class; ?>" 
                                    onclick="<?php if (!$button_disabled) echo $button_onclick; ?>"
                                    <?php echo $button_disabled ? 'disabled' : ''; ?>>
                                <?php echo $button_text; ?>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <button class="back-btn" onclick="goBack()">
                    <i class="fa-solid fa-arrow-left"></i> Back to Subjects
                </button>
            <?php endif; ?>
            
            <?php endif; ?>

        </div>
    </main>

    <div class="announcement-overlay" id="announcementOverlay" onclick="closeAnnouncementSidebar()"></div>

    <div class="announcement-sidebar" id="announcementSidebar">
        <div class="announcement-header">
            <h3><i class="fa-regular fa-message"></i> Announcements</h3>
            <button class="announcement-close" onclick="closeAnnouncementSidebar()">&times;</button>
        </div>
        <div class="announcement-content">
            <?php if (empty($announcements)): ?>
                <div class="announcement-empty">
                    <i class="fa-regular fa-circle-check" style="font-size: 32px; display:block; margin-bottom:8px;"></i>
                    No announcements yet.
                </div>
            <?php else: ?>
                <?php foreach ($announcements as $ann): ?>
                    <div 
                        class="announcement-item unread"
                        data-id="<?php echo (int)($ann['id'] ?? 0); ?>"
                        data-title="<?php echo htmlspecialchars($ann['title'], ENT_QUOTES); ?>"
                        data-body="<?php echo htmlspecialchars($ann['description'], ENT_QUOTES); ?>"
                        data-date="<?php echo date('M d, Y h:i A', strtotime($ann['created_at'])); ?>"
                        data-type="Announcement"
                        onclick="handleAnnouncementClick(this)"
                    >
                        <div class="announcement-title">
                            <span><?php echo htmlspecialchars($ann['title']); ?></span>
                            <span class="announcement-time" style="color: var(--text-gray); font-size: 12px; font-weight: 600;">
                                <?php echo date('h:i A', strtotime($ann['created_at'])); ?>
                            </span>
                        </div>
                        <div class="announcement-body"><?php echo nl2br(htmlspecialchars($ann['description'])); ?></div>
                        <div class="announcement-meta">
                            <i class="fa-regular fa-clock"></i>
                            <span><?php echo date('M d, Y h:i A', strtotime($ann['created_at'])); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div id="announcementDetailModal" class="announcement-detail-modal">
        <div class="announcement-detail-content">
            <button class="modal-close" onclick="closeAnnouncementDetail()">&times;</button>
            <h3 class="modal-title" id="announcementDetailTitle">Announcement</h3>
            <div class="announcement-detail-body">
                <div class="announcement-detail-message" id="announcementDetailMessage"></div>
                <div class="announcement-detail-info">
                    <span class="announcement-detail-type" id="announcementDetailType">Announcement</span>
                    <span class="announcement-detail-date" id="announcementDetailDate"></span>
                </div>
            </div>
        </div>
    </div>

    <div class="notification-overlay" id="notificationOverlay" onclick="closeNotificationSidebar()"></div>

    <div class="notification-sidebar" id="notificationSidebar">
        <div class="notification-header">
            <h3>
                <i class="fas fa-bell"></i>
                Notifications
                <?php if ($notification_count > 0): ?>
                    <span class="notification-header-count" style="background: rgba(255, 255, 255, 0.3); padding: 2px 8px; border-radius: 10px; font-size: 12px;">
                        <?php echo $notification_count; ?> new
                    </span>
                <?php endif; ?>
            </h3>
            <button class="notification-close" onclick="closeNotificationSidebar()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="notification-content">
            <?php if (empty($notifications)): ?>
                <div class="notification-empty">
                    <i class="fas fa-bell-slash"></i>
                    <h4>No notifications</h4>
                    <p>You're all caught up!</p>
                </div>
            <?php else: ?>
                <div id="notificationList">
                    <?php foreach ($notifications as $notification):
                        $message = htmlspecialchars($notification['message']);
                        $title = htmlspecialchars($notification['title']);
                        $type = ucfirst(str_replace('_', ' ', $notification['notification_type']));
                        $date = date('M d, Y h:i A', strtotime($notification['created_at']));
                        $shortDate = date('h:i A', strtotime($notification['created_at']));
                        $shortMessage = strlen($message) > 80 ? substr($message, 0, 80) . '...' : $message;
                        $isRead = $notification['is_read'] ? 'read' : 'unread';
                        $notifId = (int)$notification['notification_id'];
                    ?>
                        <div class="notification-item <?php echo $isRead; ?>" data-id="<?php echo $notifId; ?>" onclick="handleNotificationClick(<?php echo $notifId; ?>, this, '<?php echo addslashes($title); ?>', '<?php echo addslashes($message); ?>', '<?php echo $type; ?>', '<?php echo $date; ?>')">
                            <div class="notification-title">
                                <span><?php echo $title; ?></span>
                                <span class="notification-time"><?php echo $shortDate; ?></span>
                            </div>
                            <div class="notification-message"><?php echo $shortMessage; ?></div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px;">
                                <span class="notification-type"><i class="fas fa-tag"></i> <?php echo $type; ?></span>
                                <small style="color: var(--text-gray); font-size: 11px;">
                                    <?php echo date('M d', strtotime($notification['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php if (!empty($notifications)): ?>
        <div class="notification-actions">
            <button class="clear-all-btn" onclick="clearAllNotifications()">
                <i class="fas fa-trash-alt"></i>
                Clear All Notifications
            </button>
        </div>
        <?php endif; ?>
    </div>

    <div id="notificationDetailModal" class="notification-detail-modal">
        <div class="notification-detail-content">
            <button class="modal-close" onclick="closeDetailModal()">&times;</button>
            <h3 class="modal-title" id="detailTitle"></h3>
            <div class="notification-detail-body">
                <div class="detail-message" id="detailMessage"></div>
                <div class="detail-info">
                    <span class="detail-type" id="detailType"></span>
                    <span class="detail-date" id="detailDate"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for retake message -->
    <div id="retakeModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background: white; padding: 30px; border-radius: 10px; max-width: 500px; width: 90%;">
            <h3 style="color: #dc2626; margin-top: 0;">Retake Required</h3>
            <p>You need to resubmit this experiment. Please go to the <strong>Retake Experiments</strong> page to submit again.</p>
            <p>You'll find detailed feedback from your instructor there.</p>
            <div style="margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end;">
                <button onclick="goToRetakePage()" style="background: #dc2626; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer;">
                    Go to Retake Page
                </button>
                <button onclick="closeRetakeModal()" style="background: #6b7280; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer;">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    <script>
        function selectSubject(subject){
            window.location.href = "updated_exp.php?subject=" + encodeURIComponent(subject);
        }
        function goBack(){
            window.location.href = "updated_exp.php";
        }
        function startExperiment(path){
            window.location.href = path;
        }

        function startRetake(experimentId) {
            // Show modal with option to go to retake page
            document.getElementById('retakeModal').style.display = 'flex';
        }

        function closeRetakeModal() {
            document.getElementById('retakeModal').style.display = 'none';
        }

        function goToRetakePage() {
            window.location.href = 'retake_exp.php';
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const overlay = document.getElementById('overlay');
            if (window.innerWidth > 992) {
                sidebar.classList.toggle('closed');
                mainContent.classList.toggle('full-width');
            } else {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
            }
        }

        // Sidebar Navigation Logic
        const menuIcon = document.getElementById('menuIcon');
        const closeBtn = document.querySelector('.close-btn');
        let sidebarOpen = false;

        function setSidebarOpen(open) {
            sidebarOpen = open;
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            if (open) {
                sidebar.classList.add('active');
                overlay.classList.add('active');
            } else {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            }
        }

        menuIcon?.addEventListener('click', function(e) { 
            e.stopPropagation(); 
            setSidebarOpen(!sidebarOpen); 
        });
        
        closeBtn?.addEventListener('click', function() { 
            setSidebarOpen(false); 
        });

        window.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const menuIcon = document.getElementById('menuIcon');
            if (sidebarOpen && sidebar && !sidebar.contains(e.target) && !menuIcon.contains(e.target)) {
                setSidebarOpen(false);
            }
        });
    </script>
    <script>
        window.notificationConfig = {
            studentId: <?php echo json_encode($student_id); ?>,
            announcementCount: <?php echo json_encode($announcement_count); ?>
        };
    </script>
    <script src="script.js?v=3"></script>
</body>
</html>
<?php $conn->close(); ?>