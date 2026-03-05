<?php
session_start();
include '../db_connect.php';
require_once __DIR__ . '/announcements_inc.php';

if (!isset($_SESSION['employee_id'])) {
    header('Location: ../index.php');
    exit;
}

$employee_id = $_SESSION['employee_id'];
$employee_name = $_SESSION['employee_name'];
$employee_username = $_SESSION['username'] ?? '';

list($announcement_count, $announcements) = employee_load_announcements($conn);

// Fetch assigned subjects, branches, and sections from employee_subjects table
$assigned_subjects = [];
$assigned_branches = [];
$assigned_sections = [];
if (!empty($employee_username)) {
    $assign_sql = "SELECT DISTINCT subject, branch, section FROM employee_subjects WHERE employee_username = ? ORDER BY branch, section";
    $assign_stmt = $conn->prepare($assign_sql);
    $assign_stmt->bind_param("s", $employee_username);
    $assign_stmt->execute();
    $assign_result = $assign_stmt->get_result();
    while ($row = $assign_result->fetch_assoc()) {
        if (!in_array($row['subject'], $assigned_subjects)) {
            $assigned_subjects[] = $row['subject'];
        }
        if (!in_array($row['branch'], $assigned_branches)) {
            $assigned_branches[] = $row['branch'];
        }
        if (!in_array($row['section'], $assigned_sections)) {
            $assigned_sections[] = $row['section'];
        }
    }
    $assign_stmt->close();
    sort($assigned_branches);
    sort($assigned_sections);
}

// Handle form submission
$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $exam_date = $_POST['exam_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $subject = $_POST['subject'];
    $branch = $_POST['branch'];
    $section = $_POST['section'];
    $instructions = $_POST['instructions'];
    
    $sql = "INSERT INTO lab_schedules (employee_id, exam_date, start_time, end_time, subject, branch, section, instructions) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("isssssss", $employee_id, $exam_date, $start_time, $end_time, $subject, $branch, $section, $instructions);
        
        if ($stmt->execute()) {
            $success = "Lab exam scheduled successfully!";
            
            // Send notifications to all students in the specified branch and section for this subject
            $notification_title = "Lab Exam Scheduled - " . htmlspecialchars($subject);
            $notification_message = "Lab exam for {$subject} scheduled on " . date('M d, Y', strtotime($exam_date)) . " from {$start_time} to {$end_time}.";
            
            // Include instructions if provided
            if (!empty($instructions)) {
                $notification_message .= " Instructions: " . htmlspecialchars($instructions);
            }
            
            $student_sql = "SELECT student_id FROM students WHERE branch = ? AND section = ?";
            $student_stmt = $conn->prepare($student_sql);
            if ($student_stmt) {
                $student_stmt->bind_param("ss", $branch, $section);
                $student_stmt->execute();
                $student_result = $student_stmt->get_result();
                
                $notif_sql = "
                    INSERT INTO student_notifications (student_id, title, message, notification_type)
                    SELECT ?, ?, ?, 'exam_scheduled'
                    WHERE NOT EXISTS (
                        SELECT 1 FROM student_notifications
                        WHERE student_id = ?
                          AND title = ?
                          AND message = ?
                          AND notification_type = 'exam_scheduled'
                    )
                ";
                $notif_stmt = $conn->prepare($notif_sql);
                
                if ($notif_stmt) {
                    while ($student_row = $student_result->fetch_assoc()) {
                        $student_id_notif = $student_row['student_id'];
                        $notif_stmt->bind_param(
                            "ississ",
                            $student_id_notif,
                            $notification_title,
                            $notification_message,
                            $student_id_notif,
                            $notification_title,
                            $notification_message
                        );
                        $notif_stmt->execute();
                    }
                    $notif_stmt->close();

                    $cleanup_sql = "DELETE n1 FROM student_notifications n1
                                    INNER JOIN student_notifications n2
                                        ON n1.student_id = n2.student_id
                                       AND n1.title = n2.title
                                       AND n1.message = n2.message
                                       AND n1.notification_type = n2.notification_type
                                       AND n1.notification_id > n2.notification_id";
                    $conn->query($cleanup_sql);
                }
                
                $student_stmt->close();
            }
        } else {
            $error = "Failed to schedule exam. Please try again.";
        }
        $stmt->close();
    } else {
        $error = "Database error. Please try again.";
    }
}

