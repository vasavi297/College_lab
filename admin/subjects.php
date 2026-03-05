<?php
// subjects.php
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

// Handle Delete Subject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_subject') {
  $name = isset($_POST['subject']) ? trim($_POST['subject']) : '';
  $dept = isset($_POST['department']) ? trim($_POST['department']) : '';
  $semester = isset($_POST['semester']) ? trim($_POST['semester']) : '';
  if ($name && $dept && $semester) {
    $stmt = $conn->prepare("DELETE FROM subjects WHERE subject = ? AND department = ? AND semester = ?");
        if ($stmt) {
      $stmt->bind_param("sss", $name, $dept, $semester);
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'><i class='fa-solid fa-check'></i> Subject deleted successfully!</div>";
            } else {
                $message = "<div class='alert alert-error'><i class='fa-solid fa-triangle-exclamation'></i> Error deleting subject: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }
    }
}

// Handle Edit Subject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_subject') {
  $old_subject = isset($_POST['old_subject']) ? trim($_POST['old_subject']) : '';
  $old_department = isset($_POST['old_department']) ? trim($_POST['old_department']) : '';
  $old_semester = isset($_POST['old_semester']) ? trim($_POST['old_semester']) : '';
    $name = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $dept = isset($_POST['department']) ? trim($_POST['department']) : '';
  $semester = isset($_POST['semester']) ? trim($_POST['semester']) : '';

  if ($name && $dept && $semester) {
    if (!$old_subject) {
      $old_subject = $name;
    }
    if (!$old_department) {
      $old_department = $dept;
    }
    if (!$old_semester) {
      $old_semester = $semester;
    }
    $stmt = $conn->prepare("UPDATE subjects SET subject = ?, department = ?, semester = ? WHERE subject = ? AND department = ? AND semester = ?");
        if ($stmt) {
      $stmt->bind_param("ssssss", $name, $dept, $semester, $old_subject, $old_department, $old_semester);
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'><i class='fa-solid fa-check'></i> Subject updated successfully!</div>";
            } else {
                $message = "<div class='alert alert-error'>Error updating: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }
    } else {
        $message = "<div class='alert alert-error'>Please fill all required fields.</div>";
    }
}

// Handle Add Subject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_subject') {
    $name = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $dept = isset($_POST['department']) ? trim($_POST['department']) : '';
    $semester = isset($_POST['semester']) ? trim($_POST['semester']) : '';

  if ($name && $dept && $semester) {
    $stmt = $conn->prepare("INSERT INTO subjects (subject, department, semester) VALUES (?, ?, ?)");
        if ($stmt) {
      $stmt->bind_param("sss", $name, $dept, $semester);
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'><i class='fa-solid fa-check'></i> Subject added successfully!</div>";
            } else {
                $message = "<div class='alert alert-error'><i class='fa-solid fa-triangle-exclamation'></i> Error: " . $stmt->error . "</div>";
            }
            $stmt->close();
        } else {
             $message = "<div class='alert alert-error'>Database Error: " . $conn->error . "</div>";
        }
    } else {
        $message = "<div class='alert alert-error'>Please fill all required fields.</div>";
    }
}

