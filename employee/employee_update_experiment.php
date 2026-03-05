<?php
session_start();
include '../db_connect.php';
require_once __DIR__ . '/announcements_inc.php';

if (!isset($_SESSION['employee_id'])) {
    header('Location: ../index.php');
    exit;
}

function ensureTargetStudentColumn($conn) {
    $check = $conn->query("SHOW COLUMNS FROM weekly_experiments LIKE 'target_students'");
    if ($check && $check->num_rows === 0) {
        $conn->query("ALTER TABLE weekly_experiments ADD COLUMN target_students TEXT NULL AFTER target_section");
    }
}

ensureTargetStudentColumn($conn);

// NORMALIZATION FUNCTION
function normalizeSubject($subject) {
    $subject = trim(strtolower($subject));
    $subject = preg_replace('/[\s_]+/', '', $subject); 
    return $subject;
}

$employee_id = $_SESSION['employee_id'];
$employee_name = $_SESSION['employee_name'];
$employee_username = $_SESSION['username'] ?? '';

list($announcement_count, $announcements) = employee_load_announcements($conn);

// Get employee's assigned subjects from employee_subjects table
$assigned_subjects_raw = [];
$assigned_subjects_normalized = [];
if (!empty($employee_username)) {
    $subject_stmt = $conn->prepare("SELECT DISTINCT subject FROM employee_subjects WHERE employee_username = ?");
    if ($subject_stmt) {
        $subject_stmt->bind_param("s", $employee_username);
        $subject_stmt->execute();
        $subject_result = $subject_stmt->get_result();
        while ($row = $subject_result->fetch_assoc()) {
            $subject_raw = trim($row['subject'] ?? '');
            if ($subject_raw !== '') {
                $assigned_subjects_raw[] = $subject_raw;
                $assigned_subjects_normalized[] = normalizeSubject($subject_raw);
            }
        }
        $subject_stmt->close();
    }
}
$assigned_subjects_normalized = array_values(array_unique(array_filter($assigned_subjects_normalized)));
$assigned_subjects_display = implode(', ', array_values(array_unique($assigned_subjects_raw)));
$has_assigned_subjects = !empty($assigned_subjects_normalized);

// Get current week and year
$current_week = date('W');
$current_year = date('Y');
$current_date = date('Y-m-d');

// Auto-disable expired experiment timings
$expire_sql = "UPDATE weekly_experiments 
               SET is_active = 0 
               WHERE is_active = 1 AND enabled_until < ?";
$expire_stmt = $conn->prepare($expire_sql);
$expire_stmt->bind_param("s", $current_date);
$expire_stmt->execute();
$expire_stmt->close();

// Get filter inputs
$filter_applied = false;
$selected_branch = $_GET['branch'] ?? '';
$selected_section = $_GET['section'] ?? '';
$selected_semester = $_GET['semester'] ?? '';
$all_experiments = [];
$success = '';
$error = '';

// Check if filters are applied
if (!empty($selected_branch) && !empty($selected_section) && !empty($selected_semester)) {
    $filter_applied = true;
}

// Fetch branches and sections from employee_subjects for this employee
$student_branches = [];
$student_sects = [];
$semesters = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII'];
$emp_subjects_sql = "SELECT DISTINCT branch, section FROM employee_subjects WHERE employee_username = ? ORDER BY branch, section";
$emp_subjects_stmt = $conn->prepare($emp_subjects_sql);
if ($emp_subjects_stmt) {
    $emp_subjects_stmt->bind_param("s", $employee_username);
    $emp_subjects_stmt->execute();
    $emp_subjects_result = $emp_subjects_stmt->get_result();
    while ($row = $emp_subjects_result->fetch_assoc()) {
        if (!in_array($row['branch'], $student_branches)) {
            $student_branches[] = $row['branch'];
        }
        if (!in_array($row['section'], $student_sects)) {
            $student_sects[] = $row['section'];
        }
    }
    $emp_subjects_stmt->close();
}
sort($student_branches);
sort($student_sects);

