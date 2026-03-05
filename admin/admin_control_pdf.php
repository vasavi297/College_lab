<?php
session_start();
require_once '../db_connect.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    // header('Location: index.php');
    // exit;
}

$display_name = 'Admin';
$username = htmlspecialchars($_SESSION['username'] ?? 'admin', ENT_QUOTES);
$feedback = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['enable_downloads'])) {
        if ($conn->query("UPDATE students SET can_download = 1")) {
            $feedback = "<div class='alert alert-success'><i class='fa-solid fa-check'></i> Enabled PDF downloads for every student.</div>";
        } else {
            $feedback = "<div class='alert alert-error'><i class='fa-solid fa-triangle-exclamation'></i> Unable to enable downloads right now. " . htmlspecialchars($conn->error, ENT_QUOTES) . "</div>";
        }
    } elseif (isset($_POST['disable_downloads'])) {
        if ($conn->query("UPDATE students SET can_download = 0")) {
            $feedback = "<div class='alert alert-warning'><i class='fa-solid fa-circle-exclamation'></i> Disabled PDF downloads for every student.</div>";
        } else {
            $feedback = "<div class='alert alert-error'><i class='fa-solid fa-triangle-exclamation'></i> Unable to disable downloads right now. " . htmlspecialchars($conn->error, ENT_QUOTES) . "</div>";
        }
    }
}

$stats = [
    'enabled_count' => 0,
    'total_students' => 0,
    'stored_reports' => 0
];