// Fetch Subjects
$subjects = [];
if ($conn) {
    $result = $conn->query("SELECT * FROM subjects ORDER BY subject ASC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $subjects[] = $row;
        }
    } else {
        $message = "<div class='alert alert-error'>Error fetching subjects: " . $conn->error . "</div>";
    }
} else {
    $message = "<div class='alert alert-error'>Database connection error</div>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Subjects | Admin</title>
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

    /* FORM STYLES */
    .form-card {
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

    @media (max-width: 992px) {
        .sidebar { transform: translateX(-100%); width: 280px; }
        .sidebar.active { transform: translateX(0); }
        .main-content { margin-left: 0; width: 100%; }
        .overlay.active { opacity: 1; visibility: visible; }
    }

    @media (max-width: 768px) {
        .top-header { padding: 0 20px; }
        .dashboard-container { padding: 20px; }
        .filter-card, .form-card { padding: 20px; }
        .page-title { font-size: 20px; }
        
        .header-branding h1 { font-size: 14px; }
        .header-branding p { font-size: 9px; }
    }

    @media (max-width: 600px) {
        .header-branding { display: flex; flex-direction: column; }
        .header-branding h1 { font-size: 12px; line-height: 1.2; }
        .header-right .info-text { display: none; }
        
        .dashboard-container { padding: 15px; }
        .filter-card, .form-card { padding: 15px; }
        
        .filter-card { flex-direction: column; align-items: stretch; gap: 10px; }
        .filter-card input, .filter-card select { width: 100% !important; }
        
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
            <a href="employees.php" class="menu-item"> Employees</a>
            <a href="subjects.php" class="menu-item active"> Subjects</a>
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
            <div class="page-title">Manage Subjects</div>
            
            <?= $message ?>

            <div class="form-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid #f1f5f9; padding-bottom:15px;">
                    <h3 id="formTitle" style="margin:0; color:var(--primary-color);">Add New Subject</h3>
                </div>

                <form method="post" action="subjects.php" id="subjectForm">
                    <input type="hidden" name="action" id="formAction" value="add_subject">
                    <input type="hidden" name="old_subject" id="old_subject">
                    <input type="hidden" name="old_department" id="old_department">
                    <input type="hidden" name="old_semester" id="old_semester">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Subject Name <span style="color:#dc2626">*</span></label>
                            <input name="subject" id="subject_name" placeholder="e.g., Data Structures" required>
                        </div>
                        <div class="form-group">
                            <label>Department <span style="color:#dc2626">*</span></label>
                            <select name="department" id="department" required>
                                <option value="">Select Department</option>
                                <option value="CSE">CSE</option>
                                <option value="CST">CST</option>
                                <option value="AIML">AIML</option>
                                <option value="CAI">CAI</option>
                                <option value="CSD">CSD</option>
                                <option value="ECE">ECE</option>
                                <option value="ECT">ECT</option>
                                <option value="EEE">EEE</option>
                                <option value="MECH">MECH</option>
                                <option value="CIVIL">CIVIL</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Semester <span style="color:#dc2626">*</span></label>
                            <select name="semester" id="semester" required>
                                <option value="">Select Semester</option>
                                <option value="I">I</option>
                                <option value="II">II</option>
                                <option value="III">III</option>
                                <option value="IV">IV</option>
                                <option value="V">V</option>
                                <option value="VI">VI</option>
                                <option value="VII">VII</option>
                                <option value="VIII">VIII</option>
                            </select>
                        </div>
                    </div>
                    
                    <div style="margin-top:24px; display:flex; gap:12px;">
                        <button class="btn btn-primary" id="submitBtn" type="submit"><i class="fa-solid fa-plus"></i> Add Subject</button>
                        <button class="btn btn-danger" id="cancelBtn" type="button" onclick="cancelEdit()" style="display:none;"><i class="fa-solid fa-xmark"></i> Cancel Edit</button>
                    </div>
                </form>
            </div>

            <!-- List Section -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                <div class="page-title" style="font-size:18px; margin-bottom:0;">Subject List</div>
                <button class="btn" id="importBtn" style="background: #2563eb; color: white; gap: 8px;"><i class="fa-solid fa-upload"></i> Import Subjects</button>
            </div>
            
            <!-- Filters could theoretically go here like in students.php but keeping it simple for now -->
            <div class="filter-card" style="padding:15px; display:flex; gap:15px; flex-wrap:wrap; margin-bottom:15px;">
                 <input type="text" id="searchInput" placeholder="Search subjects..." style="padding:8px; border:1px solid #e2e8f0; border-radius:6px; flex:1; min-width:200px;">
                 <select id="filterDepartment" style="padding:8px; border:1px solid #e2e8f0; border-radius:6px;"><option value="">All Departments</option><option>CSE</option><option>CST</option><option>AIML</option><option>CAI</option><option>CSD</option><option>ECE</option><option>ECT</option><option>EEE</option><option>MECH</option><option>CIVIL</option></select>
                 <select id="filterSemester" style="padding:8px; border:1px solid #e2e8f0; border-radius:6px;"><option value="">All Semesters</option><option value="I">I</option><option value="II">II</option><option value="III">III</option><option value="IV">IV</option><option value="V">V</option><option value="VI">VI</option><option value="VII">VII</option><option value="VIII">VIII</option></select>
            </div>

            <!-- Import Modal -->
            <div id="importModal" class="modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
                <div class="modal-content" style="background:white; padding:32px; border-radius:12px; max-width:500px; width:90%; box-shadow:0 20px 25px rgba(0,0,0,0.15);">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
                        <h2 style="margin:0; font-size:20px; font-weight:700; color:var(--text-dark);">Import Subjects</h2>
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
                            <p style="margin:4px 0; font-family:monospace;">subject, department, semester</p>
                            <p style="margin:8px 0 0 0; font-size:11px; color:#1e3a8a;">Maximum 100 subjects per import</p>
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
                <table>
                    <thead>
                        <tr>
                            <th>Subject Name</th>
                            <th>Department</th>
                            <th>Semester</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($subjects) > 0): ?>
                            <?php foreach ($subjects as $s): ?>
                            <tr>
                                <td style="font-weight:600;"><?= htmlspecialchars($s['subject']) ?></td>
                                <td><span class="status-badge"><?= htmlspecialchars($s['department']) ?></span></td>
                                <td><?= htmlspecialchars($s['semester']) ?></td>
                                <td>
                                    <div style="display:flex; gap:8px;">
                                        <button class="btn btn-edit" style="padding:6px 12px; font-size:12px;" onclick="editSubject('<?= htmlspecialchars($s['subject'], ENT_QUOTES) ?>', '<?= htmlspecialchars($s['department'], ENT_QUOTES) ?>', '<?= htmlspecialchars($s['semester'], ENT_QUOTES) ?>')">
                                            <i class="fa-solid fa-pen"></i> Edit
                                        </button>
                                        <form method="post" style="margin:0;" onsubmit="return confirm('Are you sure you want to delete this subject?');">
                                            <input type="hidden" name="action" value="delete_subject">
                                            <input type="hidden" name="subject" value="<?= htmlspecialchars($s['subject']) ?>">
                                            <input type="hidden" name="department" value="<?= htmlspecialchars($s['department']) ?>">
                                            <input type="hidden" name="semester" value="<?= htmlspecialchars($s['semester']) ?>">
                                            <button type="submit" class="btn btn-danger" style="padding:6px 12px; font-size:12px;">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="empty-state">No subjects added yet</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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

        function editSubject(name, dept, semester) {
            // Populate form
            document.getElementById('old_subject').value = name;
            document.getElementById('old_department').value = dept;
            document.getElementById('old_semester').value = semester;
            document.getElementById('subject_name').value = name;
            document.getElementById('department').value = dept;
            document.getElementById('semester').value = semester;
            
            // Switch UI to edit mode
            document.getElementById('formTitle').textContent = 'Edit Subject';
            document.getElementById('formAction').value = 'edit_subject';
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '<i class="fa-solid fa-check"></i> Update Subject';
            document.getElementById('cancelBtn').style.display = 'inline-flex';
            
            document.querySelector('.form-card').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        function cancelEdit() {
            document.getElementById('subjectForm').reset();
            document.getElementById('old_subject').value = '';
            document.getElementById('old_department').value = '';
            document.getElementById('old_semester').value = '';
            
            document.getElementById('formTitle').textContent = 'Add New Subject';
            document.getElementById('formAction').value = 'add_subject';
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '<i class="fa-solid fa-plus"></i> Add Subject';
            document.getElementById('cancelBtn').style.display = 'none';
        }

        // Filter Logic
        function filterSubjects() {
            const searchValue = document.getElementById('searchInput').value.toLowerCase();
            const deptValue = document.getElementById('filterDepartment').value.toLowerCase();
            const semValue = document.getElementById('filterSemester').value; // Keep case exact for semester I, II etc if values are mixed, but here values are typically consistent.
            
            const table = document.querySelector('table tbody');
            const rows = table.getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                if (row.getElementsByTagName('td').length < 2) continue;
                
                const name = row.cells[0].textContent.toLowerCase();
                const dept = row.cells[1].textContent.toLowerCase();
                const sem = row.cells[2].textContent;
                
                const matchesSearch = !searchValue || name.includes(searchValue);
                const matchesDept = !deptValue || dept === deptValue;
                const matchesSem = !semValue || sem === semValue;
                
                row.style.display = (matchesSearch && matchesDept && matchesSem) ? '' : 'none';
            }
        }
        
        document.getElementById('searchInput').addEventListener('input', filterSubjects);
        document.getElementById('filterDepartment').addEventListener('change', filterSubjects);
        document.getElementById('filterSemester').addEventListener('change', filterSubjects);

        // Import Subjects Functionality
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
                
                const response = await fetch('import_subjects.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.status === 'success') {
                    document.getElementById('progressBar').style.width = '100%';
                    document.getElementById('importStatus').textContent = 'Import completed!';
                    
                    showImportMessage('success', `Successfully imported ${data.imported_count} subjects. ${data.failed_count > 0 ? 'Failed: ' + data.failed_count : ''}`);
                    
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