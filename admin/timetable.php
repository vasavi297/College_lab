<?php
// timetable.php
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

// Fetch employees for dropdown (teachers)
$employees = [];
if ($conn) {
    $emp_result = $conn->query("SELECT username, name FROM employees ORDER BY name");
    if ($emp_result) {
        while ($row = $emp_result->fetch_assoc()) {
            $employees[] = $row;
        }
    }
}

// Fetch subjects for dropdown
$subjects_list = [];
if ($conn) {
    $sub_result = $conn->query("SELECT DISTINCT subject FROM subjects ORDER BY subject");
    if ($sub_result) {
        while ($row = $sub_result->fetch_assoc()) {
            $subjects_list[] = $row['subject'];
        }
    }
}

// Fetch branches, sections, semesters from students table
$branches = [];
$sections = [];
$semesters = [];

if ($conn) {
    // Get distinct branches
    $branch_result = $conn->query("SELECT DISTINCT branch FROM students WHERE branch IS NOT NULL ORDER BY branch");
    if ($branch_result) {
        while ($row = $branch_result->fetch_assoc()) {
            $branches[] = $row['branch'];
        }
    }
    
    // Get distinct sections
    $section_result = $conn->query("SELECT DISTINCT section FROM students WHERE section IS NOT NULL ORDER BY section");
    if ($section_result) {
        while ($row = $section_result->fetch_assoc()) {
            $sections[] = $row['section'];
        }
    }
    
    // Get distinct semesters
    $semester_result = $conn->query("SELECT DISTINCT semester FROM students WHERE semester IS NOT NULL ORDER BY semester");
    if ($semester_result) {
        while ($row = $semester_result->fetch_assoc()) {
            $semesters[] = $row['semester'];
        }
    }
}

// Handle Delete Timetable
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_timetable') {
    $id = (int)$_POST['id'];
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM timetable WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'><i class='fa-solid fa-check'></i> Entry deleted successfully!</div>";
            } else {
                $message = "<div class='alert alert-error'>Error deleting entry: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }
    }
}

// Handle Add/Edit Timetable
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_timetable' || $_POST['action'] === 'edit_timetable') {
        $day = trim($_POST['day']);
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
        $subject = trim($_POST['subject']);
        $employee_username = $_POST['employee_username'];
        $branch = $_POST['branch'];
        $section = $_POST['section'];
        $semester = $_POST['semester'];
        
        if ($day && $start_time && $end_time && $subject && $employee_username && $branch && $section && $semester) {
            if ($_POST['action'] === 'add_timetable') {
                $stmt = $conn->prepare("INSERT INTO timetable (day_of_week, start_time, end_time, subject, employee_username, branch, section, semester) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssssss", $day, $start_time, $end_time, $subject, $employee_username, $branch, $section, $semester);
            } else {
                $id = (int)$_POST['id'];
                $stmt = $conn->prepare("UPDATE timetable SET day_of_week = ?, start_time = ?, end_time = ?, subject = ?, employee_username = ?, branch = ?, section = ?, semester = ? WHERE id = ?");
                $stmt->bind_param("ssssssssi", $day, $start_time, $end_time, $subject, $employee_username, $branch, $section, $semester, $id);
            }
            
            if ($stmt && $stmt->execute()) {
                $message = "<div class='alert alert-success'><i class='fa-solid fa-check'></i> Entry saved successfully!</div>";
            } else {
                $message = "<div class='alert alert-error'>Error saving entry: " . ($stmt ? $stmt->error : $conn->error) . "</div>";
            }
            if ($stmt) $stmt->close();
        } else {
            $message = "<div class='alert alert-error'>Please fill all required fields correctly.</div>";
        }
    }
}