$available_students = [];
$students_lookup = [];
$studentIdToRoll = [];
if ($filter_applied) {
    $students_sql = "SELECT student_id, name, roll_number FROM students WHERE branch = ? AND section = ? AND semester = ? ORDER BY name";
    $students_stmt = $conn->prepare($students_sql);
    if ($students_stmt) {
        $students_stmt->bind_param("sss", $selected_branch, $selected_section, $selected_semester);
        $students_stmt->execute();
        $students_result = $students_stmt->get_result();
        while ($row = $students_result->fetch_assoc()) {
            $available_students[] = $row;
            $studentIdStr = (string)$row['student_id'];
            $students_lookup[$studentIdStr] = $row;
            $normalizedRoll = '';
            if (!empty($row['roll_number'])) {
                $normalizedRoll = strtoupper(trim($row['roll_number']));
                $students_lookup[$normalizedRoll] = $row;
            }
            $studentIdToRoll[$studentIdStr] = $normalizedRoll;
        }
        $students_stmt->close();
    }
}

// Handle form submissions (enable/disable experiments)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $has_assigned_subjects) {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $experiment_id = $_POST['experiment_id'];
        $weekly_id = $_POST['weekly_id'] ?? 0;
        
        if ($action == 'enable') {
            $start_date = $_POST['start_date'] ?? date('Y-m-d');
            $end_date = $_POST['end_date'] ?? date('Y-m-d', strtotime('+7 days'));
            $week_number = date('W', strtotime($start_date));
            $year = date('Y', strtotime($start_date));
            $target_warning = '';
            
            $target_branch_arr = $_POST['target_branch'] ?? [];
            if (!is_array($target_branch_arr)) { $target_branch_arr = [$target_branch_arr]; }
            $target_branch_str = !empty($target_branch_arr) ? implode(',', array_map('trim', $target_branch_arr)) : null;

            $target_section_arr = $_POST['target_section'] ?? [];
            if (!is_array($target_section_arr)) { $target_section_arr = [$target_section_arr]; }
            $target_section_str = !empty($target_section_arr) ? implode(',', array_map('trim', $target_section_arr)) : null;

            $target_students_arr = $_POST['target_students'] ?? [];
            if (!is_array($target_students_arr)) { $target_students_arr = [$target_students_arr]; }
            $target_students_arr = array_filter(array_map('intval', $target_students_arr));

            $target_rolls = [];
            $missing_students = [];
            foreach ($target_students_arr as $studentId) {
                $studentIdStr = (string)$studentId;
                $rollNumber = $studentIdToRoll[$studentIdStr] ?? '';
                if ($rollNumber === '' && isset($students_lookup[$studentIdStr])) {
                    $rollNumber = strtoupper(trim($students_lookup[$studentIdStr]['roll_number'] ?? ''));
                }
                if ($rollNumber !== '') {
                    $target_rolls[] = $rollNumber;
                } else {
                    $missing_students[$studentIdStr] = $students_lookup[$studentIdStr]['name'] ?? ('Student #' . $studentIdStr);
                }
            }

            if (!empty($missing_students)) {
                $placeholders = implode(',', array_fill(0, count($missing_students), '?'));
                $lookup_sql = "SELECT student_id, name, roll_number FROM students WHERE student_id IN ($placeholders)";
                $lookup_stmt = $conn->prepare($lookup_sql);
                if ($lookup_stmt) {
                    $types = str_repeat('i', count($missing_students));
                    $ids_for_lookup = array_map('intval', array_keys($missing_students));
                    $lookup_stmt->bind_param($types, ...$ids_for_lookup);
                    $lookup_stmt->execute();
                    $lookup_result = $lookup_stmt->get_result();
                    while ($lookup_row = $lookup_result->fetch_assoc()) {
                        $lookupIdStr = (string)$lookup_row['student_id'];
                        $normalizedRoll = strtoupper(trim($lookup_row['roll_number'] ?? ''));
                        if ($normalizedRoll !== '') {
                            $target_rolls[] = $normalizedRoll;
                            $students_lookup[$lookupIdStr] = $lookup_row;
                            $students_lookup[$normalizedRoll] = $lookup_row;
                            $studentIdToRoll[$lookupIdStr] = $normalizedRoll;
                            unset($missing_students[$lookupIdStr]);
                        }
                    }
                    $lookup_stmt->close();
                }
            }

            if (!empty($missing_students)) {
                $target_warning = 'Skipped students missing roll numbers: ' . implode(', ', $missing_students);
            }
            $target_rolls = array_values(array_unique($target_rolls));
            $target_students_str = !empty($target_rolls) ? implode(',', $target_rolls) : null;

            if (strtotime($start_date) > strtotime($end_date)) {
                $error = "Start date cannot be after end date.";
            } else {
                $check_sql = "SELECT * FROM weekly_experiments WHERE experiment_id = ? AND week_number = ? AND year = ? AND is_active = 1";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("iii", $experiment_id, $week_number, $year);
                $check_stmt->execute();
                
                if ($check_stmt->get_result()->num_rows == 0) {
                    $enable_sql = "INSERT INTO weekly_experiments (experiment_id, week_number, year, enabled_date, enabled_by, enabled_until, target_branch, target_section, target_students) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $enable_stmt = $conn->prepare($enable_sql);
                    $enable_stmt->bind_param("iiisissss", $experiment_id, $week_number, $year, $start_date, $employee_id, $end_date, $target_branch_str, $target_section_str, $target_students_str);

                    if ($enable_stmt->execute()) {
                        $success = "Experiment enabled successfully.";
                        if (!empty($target_warning)) {
                            $success .= ' ' . $target_warning;
                        }
                    } else {
                        $error = "Failed to enable experiment.";
                    }
                    $enable_stmt->close();
                } else {
                    $error = "Already enabled for this week.";
                }
                $check_stmt->close();
            }
            
        } elseif ($action == 'disable') {
            if ($weekly_id > 0) {
                $disable_sql = "UPDATE weekly_experiments SET is_active = 0 WHERE weekly_id = ?";
                $disable_stmt = $conn->prepare($disable_sql);
                $disable_stmt->bind_param("i", $weekly_id);
                if ($disable_stmt->execute()) {
                    $success = "Experiment disabled.";
                }
                $disable_stmt->close();
            }
        }
    }
}

