<?php
// employees.php
session_start();
require_once '../db_connect.php';

// SESSION CHECK
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    // header('Location: index.php'); 
    // exit;
}

$display_name = 'Admin';
$username = htmlspecialchars($_SESSION['username'] ?? 'admin', ENT_QUOTES);

// Handle Form Submission
$message = '';

// Handle form submission for adding employee
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_employee') {
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $username_input = mysqli_real_escape_string($conn, trim($_POST['username']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $department = mysqli_real_escape_string($conn, trim($_POST['department']));
    $role = mysqli_real_escape_string($conn, trim($_POST['role'] ?? 'Staff'));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone'] ?? ''));
    $plain_pass = mysqli_real_escape_string($conn, trim($_POST['password'] ?? ''));
    $is_active = 1; 
    
    $password = !empty($plain_pass) ? $plain_pass : $username_input;
    
    $check_sql = "SELECT * FROM employees WHERE username = '$username_input' OR email = '$email'";
    $check_result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_result) > 0) {
        $message = "<div class='alert alert-error'>Employee with this Username or email already exists!</div>";
    } else {
        $sql = "INSERT INTO employees (username, password, name, email, role, department, phone, is_active) 
          VALUES ('$username_input', '$password', '$name', '$email', '$role', '$department', '$phone', $is_active)";
        
        if (mysqli_query($conn, $sql)) {
            $message = "<div class='alert alert-success'><i class='fa-solid fa-check'></i> Employee added successfully!</div>";
        } else {
            $message = "<div class='alert alert-error'>Error: " . mysqli_error($conn) . "</div>";
        }
    }
}

// Handle employee deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_employee') {
    $id = (int)$_POST['employee_id'];
    if ($id > 0) {
        $sql = "DELETE FROM employees WHERE employee_id = $id";
        if (mysqli_query($conn, $sql)) {
            $message = "<div class='alert alert-success'><i class='fa-solid fa-check'></i> Employee deleted successfully!</div>";
        } else {
             $message = "<div class='alert alert-error'>Error deleting employee: " . mysqli_error($conn) . "</div>";
        }
    }
}

// Handle Edit Employee
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_employee') {
    $id = (int)$_POST['id'];
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $username_input = mysqli_real_escape_string($conn, trim($_POST['username']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $department = mysqli_real_escape_string($conn, trim($_POST['department']));
    $phone = mysqli_real_escape_string($conn, trim($_POST['phone']));
    $role = mysqli_real_escape_string($conn, trim($_POST['role']));
    $plain_pass = mysqli_real_escape_string($conn, trim($_POST['password']));
    $is_active = 1; 

    if ($id > 0 && $name && $username_input && $department && $role) {
        if (!empty($plain_pass)) {
            $password = $plain_pass;
            $sql = "UPDATE employees SET 
                    username = '$username_input', 
                    password = '$password', 
                    name = '$name', 
                    email = '$email', 
                    department = '$department', 
                    phone = '$phone', 
                    role = '$role', 
                    is_active = $is_active 
                    WHERE employee_id = $id";
        } else {
            $sql = "UPDATE employees SET 
                    username = '$username_input', 
                    name = '$name', 
                    email = '$email', 
                    department = '$department', 
                    phone = '$phone', 
                    role = '$role', 
                    is_active = $is_active 
                    WHERE employee_id = $id";
        }

        if (mysqli_query($conn, $sql)) {
            $message = "<div class='alert alert-success'><i class='fa-solid fa-check'></i> Employee updated successfully!</div>";
        } else {
             $message = "<div class='alert alert-error'>Error updating employee: " . mysqli_error($conn) . "</div>";
        }
    } else {
        $message = "<div class='alert alert-error'>Please fill all required fields.</div>";
    }
}

// Handle lab assignment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'assign_lab') {
    $employee_id = intval($_POST['employee_id']);
    
    $emp_query = "SELECT username FROM employees WHERE employee_id = $employee_id";
    $emp_result = mysqli_query($conn, $emp_query);
    $emp_data = mysqli_fetch_assoc($emp_result);
    $employee_username = $emp_data['username'];
    
    $subject = mysqli_real_escape_string($conn, trim($_POST['lab_subject']));
    $branch = mysqli_real_escape_string($conn, trim($_POST['branch']));
    $section = mysqli_real_escape_string($conn, trim($_POST['section_name']));
    $semester = mysqli_real_escape_string($conn, trim($_POST['semester']));
    
    $check_sql = "SELECT * FROM employee_subjects 
                  WHERE employee_username = '$employee_username' 
                  AND subject = '$subject'
                  AND branch = '$branch'
                  AND section = '$section'
                  AND (semester = '$semester' OR (semester IS NULL AND '$semester' = ''))";
    $check_result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_result) > 0) {
        $message = "<div class='alert alert-error'>This employee is already assigned to this subject combination!</div>";
    } else {
        $sql = "INSERT INTO employee_subjects (employee_username, subject, branch, section, semester) 
                VALUES ('$employee_username', '$subject', '$branch', '$section', '$semester')";
        
        if (mysqli_query($conn, $sql)) {
            $message = "<div class='alert alert-success'><i class='fa-solid fa-check'></i> Lab assignment added successfully!</div>";
        } else {
             $message = "<div class='alert alert-error'>Error: " . mysqli_error($conn) . "</div>";
        }
    }
}