// Fetch Timetable with teacher names
$entries = [];
if ($conn) {
    $result = $conn->query("
        SELECT t.*, e.name as employee_name 
        FROM timetable t 
        LEFT JOIN employees e ON t.employee_username = e.username 
        ORDER BY FIELD(day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'), start_time
    ");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $entries[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Timetable | Admin</title>
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
        .form-card { padding: 20px; }
        .page-title { font-size: 20px; }
        
        .header-branding h1 { font-size: 14px; }
        .header-branding p { font-size: 9px; }
    }

    @media (max-width: 600px) {
        .header-branding { display: flex; flex-direction: column; }
        .header-branding h1 { font-size: 12px; line-height: 1.2; }
        .header-right .info-text { display: none; }
        
        .dashboard-container { padding: 15px; }
        .form-card { padding: 15px; }
        
        .header-logout-btn span { display: none; }
        .header-logout-btn { padding: 8px; }
        
        .form-grid { grid-template-columns: 1fr; }
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
            <a href="subjects.php" class="menu-item"> Subjects</a>
            <a href="timetable.php" class="menu-item active"> Timetable</a>
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
            <div class="page-title">Academic Timetable</div>
            
            <?= $message ?>

            <div class="form-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid #f1f5f9; padding-bottom:15px;">
                    <h3 id="formTitle" style="margin:0; color:var(--primary-color);">Add Timetable Entry</h3>
                </div>

                <form method="post" action="timetable.php" id="timetableForm">
                    <input type="hidden" name="action" id="formAction" value="add_timetable">
                    <input type="hidden" name="id" id="edit_id">
                  
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Day <span style="color:#dc2626">*</span></label>
                            <select name="day" id="day" required>
                                <option value="Monday">Monday</option>
                                <option value="Tuesday">Tuesday</option>
                                <option value="Wednesday">Wednesday</option>
                                <option value="Thursday">Thursday</option>
                                <option value="Friday">Friday</option>
                                <option value="Saturday">Saturday</option>
                            </select>
                        </div>
                    
                        <div class="form-group">
                            <label>Start Time <span style="color:#dc2626">*</span></label>
                            <input type="time" name="start_time" id="start_time" required>
                        </div>
                    
                        <div class="form-group">
                            <label>End Time <span style="color:#dc2626">*</span></label>
                            <input type="time" name="end_time" id="end_time" required>
                        </div>
                    
                        <div class="form-group">
                            <label>Subject <span style="color:#dc2626">*</span></label>
                            <input type="text" name="subject" id="subject" list="subjects_datalist" placeholder="Select or type subject" required>
                            <datalist id="subjects_datalist">
                                <?php foreach ($subjects_list as $s): ?>
                                <option value="<?= htmlspecialchars($s) ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                    
                        <div class="form-group">
                            <label>Teacher (Employee) <span style="color:#dc2626">*</span></label>
                            <select name="employee_username" id="employee_username" required>
                                <option value="">Select Teacher</option>
                                <?php foreach ($employees as $e): ?>
                                <option value="<?= $e['username'] ?>"><?= htmlspecialchars($e['name']) ?> (<?= htmlspecialchars($e['username']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    
                        <div class="form-group">
                            <label>Branch <span style="color:#dc2626">*</span></label>
                            <select name="branch" id="branch" required>
                                <option value="">Select Branch</option>
                                <?php foreach ($branches as $b): ?>
                                <option value="<?= htmlspecialchars($b) ?>"><?= htmlspecialchars($b) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    
                        <div class="form-group">
                            <label>Section <span style="color:#dc2626">*</span></label>
                            <select name="section" id="section" required>
                                <option value="">Select Section</option>
                                <?php foreach ($sections as $sec): ?>
                                <option value="<?= htmlspecialchars($sec) ?>"><?= htmlspecialchars($sec) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    
                        <div class="form-group">
                            <label>Semester <span style="color:#dc2626">*</span></label>
                            <select name="semester" id="semester" required>
                                <option value="">Select Semester</option>
                                <?php foreach ($semesters as $sem): ?>
                                <option value="<?= htmlspecialchars($sem) ?>"><?= htmlspecialchars($sem) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                  
                    <div style="margin-top:24px; display:flex; gap:12px;">
                        <button class="btn btn-primary" type="submit" id="submitBtn"><i class="fa-solid fa-plus"></i> Add Entry</button>
                        <button class="btn btn-secondary" type="button" id="cancelBtn" onclick="cancelEdit()" style="display:none;"><i class="fa-solid fa-xmark"></i> Cancel</button>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <div style="padding: 16px 24px; border-bottom: 1px solid #f1f5f9;">
                    <h3 style="margin:0;">Weekly Schedule</h3>
                </div>
                <table>
                  <thead>
                    <tr>
                      <th>Day</th>
                      <th>Time</th>
                      <th>Subject</th>
                      <th>Teacher</th>
                      <th>Branch/Sec/Sem</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (count($entries) > 0): ?>
                      <?php foreach ($entries as $e): ?>
                        <tr>
                          <td style="font-weight:600;"><?= htmlspecialchars($e['day_of_week']) ?></td>
                          <td><?= date('h:i A', strtotime($e['start_time'])) ?> - <?= date('h:i A', strtotime($e['end_time'])) ?></td>
                          <td><?= htmlspecialchars($e['subject']) ?></td>
                          <td><?= htmlspecialchars($e['employee_name'] ?? 'N/A') ?></td>
                          <td><?= htmlspecialchars($e['branch']) ?>/<?= htmlspecialchars($e['section']) ?>/<?= htmlspecialchars($e['semester']) ?></td>
                          <td>
                            <div style="display:inline-flex; gap:8px;">
                              <button class="btn btn-edit" style="padding:6px 12px; font-size:12px;" 
                                      onclick="editTimetable(
                                        <?= $e['id'] ?>, 
                                        '<?= $e['day_of_week'] ?>', 
                                        '<?= substr($e['start_time'],0,5) ?>', 
                                        '<?= substr($e['end_time'],0,5) ?>', 
                                        '<?= htmlspecialchars($e['subject'], ENT_QUOTES) ?>', 
                                        '<?= htmlspecialchars($e['employee_username'], ENT_QUOTES) ?>',
                                        '<?= htmlspecialchars($e['branch'], ENT_QUOTES) ?>',
                                        '<?= htmlspecialchars($e['section'], ENT_QUOTES) ?>',
                                        '<?= htmlspecialchars($e['semester'], ENT_QUOTES) ?>'
                                      )">Edit</button>
                              
                              <form method="post" style="display:inline; margin:0;" onsubmit="return confirm('Delete this entry?');">
                                <input type="hidden" name="action" value="delete_timetable">
                                <input type="hidden" name="id" value="<?= $e['id'] ?>">
                                <button type="submit" class="btn btn-danger" style="padding:6px 12px; font-size:12px;">Delete</button>
                              </form>
                            </div>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr><td colspan="6" class="empty-state">No schedule entries found.</td></tr>
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

        function editTimetable(id, day, start, end, subject, emp_username, branch, section, semester) {
            // Update UI to edit mode
            document.getElementById('formTitle').textContent = 'Edit Timetable Entry';
            document.getElementById('formAction').value = 'edit_timetable';
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '<i class="fa-solid fa-check"></i> Update Entry';
            document.getElementById('cancelBtn').style.display = 'inline-flex';
            
            // Fill form
            document.getElementById('edit_id').value = id;
            document.getElementById('day').value = day;
            document.getElementById('start_time').value = start;
            document.getElementById('end_time').value = end;
            document.getElementById('subject').value = subject;
            document.getElementById('employee_username').value = emp_username;
            document.getElementById('branch').value = branch;
            document.getElementById('section').value = section;
            document.getElementById('semester').value = semester;
            
            document.getElementById('timetableForm').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        function cancelEdit() {
            // Reset UI to add mode
            document.getElementById('formTitle').textContent = 'Add Timetable Entry';
            document.getElementById('formAction').value = 'add_timetable';
            document.getElementById('submitBtn').innerHTML = '<i class="fa-solid fa-plus"></i> Add Entry';
            document.getElementById('cancelBtn').style.display = 'none';
            
            document.getElementById('timetableForm').reset();
        }
    </script>
</body>
</html>