// Get upcoming schedules
$schedules_sql = "SELECT * FROM lab_schedules WHERE employee_id = ? AND exam_date >= CURDATE() ORDER BY exam_date, start_time";
$schedules_stmt = $conn->prepare($schedules_sql);
$schedules_stmt->bind_param("i", $employee_id);
$schedules_stmt->execute();
$schedules = $schedules_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$schedules_stmt->close();

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
<title>Schedule Exams | SVEC</title>
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
    
    .page-title { font-size: 26px; font-weight: 800; color: var(--primary-color); margin-bottom: 24px; }

    .schedule-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
    
    .schedule-card {
        background: var(--white);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-card);
        border: 1px solid #f1f5f9;
        display: flex;
        flex-direction: column;
    }

    .card-header {
        padding: 20px 24px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 18px;
        font-weight: 700;
        color: var(--text-dark);
    }

    .card-body { padding: 24px; flex: 1; overflow-y: auto; max-height: calc(100vh - 300px); }

    /* Form Styles */
    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; font-size: 13px; font-weight: 600; color: var(--text-dark); margin-bottom: 6px; }
    .form-group input, .form-group select, .form-group textarea {
        width: 100%; padding: 10px 12px;
        border: 1px solid #e2e8f0; border-radius: 8px;
        font-family: inherit; font-size: 14px;
        transition: 0.2s;
    }
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
        border-color: var(--accent-color); outline: none;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }
    
    .row-group { display: flex; gap: 15px; }
    .col-half { flex: 1; }

    .btn-submit {
        background: var(--accent-color); color: white;
        width: 100%; padding: 12px; border-radius: 8px;
        border: none; font-weight: 600; font-size: 15px;
        cursor: pointer; transition: 0.2s;
        margin-top: 10px;
    }
    .btn-submit:hover { background: var(--primary-color); }

    /* Schedule List */
    .schedule-list { display: flex; flex-direction: column; gap: 15px; }
    
    .schedule-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: var(--radius-md);
        padding: 20px;
        border-left: 4px solid var(--accent-color);
        transition: 0.2s;
    }
    .schedule-item:hover { transform: translateY(-2px); background: white; }
    
    .sch-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
    .sch-date { font-weight: 700; font-size: 15px; color: var(--text-dark); }
    .sch-badge { background: #eff6ff; color: var(--accent-color); font-size: 11px; padding: 4px 8px; border-radius: 6px; font-weight: 600; }
    
    .sch-subject { font-size: 14px; color: var(--text-gray); margin-bottom: 12px; font-weight: 500; }
    
    .sch-time-box {
        background: white; padding: 10px; border-radius: 6px;
        display: flex; gap: 15px; font-size: 13px; color: var(--text-dark);
        border: 1px solid #e2e8f0;
    }

    .empty-state { text-align: center; padding: 40px; color: #94a3b8; }

    .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
    .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

    .overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.4); backdrop-filter: blur(2px); z-index: 95; opacity: 0; visibility: hidden; transition: 0.3s; }
    .overlay.active { opacity: 1; visibility: visible; }
    .modal-close { position: absolute; top: 15px; right: 20px; font-size: 1.8rem; cursor: pointer; color: var(--text-gray); transition: 0.2s; border: none; background: none; }
    .modal-close:hover { color: var(--secondary-color); }
    .modal-title { text-align: center; color: var(--primary-color); margin-bottom: 1.8rem; font-size: 1.4rem; font-weight: 700; }
    

    @media (max-width: 992px) {
        .sidebar { transform: translateX(-100%); width: 280px; }
        .sidebar.active { transform: translateX(0); }
        .main-content { margin-left: 0; width: 100%; }
        .overlay.active { opacity: 1; visibility: visible; }
        .schedule-layout { grid-template-columns: 1fr; }
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
            <div style="font-size:12px; color:var(--text-gray);">@<?php echo htmlspecialchars($employee_username); ?></div>
        </div>
        <div class="sidebar-menu">
            <a href="employee_dashboard.php" class="menu-item"> Dashboard</a>
            <a href="employee_profile.php" class="menu-item"> My Profile</a>
            <a href="employee_update_experiment.php" class="menu-item"> Experiments</a>
           <a href="employee_verify.php" class="menu-item"> Verification
                <?php if($pending_count > 0): ?><span style="margin-left:auto; background:var(--secondary-color); color:white; font-size:10px; font-weight:700; padding:2px 8px; border-radius:10px;"><?php echo $pending_count; ?></span><?php endif; ?>
            <a href="employee_schedule.php" class="menu-item active"> Exams</a>
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
            
            <div class="page-title">Schedule Lab Exams</div>

            <!-- Messages -->
            <?php if ($success): ?>
                <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?php echo $success; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?></div>
            <?php endif; ?>

            <div class="schedule-layout">
                
                <!-- LEFT: FORM (50%) -->
                <div class="schedule-card">
                    <div class="card-header">New Schedule</div>
                    
                    <div class="card-body">
                        <form method="POST">
                            <div class="form-group">
                                <label>Exam Date</label>
                                <input type="date" name="exam_date" required min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            
                            <div class="row-group">
                                <div class="col-half">
                                    <div class="form-group">
                                        <label>Start Time</label>
                                        <input type="time" name="start_time" required>
                                    </div>
                                </div>
                                <div class="col-half">
                                    <div class="form-group">
                                        <label>End Time</label>
                                        <input type="time" name="end_time" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Subject</label>
                                <select name="subject" required>
                                    <option value="">Select Subject</option>
                                    <?php foreach ($assigned_subjects as $subj): ?>
                                        <option value="<?=htmlspecialchars($subj)?>">
                                            <?=htmlspecialchars($subj)?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="row-group">
                                <div class="col-half">
                                    <div class="form-group">
                                        <label>Branch</label>
                                        <select name="branch" required>
                                            <option value="">Select Branch</option>
                                            <?php foreach ($assigned_branches as $br): ?>
                                                <option value="<?=htmlspecialchars($br)?>">
                                                    <?=htmlspecialchars($br)?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-half">
                                    <div class="form-group">
                                        <label>Section</label>
                                        <select name="section" required>
                                            <option value="">Select Section</option>
                                            <?php foreach ($assigned_sections as $sec): ?>
                                                <option value="<?=htmlspecialchars($sec)?>">
                                                    <?=htmlspecialchars($sec)?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Instructions (Optional)</label>
                                <textarea name="instructions" rows="3" placeholder="Enter instructions for students..."></textarea>
                            </div>
                            
                            <button type="submit" class="btn-submit">Schedule Exam</button>
                        </form>
                    </div>
                </div>

                <!-- RIGHT: LIST (50%) -->
                <div class="schedule-card">
                    <div class="card-header">Upcoming Schedules</div>
                    
                    <div class="card-body">
                        <?php if (empty($schedules)): ?>
                            <div class="empty-state">
                                <i class="fa-regular fa-calendar-xmark" style="font-size:32px; margin-bottom:15px; opacity:0.5;"></i>
                                <div style="font-weight:500;">No upcoming lab exams.</div>
                                <div style="font-size:13px; margin-top:5px;">Scheduled exams will appear here.</div>
                            </div>
                        <?php else: ?>
                            <div class="schedule-list">
                                <?php foreach ($schedules as $sch): ?>
                                <div class="schedule-item">
                                    <div class="sch-header">
                                        <div class="sch-date">
                                            <?php echo date('d M, Y', strtotime($sch['exam_date'])); ?>
                                        </div>
                                        <div class="sch-badge">
                                            <?php echo htmlspecialchars($sch['branch'] ?? ''); ?> - <?php echo htmlspecialchars($sch['section']); ?>
                                        </div>
                                    </div>
                                    <div class="sch-subject">
                                        <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $sch['subject']))); ?>
                                    </div>
                                    <div class="sch-time-box">
                                        <div>
                                            <i class="fa-regular fa-clock"></i>
                                            <?php echo date('h:i A', strtotime($sch['start_time'])); ?>
                                        </div>
                                        <div style="color:#cbd5e1;">
                                            <i class="fa-solid fa-arrow-right-long"></i>
                                        </div>
                                        <div>
                                            <?php echo date('h:i A', strtotime($sch['end_time'])); ?>
                                        </div>
                                    </div>
                                    <?php if (!empty($sch['instructions'])): ?>
                                        <div style="margin-top:10px; font-size:12px; color:var(--text-gray); font-style:italic;">
                                            "<?php echo htmlspecialchars($sch['instructions']); ?>"
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <?php employee_render_announcement_drawer($announcements); ?>

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
    <?php employee_render_announcement_scripts($employee_id); ?>
</body>
</html>
<?php $conn->close(); ?>