// Only load experiments if filters are applied
if ($filter_applied && !empty($employee_username)) {
    // Get subjects that this teacher actually teaches for this specific branch/section
    $assigned_subjects_for_filter = [];
    $subject_filter_sql = "SELECT DISTINCT subject FROM employee_subjects 
                          WHERE employee_username = ? AND branch = ? AND section = ?";
    $subject_filter_stmt = $conn->prepare($subject_filter_sql);
    $subject_filter_stmt->bind_param("sss", $employee_username, $selected_branch, $selected_section);
    $subject_filter_stmt->execute();
    $subject_filter_result = $subject_filter_stmt->get_result();
    
    while ($row = $subject_filter_result->fetch_assoc()) {
        $assigned_subjects_for_filter[] = normalizeSubject($row['subject']);
    }
    $subject_filter_stmt->close();

    // ONLY show experiments if the teacher has subjects in this specific branch/section
    if (!empty($assigned_subjects_for_filter)) {
        $experiments_sql = "SELECT DISTINCT e.Id, e.subject, e.experiment_number, e.experiment_name,
                                   we.weekly_id, we.is_active as is_enabled, 
                                   we.enabled_date, we.enabled_until,
                                   we.week_number, we.year, we.target_branch, we.target_section, we.target_students
                            FROM experiments e
                            LEFT JOIN weekly_experiments we ON e.Id = we.experiment_id 
                                AND we.is_active = 1
                                AND (we.target_branch IS NULL OR we.target_branch = '' OR FIND_IN_SET(?, we.target_branch) > 0)
                                AND (we.target_section IS NULL OR we.target_section = '' OR FIND_IN_SET(?, we.target_section) > 0)
                            WHERE e.is_active = 1 
                            AND (";
        
        $conditions = [];
        $bind_params = [$selected_branch, $selected_section];
        $bind_types = "ss";
        
        // Only use subjects that are actually assigned to this branch/section
        foreach ($assigned_subjects_for_filter as $norm_subject) {
            $conditions[] = "LOWER(REPLACE(REPLACE(TRIM(e.subject), ' ', ''), '_', '')) = ?";
            $bind_params[] = $norm_subject;
            $bind_types .= "s";
        }
        
        $experiments_sql .= implode(" OR ", $conditions) . ")";
        $experiments_sql .= " ORDER BY e.experiment_number";
        
        if (!empty($bind_params)) {
            $stmt = $conn->prepare($experiments_sql);
            $stmt->bind_param($bind_types, ...$bind_params);
            $stmt->execute();
            $all_experiments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }
    } else {
        // No subjects assigned for this branch/section combination
        $all_experiments = [];
    }
}
    $pending_count = 0;
$pending_sql = "SELECT COUNT(*) as count FROM submissions WHERE employee_id = ? AND verification_status = 'Pending'";
$pending_stmt = $conn->prepare($pending_sql);
if ($pending_stmt) {
    $pending_stmt->bind_param("i", $employee_id);
    $pending_stmt->execute();
    $pending_result = $pending_stmt->get_result();
    if ($pending_result) {
        $pending_count = (int)($pending_result->fetch_assoc()['count'] ?? 0);
    }
    $pending_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Update Experiments | SVEC</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../css/announcements.css">

<style>
    /* --- DASHBOARD CORE STYLES --- */
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
        --shadow-card: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
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
    
    .dashboard-container { padding: 32px; max-width: 1400px; margin: 0 auto; }
    .overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.4); backdrop-filter: blur(2px); z-index: 95; opacity: 0; visibility: hidden; transition: 0.3s; }

    /* --- PAGE SPECIFIC STYLES --- */
    
    .page-title { font-size: 24px; font-weight: 800; color: var(--text-dark); margin-bottom: 24px; }

    /* Filter Card */
    .filter-card {
        background: var(--white);
        padding: 28px;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-card);
        border: 1px solid #f1f5f9;
        margin-bottom: 30px;
    }
    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 20px;
        align-items: end;
    }
    .form-group label { display: block; font-size: 12px; font-weight: 700; color: var(--text-gray); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
    .form-group select, .form-group input { width: 100%; padding: 10px 12px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 14px; color: var(--text-dark); transition: 0.2s; }
    .form-group select:focus, .form-group input:focus { outline: none; border-color: var(--accent-color); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
    .btn-filter { background: var(--accent-color); color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s; height: fit-content; }
    .btn-filter:hover { background: var(--primary-color); }

    /* Experiment List */
    .experiment-list { display: flex; flex-direction: column; gap: 16px; }
    
    .experiment-card {
        background: var(--white);
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-card);
        border: 1px solid #f1f5f9;
        overflow: hidden;
        transition: 0.2s;
    }
    .experiment-card:hover { transform: translateY(-2px); box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.15); }

    .exp-header {
        padding: 20px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }
    .exp-info { display: flex; align-items: center; gap: 15px; flex: 1; }
    .exp-badge { background: #eff6ff; color: var(--accent-color); font-weight: 700; font-size: 12px; padding: 6px 12px; border-radius: 6px; }
    .exp-title { font-weight: 600; font-size: 16px; color: var(--text-dark); }
    .exp-number { color: var(--text-gray); margin-right: 5px; }

    .btn-action { padding: 8px 16px; border-radius: 6px; font-size: 13px; font-weight: 600; border: none; cursor: pointer; transition: 0.2s; display: flex; align-items: center; gap: 6px; }
    .btn-enable { background: #dcfce7; color: #15803d; }
    .btn-enable:hover { background: #bbf7d0; }
    .btn-disable { background: #fee2e2; color: #b91c1c; }
    .btn-disable:hover { background: #fecaca; }

    /* Enable Form Area */
    .enable-form-container {
        background: #f8fafc;
        border-top: 1px solid #f1f5f9;
        padding: 24px;
        display: none;
    }
    .form-row { display: flex; gap: 20px; margin-bottom: 20px; }
    .form-col { flex: 1; }
    .form-col label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px; color: var(--text-dark); }
    .form-col input { width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; }
    
    .checkbox-group { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 8px; }
    .checkbox-item { display: flex; align-items: center; gap: 6px; font-size: 13px; background: white; padding: 6px 12px; border-radius: 20px; border: 1px solid #e2e8f0; }
    .student-search { width: 100%; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 10px; font-size: 14px; }
    .student-search:focus { outline: none; border-color: var(--accent-color); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
    .student-list { max-height: 220px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 12px; padding: 10px; background: #fff; }
    .student-option { width: 100%; justify-content: space-between; }

    .form-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
    .btn-cancel { background: white; border: 1px solid #e2e8f0; color: var(--text-gray); padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 600; }
    .btn-submit { background: var(--accent-color); color: white; border: none; padding: 8px 20px; border-radius: 6px; cursor: pointer; font-weight: 600; }

    /* Active Info Strip */
    .active-strip {
        background: #f0fdf4;
        border-top: 1px solid #dcfce7;
        padding: 12px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 13px;
        color: #166534;
    }
    .active-details { display: flex; gap: 20px; }
    .active-details span { font-weight: 600; }

    /* Alert Messages */
    .alert { padding: 16px 20px; border-radius: var(--radius-md); margin-bottom: 20px; display: flex; align-items: center; gap: 12px; }
    .alert-success { background: #f0fdf4; color: #166534; border: 1px solid #dcfce7; }
    .alert-error { background: #fef2f2; color: #991b1b; border: 1px solid #fee2e2; }
    .alert i { font-size: 16px; }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 40px;
        background: white;
        border-radius: var(--radius-lg);
        border: 1px dashed #cbd5e1;
        color: #94a3b8;
    }
    .empty-state i { font-size: 48px; margin-bottom: 16px; opacity: 0.5; display: block; }
    .empty-state p { font-size: 15px; margin: 0; }

    /* Modal */
    .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; display: none; align-items: center; justify-content: center; backdrop-filter: blur(2px); }
    .modal-box { background: white; padding: 30px; border-radius: 16px; width: 400px; text-align: center; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }
    .modal-title { font-size: 18px; font-weight: 700; margin-bottom: 10px; color: var(--text-dark); }
    .modal-text { color: var(--text-gray); margin-bottom: 24px; font-size: 14px; }
    .modal-close { position: absolute; top: 15px; right: 20px; font-size: 1.8rem; cursor: pointer; color: var(--text-gray); transition: 0.2s; border: none; background: none; }
    .modal-close:hover { color: var(--secondary-color); }
    .modal-title { text-align: center; color: var(--primary-color); margin-bottom: 1.8rem; font-size: 1.4rem; font-weight: 700; }
    
    
    @media (max-width: 992px) {
        .sidebar { transform: translateX(-100%); width: 280px; }
        .sidebar.active { transform: translateX(0); }
        .main-content { margin-left: 0; width: 100%; }
        .overlay.active { opacity: 1; visibility: visible; }
    }
    @media (max-width: 768px) {
        .filter-grid { grid-template-columns: 1fr; }
        .form-row { flex-direction: column; gap: 10px; }
        .active-strip { flex-direction: column; align-items: flex-start; gap: 8px; }
        .exp-header { flex-direction: column; align-items: flex-start; }
    }
      @media (max-width: 600px) {
        .header-branding { display: flex; flex-direction: column; }
        .header-branding h1 { font-size: 12px; line-height: 1.1; }
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
            <img src="n1.jpg" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($employee_name); ?>&background=1e3a8a&color=fff'">
            <div style="font-weight:700; color:var(--text-dark);"><?php echo htmlspecialchars($employee_name); ?></div>
            <div style="font-size:12px; color:var(--text-gray);"><?php echo htmlspecialchars($employee_username); ?></div>
        </div>
        <div class="sidebar-menu">
            <a href="employee_dashboard.php" class="menu-item"> Dashboard</a>
            <a href="employee_profile.php" class="menu-item"> My Profile</a>
            <!-- ACTIVE PAGE -->
            <a href="employee_update_experiment.php" class="menu-item active"> Experiments</a>
           <a href="employee_verify.php" class="menu-item"> Verification
                <?php if($pending_count > 0): ?><span style="margin-left:auto; background:var(--secondary-color); color:white; font-size:10px; font-weight:700; padding:2px 8px; border-radius:10px;"><?php echo $pending_count; ?></span><?php endif; ?>
            <a href="employee_schedule.php" class="menu-item"> Exams</a>
            <a href="timetable.php" class="menu-item"> Timetable</a>
        </div>
        <div class="logout-container">
            <a href="employee_logout.php" class="logout-btn"><i class="fa-solid fa-arrow-right-from-bracket" style="margin-right:8px;"></i> Logout</a>
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
                <?php employee_render_announcement_icon($announcement_count); ?>
                <div class="info-text" style="text-align:right;">
        <div style="font-size:14px; font-weight:700; color:var(--text-dark);"><?php echo htmlspecialchars($employee_name); ?></div>
          <div style="font-size:11px; color:var(--text-gray); font-weight:600;">Faculty</div>
          
        </div>
                <img src="n1.jpg" style="width:42px; height:42px; border-radius:50%; border:2px solid #e2e8f0;" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($employee_name); ?>'">
                <a href="employee_logout.php" class="header-logout-btn" style="display: flex; align-items: center; gap: 6px; padding: 8px 14px; background: #fef2f2; color: var(--secondary-color); border: 1px solid #fecaca; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none; transition: all 0.2s;"><i class="fa-solid fa-arrow-right-from-bracket"></i> LOG OUT</a>
            </div>
        </header>

        <div class="dashboard-container">
            
            <!-- Page Title -->
            <div class="page-title">Update Experiments</div>

            <!-- Alert Messages -->
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><i class="fa-solid fa-check-circle"></i> <?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><i class="fa-solid fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- Filter Section -->
            <div class="filter-card">
                <form method="GET" class="filter-grid">
                    <div class="form-group">
                        <label>Semester <span style="color:#dc2626;">*</span></label>
                        <select name="semester" required>
                            <option value="">Select Semester</option>
                            <?php foreach ($semesters as $sem): ?>
                                <option value="<?php echo $sem; ?>" <?php echo $selected_semester === $sem ? 'selected' : ''; ?>><?php echo $sem; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Branch <span style="color:#dc2626;">*</span></label>
                        <select name="branch" required>
                            <option value="">Select Branch</option>
                            <?php foreach ($student_branches as $d): ?>
                                <option value="<?php echo htmlspecialchars($d); ?>" <?php echo $selected_branch === $d ? 'selected' : ''; ?>><?php echo htmlspecialchars($d); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Section <span style="color:#dc2626;">*</span></label>
                        <select name="section" required>
                            <option value="">Select Section</option>
                            <?php foreach ($student_sects as $s): ?>
                                <option value="<?php echo htmlspecialchars($s); ?>" <?php echo $selected_section === $s ? 'selected' : ''; ?>><?php echo htmlspecialchars($s); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn-filter"><i class="fa-solid fa-filter"></i> Apply Filters</button>
                </form>
            </div>

            <!-- Experiment List -->
            <?php if (!$filter_applied): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-flask-vial"></i>
                    <p>Please select semester, branch, and section to view experiments</p>
                </div>
            <?php elseif (empty($all_experiments)): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-inbox"></i>
                    <p>No experiments available for the selected filters</p>
                </div>
            <?php else: ?>
                <div class="experiment-list" id="experimentList">
                    <?php foreach ($all_experiments as $exp): 
                        $is_enabled = !empty($exp['is_enabled']);
                        $enabled_date = $exp['enabled_date'] ?? '';
                        $enabled_until = $exp['enabled_until'] ?? '';
                        $week_number = $exp['week_number'] ?? '';
                        $year = $exp['year'] ?? '';
                        $target_students_labels = [];
                        $total_target_students = 0;
                        if (!empty($exp['target_students'])) {
                            $student_tokens = array_filter(array_map('trim', explode(',', $exp['target_students'])));
                            $total_target_students = count($student_tokens);
                            $display_tokens = array_slice($student_tokens, 0, 3);
                            foreach ($display_tokens as $token) {
                                $normalizedToken = strtoupper($token);
                                $labelRow = $students_lookup[$normalizedToken] ?? $students_lookup[$token] ?? null;
                                if ($labelRow) {
                                    $label = $labelRow['name'];
                                    if (!empty($labelRow['roll_number'])) {
                                        $label .= ' (' . $labelRow['roll_number'] . ')';
                                    }
                                } else {
                                    $label = 'Roll ' . $token;
                                }
                                $target_students_labels[] = $label;
                            }
                            if ($total_target_students > count($target_students_labels)) {
                                $target_students_labels[] = '+' . ($total_target_students - count($target_students_labels)) . ' more';
                            }
                        }
                    ?>
                    <div class="experiment-card">
                        
                        <!-- Header Line -->
                        <div class="exp-header">
                            <div class="exp-info">
                                <div class="exp-badge"><?php echo htmlspecialchars(ucfirst($exp['subject'])); ?></div>
                                <div class="exp-title">
                                    <span class="exp-number">Exp <?php echo htmlspecialchars($exp['experiment_number']); ?>:</span> 
                                    <?php echo htmlspecialchars($exp['experiment_name']); ?>
                                </div>
                            </div>
                            <div class="exp-actions">
                                <?php if (!$is_enabled): ?>
                                    <button class="btn-action btn-enable" onclick="showEnableForm(<?php echo $exp['Id']; ?>)">
                                        <i class="fa-regular fa-clock"></i> Set Timing
                                    </button>
                                <?php else: ?>
                                    <form method="POST" style="margin:0; display:inline;">
                                        <input type="hidden" name="action" value="disable">
                                        <input type="hidden" name="weekly_id" value="<?php echo $exp['weekly_id']; ?>">
                                        <input type="hidden" name="experiment_id" value="<?php echo $exp['Id']; ?>">
                                        <button type="button" class="btn-action btn-disable" onclick="confirmDisable(this)">
                                            <i class="fa-solid fa-ban"></i> Disable
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Active Info Strip (Visible if Enabled) -->
                        <?php if ($is_enabled): ?>
                        <div class="active-strip">
                            <div class="active-details">
                                <div>Active Week: <span><?php echo $week_number; ?></span></div>
                                <div>From: <span><?php echo date('d M', strtotime($enabled_date)); ?></span></div>
                                <div>Until: <span><?php echo date('d M', strtotime($enabled_until)); ?></span></div>
                            </div>
                            <?php if (!empty($exp['target_branch']) || !empty($exp['target_section']) || $total_target_students > 0): ?>
                                <div style="background:white; padding:4px 10px; border-radius:6px; border:1px solid #dcfce7; font-size:11px; display:flex; flex-wrap:wrap; gap:6px;">
                                    <?php if (!empty($exp['target_branch'])): ?>
                                        <span>Branch: <?php echo htmlspecialchars($exp['target_branch']); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($exp['target_section'])): ?>
                                        <span>Sec: <?php echo htmlspecialchars($exp['target_section']); ?></span>
                                    <?php endif; ?>
                                    <?php if ($total_target_students > 0): ?>
                                        <span>Students: <?php echo htmlspecialchars(implode(', ', $target_students_labels)); ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Enable Form (Hidden) -->
                        <div id="enableForm-<?php echo $exp['Id']; ?>" class="enable-form-container">
                            <form method="POST">
                                <input type="hidden" name="action" value="enable">
                                <input type="hidden" name="experiment_id" value="<?php echo $exp['Id']; ?>">
                                
                                <div class="form-row">
                                    <div class="form-col">
                                        <label>Start Date</label>
                                        <input type="date" name="start_date" value="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                    <div class="form-col">
                                        <label>End Date</label>
                                        <input type="date" name="end_date" value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>" required>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-col">
                                        <label>Target Branches (Optional)</label>
                                        <div class="checkbox-group">
                                            <?php foreach ($student_branches as $d): ?>
                                                <div class="checkbox-item">
                                                    <input type="checkbox" name="target_branch[]" value="<?php echo $d; ?>"> <?php echo $d; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-col">
                                        <label>Target Sections (Optional)</label>
                                        <div class="checkbox-group">
                                            <?php foreach ($student_sects as $s): ?>
                                                <div class="checkbox-item">
                                                    <input type="checkbox" name="target_section[]" value="<?php echo $s; ?>"> <?php echo $s; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-col">
                                        <label>Target Students (Optional)</label>
                                        <?php if (!empty($available_students)): ?>
                                            <?php $studentListId = 'student-list-' . $exp['Id']; ?>
                                            <input type="text" class="student-search" data-target="<?php echo $studentListId; ?>" placeholder="Search by name or roll">
                                            <div class="checkbox-group student-list" id="<?php echo $studentListId; ?>">
                                                <?php foreach ($available_students as $stu): ?>
                                                    <?php
                                                        $label = $stu['name'];
                                                        if (!empty($stu['roll_number'])) {
                                                            $label .= ' (' . $stu['roll_number'] . ')';
                                                        }
                                                        $label_attr = strtolower($label);
                                                    ?>
                                                    <label class="checkbox-item student-option" data-label="<?php echo htmlspecialchars($label_attr); ?>">
                                                        <input type="checkbox" name="target_students[]" value="<?php echo $stu['student_id']; ?>"> <?php echo htmlspecialchars($label); ?>
                                                    </label>
                                                <?php endforeach; ?>
                                            </div>
                                            <small style="font-size:12px; color:var(--text-gray); display:block; margin-top:6px;">Leave blank to allow every student in the section.</small>
                                        <?php else: ?>
                                            <p style="color:var(--text-gray); font-size:13px;">Roster not available for the selected filters.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <button type="button" class="btn-cancel" onclick="hideEnableForm(<?php echo $exp['Id']; ?>)">Cancel</button>
                                    <button type="submit" class="btn-submit">Enable Experiment</button>
                                </div>
                            </form>
                        </div>

                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </main>

    <?php employee_render_announcement_drawer($announcements); ?>

    <!-- CONFIRMATION MODAL -->
    <div class="modal-overlay" id="confirmModal">
        <div class="modal-box">
            <div style="font-size:48px; color:#ef4444; margin-bottom:15px;"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <div class="modal-title">Disable Experiment?</div>
            <div class="modal-text">Students will no longer be able to submit this experiment. Are you sure you want to continue?</div>
            <div style="display:flex; justify-content:center; gap:15px;">
                <button class="btn-cancel" onclick="closeModal()">Cancel</button>
                <button class="btn-disable" style="padding:10px 24px;" onclick="proceedDisable()">Yes, Disable</button>
            </div>
        </div>
    </div>

    <script>
        // SIDEBAR TOGGLE
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

        // FORM TOGGLING
        function showEnableForm(id) {
            document.querySelectorAll('.enable-form-container').forEach(el => el.style.display = 'none');
            document.getElementById('enableForm-' + id).style.display = 'block';
        }
        function hideEnableForm(id) {
            document.getElementById('enableForm-' + id).style.display = 'none';
        }

        // MODAL LOGIC
        let formToSubmit = null;
        function confirmDisable(btn) {
            formToSubmit = btn.closest('form');
            document.getElementById('confirmModal').style.display = 'flex';
        }
        function closeModal() {
            document.getElementById('confirmModal').style.display = 'none';
            formToSubmit = null;
        }
        function proceedDisable() {
            if(formToSubmit) formToSubmit.submit();
        }

        document.querySelectorAll('.student-search').forEach(input => {
            input.addEventListener('input', function() {
                const targetId = this.dataset.target;
                const targetEl = document.getElementById(targetId);
                if (!targetEl) return;
                const query = this.value.trim().toLowerCase();
                targetEl.querySelectorAll('.student-option').forEach(option => {
                    const label = (option.dataset.label || '').toLowerCase();
                    option.style.display = label.includes(query) ? 'flex' : 'none';
                });
            });
        });
    </script>
    <?php employee_render_announcement_scripts($employee_id); ?>
</body>
</html>
<?php $conn->close(); ?>
