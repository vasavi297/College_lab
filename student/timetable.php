<?php
session_start();
require_once '../db_connect.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$branch = $_SESSION['branch'] ?? '';
$section = $_SESSION['section'] ?? '';
$semester = $_SESSION['semester'] ?? '';
$student_semester = $semester; // keep original representation for display
$semester_number = (function ($semester) {
    $map = [
        'I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4,
        'V' => 5, 'VI' => 6, 'VII' => 7, 'VIII' => 8
    ];
    if (isset($map[$semester])) {
        return $map[$semester];
    }
    if (is_numeric($semester)) {
        return (int)$semester;
    }
    return 1;
})($semester);
$roll_number = $_SESSION['roll_number'] ?? '';
$student_name = $_SESSION['name'] ?? 'Student';
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
$debug_mode = false;

// Fetch timetable for student's branch, section, and semester (ALL subjects)
$timetable = [];
if ($conn) {
    $sql = "
        SELECT t.*, e.name as teacher_name 
        FROM timetable t 
        LEFT JOIN employees e ON t.employee_username = e.username 
        WHERE t.branch = ? 
          AND t.section = ? 
          AND t.semester = ?
        ORDER BY FIELD(t.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'), t.start_time
    ";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("sss", $branch, $section, $semester);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $timetable[] = $row;
        }
        $stmt->close();
        // Debug: Count records fetched
        // echo "Debug - Records found: " . count($timetable) . "<br>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Timetable | SVEC</title>
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
        --success-color: #10b981;
        --warning-color: #f59e0b;
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
    .menu-item.no-icon { justify-content: center; }

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

    /* PAGE HEADER */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }
    .page-title {
        font-size: 24px;
        font-weight: 800;
        color: var(--text-dark);
    }

    /* TIMETABLE CONTAINER */
    .timetable-container {
        background: var(--white);
        border-radius: var(--radius-lg);
        padding: 24px;
        box-shadow: var(--shadow-card);
        border: 1px solid #e2e8f0;
        overflow-x: auto;
    }
    
    .timetable-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding-bottom: 20px;
        border-bottom: 1px solid #f1f5f9;
    }
    
    .timetable-title {
        display: flex;
        align-items: center;
        gap: 16px;
    }
    
    .timetable-icon {
        background: linear-gradient(135deg, var(--accent-color), #1d4ed8);
        color: white;
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }
    
    .timetable-info h2 {
        font-size: 18px;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 4px;
    }
    
    .timetable-info p {
        color: var(--text-gray);
        font-size: 13px;
        font-weight: 500;
    }
    
    .timetable-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 700px;
    }
    
    .timetable-table th {
        background: linear-gradient(135deg, var(--primary-color), #1e40af);
        color: white;
        padding: 16px;
        text-align: left;
        font-weight: 600;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .timetable-table th:first-child {
        border-radius: 12px 0 0 0;
    }
    
    .timetable-table th:last-child {
        border-radius: 0 12px 0 0;
    }
    
    .timetable-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #f1f5f9;
        color: var(--text-dark);
        font-size: 14px;
    }
    
    .timetable-table tbody tr:hover {
        background: #f8fafc;
    }
    
    .timetable-table tbody tr:last-child td {
        border-bottom: none;
    }
    
    .day-header-row {
        background: linear-gradient(90deg, #f8fafc, #f1f5f9) !important;
    }
    
    .day-header-row td {
        font-weight: 700;
        color: var(--primary-color);
        font-size: 14px;
        padding: 14px 16px;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .day-header-row td i {
        margin-right: 10px;
        color: var(--accent-color);
    }
    
    .time-cell {
        white-space: nowrap;
        color: var(--text-gray);
        font-weight: 600;
        font-size: 13px;
    }
    
    .time-cell i {
        margin-right: 6px;
        color: var(--accent-color);
    }
    
    .subject-cell {
        font-weight: 600;
        color: var(--text-dark);
    }
    
    .subject-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 12px;
        background: var(--primary-light);
        border-radius: 8px;
        font-weight: 600;
        font-size: 13px;
        color: var(--primary-color);
    }
    
    .subject-badge.lab {
        background: #fef3c7;
        color: #92400e;
    }
    
    .teacher-cell {
        color: var(--text-gray);
        font-size: 13px;
    }
    
    .teacher-cell i {
        margin-right: 6px;
        color: #94a3b8;
    }
    
    .empty-state {
        text-align: center;
        padding: 80px 40px;
        background: var(--white);
        border-radius: var(--radius-lg);
        border: 2px dashed #e2e8f0;
    }
    .empty-state i {
        font-size: 64px;
        color: #cbd5e1;
        margin-bottom: 20px;
    }
    .empty-state h3 {
        color: var(--text-dark);
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 8px;
    }
    .empty-state p {
        color: var(--text-gray);
        font-size: 15px;
    }
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
        .page-header { flex-direction: column; align-items: flex-start; gap: 16px; }
        .top-header { padding: 0 16px; }
        .dashboard-container { padding: 16px; }
        .timetable-container { padding: 16px; }
        .timetable-header { flex-direction: column; align-items: flex-start; gap: 12px; }
    }

    @media (max-width: 600px) {
       
        .timetable-table th,
        .timetable-table td {
            padding: 10px 8px;
            font-size: 12px;
        }
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
            <img src="student.jpg" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($student_name); ?>&background=1e3a8a&color=fff'">
            <div style="font-weight:700; color:var(--text-dark);">Welcome <?php echo htmlspecialchars($student_name); ?> !!</div>
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
            <a href="updated_exp.php" class="menu-item"> Dashboard</a>
            <a href="profile.php" class="menu-item"> My Profile</a>
            <a href="completed_exp.php" class="menu-item"> Completed Experiments</a>
            <a href="retake_exp.php" class="menu-item"> Retake Experiments</a>
            <a href="timetable.php" class="menu-item active"> Timetable</a>
        </div>
        <div class="logout-container">
            <a href="logout.php" class="logout-btn"><i class="fa-solid fa-arrow-right-from-bracket" style="margin-right:8px;"></i> Logout</a>
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
            
            <div class="page-header">
                <h1 class="page-title">Class Timetable</h1>
            </div>
            
            <div class="timetable-container">
                <div class="timetable-header">
                    <div class="timetable-title">
                        <div class="timetable-icon">
                            <i class="fa-regular fa-calendar-check"></i>
                        </div>
                        <div class="timetable-info">
                            <h2>Weekly Schedule</h2>
                            <p>Your class schedule for the current semester</p>
                        </div>
                    </div>
                </div>
                
                <?php if (empty($timetable)): ?>
                    <div class="empty-state">
                        <i class="fa-regular fa-calendar-xmark"></i>
                        <h3>No Timetable Available</h3>
                        <p>The timetable for your class has not been uploaded yet.</p>
                    </div>
                <?php else: ?>
                    <table class="timetable-table">
                        <thead>
                            <tr>
                                <th><i class="fa-regular fa-calendar"></i> Day</th>
                                <th><i class="fa-regular fa-clock"></i> Start Time</th>
                                <th><i class="fa-regular fa-clock"></i> End Time</th>
                                <th><i class="fa-solid fa-book"></i> Subject</th>
                                <th><i class="fa-solid fa-chalkboard-user"></i> Teacher</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $current_day = '';
                            foreach ($timetable as $slot): 
                                $show_day = ($slot['day_of_week'] !== $current_day);
                                $current_day = $slot['day_of_week'];
                                $is_lab = strpos(strtolower($slot['subject']), 'lab') !== false;
                            ?>
                            <?php if ($show_day): ?>
                            <tr class="day-header-row">
                                <td colspan="5"><i class="fa-regular fa-calendar"></i> <?php echo htmlspecialchars($slot['day_of_week']); ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr>
                                <td class="day-cell"><?php echo htmlspecialchars($slot['day_of_week']); ?></td>
                                <td class="time-cell"><i class="fa-regular fa-clock"></i> <?php echo date('h:i A', strtotime($slot['start_time'])); ?></td>
                                <td class="time-cell"><?php echo date('h:i A', strtotime($slot['end_time'])); ?></td>
                                <td class="subject-cell">
                                    <span class="subject-badge <?php echo $is_lab ? 'lab' : ''; ?>">
                                        <i class="fa-solid <?php echo $is_lab ? 'fa-flask' : 'fa-book-open'; ?>"></i>
                                        <?php echo htmlspecialchars($slot['subject']); ?>
                                    </span>
                                </td>
                                <td class="teacher-cell"><i class="fa-solid fa-user-tie"></i> <?php echo htmlspecialchars($slot['teacher_name'] ?? 'N/A'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

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

    <!-- Notification Overlay -->
    <div class="notification-overlay" id="notificationOverlay" onclick="closeNotificationSidebar()"></div>

    <!-- Notification Sidebar -->
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
                        <div class="notification-item <?php echo $isRead; ?>" 
                             data-id="<?php echo $notifId; ?>"
                             onclick="handleNotificationClick(<?php echo $notifId; ?>, this, '<?php echo addslashes($title); ?>', '<?php echo addslashes($message); ?>', '<?php echo $type; ?>', '<?php echo $date; ?>')">
                            <div class="notification-title">
                                <span><?php echo $title; ?></span>
                                <span class="notification-time">
                                    <?php echo $shortDate; ?>
                                </span>
                            </div>
                            <div class="notification-message">
                                <?php echo $shortMessage; ?>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px;">
                                <span class="notification-type">
                                    <i class="fas fa-tag"></i>
                                    <?php echo $type; ?>
                                </span>
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

    <!-- Notification Detail Modal -->
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
    <script>
        window.notificationConfig = {
            studentId: <?php echo json_encode($student_id); ?>,
            announcementCount: <?php echo json_encode($announcement_count); ?>
        };
    </script>
    <script src="script.js?v=3"></script>
</body>
</html><?php $conn->close(); ?>