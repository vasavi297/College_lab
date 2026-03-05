<?php
// admin_dashboard.php - ENHANCED VERSION
session_start();
require_once '../db_connect.php';

// SESSION CHECK
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    // header('Location: index.php');
    // exit;
}

$display_name = 'Admin';
$username = htmlspecialchars($_SESSION['username'] ?? 'admin', ENT_QUOTES);

// FETCH REAL DATA FROM DATABASE
$total_students = 0;
$total_employees = 0;
$total_subjects = 0;
$classes_today = 0;

if ($conn) {
    // Get total students
    $res = $conn->query("SELECT COUNT(*) as count FROM students");
    if ($res) { $total_students = $res->fetch_assoc()['count']; }

    // Get total employees
    $res = $conn->query("SELECT COUNT(*) as count FROM employees");
    if ($res) { $total_employees = $res->fetch_assoc()['count']; }

    // Get total subjects
    $res = $conn->query("SELECT COUNT(*) as count FROM subjects");
    if ($res) { $total_subjects = $res->fetch_assoc()['count']; }

    // Get today's classes
    $today = date('l'); 
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM timetable WHERE day_of_week = ?");
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res) { $classes_today = $res->fetch_assoc()['count']; }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Dashboard | Admin</title>
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
    
    /* FIX SCREEN / NO SCROLL ON BODY */
    html, body {
        height: 100%;
        width: 100%;
        overflow: hidden; /* Prevents entire page scrolling */
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
        height: 100vh; /* Fixed height matching screen */
        overflow-y: auto; /* Internal scroll only */
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
    
    /* ================= DASHBOARD LAYOUT ================= */
    .dashboard-container { padding: 32px; max-width: 1800px; margin: 0 auto; }
    
    .dashboard-grid { 
        display: grid; 
        grid-template-columns: 1fr 1fr; 
        gap: 28px; 
        align-items: stretch; 
    }

    /* LEFT COLUMN */
    .left-column { display: flex; flex-direction: column; gap: 24px; }

    /* Stats Matrix */
    .stats-matrix { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .stat-box { background: var(--white); padding: 24px; border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid #f1f5f9; display: flex; flex-direction: column; justify-content: space-between; height: 140px; position: relative; overflow: hidden; transition: transform 0.2s; }
    .stat-box:hover { transform: translateY(-4px); box-shadow: var(--shadow-hover); }
    .stat-box::after { content: ''; position: absolute; bottom: 0; left: 0; width: 100%; height: 4px; }
    .stat-box.total::after { background: #3b82f6; }
    .stat-box.verified::after { background: #10b981; }
    .stat-box.pending::after { background: #f59e0b; }
    .stat-box.incomplete::after { background: #ef4444; }
    .stat-count { font-size: 36px; font-weight: 800; color: var(--text-dark); letter-spacing: -1px; }
    .stat-label { font-size: 13px; color: var(--text-gray); font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; margin-top: auto; }
    .stat-icon-bg { position: absolute; top: 15px; right: 15px; width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; opacity: 0.9; }
    .stat-box.total .stat-icon-bg { background: #eff6ff; color: #3b82f6; }
    .stat-box.verified .stat-icon-bg { background: #d1fae5; color: #10b981; }
    .stat-box.pending .stat-icon-bg { background: #fffbeb; color: #f59e0b; }
    .stat-box.incomplete .stat-icon-bg { background: #fef2f2; color: #ef4444; }

    /* STATUS SECTION */
    .section-heading {
        font-size: 16px; font-weight: 700; color: var(--text-dark); margin-bottom: 12px; margin-top: 10px;
    }

    .status-panel {
        background: var(--white);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-card);
        border: 1px solid #f1f5f9;
        display: flex; flex-direction: column;
        overflow: hidden;
    }

    .status-item {
        display: flex; align-items: center;
        padding: 20px 24px;
        border-bottom: 1px solid #f1f5f9;
        transition: 0.2s;
    }
    .status-item:last-child { border-bottom: none; }
    .status-item:hover { background: #f8fafc; }

    .status-left { display: flex; align-items: center; gap: 15px; width: 100%; }
    
    .status-icon-box {
        width: 40px; height: 40px; border-radius: 10px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: 16px;
    }
    
    .status-text {
        font-size: 14px;
        color: var(--text-gray);
        font-weight: 600;
        display: flex;
        align-items: center;
        width: 100%;
    }
    
    .status-value {
        margin-left: 6px;
        font-weight: 700;
        color: var(--text-dark);
        font-size: 14px;
    }

    .st-online .status-icon-box { background: #d1fae5; color: #10b981; }
    .st-login .status-icon-box { background: #eff6ff; color: #3b82f6; }
    
    .status-active-badge {
        display: inline-flex; align-items: center; gap: 6px;
        color: #10b981; font-weight: 700; margin-left: 6px;
    }
    .blink-dot { width: 8px; height: 8px; background: #10b981; border-radius: 50%; box-shadow: 0 0 0 2px #d1fae5; animation: blink 1.5s infinite; }
    @keyframes blink { 50% { opacity: 0.5; } }

    /* RIGHT COLUMN */
    .right-column { background: var(--white); border-radius: var(--radius-lg); box-shadow: var(--shadow-card); border: 1px solid #f1f5f9; overflow: hidden; height: auto; min-height: 380px; }
    .table-header { padding: 20px 24px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; }
    .table-title { font-size: 16px; font-weight: 800; color: var(--text-dark); }
    .btn-link { font-size: 12px; font-weight: 600; color: var(--accent-color); display: flex; align-items: center; gap: 6px; }
    
    .table-responsive { width: 100%; overflow-x: auto; }
    table { width: 100%; border-collapse: separate; border-spacing: 0; }
    th { text-align: left; padding: 14px 24px; background: #f8fafc; color: var(--text-gray); font-size: 11px; font-weight: 700; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; }
    td { padding: 14px 24px; border-bottom: 1px solid #f1f5f9; font-size: 13px; color: var(--text-dark); vertical-align: middle; }
    tr:hover td { background: #f8fafc; }
    
    .announcements-wide {
        margin-top: 28px;
        background: var(--white);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-card);
        border: 1px solid #f1f5f9;
        overflow: hidden;
    }
    .announcements-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .announcement-card {
        background: var(--white);
        padding: 20px 24px;
        display: flex;
        gap: 16px;
        transition: 0.2s;
        border-radius: var(--radius-md);
        border: 1px solid #f1f5f9;
    }
    .announcement-card:hover { background: #f8fafc; }
    
    .user-avatar-sm { width: 32px; height: 32px; border-radius: 50%; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px; margin-right: 10px;}
    .status-badge { padding: 6px 12px; border-radius: 30px; font-size: 11px; font-weight: 700; text-transform: uppercase; }

    .overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.4); backdrop-filter: blur(2px); z-index: 95; opacity: 0; visibility: hidden; transition: 0.3s; }

    @media (max-width: 1200px) {
        .dashboard-grid { grid-template-columns: 1fr; gap: 30px; }
        .left-column { flex-direction: row; flex-wrap: wrap; }
        .stats-matrix { flex: 1; min-width: 300px; }
        .status-panel { flex: 1; min-width: 300px; }
    }
    @media (max-width: 992px) {
        .sidebar { transform: translateX(-100%); width: 280px; }
        .sidebar.active { transform: translateX(0); }
        .main-content { margin-left: 0; width: 100%; }
        .overlay.active { opacity: 1; visibility: visible; }
    }

    @media (max-width: 768px) {
        .top-header { padding: 0 20px; }
        .dashboard-container { padding: 20px; }
        .stat-box { padding: 15px; height: 120px; }
        .stat-count { font-size: 28px; }
        .page-title { font-size: 20px; }
        
        .header-branding h1 { font-size: 14px; }
        .header-branding p { font-size: 9px; }
    }

    @media (max-width: 600px) {
        .header-branding { display: flex; flex-direction: column; }
        .header-branding h1 { font-size: 12px; line-height: 1.2; }
        .header-right .info-text { display: none; }
        
        .dashboard-container { padding: 15px; }
        .stat-box { height: 100px; padding: 12px; }
        .stat-count { font-size: 24px; }
        
        .header-logout-btn span { display: none; }
        .header-logout-btn { padding: 8px; }
        
        .stats-matrix { grid-template-columns: 1fr; }
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
            <a href="admin_dashboard.php" class="menu-item active"> Dashboard</a>
            <a href="students.php" class="menu-item"> Students</a>
            <a href="employees.php" class="menu-item"> Employees</a>
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
                <!-- Profile Image -->
                <img src="https://ui-avatars.com/api/?name=Admin&background=1e3a8a&color=fff" style="width:42px; height:42px; border-radius:50%; border:2px solid #e2e8f0;">
                <a href="logout.php" class="header-logout-btn" style="display: flex; align-items: center; gap: 6px; padding: 8px 14px; background: #fef2f2; color: var(--secondary-color); border: 1px solid #fecaca; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none; transition: all 0.2s;"><i class="fa-solid fa-arrow-right-from-bracket"></i> <span>LOG OUT</span></a>
            </div>
        </header>

        <div class="dashboard-container">
            <div class="dashboard-grid">
                <!-- LEFT COLUMN -->
                <div class="left-column">
                    <!-- Stats -->
                    <div class="stats-matrix">
                        <div class="stat-box total">
                            <div class="stat-icon-bg"><i class="fa-solid fa-user-graduate"></i></div>
                            <div class="stat-count"><?php echo $total_students; ?></div>
                            <div class="stat-label">Total Students</div>
                        </div>
                        <div class="stat-box verified">
                            <div class="stat-icon-bg"><i class="fa-solid fa-chalkboard-user"></i></div>
                            <div class="stat-count"><?php echo $total_employees; ?></div>
                            <div class="stat-label">Total Employees</div>
                        </div>
                        <div class="stat-box pending">
                            <div class="stat-icon-bg"><i class="fa-solid fa-book"></i></div>
                            <div class="stat-count"><?php echo $total_subjects; ?></div>
                            <div class="stat-label">Total Subjects</div>
                        </div>
                        <div class="stat-box incomplete">
                            <div class="stat-icon-bg"><i class="fa-regular fa-clock"></i></div>
                            <div class="stat-count"><?php echo $classes_today; ?></div>
                            <div class="stat-label">Classes Today</div>
                        </div>
                    </div>

                    <!-- STATUS SECTION -->
                    <div class="section-heading">System Status</div>
                    <div class="status-panel">
                        <div class="status-item st-online">
                            <div class="status-left">
                                <div class="status-icon-box"><i class="fa-solid fa-server"></i></div>
                                <div class="status-text">
                                    System Status: 
                                    <div class="status-active-badge">
                                        <div class="blink-dot"></div> Active
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="status-item st-login">
                            <div class="status-left">
                                <div class="status-icon-box"><i class="fa-regular fa-calendar"></i></div>
                                <div class="status-text">
                                    Date: <span class="status-value"><?php echo date('l, F j'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- RIGHT COLUMN -->
                <div class="right-column">
                    <div class="table-header">
                        <div class="table-title">Recent Students</div>
                        <a href="students.php" class="btn-link">View All <i class="fa-solid fa-arrow-right"></i></a>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Branch</th>
                                    <th>Section</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql_rec = "SELECT * FROM students ORDER BY student_id DESC LIMIT 7";
                                $res_rec = $conn->query($sql_rec);
                                if ($res_rec && $res_rec->num_rows > 0):
                                    while ($row = $res_rec->fetch_assoc()):
                                ?>
                                <tr>
                                    <td style="font-family:monospace; color:var(--text-gray);"><?= htmlspecialchars($row['student_id']) ?></td>
                                    <td>
                                        <div style="display:flex; align-items:center;">
                                            <div class="user-avatar-sm"><?= strtoupper(substr($row['name'], 0, 1)) ?></div>
                                            <span style="font-weight:600;"><?= htmlspecialchars($row['name']) ?></span>
                                        </div>
                                    </td>
                                    <td><span class="status-badge" style="background:#f1f5f9; color:#475569;"><?= htmlspecialchars($row['branch']) ?></span></td>
                                    <td><?= htmlspecialchars($row['section']) ?></td>
                                </tr>
                                <?php endwhile; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div> <!-- End Dashboard Grid -->

            <!-- FULL WIDTH ANNOUNCEMENTS -->
            <div class="section-heading" style="margin-top:32px;">Recent Announcements</div>
            <div class="announcements-list">
                     <?php
                     $sql_ann = "SELECT * FROM announcements ORDER BY created_at DESC LIMIT 4";
                     $res_ann = $conn->query($sql_ann);
                     if ($res_ann && $res_ann->num_rows > 0):
                        while ($ann = $res_ann->fetch_assoc()):
                     ?>
                     <div class="announcement-card">
                         <div class="status-icon-box" style="background:#eff6ff; color:#3b82f6;"><i class="fa-solid fa-bullhorn"></i></div>
                         <div style="flex: 1;">
                             <div style="color:var(--text-dark); font-weight:700; font-size:14px; margin-bottom:4px;"><?= htmlspecialchars($ann['title']) ?></div>
                             <div style="font-size:12px; color:var(--text-gray); font-weight:500;"><?= date('M d, Y • g:i A', strtotime($ann['created_at'])) ?></div>
                         </div>
                     </div>
                     <?php endwhile; 
                     else: ?>
                     <div class="announcement-card" style="grid-column: 1 / -1; justify-content: center; color: var(--text-gray);">
                         No recent announcements
                     </div>
                     <?php endif; ?>
                </div>
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
        
        // Auto refresh
        setTimeout(function() {
            window.location.reload();
        }, 300000);
    </script>
</body>
</html>