<?php
session_start();
include '../db_connect.php';

// SESSION CHECK
if (!isset($_SESSION['employee_id'])) {
    header('Location: ../index.php');
    exit;
}

$employee_id = $_SESSION['employee_id'];
$employee_name = $_SESSION['employee_name'];
$employee_username = $_SESSION['username'] ?? '';

// --- DATA FETCHING ---
$current_week_start = date('Y-m-d', strtotime('monday this week'));
$current_week_end = date('Y-m-d', strtotime('sunday this week'));

// 1. Total Students
$total_students_sql = "SELECT COUNT(DISTINCT student_id) as count FROM submissions WHERE employee_id = ?";
$stmt = $conn->prepare($total_students_sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$total_students = $stmt->get_result()->fetch_assoc()['count'];

// 2. Completed This Week
$completed_week_sql = "SELECT COUNT(*) as count FROM submissions 
                       WHERE employee_id = ? 
                       AND verification_status = 'Verified'
                       AND DATE(verification_date) BETWEEN ? AND ?";
$stmt = $conn->prepare($completed_week_sql);
$stmt->bind_param("iss", $employee_id, $current_week_start, $current_week_end);
$stmt->execute();
$completed_this_week = $stmt->get_result()->fetch_assoc()['count'];

// 3. Pending
$pending_sql = "SELECT COUNT(*) as count FROM submissions 
                WHERE employee_id = ? AND verification_status = 'Pending'";
$stmt = $conn->prepare($pending_sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$pending_count = $stmt->get_result()->fetch_assoc()['count'];

// 4. Not Completed
$not_completed = $total_students - ($completed_this_week + $pending_count);
if ($not_completed < 0) $not_completed = 0;

// 5. Total Labs Dealing (from employee_subjects table)
$labs_sql = "SELECT COUNT(DISTINCT subject) as count FROM employee_subjects WHERE employee_username = ?";
$stmt = $conn->prepare($labs_sql);
$stmt->bind_param("s", $employee_username);
$stmt->execute();
$total_labs = $stmt->get_result()->fetch_assoc()['count'];

// 6. Total Subjects
$total_subjects = 1; 

// 7. Announcements (shared with students)
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

// 7. Recent Submissions
$recent_sql = "SELECT s.*, stu.name as student_name, stu.roll_number, e.experiment_number
               FROM submissions s 
               JOIN students stu ON s.student_id = stu.student_id 
               JOIN experiments e ON s.experiment_id = e.id
               WHERE s.employee_id = ? 
               ORDER BY s.submitted_date DESC 
               LIMIT 7"; 
$stmt = $conn->prepare($recent_sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$recent_submissions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Faculty Dashboard | SVEC</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
    
    /* ================= DASHBOARD LAYOUT (50-50 SPLIT) ================= */
    .dashboard-container { padding: 32px; max-width: 1800px; margin: 0 auto; }
    
    .dashboard-grid { 
        display: grid; 
        grid-template-columns: 1fr 1fr; 
        gap: 28px; 
        align-items: start; 
    }

    /* LEFT COLUMN (50%) */
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
        font-size: 16px; font-weight: 700; color: var(--text-dark); margin-bottom: -10px; margin-top: 10px;
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
    .st-labs .status-icon-box { background: #fef3c7; color: #d97706; }
    .st-subj .status-icon-box { background: #f3e8ff; color: #9333ea; }
    
    .status-active-badge {
        display: inline-flex; align-items: center; gap: 6px;
        color: #10b981; font-weight: 700; margin-left: 6px;
    }
    .blink-dot { width: 8px; height: 8px; background: #10b981; border-radius: 50%; box-shadow: 0 0 0 2px #d1fae5; animation: blink 1.5s infinite; }
    @keyframes blink { 50% { opacity: 0.5; } }

    /* RIGHT COLUMN (50%) */
    .right-column { background: var(--white); border-radius: var(--radius-lg); box-shadow: var(--shadow-card); border: 1px solid #f1f5f9; overflow: hidden; height: 100%; min-height: 500px; }
    .table-header { padding: 24px 28px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; }
    .table-title { font-size: 18px; font-weight: 800; color: var(--text-dark); }
    .btn-link { font-size: 13px; font-weight: 600; color: var(--accent-color); display: flex; align-items: center; gap: 6px; }
    
    .table-responsive { width: 100%; overflow-x: auto; }
    table { width: 100%; border-collapse: separate; border-spacing: 0; }
    th { text-align: left; padding: 18px 28px; background: #f8fafc; color: var(--text-gray); font-size: 12px; font-weight: 700; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; }
    td { padding: 18px 28px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: var(--text-dark); vertical-align: middle; }
    tr:hover td { background: #f8fafc; }
    
    .user-avatar-sm { width: 32px; height: 32px; border-radius: 50%; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px; margin-right: 10px;}
    .status-badge { padding: 6px 12px; border-radius: 30px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
    .status-badge.verified { background: #dcfce7; color: #15803d; }
    .status-badge.pending { background: #fef9c3; color: #a16207; }
    .status-badge.retake { background: #fee2e2; color: #b91c1c; }
    .modal-close { position: absolute; top: 15px; right: 20px; font-size: 1.8rem; cursor: pointer; color: var(--text-gray); transition: 0.2s; border: none; background: none; }
    .modal-close:hover { color: var(--secondary-color); }
    .modal-title { text-align: center; color: var(--primary-color); margin-bottom: 1.8rem; font-size: 1.4rem; font-weight: 700; }
    

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
            <a href="employee_dashboard.php" class="menu-item active"> Dashboard</a>
            <a href="employee_profile.php" class="menu-item"> My Profile</a>
            <a href="employee_update_experiment.php" class="menu-item"> Experiments</a>
            <a href="employee_verify.php" class="menu-item"> Verification
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
                <button class="toggle-btn" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
                <div class="header-branding">
                    <h1>SRI VASAVI ENGINEERING COLLEGE</h1>
                    
                </div>
            </div>
            <div class="header-right" style="display:flex; align-items:center; gap:15px;">
                <div class="header-icon" title="Messages" onclick="toggleAnnouncementSidebar()" style="width:40px; height:40px; display:flex; align-items:center; justify-content:center; background:#f1f5f9; border-radius:50%; cursor:pointer; position:relative; color:var(--primary-color);">
                    <i class="fa-regular fa-message"></i>
                    <?php if ($announcement_count > 0): ?>
                        <span class="badge" style="position:absolute; top:2px; right:2px; background:var(--secondary-color); color:white; border-radius:50%; width:18px; height:18px; display:flex; align-items:center; justify-content:center; font-size:10px; font-weight:700; border:2px solid #fff;">
                            <?php echo $announcement_count > 9 ? '9+' : $announcement_count; ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="info-text" style="text-align:right;">
                    <div style="font-size:14px; font-weight:700; color:var(--text-dark);"><?php echo htmlspecialchars($employee_name); ?></div>
          <div style="font-size:11px; color:var(--text-gray); font-weight:600;">Faculty</div>
          
        </div>
                <img src="n1.jpg" style="width:42px; height:42px; border-radius:50%; border:2px solid #e2e8f0;" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($employee_name); ?>'">
                <a href="employee_logout.php" class="header-logout-btn" style="display: flex; align-items: center; gap: 6px; padding: 8px 14px; background: #fef2f2; color: var(--secondary-color); border: 1px solid #fecaca; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none; transition: all 0.2s;"><i class="fa-solid fa-arrow-right-from-bracket"></i> LOG OUT</a>
            </div>
            
            
           
        </header>

        <div class="dashboard-container">
            <div class="dashboard-grid">
                
                <!-- LEFT COLUMN -->
                <div class="left-column">
                    <!-- Stats -->
                    <div class="stats-matrix">
                        <div class="stat-box total">
                            <div class="stat-icon-bg"><i class="fa-solid fa-users"></i></div>
                            <div class="stat-count"><?php echo $total_students; ?></div>
                            <div class="stat-label">Total Assigned</div>
                        </div>
                        <div class="stat-box verified">
                            <div class="stat-icon-bg"><i class="fa-solid fa-check"></i></div>
                            <div class="stat-count"><?php echo $completed_this_week; ?></div>
                            <div class="stat-label">Verified This Week</div>
                        </div>
                        <div class="stat-box pending">
                            <div class="stat-icon-bg"><i class="fa-regular fa-clock"></i></div>
                            <div class="stat-count"><?php echo $pending_count; ?></div>
                            <div class="stat-label">Pending Review</div>
                        </div>
                        <div class="stat-box incomplete">
                            <div class="stat-icon-bg"><i class="fa-solid fa-triangle-exclamation"></i></div>
                            <div class="stat-count"><?php echo $not_completed; ?></div>
                            <div class="stat-label">Action Required</div>
                        </div>
                    </div>

                    <!-- STATUS SECTION -->
                    <div class="section-heading">Status</div>
                    <div class="status-panel">
                        
                        <!-- 1. Active Status -->
                        <div class="status-item st-online">
                            <div class="status-left">
                                <div class="status-icon-box"><i class="fa-solid fa-signal"></i></div>
                                <div class="status-text">
                                    Active Status: 
                                    <div class="status-active-badge">
                                        <div class="blink-dot"></div> Online
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Last Login -->
                        <div class="status-item st-login">
                            <div class="status-left">
                                <div class="status-icon-box"><i class="fa-regular fa-calendar-check"></i></div>
                                <div class="status-text">
                                    Last login: <span class="status-value"><?php echo date('d M, h:i A'); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Total Labs Dealing -->
                        <div class="status-item st-labs">
                            <div class="status-left">
                                <div class="status-icon-box"><i class="fa-solid fa-flask"></i></div>
                                <div class="status-text">
                                    Total Labs Dealing: <span class="status-value"><?php echo $total_labs; ?> Labs</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- RIGHT COLUMN -->
                <div class="right-column">
                    <div class="table-header">
                        <div class="table-title">Recent Submissions</div>
                        <a href="employee_verify.php" class="btn-link">View All <i class="fa-solid fa-arrow-right"></i></a>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Roll No</th>
                                    <th>Exp No</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_submissions)): ?>
                                    <tr><td colspan="5" style="text-align:center; padding: 40px; color:var(--text-gray);">No recent activity found.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($recent_submissions as $sub): ?>
                                    <tr>
                                        <td>
                                            <div style="display:flex; align-items:center;">
                                                <div class="user-avatar-sm"><?php echo strtoupper(substr($sub['student_name'], 0, 1)); ?></div>
                                                <span style="font-weight:600;"><?php echo htmlspecialchars($sub['student_name']); ?></span>
                                            </div>
                                        </td>
                                        <td style="font-family:monospace; color:var(--text-gray);"><?php echo htmlspecialchars($sub['roll_number']); ?></td>
                                        <td><span style="background:#f1f5f9; padding:4px 8px; border-radius:6px; font-weight:600; font-size:12px;">#<?php echo htmlspecialchars($sub['experiment_number']); ?></span></td>
                                        <td><span class="status-badge <?php echo strtolower($sub['verification_status']); ?>"><?php echo htmlspecialchars($sub['verification_status']); ?></span></td>
                                        <td style="color:var(--text-gray);"><?php echo date('M d', strtotime($sub['submitted_date'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- Announcements Drawer (shared look with student pages) -->
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

        // Announcements (mirrors student pages)
        (function() {
            const announcementStorageKey = `announcement_read_employee_${<?php echo json_encode($employee_id); ?>}`;

            function loadReadAnnouncements() {
                try {
                    const stored = localStorage.getItem(announcementStorageKey);
                    return new Set(stored ? JSON.parse(stored) : []);
                } catch (e) {
                    return new Set();
                }
            }

            function saveReadAnnouncements(set) {
                try {
                    localStorage.setItem(announcementStorageKey, JSON.stringify(Array.from(set)));
                } catch (e) {
                    /* ignore */
                }
            }

            function updateAnnouncementBadge(unreadCount) {
                const badge = document.querySelector('.header-icon[title="Messages"] .badge');
                if (unreadCount > 0) {
                    if (badge) {
                        badge.textContent = unreadCount > 9 ? '9+' : unreadCount;
                    }
                } else if (badge) {
                    badge.remove();
                }
            }

            function refreshAnnouncementState() {
                const items = document.querySelectorAll('.announcement-item');
                if (!items.length) { updateAnnouncementBadge(0); return; }

                const readSet = loadReadAnnouncements();
                let unread = 0;
                items.forEach(item => {
                    const id = (item.dataset.id || '').toString();
                    if (id && readSet.has(id)) {
                        item.classList.remove('unread');
                        item.classList.add('read');
                    } else {
                        item.classList.remove('read');
                        item.classList.add('unread');
                        unread += 1;
                    }
                });
                updateAnnouncementBadge(unread);
            }

            function markAnnouncementRead(itemEl) {
                if (!itemEl) return;
                const id = (itemEl.dataset.id || '').toString();
                if (!id) return;
                const readSet = loadReadAnnouncements();
                const wasUnread = !readSet.has(id);
                if (wasUnread) {
                    readSet.add(id);
                    saveReadAnnouncements(readSet);
                }
                itemEl.classList.remove('unread');
                itemEl.classList.add('read');
                if (wasUnread) {
                    const remaining = document.querySelectorAll('.announcement-item.unread').length;
                    updateAnnouncementBadge(remaining);
                }
            }

            window.handleAnnouncementClick = function(el) {
                if (!el) return;
                markAnnouncementRead(el);
                const title = el.dataset.title || 'Announcement';
                const body = el.dataset.body || '';
                const date = el.dataset.date || '';
                const type = el.dataset.type || 'Announcement';
                showAnnouncementDetails(title, body, date, type);
            };

            window.toggleAnnouncementSidebar = function() {
                const sidebar = document.getElementById('announcementSidebar');
                const overlay = document.getElementById('announcementOverlay');
                if (!sidebar || !overlay) return;
                sidebar.classList.add('active');
                overlay.classList.add('active');
                refreshAnnouncementState();
            };

            window.closeAnnouncementSidebar = function() {
                const sidebar = document.getElementById('announcementSidebar');
                const overlay = document.getElementById('announcementOverlay');
                if (sidebar) sidebar.classList.remove('active');
                if (overlay) overlay.classList.remove('active');
            };

            window.showAnnouncementDetails = function(title, message, date, type) {
                const modal = document.getElementById('announcementDetailModal');
                if (!modal) return;
                document.getElementById('announcementDetailTitle').textContent = title || 'Announcement';
                document.getElementById('announcementDetailMessage').textContent = message || '';
                document.getElementById('announcementDetailType').textContent = type || 'Announcement';
                document.getElementById('announcementDetailDate').textContent = date ? `Published: ${date}` : '';
                modal.style.display = 'flex';
            };

            window.closeAnnouncementDetail = function() {
                const modal = document.getElementById('announcementDetailModal');
                if (modal) modal.style.display = 'none';
            };

            document.addEventListener('DOMContentLoaded', refreshAnnouncementState);
        })();
    </script>
</body>
</html>
<?php $conn->close(); ?>