// Handle Edit Lab Assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_assignment') {
    $assignment_id = (int)$_POST['assignment_id'];
    $employee_id = intval($_POST['employee_id']);
    
    $emp_query = "SELECT username FROM employees WHERE employee_id = $employee_id";
    $emp_result = mysqli_query($conn, $emp_query);
    $emp_data = mysqli_fetch_assoc($emp_result);
    $employee_username = $emp_data['username'];
    
    $subject = mysqli_real_escape_string($conn, trim($_POST['lab_subject']));
    $branch = mysqli_real_escape_string($conn, trim($_POST['branch']));
    $section = mysqli_real_escape_string($conn, trim($_POST['section_name']));
    $semester = mysqli_real_escape_string($conn, trim($_POST['semester']));

    if ($assignment_id > 0 && $employee_username && $subject && $section) {
        $sql = "UPDATE employee_subjects SET 
                employee_username = '$employee_username',
                subject = '$subject', 
                branch = '$branch',
                section = '$section', 
                semester = '$semester'
                WHERE id = $assignment_id";

        if (mysqli_query($conn, $sql)) {
             $message = "<div class='alert alert-success'><i class='fa-solid fa-check'></i> Lab assignment updated successfully!</div>";
        } else {
             $message = "<div class='alert alert-error'>Error updating assignment: " . mysqli_error($conn) . "</div>";
        }
    } else {
        $message = "<div class='alert alert-error'>Please fill all required fields.</div>";
    }
}

// Handle lab assignment deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_assignment') {
    $id = (int)$_POST['assignment_id'];
    if ($id > 0) {
        $sql = "DELETE FROM employee_subjects WHERE id = $id";
        if (mysqli_query($conn, $sql)) {
            $message = "<div class='alert alert-success'><i class='fa-solid fa-check'></i> Lab assignment deleted successfully!</div>";
        } else {
            $message = "<div class='alert alert-error'>Error deleting assignment: " . mysqli_error($conn) . "</div>";
        }
    }
}

// Fetch all employees
$employees = [];
$sql = "SELECT * FROM employees ORDER BY employee_id DESC";
$result = mysqli_query($conn, $sql);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $employees[] = $row;
    }
}

// Fetch all subjects from subjects table
$subjects = [];
$sql_subjects = "SELECT subject, department, semester FROM subjects ORDER BY subject";
$result_subjects = mysqli_query($conn, $sql_subjects);
if ($result_subjects && mysqli_num_rows($result_subjects) > 0) {
    while ($row = mysqli_fetch_assoc($result_subjects)) {
        $subjects[] = $row;
    }
}

// Group subjects by semester
$subjects_by_semester = [];
foreach ($subjects as $subject) {
    $semester = $subject['semester'];
    if (!isset($subjects_by_semester[$semester])) {
        $subjects_by_semester[$semester] = [];
    }
    $subjects_by_semester[$semester][] = $subject['subject'];
}

// Fetch assignments
$assignments = [];
$sql = "SELECT es.id as assignment_id, es.employee_username, es.subject, es.branch, es.section, es.semester,
               e.employee_id, e.name as employee_name, e.department
        FROM employee_subjects es 
        LEFT JOIN employees e ON es.employee_username = e.username 
        ORDER BY es.subject, es.section";
$result = mysqli_query($conn, $sql);
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $assignments[] = $row;
    }
}

