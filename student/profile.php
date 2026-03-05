<?php
session_start();
require_once '../db_connect.php';

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}

$student_id = $_SESSION['student_id'];
$name = $_SESSION['name'] ?? 'Student';
$roll_number = $_SESSION['roll_number'] ?? '';
$branch = $_SESSION['branch'] ?? '';
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
$email = $_SESSION['email'] ?? '';
$phone = $_SESSION['phone'] ?? 'Not provided';
$linkedin = $_SESSION['linkedin'] ?? '';
$github = $_SESSION['github'] ?? '';

$notification_message = '';
$notification_class = '';

// Fetch notifications for the student
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
    
    // Count unread notifications
    $count_sql = "SELECT COUNT(*) as count FROM student_notifications 
                  WHERE student_id = ? AND is_read = 0";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param("i", $student_id);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $count_row = $count_result->fetch_assoc();
    $notification_count = $count_row['count'];
    
    $notif_stmt->close();
    $count_stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_contact'])) {
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $linkedin = trim($_POST['linkedin'] ?? '');
    $github = trim($_POST['github'] ?? '');
    $errors = [];
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address";
    }
    if ($phone && !preg_match('/^[0-9]{10}$/', $phone)) {
        $errors[] = "Phone number must be exactly 10 digits";
    }
    if (!empty($errors)) {
        $notification_message = implode("<br>", $errors);
        $notification_class = 'error';
    } else {
        $sql = "UPDATE students
                SET email = ?, phone = ?, linkedin = ?, github = ?
                WHERE student_id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ssssi", $email, $phone, $linkedin, $github, $_SESSION['student_id']);
            if ($stmt->execute()) {
                $_SESSION['email'] = $email;
                $_SESSION['phone'] = $phone;
                $_SESSION['linkedin'] = $linkedin;
                $_SESSION['github'] = $github;
                $notification_message = "Contact details updated successfully!";
                $notification_class = 'success';
            }
            $stmt->close();
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>My Profile | SVEC</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../css/notifications.css">
<link rel="stylesheet" href="../css/announcements.css">

<style>
    :root {
        --primary-color: #1e3a8a;
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
    .menu-item { display: flex; align-items: center; padding: 14px 18px; margin-bottom: 6px; border-radius: var(--radius-md); color: var(--text-gray); font-weight: 500; font-size: 14px; transition: all 0.2s; }
    .menu-item i { width: 26px; font-size: 18px; margin-right: 12px; color: #94a3b8; transition: 0.2s; }
    .menu-item:hover { background-color: #f8fafc; color: var(--primary-color); }
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
    .header-branding h1 { font-size: 18px; font-weight: 800; color: var(--primary-color); }
    .header-branding p { font-size: 11px; color: var(--text-gray); font-weight: 600; letter-spacing: 0.5px; }
    .toggle-btn { font-size: 20px; cursor: pointer; padding: 8px; border-radius: 8px; border: none; background: transparent; color: var(--text-dark); }
    
    .header-icons { display: flex; align-items: center; gap: 12px; }
    .header-icon { width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; background: #f1f5f9; border-radius: 50%; cursor: pointer; transition: all 0.2s; position: relative; color: var(--primary-color); }
    .header-icon:hover { background: #e2e8f0; transform: translateY(-1px); }
  .header-icon .badge { position: absolute; top: 2px; right: 2px; min-width: 18px; height: 18px; padding: 0 5px; background: var(--secondary-color); color: #fff; border-radius: 10px; border: 2px solid #fff; font-size: 11px; font-weight: 700; display: flex; align-items: center; justify-content: center; }
    
    
    .profile-section { display: flex; align-items: center; gap: 12px; }
    .profile-section img { width: 42px; height: 42px; border-radius: 50%; border: 2px solid #e2e8f0; object-fit: cover; }
    .profile-section .user-info { text-align: right; }
    .profile-section .user-name { font-size: 14px; font-weight: 700; color: var(--text-dark); }
    .profile-section .user-role { font-size: 11px; color: var(--text-gray); font-weight: 600; }
    
    .header-logout-btn { display: flex; align-items: center; gap: 6px; padding: 8px 14px; background: #fef2f2; color: var(--secondary-color); border: 1px solid #fecaca; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
    .header-logout-btn:hover { background: #fee2e2; transform: translateY(-1px); }
    
    .dashboard-container { padding: 24px; margin: 0; height: calc(100vh - var(--header-height)); overflow-y: auto; }
    .overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.4); backdrop-filter: blur(2px); z-index: 95; opacity: 0; visibility: hidden; transition: 0.3s; }

   .profile-card {
        background: var(--white);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-card);
        border: 1px solid #e2e8f0;
        overflow: hidden;
        position: relative;
    }

    .profile-banner {
        height: 110px; 
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        width: 100%;
    }

    .profile-body {
        padding: 0 40px 40px 40px;
    }

    .profile-header-group {
        display: flex;
        align-items: flex-end;
        position: relative;
        margin-top: -50px; 
        margin-bottom: 30px;
        gap: 25px;
    }

    .profile-photo-lg {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 5px solid var(--white); 
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        object-fit: cover;
        background: var(--white);
        z-index: 10; 
        flex-shrink: 0;
    }

    .profile-title-box {
        padding-bottom: 5px;
        padding-top: 15px; 
    }

    .profile-name { 
        font-size: 28px; 
        font-weight: 800; 
        color: var(--text-dark); 
        line-height: 1.1; 
        margin-bottom: 4px;
    }

    .profile-role-badge { 
        display: inline-block; 
        background: #e0f2fe; 
        color: #0369a1; 
        padding: 4px 12px; 
        border-radius: 20px; 
        font-size: 11px; 
        font-weight: 700; 
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .profile-details-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 50px;
        border-top: 1px solid #f1f5f9;
        padding-top: 30px;
    }
    .detail-section h3 { font-size: 16px; font-weight: 700; color: var(--primary-color); margin-bottom: 24px; display: flex; align-items: center; gap: 10px; text-transform: uppercase; }
    .info-item { margin-bottom: 20px; display: flex; flex-direction: column; }
    .info-label { font-size: 11px; font-weight: 700; color: var(--text-gray); text-transform: uppercase; margin-bottom: 6px; }
    .info-value { font-size: 15px; font-weight: 500; color: var(--text-dark); padding-bottom: 8px; border-bottom: 1px solid #f1f5f9; }

    .edit-btn { background: var(--accent-color); color: white; border: none; padding: 8px 16px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; margin-left: auto; transition: 0.2s; }
    .edit-btn:hover { background: var(--primary-color); }

    .modal-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center; }
    .modal-content { background: white; padding: 30px; border-radius: 16px; width: 90%; max-width: 500px; box-shadow: 0 10px 30px rgba(0,0,0,0.25); }
    .modal-close { position: absolute; top: 15px; right: 20px; font-size: 1.8rem; cursor: pointer; color: var(--text-gray); transition: 0.2s; border: none; background: none; }
    .modal-close:hover { color: var(--secondary-color); }
    .modal-title { text-align: center; color: var(--primary-color); margin-bottom: 1.8rem; font-size: 1.4rem; font-weight: 700; }
    .modal-content label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-dark); font-size: 13px; }
    .modal-content input { width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 14px; box-sizing: border-box; transition: border-color 0.2s; }
    .modal-content input:focus { outline: none; border-color: var(--accent-color); }
    .modal-content button[type="submit"] { width: 100%; padding: 14px; background: linear-gradient(45deg, var(--primary-color), var(--accent-color)); color: white; border: none; border-radius: 10px; font-size: 15px; font-weight: 600; cursor: pointer; transition: 0.2s; }
    .modal-content button[type="submit"]:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3); }

    @media (max-width: 992px) {
        .sidebar { transform: translateX(-100%); width: 280px; }
        .sidebar.active { transform: translateX(0); }
        .main-content { margin-left: 0; width: 100%; }
        .overlay.active { opacity: 1; visibility: visible; }
    }

    @media (max-width: 768px) {
        .profile-details-grid { grid-template-columns: 1fr; gap: 30px; }
        .profile-header-group { flex-direction: column; align-items: center; text-align: center; margin-top: -60px; }
        .profile-body { padding: 0 20px 30px 20px; }
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
            <img src="student.jpg" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($name); ?>&background=1e3a8a&color=fff'">
            <div style="font-weight:700; color:var(--text-dark);">Welcome <?php echo htmlspecialchars($name); ?> !!</div>
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
            <a href="profile.php" class="menu-item active"> My Profile</a>
            <a href="completed_exp.php" class="menu-item"> Completed Experiments</a>
            <a href="retake_exp.php" class="menu-item"> Retake Experiments</a>
            <a href="timetable.php" class="menu-item"> Timetable</a>
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
            
            <div class="profile-card">
                <!-- Decorative Banner -->
                <div class="profile-banner"></div>

                <div class="profile-body">
                    <!-- Photo & Title -->
                    <div class="profile-header-group">
                        <img src="student.jpg" class="profile-photo-lg" alt="Profile Photo" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($name); ?>&size=140&background=1e3a8a&color=fff'">
                        <div class="profile-title-box">
                            <div class="profile-name"><?php echo htmlspecialchars($name); ?></div>
                            <span class="profile-role-badge">Student Account</span>
                        </div>
                    </div>

                    <!-- Details Grid -->
                    <div class="profile-details-grid">
                        
                        <!-- Col 1: Student Info -->
                        <div class="detail-section">
                            <h3><i class="fa-regular fa-id-card"></i> Student Information</h3>
                            
                            <div class="info-item">
                                <span class="info-label">Full Name</span>
                                <div class="info-value"><?php echo htmlspecialchars($name); ?></div>
                            </div>

                            <div class="info-item">
                                <span class="info-label">Roll Number</span>
                                <div class="info-value"><?php echo htmlspecialchars($roll_number); ?></div>
                            </div>

                            <div class="info-item">
                                <span class="info-label">Branch</span>
                                <div class="info-value"><?php echo htmlspecialchars($branch); ?></div>
                            </div>

                            <div class="info-item">
                                <span class="info-label">Section</span>
                                <div class="info-value"><?php echo isset($_SESSION['section']) ? htmlspecialchars($_SESSION['section']) : 'A'; ?></div>
                            </div>

                            <div class="info-item">
                                <span class="info-label">Semester</span>
                                <div class="info-value"><?php echo htmlspecialchars($semester); ?></div>
                            </div>
                        </div>

                        <!-- Col 2: Contact Info -->
                        <div class="detail-section">
                            <h3><i class="fa-solid fa-address-book"></i> Contact Information
                                <button class="edit-btn" onclick="document.getElementById('editModal').style.display='flex'"><i class="fa-solid fa-pen"></i> Edit</button>
                            </h3>
                            
                            <div class="info-item">
                                <span class="info-label">Email Address</span>
                                <div class="info-value"><?php echo htmlspecialchars($email); ?></div>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Phone Number</span>
                                <div class="info-value"><?php echo htmlspecialchars($phone); ?></div>
                            </div>

                            <div class="info-item">
                                <span class="info-label">LinkedIn Profile</span>
                                <div class="info-value"><?php echo $linkedin ? htmlspecialchars($linkedin) : 'Not added'; ?></div>
                            </div>

                            <div class="info-item">
                                <span class="info-label">GitHub Profile</span>
                                <div class="info-value"><?php echo $github ? htmlspecialchars($github) : 'Not added'; ?></div>
                            </div>

                            <div class="info-item">
                                <span class="info-label">Account Status</span>
                                <div class="info-value">
                                    <span style="color:#10b981; font-weight:700;"><i class="fa-solid fa-circle-check"></i> Active</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- EDIT MODAL POPUP -->
    <div id="editModal" class="modal-overlay">
        <div class="modal-content">
            <span class="modal-close" onclick="document.getElementById('editModal').style.display='none'">&times;</span>
            <h3 class="modal-title">Update Contact Details</h3>

            <form method="POST">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($_SESSION['phone'] ?? ''); ?>" pattern="[0-9]{10}" title="10-digit phone number" required>

                <label for="linkedin">LinkedIn Profile URL</label>
                <input type="url" id="linkedin" name="linkedin" value="<?php echo htmlspecialchars($linkedin); ?>" placeholder="https://www.linkedin.com/in/your-profile">

                <label for="github">GitHub Profile URL</label>
                <input type="url" id="github" name="github" value="<?php echo htmlspecialchars($github); ?>" placeholder="https://github.com/your-username">

                <button type="submit" name="save_contact">Save Changes</button>
            </form>
        </div>
    </div>
    
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
    // Sidebar toggle function
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

    // Expose student id and announcement count for notification script
    window.notificationConfig = {
        studentId: <?php echo json_encode($student_id); ?>,
        announcementCount: <?php echo json_encode($announcement_count); ?>
    };
</script>
<script src="script.js?v=3"></script>
</body>
</html>