$stats_query = $conn->query("
    SELECT 
        (SELECT COUNT(*) FROM students WHERE can_download = 1) AS enabled_count,
        (SELECT COUNT(*) FROM students) AS total_students,
        (SELECT COUNT(*) FROM student_reports) AS stored_reports
");

if ($stats_query) {
    $stats = array_merge($stats, $stats_query->fetch_assoc() ?: []);
}

$enabled_count = (int)($stats['enabled_count'] ?? 0);
$total_students = (int)($stats['total_students'] ?? 0);
$stored_reports = (int)($stats['stored_reports'] ?? 0);
$disabled_count = max($total_students - $enabled_count, 0);
$coverage_percent = $total_students > 0 ? (int)round(($enabled_count / $total_students) * 100) : 0;
$coverage_percent = max(0, min(100, $coverage_percent));

if ($enabled_count === 0) {
    $status_class = 'danger';
    $status_label = 'Downloads Disabled';
} elseif ($enabled_count === $total_students) {
    $status_class = 'success';
    $status_label = 'Fully Enabled';
} else {
    $status_class = 'warning';
    $status_label = 'Partially Enabled';
}

$enable_button_class = $enabled_count === 0 ? 'btn-state-active' : 'btn-state-muted';
$disable_button_class = $enabled_count > 0 ? 'btn-state-active' : 'btn-state-muted';

$snapshot_time = date('M d, Y h:i A');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Downloads | Admin</title>
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

    .toggle-btn { font-size: 20px; cursor: pointer; padding: 8px; border-radius: 8px; border: none; background: transparent; margin-right: 15px; color: var(--text-dark); }
    .header-branding h1 { font-size: 18px; font-weight: 800; color: var(--primary-color); }

    .dashboard-container { padding: 32px; max-width: 1200px; margin: 0 auto; }
    .page-title { font-size: 24px; font-weight: 800; color: var(--text-dark); margin-bottom: 16px; }

    .alert { padding: 16px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; display: flex; align-items: center; gap: 10px; border: 1px solid transparent; }
    .alert-success { background: #f0fdf4; color: #15803d; border-color: #dcfce7; }
    .alert-error { background: #fef2f2; color: #b91c1c; border-color: #fee2e2; }
    .alert-warning { background: #fffbeb; color: #92400e; border-color: #fef3c7; }

    .stats-matrix { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 32px; }
    .stat-box { background: var(--white); padding: 24px; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid #f1f5f9; position: relative; overflow: hidden; }
    .stat-box .stat-count { font-size: 36px; font-weight: 800; color: var(--text-dark); letter-spacing: -1px; }
    .stat-box .stat-label { font-size: 13px; color: var(--text-gray); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
    .stat-box::after { content: ''; position: absolute; bottom: 0; left: 0; width: 100%; height: 4px; }
    .stat-box.total::after { background: #3b82f6; }
    .stat-box.enabled::after { background: #16a34a; }
    .stat-box.storage::after { background: #7c3aed; }

    .control-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px; }
    .control-card { background: var(--white); padding: 28px; border-radius: var(--radius-lg); box-shadow: var(--shadow-card); border: 1px solid #f1f5f9; display: flex; flex-direction: column; gap: 18px; }
    .control-card h3 { font-size: 18px; font-weight: 700; color: var(--text-dark); }
    .control-card p { color: var(--text-gray); line-height: 1.5; }

    .control-actions { display: flex; flex-wrap: wrap; gap: 12px; }
    .btn { padding: 12px 20px; border-radius: 10px; font-weight: 600; cursor: pointer; transition: 0.2s; border: none; font-size: 14px; display: inline-flex; align-items: center; gap: 8px; }
    .btn-success { background: #16a34a; color: #fff; }
    .btn-success:hover { background: #15803d; }
    .btn-danger { background: #dc2626; color: #fff; }
    .btn-danger:hover { background: #b91c1c; }
    .btn-success.btn-state-active { background: #0f9d58; box-shadow: 0 10px 18px rgba(15, 157, 88, 0.35); }
    .btn-success.btn-state-active:hover { background: #0c7a43; }
    .btn-success.btn-state-muted { background: #bbf7d0; color: #166534; box-shadow: none; }
    .btn-success.btn-state-muted:hover { background: #a7f3c5; }
    .btn-danger.btn-state-active { background: #b91c1c; box-shadow: 0 10px 18px rgba(185, 28, 28, 0.35); }
    .btn-danger.btn-state-active:hover { background: #991b1b; }
    .btn-danger.btn-state-muted { background: #fecaca; color: #7f1d1d; box-shadow: none; }
    .btn-danger.btn-state-muted:hover { background: #fca5a5; color: #7f1d1d; }
    .btn-outline { background: transparent; color: var(--accent-color); border: 1px solid rgba(37, 99, 235, 0.3); }
    .btn-outline:hover { background: var(--primary-light); }

    .status-row { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; }
    .status-chip { padding: 8px 14px; border-radius: 999px; font-size: 12px; font-weight: 700; text-transform: uppercase; display: inline-flex; gap: 8px; align-items: center; }
    .status-chip.success { background: #ecfccb; color: #3f6212; }
    .status-chip.warning { background: #fef9c3; color: #92400e; }
    .status-chip.danger { background: #fee2e2; color: #991b1b; }

    .progress-track { width: 100%; height: 10px; border-radius: 999px; background: #e2e8f0; overflow: hidden; }
    .progress-fill { height: 100%; border-radius: 999px; background: linear-gradient(90deg, #3b82f6, #2563eb); transition: width 0.4s ease; }

    .download-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 16px; }
    .download-stat { padding: 16px; border-radius: 12px; background: #f8fafc; border: 1px solid #e2e8f0; }
    .download-stat .label { font-size: 11px; font-weight: 700; color: var(--text-gray); text-transform: uppercase; letter-spacing: 0.6px; }
    .download-stat .value { font-size: 20px; font-weight: 800; color: var(--text-dark); }

    .meta { font-size: 12px; color: var(--text-gray); }

    .overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.4); backdrop-filter: blur(2px); z-index: 95; opacity: 0; visibility: hidden; transition: 0.3s; }
    .overlay.active { opacity: 1; visibility: visible; }

    @media (max-width: 992px) {
        .sidebar { transform: translateX(-100%); width: 280px; }
        .sidebar.active { transform: translateX(0); }
        .main-content { margin-left: 0; width: 100%; }
    }

    @media (max-width: 768px) {
        .top-header { padding: 0 20px; }
        .dashboard-container { padding: 20px; }
        .page-title { font-size: 20px; }
    }

    @media (max-width: 600px) {
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
            <img src="https://ui-avatars.com/api/?name=Admin&background=1e3a8a&color=fff&size=128" alt="Admin Avatar">
            <div style="font-weight:700; color:var(--text-dark);">Administrator</div>
            <div style="font-size:12px; color:var(--text-gray);"><?= $username ?></div>
        </div>
        <div class="sidebar-menu">
            <a href="admin_dashboard.php" class="menu-item"> Dashboard</a>
            <a href="students.php" class="menu-item"> Students</a>
            <a href="employees.php" class="menu-item"> Employees</a>
            <a href="subjects.php" class="menu-item"> Subjects</a>
            <a href="timetable.php" class="menu-item"> Timetable</a>
            <a href="reports.php" class="menu-item"> Reports</a>
                    <a href="admin_control_pdf.php" class="menu-item active"> Downloads</a>
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
                <img src="https://ui-avatars.com/api/?name=Admin&background=1e3a8a&color=fff" style="width:42px; height:42px; border-radius:50%; border:2px solid #e2e8f0;" alt="Admin Avatar">
                <a href="logout.php" class="header-logout-btn" style="display: flex; align-items: center; gap: 6px; padding: 8px 14px; background: #fef2f2; color: var(--secondary-color); border: 1px solid #fecaca; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none; transition: all 0.2s;"><i class="fa-solid fa-arrow-right-from-bracket"></i> <span>LOG OUT</span></a>
            </div>
        </header>

        <div class="dashboard-container">
            <div class="page-title">Downloads</div>

            <?php if ($feedback) { echo $feedback; } ?>

            <div class="stats-matrix">
                <div class="stat-box total">
                    <div class="stat-count"><?= number_format($total_students) ?></div>
                    <div class="stat-label">Total Students</div>
                </div>
                <div class="stat-box enabled">
                    <div class="stat-count"><?= number_format($enabled_count) ?></div>
                    <div class="stat-label">Can Download</div>
                </div>
                <div class="stat-box storage">
                    <div class="stat-count"><?= number_format($stored_reports) ?></div>
                    <div class="stat-label">Stored Reports</div>
                </div>
            </div>

            <div class="control-grid">
                <div class="control-card">
                    <div>
                        <h3>Manage Download Permissions</h3><br>
                        <p>Toggle access to generated PDFs for every student account. Changes apply instantly across the student portal.</p>
                    </div>
                    <form method="POST" class="control-actions">
                        <button type="submit" name="enable_downloads" class="btn btn-success <?= $enable_button_class ?>"><i class="fa-solid fa-toggle-on"></i> Enable Downloads</button>
                        <button type="submit" name="disable_downloads" class="btn btn-danger <?= $disable_button_class ?>"><i class="fa-solid fa-ban"></i> Disable Downloads</button>
                    </form>
                    <div class="meta"><i class="fa-regular fa-clock"></i> Last sync <?= $snapshot_time ?></div>
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
    </script>
</body>
</html>