$employees_count = count($employees);
$assignments_count = count($assignments);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Employees | Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

    /* ================= SIDEBAR ================= */
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

    /* ================= MAIN CONTENT ================= */
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

    /* --- PAGE SPECIFIC --- */
    .page-title { font-size: 24px; font-weight: 800; color: var(--text-dark); margin-bottom: 24px; }

    /* TAB NAV */
    .tab-nav { display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; }
    .tab-btn { padding: 10px 20px; background: transparent; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; color: var(--text-gray); transition: all 0.3s ease; font-size: 14px; display: inline-flex; align-items: center; gap: 8px; }
    .tab-btn:hover { background: #f1f5f9; color: var(--primary-color); }
    .tab-btn.active { background: var(--primary-light); color: var(--primary-color); font-weight: 700; }
    .tab-content { display: none; }
    .tab-content.active { display: block; }
    
    /* FORM STYLES */
    .form-card, .filter-card {
        background: var(--white);
        padding: 28px;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-card);
        border: 1px solid #f1f5f9;
        margin-bottom: 30px;
    }
    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        align-items: end;
    }
    .form-group { margin-bottom: 5px; }
    .form-group label { display: block; font-size: 12px; font-weight: 700; color: var(--text-gray); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
    .form-group select, .form-group input { width: 100%; padding: 10px 12px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 14px; color: var(--text-dark); transition: 0.2s; background: #fff; }
    .form-group select:focus, .form-group input:focus { outline: none; border-color: var(--accent-color); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
    
    .btn { padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s; border: none; font-size: 14px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; }
    .btn-primary { background: var(--accent-color); color: white; }
    .btn-primary:hover { background: var(--primary-color); }
    .btn-danger { background: #fee2e2; color: #b91c1c; }
    .btn-danger:hover { background: #fecaca; }
    .btn-edit { background: #dcfce7; color: #15803d; }
    .btn-secondary { background: #f1f5f9; color: var(--text-gray); }
    
    /* TABLE STYLES */
    .table-responsive { width: 100%; overflow-x: auto; background: var(--white); border-radius: var(--radius-lg); box-shadow: var(--shadow-card); border: 1px solid #f1f5f9; }
    table { width: 100%; border-collapse: separate; border-spacing: 0; }
    th { text-align: left; padding: 18px 24px; background: #f8fafc; color: var(--text-gray); font-size: 12px; font-weight: 700; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; }
    td { padding: 16px 24px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: var(--text-dark); vertical-align: middle; }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: #f8fafc; }
    .empty-state { text-align: center; padding: 40px; color: var(--text-gray); }

    .status-badge { padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; background: #f1f5f9; color: var(--text-gray); }
    
    /* ALERTS */
    .alert { padding: 16px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; display: flex; align-items: center; gap: 10px; }
    .alert-success { background: #f0fdf4; color: #15803d; border: 1px solid #dcfce7; }
    .alert-error { background: #fef2f2; color: #b91c1c; border: 1px solid #fee2e2; }

    /* Dropdown for Employee Search */
    #employeeDropdownList {
        position: absolute; top: 100%; left: 0; right: 0; 
        border: 1px solid #e2e8f0; border-top: none; 
        background: white; border-radius: 0 0 8px 8px; 
        max-height: 200px; overflow-y: auto; display: none; z-index: 10; 
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .dropdown-item { padding: 10px 12px; cursor: pointer; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
    .dropdown-item:hover { background: #f8fafc; color: var(--accent-color); }

    @media (max-width: 992px) {
        .sidebar { transform: translateX(-100%); width: 280px; }
        .sidebar.active { transform: translateX(0); }
        .main-content { margin-left: 0; width: 100%; }
        .overlay.active { opacity: 1; visibility: visible; }
    }

    @media (max-width: 768px) {
        .top-header { padding: 0 20px; }
        .dashboard-container { padding: 20px; }
        .card, .form-card { padding: 20px; }
        .page-title { font-size: 20px; }
        
        .header-branding h1 { font-size: 14px; }
        .header-branding p { font-size: 9px; }
    }

    @media (max-width: 600px) {
        .header-branding { display: flex; flex-direction: column; }
        .header-branding h1 { font-size: 12px; line-height: 1.2; }
        .header-right .info-text { display: none; }
        
        .dashboard-container { padding: 15px; }
        .card, .form-card { padding: 15px; }
        
        .tab-menu { overflow-x: auto; white-space: nowrap; padding-bottom: 5px; }
        .tab-btn { padding: 10px 15px; font-size: 13px; }
        
        .header-logout-btn span { display: none; }
        .header-logout-btn { padding: 8px; }
    }
</style>
</head>
<body>

    <div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

    <nav class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-wrapper">
                <img src="../images/vasavi.png" alt="Logo">
                <div class="brand-text">
                    <span class="brand-title">SVEC</span>
                    <span class="brand-subtitle">Administration</span>
                </div>
            </div>
            <div class="close-btn" onclick="toggleSidebar()"><i class="fa-solid fa-xmark"></i></div>
        </div>
        <div class="sidebar-user">
            <img src="https://ui-avatars.com/api/?name=Admin&background=1e3a8a&color=fff&size=128">
            <div style="font-weight:700; color:var(--text-dark);">Administrator</div>
            <div style="font-size:12px; color:var(--text-gray);">admin</div>
        </div>
        <div class="sidebar-menu">
            <a href="admin_dashboard.php" class="menu-item"> Dashboard</a>
            <a href="students.php" class="menu-item"> Students</a>
            <a href="employees.php" class="menu-item active"> Employees</a>
            <a href="subjects.php" class="menu-item"> Subjects</a>
            <a href="timetable.php" class="menu-item"> Timetable</a>
            <a href="reports.php" class="menu-item"> Reports</a>
              <a href="admin_control_pdf.php" class="menu-item"> Downloads</a>
            <a href="announcements.php" class="menu-item"> Announcements</a>
        </div>
        <div class="logout-container">
            <a href="logout.php" class="logout-btn"><i class="fa-solid fa-arrow-right-from-bracket" style="margin-right:8px;"></i> Logout</a>
        </div>
    </nav>

    <main class="main-content" id="mainContent">
        <header class="top-header">
            <div style="display:flex; align-items:center;">
                <button class="toggle-btn" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
                <div class="header-branding">
                    <h1>SRI VASAVI ENGINEERING COLLEGE</h1>
                </div>
            </div>
            <div class="header-right" style="display:flex; align-items:center; gap:15px;">
                <div class="info-text" style="text-align:right;">
                    <div style="font-size:14px; font-weight:700; color:var(--text-dark);">Administrator</div>
                    <div style="font-size:11px; color:var(--text-gray); font-weight:600;">Admin</div>
                </div>
                <img src="https://ui-avatars.com/api/?name=Admin&background=1e3a8a&color=fff" style="width:42px; height:42px; border-radius:50%; border:2px solid #e2e8f0;">
                <a href="logout.php" class="header-logout-btn" style="display: flex; align-items: center; gap: 6px; padding: 8px 14px; background: #fef2f2; color: var(--secondary-color); border: 1px solid #fecaca; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none; transition: all 0.2s;"><i class="fa-solid fa-arrow-right-from-bracket"></i> <span>LOG OUT</span></a>
            </div>
        </header>

        <div class="dashboard-container">
            <div class="page-title">Employees & Lab Management</div>
            
            <?= $message ?>

            <div class="tab-nav">
                <button class="tab-btn active" onclick="showTab('employees-tab')"><i class="fa-solid fa-users"></i> Employees List</button>
                <button class="tab-btn" onclick="showTab('lab-assignments-tab')"><i class="fa-solid fa-flask"></i> Lab Assignments</button>
            </div>

            <!-- EMPLOYEES TAB -->
            <div id="employees-tab" class="tab-content active">
                <div class="form-card">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid #f1f5f9; padding-bottom:15px;">
                        <h3 id="employeeFormTitle" style="margin:0; color:var(--primary-color);">Add New Employee</h3>
                    </div>

                    <form method="POST" action="" id="employeeForm">
                        <input type="hidden" name="action" value="add_employee" id="employeeFormAction">
                        <input type="hidden" name="id" id="employee_id">
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Name <span style="color:#dc2626">*</span></label>
                                <input type="text" name="name" id="employee_name" required placeholder="Enter full name">
                            </div>
                            <div class="form-group">
                                <label>Username <span style="color:#dc2626">*</span></label>
                                <input type="text" name="username" id="employee_username" required placeholder="Enter username">
                            </div>
                            <div class="form-group">
                                <label>Password</label>
                                <input type="password" name="password" id="employee_password" placeholder="Default: Same as Username">
                            </div>
                            <div class="form-group">
                                <label>Email <span style="color:#dc2626">*</span></label>
                                <input type="email" name="email" id="employee_email" required placeholder="Enter email address">
                            </div>
                            <div class="form-group">
                                <label>Department <span style="color:#dc2626">*</span></label>
                                <select name="department" id="employee_department" required>
                                     <option value="">Select Department</option>
                                    <option value="BSH">BSH</option>
                                    <option value="CSE">CSE</option>
                                    <option value="ECE">ECE</option>
                                    <option value="MECH">MECH</option>
                                    <option value="CIVIL">CIVIL</option>
                                    <option value="EEE">EEE</option>
                                    <option value="AIML">AIML</option>
                                    <option value="CSE(AI)">CSE(AI)</option>
                                     <option value="CSD">CSD</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Role <span style="color:#dc2626">*</span></label>
                                <select name="role" id="employee_role" required>
                                    <option value="Staff">Staff</option>
                                    <option value="Associate Professor">Associate Professor</option>
                                    <option value="Assistant Professor">Assistant Professor</option>
                                    <option value="Professor">Professor</option>
                                    <option value="Lab Assistant">Lab Assistant</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="text" name="phone" id="employee_phone" placeholder="Enter phone number">
                            </div>
                        </div>
                        <div style="margin-top:24px; display:flex; gap:12px;">
                            <button class="btn btn-primary" type="submit" id="employeeSubmitBtn"><i class="fa-solid fa-plus"></i> Add Employee</button>
                            <button class="btn btn-secondary" type="button" onclick="resetEmployeeForm()" id="employeeCancelBtn" style="display:none;"><i class="fa-solid fa-xmark"></i> Cancel</button>
                        </div>
                    </form>
                </div>

                <!-- List Section -->
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                    <div class="page-title" style="font-size:18px; margin-bottom:0;">Employee List</div>
                    <button class="btn" id="importBtn" style="background: #2563eb; color: white; gap: 8px;"><i class="fa-solid fa-upload"></i> Import Employees</button>
                </div>

                <div class="filter-card" style="padding:15px; display:flex; gap:15px; flex-wrap:wrap; margin-bottom:15px;">
                     <input type="text" id="searchEmployees" placeholder="Search employees..." style="padding:8px; border:1px solid #e2e8f0; border-radius:6px; flex:1; min-width:200px;">
                     <select id="filterEmployeeDepartment" style="padding:8px; border:1px solid #e2e8f0; border-radius:6px; min-width:150px;">
                        <option value="">All Departments</option>
                        <?php 
                          $departments = array_unique(array_column($employees, 'department'));
                          sort($departments);
                          foreach ($departments as $dept) { if (!empty($dept)) echo "<option value='".htmlspecialchars($dept)."'>".htmlspecialchars($dept)."</option>"; }
                        ?>
                     </select>
                </div>

                <!-- Import Modal -->
                <div id="importModal" class="modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
                    <div class="modal-content" style="background:white; padding:32px; border-radius:12px; max-width:500px; width:90%; box-shadow:0 20px 25px rgba(0,0,0,0.15);">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
                            <h2 style="margin:0; font-size:20px; font-weight:700; color:var(--text-dark);">Import Employees</h2>
                            <button type="button" onclick="closeImportModal()" style="background:none; border:none; font-size:24px; cursor:pointer; color:var(--text-gray);">&times;</button>
                        </div>
                        
                        <div id="importMessage" style="display:none; padding:12px; border-radius:8px; margin-bottom:16px;"></div>
                        
                        <form id="importForm" style="display:flex; flex-direction:column; gap:16px;">
                            <div style="border:2px dashed #2563eb; border-radius:8px; padding:32px; text-align:center; cursor:pointer; background:#f0f7ff;" id="dropZone" onclick="document.getElementById('fileInput').click();">
                                <div style="font-size:32px; color:#2563eb; margin-bottom:8px;"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                                <p style="margin:0; color:var(--text-dark); font-weight:600;">Click to browse or drag file here</p>
                                <p style="margin:8px 0 0 0; font-size:12px; color:var(--text-gray);">CSV or Excel format (.csv, .xlsx, .xls)</p>
                            </div>
                            
                            <input type="file" id="fileInput" accept=".csv,.xlsx,.xls" style="display:none;">
                            <p id="fileName" style="margin:0; font-size:12px; color:var(--text-gray);"></p>
                            
                            <div style="background:#f0f7ff; border:1px solid #bfdbfe; border-radius:8px; padding:16px; font-size:12px; color:#1e40af;">
                                <p style="margin:0 0 8px 0; font-weight:600;"><i class="fa-solid fa-info-circle"></i> Required CSV Columns:</p>
                                <p style="margin:4px 0; font-family:monospace;">name, username, password, email, department, role, phone</p>
                                <p style="margin:8px 0 0 0; font-size:11px; color:#1e3a8a;">Maximum 10,000 employees per import</p>
                            </div>
                            
                            <div style="display:flex; gap:12px;">
                                <button type="button" class="btn btn-primary" id="submitImportBtn" style="flex:1;"><i class="fa-solid fa-upload"></i> Import</button>
                                <button type="button" class="btn" onclick="closeImportModal()" style="flex:1; background:#f1f5f9; color:var(--text-dark);">Cancel</button>
                            </div>
                        </form>
                        
                        <div id="importProgress" style="display:none; margin-top:16px;">
                            <div style="height:6px; background:#e2e8f0; border-radius:4px; overflow:hidden;">
                                <div id="progressBar" style="height:100%; background:#2563eb; width:0%; transition:width 0.3s;"></div>
                            </div>
                            <p id="importStatus" style="margin:8px 0 0 0; font-size:12px; color:var(--text-gray); text-align:center;"></p>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="employeesTable">
                      <thead>
                        <tr>
                          <th>Name</th>
                          <th>Username</th>
                          <th>Department</th>
                          <th>Role</th>
                          <th>Email</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody id="employeesTableBody">
                        <?php if (empty($employees)): ?>
                            <tr><td colspan="6" class="empty-state">No employees found</td></tr>
                        <?php else: ?>
                            <?php foreach ($employees as $emp): ?>
                            <tr>
                                <td style="font-weight:600;"><?= htmlspecialchars($emp['name']) ?></td>
                                <td><?= htmlspecialchars($emp['username']) ?></td>
                                <td><span class="status-badge"><?= htmlspecialchars($emp['department']) ?></span></td>
                                <td><?= htmlspecialchars($emp['role']) ?></td>
                                <td><?= htmlspecialchars($emp['email']) ?></td>
                                <td>
                                    <div style="display:flex; gap:8px;">
                                        <button class="btn btn-edit" style="padding:6px 12px; font-size:12px;" onclick="editEmployee(<?= $emp['employee_id'] ?>, '<?= htmlspecialchars($emp['name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($emp['username'], ENT_QUOTES) ?>', '<?= htmlspecialchars($emp['email'], ENT_QUOTES) ?>', '<?= htmlspecialchars($emp['department'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($emp['phone'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($emp['role'] ?? 'Staff', ENT_QUOTES) ?>')">
                                            <i class="fa-solid fa-pen"></i> Edit
                                        </button>
                                        <form method="post" style="margin:0;" onsubmit="return confirm('Delete this employee?');">
                                            <input type="hidden" name="action" value="delete_employee">
                                            <input type="hidden" name="employee_id" value="<?= $emp['employee_id'] ?>">
                                            <button type="submit" class="btn btn-danger" style="padding:6px 12px; font-size:12px;">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                      </tbody>
                    </table>
                </div>
            </div>

            <!-- LAB ASSIGNMENTS TAB -->
            <div id="lab-assignments-tab" class="tab-content">
                <div class="form-card">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid #f1f5f9; padding-bottom:15px;">
                        <h3 id="assignmentFormTitle" style="margin:0; color:var(--primary-color);">Assign Lab to Employee</h3>
                    </div>

                    <form method="POST" action="" id="assignmentForm">
                        <input type="hidden" name="action" value="assign_lab" id="assignmentFormAction">
                        <input type="hidden" name="assignment_id" id="assignment_id">
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Employee Department <span style="color:#dc2626">*</span></label>
                                <select name="employee_department" id="assignment_employee_department" required onchange="filterEmployeesByDepartment()">
                                    <option value="">Select Department</option>
                                    <option value="BSH">BSH</option>
                                    <option value="CSE">CSE</option>
                                    <option value="ECE">ECE</option>
                                    <option value="MECH">MECH</option>
                                    <option value="CIVIL">CIVIL</option>
                                    <option value="EEE">EEE</option>
                                    <option value="AIML">AIML</option>
                                    <option value="CSE(AI)">CSE(AI)</option>
                                     <option value="CSD">CSD</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Employee <span style="color:#dc2626">*</span></label>
                                <div style="position: relative;">
                                    <input type="text" id="employeeSearchInput" placeholder="Select Department First" disabled style="width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-family: inherit; background-color: #f9f9f9; cursor: not-allowed;" oninput="filterEmployeeDropdown()" onfocus="showEmployeeDropdown()" onkeydown="handleEmployeeSearch(event)">
                                    <input type="hidden" name="employee_id" id="assignment_employee_id">
                                    <div id="employeeDropdownList"></div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Branch <span style="color:#dc2626">*</span></label>
                                <select name="branch" id="assignment_branch" required>
                                    <option value="">Select Branch</option>
                                    <option>CSE</option>
                                    <option>CST</option>
                                    <option>AIML</option>
                                    <option>CAI</option>
                                    <option>CSD</option>
                                    <option>ECE</option>
                                    <option>ECT</option>
                                    <option>EEE</option>
                                    <option>MECH</option>
                                    <option>CIVIL</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Lab Subject <span style="color:#dc2626">*</span></label>
                                <select name="lab_subject" id="assignment_lab_subject" required>
                                    <option value="">Select Subject</option>
                                    <?php foreach ($subjects_by_semester as $semester => $subject_list): ?>
                                    <optgroup label="Semester <?= htmlspecialchars($semester) ?>">
                                        <?php foreach ($subject_list as $subject): ?>
                                        <option value="<?= htmlspecialchars($subject) ?>"><?= htmlspecialchars($subject) ?></option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Section <span style="color:#dc2626">*</span></label>
                                <select name="section_name" id="assignment_section_name" required>
                                    <option value="">Select Section</option>
                                    <option value="A">A</option><option value="B">B</option><option value="C">C</option><option value="D">D</option><option value="E">E</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Semester</label>
                                <select name="semester" id="assignment_semester">
                                    <option value="">Select Semester</option>
                                    <option value="I">I</option><option value="II">II</option><option value="III">III</option><option value="IV">IV</option><option value="V">V</option><option value="VI">VI</option><option value="VII">VII</option><option value="VIII">VIII</option>
                                </select>
                            </div>
                        </div>
                        <div style="margin-top:24px; display:flex; gap:12px;">
                            <button class="btn btn-primary" type="submit" id="assignmentSubmitBtn"><i class="fa-solid fa-plus"></i> Assign Lab</button>
                            <button class="btn btn-secondary" type="button" onclick="resetAssignmentForm()" id="assignmentCancelBtn" style="display:none;"><i class="fa-solid fa-xmark"></i> Cancel</button>
                        </div>
                    </form>
                </div>

                <div class="filter-card" style="padding:15px; display:flex; gap:15px; flex-wrap:wrap; margin-bottom:15px;">
                     <input type="text" id="searchAssignments" placeholder="Search assignments..." style="padding:8px; border:1px solid #e2e8f0; border-radius:6px; flex:1; min-width:200px;">
                     <select id="filterAssignmentBranch" style="padding:8px; border-radius:6px; border:1px solid #e2e8f0;"><option value="">All Branches</option><?php foreach(array_unique(array_column($assignments, 'branch')) as $b) { if($b) echo "<option>$b</option>"; } ?></select>
                </div>

                <div class="table-responsive">
                    <table id="assignmentsTable">
                      <thead>
                        <tr>
                          <th>Subject</th>
                          <th>Employee</th>
                          <th>Dept</th>
                          <th>Branch</th>
                          <th>Sec</th>
                          <th>Sem</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody id="assignmentsTableBody">
                        <?php if (empty($assignments)): ?>
                            <tr><td colspan="7" class="empty-state">No lab assignments found</td></tr>
                        <?php else: ?>
                            <?php foreach ($assignments as $a): ?>
                            <tr>
                                <td style="font-weight:600;"><?= htmlspecialchars($a['subject']) ?></td>
                                <td><?= htmlspecialchars($a['employee_name']) ?></td>
                                <td><span class="status-badge"><?= htmlspecialchars($a['department']) ?></span></td>
                                <td><?= htmlspecialchars($a['branch']) ?></td>
                                <td><?= htmlspecialchars($a['section']) ?></td>
                                <td><?= htmlspecialchars($a['semester']) ?></td>
                                <td>
                                    <div style="display:flex; gap:8px;">
                                        <button class="btn btn-edit" style="padding:6px 12px; font-size:12px;" onclick="editAssignment(<?= $a['assignment_id'] ?>, <?= $a['employee_id'] ?>, '<?= htmlspecialchars($a['department'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($a['branch'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($a['subject'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($a['section'] ?? '', ENT_QUOTES) ?>', '<?= htmlspecialchars($a['semester'] ?? '', ENT_QUOTES) ?>')">
                                            <i class="fa-solid fa-pen"></i> Edit
                                        </button>
                                        <form method="post" style="margin:0;" onsubmit="return confirm('Delete this assignment?');">
                                            <input type="hidden" name="action" value="delete_assignment">
                                            <input type="hidden" name="assignment_id" value="<?= $a['assignment_id'] ?>">
                                            <button type="submit" class="btn btn-danger" style="padding:6px 12px; font-size:12px;">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                      </tbody>
                    </table>
                </div>
            </div>
            
        </div>
    </main>

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

        function showTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            
            // Highlight button
            const btns = document.querySelectorAll('.tab-btn');
            if (tabId === 'employees-tab') btns[0].classList.add('active');
            else btns[1].classList.add('active');
        }

        // --- EMPLOYEE LOGIC ---
        function editEmployee(id, name, username, email, dept, phone, role) {
            document.getElementById('employee_id').value = id;
            document.getElementById('employee_name').value = name;
            document.getElementById('employee_username').value = username;
            document.getElementById('employee_email').value = email;
            document.getElementById('employee_department').value = dept;
            document.getElementById('employee_phone').value = phone;
            document.getElementById('employee_role').value = role;
            document.getElementById('employee_password').placeholder = 'Leave blank to keep current';

            document.getElementById('employeeFormTitle').textContent = 'Edit Employee';
            document.getElementById('employeeFormAction').value = 'edit_employee';
            const submitBtn = document.getElementById('employeeSubmitBtn');
            submitBtn.innerHTML = '<i class="fa-solid fa-check"></i> Update Employee';
            document.getElementById('employeeCancelBtn').style.display = 'inline-flex';
            
            document.getElementById('employeeForm').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        function resetEmployeeForm() {
            document.getElementById('employeeForm').reset();
            document.getElementById('employee_id').value = '';
            document.getElementById('employeeFormTitle').textContent = 'Add New Employee';
            document.getElementById('employeeFormAction').value = 'add_employee';
            document.getElementById('employeeSubmitBtn').innerHTML = '<i class="fa-solid fa-plus"></i> Add Employee';
            document.getElementById('employeeCancelBtn').style.display = 'none';
        }

        // Employee Filtering
        const searchInput = document.getElementById('searchEmployees');
        const deptFilter = document.getElementById('filterEmployeeDepartment');
        
        function filterEmployees() {
            const term = searchInput.value.toLowerCase();
            const dept = deptFilter.value.toLowerCase();
            const rows = document.getElementById('employeesTableBody').getElementsByTagName('tr');
            
            for (let row of rows) {
                if (row.cells.length < 2) continue;
                const name = row.cells[0].textContent.toLowerCase();
                const user = row.cells[1].textContent.toLowerCase();
                const d = row.cells[2].textContent.toLowerCase();
                
                const matchesSearch = !term || name.includes(term) || user.includes(term);
                const matchesDept = !dept || d === dept;
                
                row.style.display = (matchesSearch && matchesDept) ? '' : 'none';
            }
        }
        searchInput.addEventListener('input', filterEmployees);
        deptFilter.addEventListener('change', filterEmployees);

        // --- ASSIGNMENT LOGIC ---
        const allEmployees = <?= json_encode($employees) ?>;
        
        function filterEmployeesByDepartment() {
            const dept = document.getElementById('assignment_employee_department').value;
            const searchInput = document.getElementById('employeeSearchInput');
            const dropdown = document.getElementById('employeeDropdownList');
            
            searchInput.value = '';
            document.getElementById('assignment_employee_id').value = '';
            
            if (dept) {
                searchInput.disabled = false;
                searchInput.placeholder = 'Search Employee...';
                searchInput.style.backgroundColor = '#fff';
                searchInput.style.cursor = 'text';
            } else {
                searchInput.disabled = true;
                searchInput.placeholder = 'Select Department First';
                searchInput.style.backgroundColor = '#f9f9f9';
                searchInput.style.cursor = 'not-allowed';
                dropdown.style.display = 'none';
            }
        }

        function filterEmployeeDropdown() {
            const searchVal = document.getElementById('employeeSearchInput').value.toLowerCase();
            const dept = document.getElementById('assignment_employee_department').value;
            const dropdown = document.getElementById('employeeDropdownList');
            
            if (!dept) return;

            const filtered = allEmployees.filter(e => 
                (e.department === dept) && 
                (e.name.toLowerCase().includes(searchVal) || e.username.toLowerCase().includes(searchVal))
            );
            
            dropdown.innerHTML = '';
            if (filtered.length > 0) {
                filtered.forEach(e => {
                    const div = document.createElement('div');
                    div.className = 'dropdown-item';
                    div.textContent = `${e.name} (${e.username})`;
                    div.onclick = () => selectEmployee(e);
                    dropdown.appendChild(div);
                });
                dropdown.style.display = 'block';
            } else {
                dropdown.style.display = 'none';
            }
        }

        function showEmployeeDropdown() {
            filterEmployeeDropdown();
        }

        function selectEmployee(emp) {
            document.getElementById('employeeSearchInput').value = `${emp.name} (${emp.username})`;
            document.getElementById('assignment_employee_id').value = emp.employee_id;
            document.getElementById('employeeDropdownList').style.display = 'none';
        }

        // Hide dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('#employeeSearchInput') && !e.target.closest('#employeeDropdownList')) {
                document.getElementById('employeeDropdownList').style.display = 'none';
            }
        });

        function editAssignment(id, empId, dept, branch, subject, section, semester) {
             // Switch Tab
             showTab('lab-assignments-tab');
             
             document.getElementById('assignment_id').value = id;
             document.getElementById('assignment_employee_department').value = dept;
             
             // Trigger filter to enable employee search
             filterEmployeesByDepartment();
             
             // Find employee details
             const emp = allEmployees.find(e => e.employee_id == empId);
             if (emp) {
                 selectEmployee(emp);
             }
             
             document.getElementById('assignment_branch').value = branch;
             document.getElementById('assignment_lab_subject').value = subject;
             document.getElementById('assignment_section_name').value = section;
             document.getElementById('assignment_semester').value = semester;

             document.getElementById('assignmentFormTitle').textContent = 'Edit Lab Assignment';
             document.getElementById('assignmentFormAction').value = 'edit_assignment';
             document.getElementById('assignmentSubmitBtn').innerHTML = '<i class="fa-solid fa-check"></i> Update Assignment';
             document.getElementById('assignmentCancelBtn').style.display = 'inline-flex';
             
             document.getElementById('assignmentForm').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        function resetAssignmentForm() {
            document.getElementById('assignmentForm').reset();
            document.getElementById('assignment_id').value = '';
            document.getElementById('assignment_employee_id').value = '';
            filterEmployeesByDepartment(); // Reset search input state
            
            document.getElementById('assignmentFormTitle').textContent = 'Assign Lab to Employee';
            document.getElementById('assignmentFormAction').value = 'assign_lab';
            document.getElementById('assignmentSubmitBtn').innerHTML = '<i class="fa-solid fa-plus"></i> Assign Lab';
            document.getElementById('assignmentCancelBtn').style.display = 'none';
        }
        
        // Assignment Filtering
         const searchAssign = document.getElementById('searchAssignments');
         const branchFilter = document.getElementById('filterAssignmentBranch');

         function filterAssignments() {
             const term = searchAssign.value.toLowerCase();
             const branch = branchFilter.value;
             const rows = document.getElementById('assignmentsTableBody').getElementsByTagName('tr');

             for (let row of rows) {
                 if (row.cells.length < 2) continue;
                 const subject = row.cells[0].textContent.toLowerCase();
                 const name = row.cells[1].textContent.toLowerCase();
                 const b = row.cells[3].textContent;

                 const matchesSearch = !term || subject.includes(term) || name.includes(term);
                 const matchesBranch = !branch || b === branch;

                 row.style.display = (matchesSearch && matchesBranch) ? '' : 'none';
             }
         }
         searchAssign.addEventListener('input', filterAssignments);
         branchFilter.addEventListener('change', filterAssignments);

        // Import Employees Functionality
        let selectedFile = null;

        document.getElementById('importBtn').addEventListener('click', function() {
            document.getElementById('importModal').style.display = 'flex';
            selectedFile = null;
            document.getElementById('fileInput').value = '';
            document.getElementById('fileName').textContent = '';
            document.getElementById('importMessage').style.display = 'none';
            document.getElementById('importProgress').style.display = 'none';
            document.getElementById('importForm').style.display = 'flex';
        });

        function closeImportModal() {
            document.getElementById('importModal').style.display = 'none';
            selectedFile = null;
            document.getElementById('fileInput').value = '';
        }

        // Drag and drop handling
        const dropZone = document.getElementById('dropZone');
        
        dropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            dropZone.style.background = '#e0eeff';
            dropZone.style.borderColor = '#1e40af';
        });

        dropZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            dropZone.style.background = '#f0f7ff';
            dropZone.style.borderColor = '#2563eb';
        });

        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            dropZone.style.background = '#f0f7ff';
            dropZone.style.borderColor = '#2563eb';
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleFileSelect(files[0]);
            }
        });

        document.getElementById('fileInput').addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                handleFileSelect(e.target.files[0]);
            }
        });

        function handleFileSelect(file) {
            const allowedTypes = ['text/csv', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
            const allowedExtensions = ['csv', 'xls', 'xlsx'];
            const fileExtension = file.name.split('.').pop().toLowerCase();
            
            if (!allowedExtensions.includes(fileExtension)) {
                showImportMessage('error', 'Invalid file type. Please upload CSV or Excel file.');
                return;
            }
            
            selectedFile = file;
            document.getElementById('fileName').textContent = '📄 Selected: ' + file.name + ' (' + (file.size / 1024).toFixed(2) + ' KB)';
        }

        document.getElementById('submitImportBtn').addEventListener('click', async function() {
            if (!selectedFile) {
                showImportMessage('error', 'Please select a file first');
                return;
            }

            document.getElementById('importForm').style.display = 'none';
            document.getElementById('importProgress').style.display = 'block';
            
            const formData = new FormData();
            formData.append('import_file', selectedFile);

            try {
                document.getElementById('importStatus').textContent = 'Uploading and processing file...';
                
                const response = await fetch('import_employees.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.status === 'success') {
                    document.getElementById('progressBar').style.width = '100%';
                    document.getElementById('importStatus').textContent = 'Import completed!';
                    
                    showImportMessage('success', `Successfully imported ${data.imported_count} employees. ${data.failed_count > 0 ? 'Failed: ' + data.failed_count : ''}`);
                    
                    // Reload page after 2 seconds
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    document.getElementById('importStatus').textContent = 'Import failed';
                    showImportMessage('error', data.message);
                    document.getElementById('importForm').style.display = 'flex';
                    document.getElementById('importProgress').style.display = 'none';
                }
            } catch (error) {
                document.getElementById('importStatus').textContent = 'Error during import';
                showImportMessage('error', 'Error: ' + error.message);
                document.getElementById('importForm').style.display = 'flex';
                document.getElementById('importProgress').style.display = 'none';
            }
        });

        function showImportMessage(type, message) {
            const messageDiv = document.getElementById('importMessage');
            messageDiv.textContent = message;
            messageDiv.style.display = 'block';
            
            if (type === 'success') {
                messageDiv.style.background = '#dcfce7';
                messageDiv.style.color = '#166534';
                messageDiv.style.border = '1px solid #bbf7d0';
            } else {
                messageDiv.style.background = '#fee2e2';
                messageDiv.style.color = '#991b1b';
                messageDiv.style.border = '1px solid #fecaca';
            }
        }

    </script>
</body>
</html>