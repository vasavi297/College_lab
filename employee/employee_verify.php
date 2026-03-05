<?php
session_start();
include '../db_connect.php';

// Block mobile access like experiment pages
include_once '../student/device_guard.php';
ensure_desktop_only();

require_once __DIR__ . '/announcements_inc.php';
list($announcement_count, $announcements) = employee_load_announcements($conn);

// Authentication
if (!isset($_SESSION['employee_id'])) {
    header('Location: ../index.php');
    exit;
}

$employee_id = $_SESSION['employee_id'];
$employee_name = $_SESSION['employee_name'];
$employee_username = $_SESSION['username'] ?? '';

// Handle form submission for verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $submission_id = intval($_POST['submission_id']);
    $action = $_POST['action']; 
    $marks = isset($_POST['marks']) ? floatval($_POST['marks']) : null;
    $feedback = trim($_POST['feedback'] ?? '');
   
    if (empty($feedback)) {
        $error = "Feedback is required for both verification and retake.";
    } 
    else if ($action === 'verify' && ($marks === null || $marks < 0 || $marks > 10)) {
        $error = "Marks must be between 0 and 10 for verification.";
    } else {
        if ($action === 'verify') {
            $status = 'Verified';
            $sql = "UPDATE submissions SET verification_status = ?, marks_obtained = ?, feedback = ?, verification_date = NOW(), can_retake_again = 0 WHERE submission_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sdsi", $status, $marks, $feedback, $submission_id);
        } elseif ($action === 'retake') {
            $status = 'Retake';
            $sql = "UPDATE submissions SET verification_status = ?, feedback = ?, verification_date = NOW(), marks_obtained = NULL, can_retake_again = 1, last_retake_date = NOW() WHERE submission_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $status, $feedback, $submission_id);
        }
        
        if ($stmt->execute()) {
            header("Location: employee_verify.php?status=" . ($status === 'Retake' ? 'Pending' : $status) . "&success=1");
            exit();
        } else {
            $error = "Failed to update submission: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Get filters
$status = $_GET['status'] ?? 'Pending';
$view_id = $_GET['view'] ?? 0;
$semester = $_GET['semester'] ?? null;
$branch  = $_GET['branch'] ?? null;
$section = $_GET['section'] ?? null;
$subject = $_GET['subject'] ?? null;
$search  = $_GET['student_search'] ?? '';

$filters_applied = ($semester && $branch && $section && $subject);

// Get employee's assigned subjects and their branches/sections
$assigned_subjects = [];
$assigned_branches = [];
$assigned_sections = [];
if (!empty($employee_username)) {
    $subj_sql = "SELECT DISTINCT subject, branch, section FROM employee_subjects WHERE employee_username = ? ORDER BY branch, section";
    $subj_stmt = $conn->prepare($subj_sql);
    $subj_stmt->bind_param("s", $employee_username);
    $subj_stmt->execute();
    $subj_result = $subj_stmt->get_result();
    while ($row = $subj_result->fetch_assoc()) {
        $assigned_subjects[] = $row['subject'];
        if (!in_array($row['branch'], $assigned_branches)) {
            $assigned_branches[] = $row['branch'];
        }
        if (!in_array($row['section'], $assigned_sections)) {
            $assigned_sections[] = $row['section'];
        }
    }
    $subj_stmt->close();
    $assigned_subjects = array_values(array_unique($assigned_subjects));
    sort($assigned_branches);
    sort($assigned_sections);
}

// Get counts for sidebar and tabs
$count_sql = "SELECT 
                SUM(CASE WHEN s.verification_status = 'Pending' THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN s.verification_status = 'Verified' THEN 1 ELSE 0 END) as verified_count,
                SUM(CASE WHEN s.verification_status = 'Retake' THEN 1 ELSE 0 END) as retake_count,
                COUNT(*) as total_count
              FROM submissions s
              JOIN students stu ON s.student_id = stu.student_id
              JOIN experiments e ON s.experiment_id = e.Id
              JOIN student_subject_employees sse ON sse.student_username = stu.username AND sse.subject = e.subject AND sse.employee_username = ?";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("s", $employee_username);
$count_stmt->execute();
$count_result = $count_stmt->get_result()->fetch_assoc();
$count_stmt->close();

$pending_count = $count_result['pending_count'] ?? 0;
$verified_count = $count_result['verified_count'] ?? 0;
$retake_count = $count_result['retake_count'] ?? 0;
$total_count = $count_result['total_count'] ?? 0;

// Fetch Submissions
$submissions = [];
if ($filters_applied && !empty($employee_username)) {
    $sql = "SELECT s.*, stu.name AS student_name, stu.roll_number, stu.branch, stu.section, stu.semester, e.experiment_number, e.experiment_name, e.subject 
            FROM submissions s 
            JOIN students stu ON s.student_id = stu.student_id 
            JOIN experiments e ON s.experiment_id = e.Id 
            JOIN student_subject_employees sse ON sse.student_username = stu.username AND sse.subject = e.subject AND sse.employee_username = ?
            WHERE stu.semester = ? AND stu.branch = ? AND stu.section = ? AND e.subject = ?";
    
    if ($status !== 'all') $sql .= " AND s.verification_status = ?";
    if (!empty($search)) $sql .= " AND (stu.name LIKE ? OR stu.roll_number LIKE ?)";
    
    $sql .= " ORDER BY s.submitted_date DESC";
    $stmt = $conn->prepare($sql);
    $search_param = "%$search%";
    
    if ($status === 'all') {
        if (!empty($search)) $stmt->bind_param("sssssss", $employee_username, $semester, $branch, $section, $subject, $search_param, $search_param);
        else $stmt->bind_param("sssss", $employee_username, $semester, $branch, $section, $subject);
    } else {
        if (!empty($search)) $stmt->bind_param("ssssssss", $employee_username, $semester, $branch, $section, $subject, $status, $search_param, $search_param);
        else $stmt->bind_param("ssssss", $employee_username, $semester, $branch, $section, $subject, $status);
    }
    $stmt->execute();
    $submissions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$submission_detail = null;
if ($view_id > 0) {
    $detail_sql = "SELECT s.*, stu.name as student_name, stu.roll_number, stu.branch, stu.semester, stu.section, e.experiment_number, e.experiment_name, e.subject FROM submissions s JOIN students stu ON s.student_id = stu.student_id JOIN experiments e ON s.experiment_id = e.Id WHERE s.submission_id = ?";
    $detail_stmt = $conn->prepare($detail_sql);
    $detail_stmt->bind_param("i", $view_id);
    $detail_stmt->execute();
    $submission_detail = $detail_stmt->get_result()->fetch_assoc();
    $detail_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Verify Students | SVEC</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../css/announcements.css">

<style>
    /* RESTORED YOUR EXACT ROOT VARIABLES FROM DASHBOARD */
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
    html, body { height: 100%; width: 100%; overflow: hidden; background-color: var(--bg-body); color: var(--text-dark); }
    a { text-decoration: none; color: inherit; }

    /* RESTORED YOUR EXACT SIDEBAR STYLING */
    .sidebar { width: var(--sidebar-width); background: var(--white); height: 100vh; position: fixed; left: 0; top: 0; z-index: 100; border-right: 1px solid #e2e8f0; display: flex; flex-direction: column; transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 10px 0 30px rgba(0,0,0,0.03); }
    .sidebar.active { transform: translateX(0); }
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
    .menu-item.active { background: linear-gradient(45deg, var(--primary-color), #2563eb); color: var(--white); box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3); }
    .menu-item.active i { color: var(--white); }
    .logout-container { padding: 20px; border-top: 1px solid #f1f5f9; flex-shrink: 0; }
    .logout-btn { display: flex; justify-content: center; align-items: center; width: 100%; padding: 12px; border-radius: var(--radius-md); background-color: #fef2f2; color: var(--secondary-color); font-weight: 600; font-size: 14px; transition: 0.2s; border: 1px solid #fee2e2; }

    /* MAIN CONTENT */
    .main-content { margin-left: var(--sidebar-width); width: calc(100% - var(--sidebar-width)); height: 100vh; overflow-y: auto; transition: all 0.3s; display: flex; flex-direction: column; }
    .main-content.full-width { margin-left: 0; width: 100%; }
    .top-header { height: var(--header-height); background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); display: flex; justify-content: space-between; align-items: center; padding: 0 32px; border-bottom: 1px solid #e2e8f0; }

    /* HERO BANNER & THE NEW WHITE PILL SEARCH BAR */
    .hero-banner {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
        padding: 40px 32px;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0;
    }
    .hero-text h2 { font-size: 28px; font-weight: 800; margin-bottom: 4px; }
    
    .search-wrapper { position: relative; flex: 1; max-width: 600px; margin-left: 40px; }
    .search-bar-container {
        display: flex;
        align-items: center;
        background: #ffffff;
        border-radius: 50px;
        padding: 4px 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        height: 48px;
    }
    .search-bar-container i { padding: 0 15px; color: #5f6368; font-size: 18px; }
    .search-bar-container input { border: none; background: transparent; width: 100%; height: 100%; font-size: 15px; color: #333; outline: none; }
    .search-bar-container input::placeholder { color: #9aa0a6; }

    /* Filtering & Lists */
    .dashboard-container { padding: 32px; max-width: 1400px; width: 100%; flex: 1; }
    .filter-card { background: var(--white); padding: 24px; border-radius: var(--radius-lg); box-shadow: var(--shadow-card); margin-bottom: 30px; }
    .filter-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 20px; align-items: end; }
    .form-group label { display: block; font-size: 12px; font-weight: 700; color: var(--text-gray); margin-bottom: 8px; text-transform: uppercase; }
    .form-group select, .btn-filter { width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 14px; }
    .btn-filter { background: var(--accent-color); color: white; border: none; font-weight: 600; cursor: pointer; height: 42px; }

    .status-filter { display: flex; gap: 10px; margin-bottom: 25px; }
    .status-filter-btn { padding: 10px 20px; border: 1px solid #e2e8f0; border-radius: 8px; background: white; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 8px; }
    .status-filter-btn.active { background: var(--accent-color); color: white; border-color: var(--accent-color); }
    .filter-badge { background: rgba(0,0,0,0.1); padding: 2px 8px; border-radius: 12px; font-size: 11px; }
    .status-filter-btn.active .filter-badge { background: rgba(255,255,255,0.2); }

    .submission-card { background: white; border-radius: var(--radius-md); padding: 24px; margin-bottom: 16px; box-shadow: var(--shadow-card); border-left: 5px solid transparent; cursor: pointer; border: 1px solid #f1f5f9; border-left-width: 5px; }
    .submission-card.pending { border-left-color: #f59e0b; }
    .submission-card.verified { border-left-color: #10b981; }
    .submission-card.retake { border-left-color: #ef4444; }

    /* New Submission Viewer Styles - Matching your design */
    .fullscreen-viewer { 
        position: fixed; 
        inset: 0; 
        background: #f8fafc; 
        z-index: 2000; 
        display: flex; 
        flex-direction: column; 
        overflow: hidden;
    }
    
    .viewer-header {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
        color: white;
        padding: 20px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0;
        min-height: 80px;
    }
    
    .viewer-header h2 {
        margin: 0;
        font-size: 24px;
        font-weight: 800;
    }
    
    .viewer-header .student-info {
        display: flex;
        gap: 20px;
        font-size: 14px;
        flex-wrap: wrap;
    }
    
    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .status-pending {
        background: #f59e0b;
        color: white;
    }
    
    .status-verified {
        background: #10b981;
        color: white;
    }
    
    .status-retake {
        background: #ef4444;
        color: white;
    }
    
    .viewer-controls {
        display: flex;
        gap: 15px;
        align-items: center;
    }
    
    .viewer-controls button {
        padding: 10px 24px;
        border: none;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }
    
    .verify-btn {
        background: #10b981;
        color: white;
    }
    
    .retake-btn {
        background: #ef4444;
        color: white;
    }
    
    .close-btn-viewer {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }
    
    .viewer-content {
        flex: 1;
        overflow-y: auto;
        padding: 40px;
        background: #f8fafc;
    }
    
    .submission-container {
        background: white;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-card);
        padding: 40px;
        max-width: 1000px;
        margin: 0 auto;
        width: 100%;
        min-height: 100%;
    }
    
    .retake-notice {
        background: #fef3c7;
        padding: 16px 20px;
        border-radius: var(--radius-md);
        border-left: 4px solid #f59e0b;
        margin-bottom: 30px;
    }
    
    .retake-notice strong {
        color: #92400e;
    }
    
    .student-submission-html {
        line-height: 1.6;
        font-family: 'Inter', sans-serif;
    }
    
    .student-submission-html table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
        border: 1px solid #e2e8f0;
    }
    
    .student-submission-html th,
    .student-submission-html td {
        padding: 12px;
        border: 1px solid #e2e8f0;
        text-align: left;
    }
    
    .student-submission-html th {
        background: #f8fafc;
        font-weight: 600;
    }
    
    .viewer-footer {
        background: white;
        border-top: 1px solid #e2e8f0;
        padding: 15px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 14px;
        color: var(--text-gray);
        flex-shrink: 0;
    }
    
    /* Modal Styles */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.8);
        z-index: 3000;
        display: none;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(4px);
    }
    
    .modal-box {
        background: white;
        width: 90%;
        max-width: 500px;
        padding: 30px;
        border-radius: var(--radius-lg);
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }
    
    .modal-box h3 {
        margin-top: 0;
        color: var(--primary-color);
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 15px;
        margin-bottom: 20px;
    }
    .modal-close { position: absolute; top: 15px; right: 20px; font-size: 1.8rem; cursor: pointer; color: var(--text-gray); transition: 0.2s; border: none; background: none; }
    .modal-close:hover { color: var(--secondary-color); }
    .modal-title { text-align: center; color: var(--primary-color); margin-bottom: 1.8rem; font-size: 1.4rem; font-weight: 700; }
    
    
    .overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 95; opacity: 0; visibility: hidden; transition: 0.3s; }
    .overlay.active { opacity: 1; visibility: visible; }

</style>
</head>
<body>

    <div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

    <!-- RESTORED YOUR EXACT SIDEBAR CODE -->
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
            <a href="employee_update_experiment.php" class="menu-item"> Experiments</a>
            <a href="employee_verify.php" class="menu-item active"> Verification
            <?php if($pending_count > 0): ?><span style="margin-left:auto; background:var(--secondary-color); color:white; font-size:10px; font-weight:700; padding:2px 8px; border-radius:10px;"><?php echo $pending_count; ?></span><?php endif; ?></a>
            <a href="employee_schedule.php" class="menu-item"> Exams</a>
            <a href="timetable.php" class="menu-item"> Timetable</a>
        </div>
        <div class="logout-container">
            <a href="employee_logout.php" class="logout-btn"><i class="fa-solid fa-arrow-right-from-bracket" style="margin-right:8px;"></i> Logout</a>
        </div>
    </nav>

    <main class="main-content" id="mainContent">
        <header class="top-header">
            <div style="display:flex; align-items:center;">
                <button class="toggle-btn" style="border:none; background:none; font-size:20px; cursor:pointer; margin-right:15px;" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
                <div class="header-branding">
                    <h1 style="font-size:18px; font-weight:800; color:var(--primary-color);">SRI VASAVI ENGINEERING COLLEGE</h1>
                    
                </div>
            </div>
            <div class="header-right" style="display:flex; align-items:center; gap:15px;">
                <?php employee_render_announcement_icon($announcement_count); ?>
                <div style="text-align:right;">
                    <div style="font-size:14px; font-weight:700; color:var(--text-dark);"><?php echo htmlspecialchars($employee_name); ?></div>
                    <div style="font-size:11px; color:var(--text-gray); font-weight:600;">Faculty</div>
                </div>
                <img src="n1.jpg" style="width:42px; height:42px; border-radius:50%; border:2px solid #e2e8f0;" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($employee_name); ?>'">
                <a href="employee_logout.php" class="header-logout-btn" style="display: flex; align-items: center; gap: 6px; padding: 8px 14px; background: #fef2f2; color: var(--secondary-color); border: 1px solid #fecaca; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none; transition: all 0.2s;"><i class="fa-solid fa-arrow-right-from-bracket"></i> LOG OUT</a>
            </div>
        </header>

        <!-- HERO BANNER WITH THE NEW SEARCH BAR -->
        <div class="hero-banner">
            <div class="hero-text">
                <h2>Verify Submissions</h2>
                <p>Manage and grade student experiment submissions.</p>
            </div>
            
            <form method="GET" class="search-wrapper">
                <div class="search-bar-container">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" name="student_search" placeholder="Search by Roll Number or Name..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <input type="hidden" name="status" value="<?php echo htmlspecialchars($status); ?>">
                <input type="hidden" name="semester" value="<?php echo htmlspecialchars($semester); ?>">
                <input type="hidden" name="branch" value="<?php echo htmlspecialchars($branch); ?>">
                <input type="hidden" name="section" value="<?php echo htmlspecialchars($section); ?>">
                <input type="hidden" name="subject" value="<?php echo htmlspecialchars($subject); ?>">
            </form>
        </div>

        <div class="dashboard-container">
            <!-- Filter Card -->
            <div class="filter-card">
                <form method="GET" class="filter-grid">
                    <div class="form-group">
                        <label>Semester</label>
                        <select name="semester" required>
                            <option value="">Select Semester</option>
                            <?php foreach (['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII'] as $sem): ?>
                                <option value="<?=$sem?>" <?=($semester==$sem?'selected':'')?>><?=$sem?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Branch</label>
                        <select name="branch" required>
                            <option value="">Select Branch</option>
                            <?php foreach ($assigned_branches as $br): ?>
                                <option value="<?=htmlspecialchars($br)?>" <?=($branch==$br?'selected':'')?>><?=htmlspecialchars($br)?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Section</label>
                        <select name="section" required>
                            <option value="">Select Section</option>
                            <?php foreach ($assigned_sections as $sec): ?>
                                <option value="<?=htmlspecialchars($sec)?>" <?=($section==$sec?'selected':'')?>><?=htmlspecialchars($sec)?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Subject</label>
                        <select name="subject" required>
                            <option value="">Select Subject</option>
                            <?php foreach ($assigned_subjects as $subj): ?>
                                <option value="<?=htmlspecialchars($subj)?>" <?=($subject==$subj?'selected':'')?>><?=htmlspecialchars($subj)?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn-filter">Apply Filters</button>
                    </div>
                    <input type="hidden" name="status" value="<?php echo htmlspecialchars($status); ?>">
                </form>
            </div>

            <!-- Tabs -->
            <div class="status-filter">
                <a href="?status=Pending&semester=<?=$semester?>&branch=<?=$branch?>&section=<?=$section?>&subject=<?=$subject?>" class="status-filter-btn <?=$status=='Pending'?'active':''?>">
                    Pending <span class="filter-badge"><?=$pending_count?></span>
                </a>
                <a href="?status=Verified&semester=<?=$semester?>&branch=<?=$branch?>&section=<?=$section?>&subject=<?=$subject?>" class="status-filter-btn <?=$status=='Verified'?'active':''?>">
                    Verified <span class="filter-badge"><?=$verified_count?></span>
                </a>
                <a href="?status=Retake&semester=<?=$semester?>&branch=<?=$branch?>&section=<?=$section?>&subject=<?=$subject?>" class="status-filter-btn <?=$status=='Retake'?'active':''?>">
                    Retake <span class="filter-badge"><?=$retake_count?></span>
                </a>
                <a href="?status=all&semester=<?=$semester?>&branch=<?=$branch?>&section=<?=$section?>&subject=<?=$subject?>" class="status-filter-btn <?=$status=='all'?'active':''?>">
                    All <span class="filter-badge"><?=$total_count?></span>
                </a>
            </div>

            <!-- List -->
            <?php if (!$filters_applied): ?>
                <div style="text-align:center; padding:80px; background:white; border-radius:12px; border:1px dashed #cbd5e1; color:#94a3b8;">
                    <i class="fa-solid fa-filter" style="font-size:32px; margin-bottom:15px; opacity:0.5;"></i><br>
                    Please select Semester, Branch, Section, and Subject to view submissions.
                </div>
            <?php elseif (empty($submissions)): ?>
                <div style="text-align:center; padding:60px; background:white; border-radius:12px; color:#94a3b8;">
                    No submissions found for the selected criteria.
                </div>
            <?php else: ?>
                <?php foreach ($submissions as $sub): ?>
                <div class="submission-card <?=strtolower($sub['verification_status'])?>" onclick="window.location.href='?view=<?=$sub['submission_id']?>&status=<?=$status?>&semester=<?=$semester?>&branch=<?=$branch?>&section=<?=$section?>&subject=<?=$subject?>'">
                    <div style="display:flex; justify-content:space-between;">
                        <div style="font-weight:700; font-size:16px;"><?=$sub['experiment_name']?></div>
                        <div style="font-size:12px; color:var(--text-gray);"><?=date('d M Y', strtotime($sub['submitted_date']))?></div>
                    </div>
                    <div style="margin-top:10px; font-size:14px;">
                        Student: <strong><?=$sub['student_name']?></strong> (<?=$sub['roll_number']?>)
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <?php employee_render_announcement_drawer($announcements); ?>

    <!-- Submission Viewer (Only shown when viewing a specific submission) -->
    <?php if ($submission_detail): ?>
    <div class="fullscreen-viewer" id="fullscreenViewer">
        <!-- Header -->
        <div class="viewer-header">
            <div style="display: flex; align-items: center; gap: 20px;">
                <h2>Submission Viewer</h2>
                <div class="student-info">
                    <span><strong>Student:</strong> <?php echo htmlspecialchars($submission_detail['student_name']); ?></span>
                    <span><strong>Experiment:</strong> <?php echo htmlspecialchars($submission_detail['experiment_number'] . ': ' . $submission_detail['experiment_name']); ?></span>
                    <span><strong>Status:</strong> 
                        <span class="status-badge status-<?php echo strtolower($submission_detail['verification_status']); ?>">
                            <?php echo htmlspecialchars($submission_detail['verification_status']); ?>
                        </span>
                    </span>
                </div>
            </div>
            
            <div class="viewer-controls">
                <!-- Verification Controls -->
                <?php if ($submission_detail['verification_status'] == 'Pending'): ?>
                <button onclick="showVerificationForm('verify')" class="verify-btn">
                    <i class="fa-solid fa-check"></i> Verify
                </button>
                <button onclick="showVerificationForm('retake')" class="retake-btn">
                    <i class="fa-solid fa-rotate"></i> Retake
                </button>
                <?php endif; ?>
                
                <a href="?status=<?php echo $status; ?>&semester=<?=$semester?>&branch=<?=$branch?>&section=<?=$section?>&subject=<?=$subject?>" 
                   class="close-btn-viewer">
                    <i class="fa-solid fa-xmark"></i> Close (ESC)
                </a>
            </div>
        </div>
        
        <!-- Content -->
        <div class="viewer-content">
            <div class="submission-container">
                <?php 
                // Check if this is a retake submission
                $retake_sql = "SELECT retake_count, last_retake_date FROM submissions WHERE submission_id = ?";
                $retake_stmt = $conn->prepare($retake_sql);
                $retake_stmt->bind_param("i", $view_id);
                $retake_stmt->execute();
                $retake_result = $retake_stmt->get_result()->fetch_assoc();
                $retake_stmt->close();
                
                $retake_count = $retake_result['retake_count'] ?? 0;
                $last_retake_date = $retake_result['last_retake_date'] ?? null;
                ?>
                
                <!-- RETAKE MESSAGE -->
                <?php if ($retake_count > 0): 
                    $attempt_number = $retake_count + 1;
                ?>
                <div class="retake-notice">
                    <strong>⚠️ Retake Submission - Attempt <?php echo $attempt_number; ?></strong>
                    <p style="margin: 5px 0 0 0; font-size: 0.9rem; color: #92400e;">
                        Please review the student's resubmission carefully. 
                        This is their <?php echo ($retake_count == 1 ? 'second' : ($retake_count == 2 ? 'third' : ($retake_count+1).'th')); ?> attempt.
                        <?php if ($last_retake_date): ?>
                            Last retake: <?php echo date('d/m/Y H:i', strtotime($last_retake_date)); ?>
                        <?php endif; ?>
                    </p>
                </div>
                <?php endif; ?>
                
                <div class="student-submission-html">
                    <?php 
                    // Get and process the submission HTML
                    $html_content = $submission_detail['submission_data'] ?? '<p style="color: #64748b; text-align: center; padding: 40px;">No submission content found.</p>';
                    
                    // Fix the newline issue - replace literal \n with actual line breaks
                    $html_content = str_replace('\n', "\n", $html_content);
                    $html_content = str_replace("\\n", "\n", $html_content);
                    
                    // Also decode any other escaped characters
                    $html_content = stripslashes($html_content);
                    
                    // Decode HTML entities
                    $html_content = html_entity_decode($html_content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    
                    // Fix image paths to point to shared images folder
                    $html_content = str_replace('src="../../images/', 'src="../images/', $html_content);
                    $html_content = str_replace("src='../../images/", "src='../images/", $html_content);
                    
                    // Output the HTML
                    echo $html_content;
                    ?>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="viewer-footer">
            <div>
                <strong>Roll No:</strong> <?php echo htmlspecialchars($submission_detail['roll_number']); ?> | 
                <strong>Branch:</strong> <?php echo htmlspecialchars($submission_detail['branch']); ?> | 
                <strong>Section:</strong> <?php echo htmlspecialchars($submission_detail['section'] ?? 'N/A'); ?> |
                <strong>Submitted:</strong> <?php echo date('d/m/Y H:i', strtotime($submission_detail['submitted_date'])); ?>
                <?php if ($submission_detail['verification_date']): ?>
                | <strong>Verified:</strong> <?php echo date('d/m/Y H:i', strtotime($submission_detail['verification_date'])); ?>
                <?php endif; ?>
            </div>
            <div>
                <strong>Use:</strong> ESC to close | Ctrl+Mouse Wheel to zoom
            </div>
        </div>
    </div>

    <!-- Verification Form Modal -->
    <div id="verificationModal" class="modal-overlay">
        <div class="modal-box">
            <h3 id="modalTitle">Verify Submission</h3>
            
            <form method="POST" id="verifyForm">
                <input type="hidden" name="submission_id" value="<?php echo $submission_detail['submission_id']; ?>">
                <input type="hidden" name="action" id="actionType" value="verify">
                
                <div class="form-group" id="marksSection" style="margin-bottom: 20px;">
                    <label for="modalMarks">Marks (0-10)</label>
                    <input type="number" id="modalMarks" name="marks" min="0" max="10" step="0.5" 
                           placeholder="Enter marks" required
                           style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 1rem;">
                    <div style="color: #64748b; font-size: 0.85rem; margin-top: 5px;">
                        Enter marks between 0 and 10. Decimal points allowed.
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom: 25px;">
                    <label for="modalFeedback">Feedback / Comments</label>
                    <textarea id="modalFeedback" name="feedback" rows="5" 
                              placeholder="Provide constructive feedback to the student..."
                              style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-family: inherit; font-size: 0.95rem;"><?php 
                              echo htmlspecialchars($submission_detail['feedback'] ?? ''); ?></textarea>
                </div>
                
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" onclick="hideVerificationModal()" 
                            style="background: #64748b; color: white; border: none; padding: 12px 24px; border-radius: 6px; cursor: pointer; font-weight: 600;">
                        Cancel
                    </button>
                    <button type="submit" id="submitBtn"
                            style="background: #10b981; color: white; border: none; padding: 12px 24px; border-radius: 6px; cursor: pointer; font-weight: 600;">
                        Submit Verification
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script>
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

        function handleResponsiveSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const overlay = document.getElementById('overlay');

            if (window.innerWidth > 992) {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
                sidebar.classList.remove('closed');
                mainContent.classList.remove('full-width');
            } else {
                sidebar.classList.remove('closed');
                mainContent.classList.add('full-width');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            handleResponsiveSidebar();

            const sidebarLinks = document.querySelectorAll('.sidebar-menu a');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');

            sidebarLinks.forEach(link => {
                link.addEventListener('click', () => {
                    if (window.innerWidth <= 992) {
                        sidebar.classList.remove('active');
                        overlay.classList.remove('active');
                    }
                });
            });
        });

        window.addEventListener('resize', handleResponsiveSidebar);

        // Only run these functions if we're in the viewer
        <?php if ($submission_detail): ?>
        function showVerificationForm(action) {
            const modal = document.getElementById('verificationModal');
            const title = document.getElementById('modalTitle');
            const actionType = document.getElementById('actionType');
            const marksSection = document.getElementById('marksSection');
            const submitBtn = document.getElementById('submitBtn');
            
            actionType.value = action;
            
            if (action === 'verify') {
                title.textContent = 'Verify Submission';
                marksSection.style.display = 'block';
                modalMarks.required = true;
                submitBtn.textContent = 'Submit Verification';
                submitBtn.style.background = '#10b981';
            } else {
                title.textContent = 'Request Retake';
                marksSection.style.display = 'none';
                modalMarks.required = false;
                submitBtn.textContent = 'Submit Retake Request';
                submitBtn.style.background = '#ef4444';
            }
            
            modal.style.display = 'flex';
        }
        
        function hideVerificationModal() {
            document.getElementById('verificationModal').style.display = 'none';
        }
        
        // ESC key to close viewer
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (document.getElementById('verificationModal')?.style.display === 'flex') {
                    hideVerificationModal();
                } else {
                    window.location.href = '?status=<?php echo $status; ?>&semester=<?=$semester?>&branch=<?=$branch?>&section=<?=$section?>&subject=<?=$subject?>';
                }
            }
        });
        
        // Zoom with Ctrl + Mouse Wheel
        document.addEventListener('wheel', function(e) {
            if (e.ctrlKey) {
                e.preventDefault();
                const container = document.querySelector('.submission-container');
                if (container) {
                    const currentScale = parseFloat(container.style.transform?.replace('scale(', '')?.replace(')', '')) || 1;
                    const newScale = e.deltaY > 0 ? currentScale * 0.9 : currentScale * 1.1;
                    container.style.transform = `scale(${Math.max(0.5, Math.min(2, newScale))})`;
                    container.style.transformOrigin = 'top center';
                }
            }
        }, { passive: false });
        <?php endif; ?>
    </script>
    <?php employee_render_announcement_scripts($employee_id); ?>
</body>
</html>
<?php $conn->close(); ?>