<?php
session_start();
include '../db_connect.php';
require_once __DIR__ . '/announcements_inc.php';

list($announcement_count, $announcements) = employee_load_announcements($conn);

// SESSION CHECK
if (!isset($_SESSION['employee_id'])) {
    header('Location: ../index.php');
    exit;
}

$employee_id = $_SESSION['employee_id'];

// FETCH EMPLOYEE DETAILS
$sql = "SELECT * FROM employees WHERE employee_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();

// Use session data or database data
$name = $_SESSION['employee_name'] ?? $employee['name'];
$username = $_SESSION['username'] ?? $employee['username'];
$email = $_SESSION['email'] ?? $employee['email'];
$department = $_SESSION['department'] ?? $employee['department'];
$phone = $employee['phone'] ?? 'Not provided';
$role = $_SESSION['role'] ?? 'Employee';

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
<title>My Profile | SVEC</title>
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
    .overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.4); backdrop-filter: blur(2px); z-index: 95; opacity: 0; visibility: hidden; transition: 0.3s; }

    /* --- PROFILE SPECIFIC STYLES --- */
    
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

    .detail-section h3 {
        font-size: 16px;
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-item {
        margin-bottom: 20px;
        display: flex;
        flex-direction: column;
    }

    .info-label {
        font-size: 11px;
        font-weight: 700;
        color: var(--text-gray);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
    }

    .info-value {
        font-size: 15px;
        font-weight: 500;
        color: var(--text-dark);
        padding-bottom: 8px;
        border-bottom: 1px solid #f1f5f9;
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
        .profile-details-grid { grid-template-columns: 1fr; gap: 30px; }
        .profile-header-group { flex-direction: column; align-items: center; text-align: center; margin-top: -60px; }
        .profile-title-box { padding-top: 5px; }
        .profile-body { padding: 0 20px 30px 20px; }
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
            <img src="n1.jpg" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($name); ?>&background=1e3a8a&color=fff'">
            <div style="font-weight:700; color:var(--text-dark);"><?php echo htmlspecialchars($name); ?></div>
            <div style="font-size:12px; color:var(--text-gray);"><?php echo htmlspecialchars($username); ?></div>
        </div>
        <div class="sidebar-menu">
            <a href="employee_dashboard.php" class="menu-item"> Dashboard</a>
            <a href="employee_profile.php" class="menu-item active"> My Profile</a>
            <a href="employee_update_experiment.php" class="menu-item"> Experiments</a>
            <a href="employee_verify.php" class="menu-item"> Verification
                <?php if($pending_count > 0): ?><span style="margin-left:auto; background:var(--secondary-color); color:white; font-size:10px; font-weight:700; padding:2px 8px; border-radius:10px;"><?php echo $pending_count; ?></span><?php endif; ?>
            </a>
            <a href="employee_schedule.php" class="menu-item"> Exams</a>
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
            <div style="font-size:14px; font-weight:700; color:var(--text-dark);"><?php echo htmlspecialchars($name); ?></div>
          <div style="font-size:11px; color:var(--text-gray); font-weight:600;">Faculty</div>
          
        </div>
                <img src="n1.jpg" style="width:42px; height:42px; border-radius:50%; border:2px solid #e2e8f0;" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($name); ?>'">                <a href="employee_logout.php" class="header-logout-btn" style="display: flex; align-items: center; gap: 6px; padding: 8px 14px; background: #fef2f2; color: var(--secondary-color); border: 1px solid #fecaca; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none; transition: all 0.2s;"><i class="fa-solid fa-arrow-right-from-bracket"></i> LOG OUT</a>            </div>
        </header>

        <div class="dashboard-container">
            
            <div class="profile-card">
                <!-- Decorative Banner -->
                <div class="profile-banner"></div>

                <div class="profile-body">
                    <!-- Photo & Title -->
                    <div class="profile-header-group">
                        <img src="n1.jpg" class="profile-photo-lg" alt="Profile Photo" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($name); ?>&size=140&background=1e3a8a&color=fff'">
                        <div class="profile-title-box">
                            <div class="profile-name"><?php echo htmlspecialchars($name); ?></div>
                            <span class="profile-role-badge">Faculty Account</span>
                        </div>
                    </div>

                    <!-- Details Grid -->
                    <div class="profile-details-grid">
                        
                        <!-- Col 1: Employee Info -->
                        <div class="detail-section">
                            <h3><i class="fa-regular fa-id-card"></i> Employee Information</h3>
                            
                           
                            
                            <div class="info-item">
                                <span class="info-label">Full Name</span>
                                <div class="info-value"><?php echo htmlspecialchars($name); ?></div>
                            </div>

                            <div class="info-item">
                                <span class="info-label">Username</span>
                                <div class="info-value"><?php echo htmlspecialchars($username); ?></div>
                            </div>

                            <div class="info-item">
                                <span class="info-label">Department</span>
                                <div class="info-value"><?php echo htmlspecialchars($department); ?></div>
                            </div>
                        </div>

                        <!-- Col 2: Contact Info -->
                        <div class="detail-section">
                            <h3><i class="fa-solid fa-address-book"></i> Contact Information</h3>
                            
                            <div class="info-item">
                                <span class="info-label">Email Address</span>
                                <div class="info-value"><?php echo htmlspecialchars($email); ?></div>
                            </div>
                            
                            <div class="info-item">
                                <span class="info-label">Phone Number</span>
                                <div class="info-value"><?php echo htmlspecialchars($phone); ?></div>
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