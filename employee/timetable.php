<?php
session_start();
require_once '../db_connect.php';
require_once __DIR__ . '/announcements_inc.php';

list($announcement_count, $announcements) = employee_load_announcements($conn);

$employee_username = $_SESSION['username'] ?? '';
$employee_name = $_SESSION['employee_name'] ?? 'Employee';
$employee_role = $_SESSION['role'] ?? 'Staff';
$employee_id = $_SESSION['employee_id'] ?? 0;

// Fetch employee's assigned subjects
$employee_subjects = [];
if ($conn) {
    $stmt = $conn->prepare("
        SELECT DISTINCT subject, branch, section, semester 
        FROM employee_subjects 
        WHERE employee_username = ?
    ");
    if ($stmt) {
        $stmt->bind_param("s", $employee_username);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $employee_subjects[] = $row;
        }
        $stmt->close();
    }
}

// Fetch timetable for employee's subjects
$timetable = [];
if ($conn && !empty($employee_subjects)) {
    // Prepare WHERE clause for all assigned subjects
    $where_conditions = [];
    $params = [];
    $types = '';
    
    foreach ($employee_subjects as $subject) {
        $where_conditions[] = "(subject = ? AND branch = ? AND section = ? AND semester = ?)";
        $params[] = $subject['subject'];
        $params[] = $subject['branch'];
        $params[] = $subject['section'];
        $params[] = $subject['semester'];
        $types .= 'ssss';
    }
    
    $where_clause = implode(' OR ', $where_conditions);
    
    $sql = "
        SELECT t.* 
        FROM timetable t 
        WHERE ($where_clause) 
          AND t.employee_username = ?
        ORDER BY FIELD(t.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'), t.start_time
    ";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        // Add employee_username as last parameter
        $params[] = $employee_username;
        $types .= 's';
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $timetable[] = $row;
        }
        $stmt->close();
    }
}
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
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Timetable - Sri Vasavi Engineering College</title>
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

  /* Timetable Styles */
  .timetable-container {
    background: var(--white);
    border-radius: var(--radius-lg);
    padding: 24px;
    box-shadow: var(--shadow-card);
    border: 1px solid #f1f5f9;
    overflow-x: auto;
  }
    
    .timetable-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .timetable-title {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .timetable-icon {
      background: var(--primary-color);
        color: white;
        width: 45px;
        height: 45px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }
    
    .timetable-info h2 {
        margin: 0;
        color: var(--text-dark);
        font-size: 1.3rem;
    }
    
    .timetable-info p {
        margin: 4px 0 0;
        color: var(--text-gray);
        font-size: 0.9rem;
    }
    
    .subject-summary {
        background: #f8fafc;
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 20px;
        border-left: 4px solid var(--primary-color);
    }
    
    .subject-summary h3 {
        margin: 0 0 10px 0;
        color: var(--text-dark);
        font-size: 1.1rem;
    }
    
    .subject-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    
    .subject-tag {
        background: white;
        border: 1px solid #e2e8f0;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.9rem;
        color: var(--text-gray);
    }
    
    .timetable-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 600px;
    }
    
    .timetable-table th {
      background: var(--primary-color);
        color: white;
        padding: 14px 16px;
        text-align: left;
        font-weight: 600;
        font-size: 0.95rem;
    }
    
    .timetable-table th:first-child {
        border-radius: 8px 0 0 0;
    }
    
    .timetable-table th:last-child {
        border-radius: 0 8px 0 0;
    }
    
    .timetable-table td {
        padding: 14px 16px;
        border-bottom: 1px solid #e2e8f0;
        color: var(--text-dark);
        font-size: 0.95rem;
    }
    
    .timetable-table tbody tr:hover {
        background: #f8fafc;
    }
    
    .timetable-table tbody tr:last-child td {
        border-bottom: none;
    }
    
    .day-cell {
        font-weight: 600;
        color: var(--primary-color);
        background: var(--primary-light);
    }
    
    .day-header-row {
        background: #e2e8f0 !important;
    }
    
    .day-header-row td {
        font-weight: 700;
        color: var(--primary-color);
        font-size: 1rem;
        padding: 12px 16px;
        border-bottom: 2px solid #cbd5e1;
    }
    
    .time-cell {
        white-space: nowrap;
        color: var(--text-gray);
        font-weight: 500;
    }
    
    .subject-cell {
        font-weight: 600;
        color: var(--text-dark);
    }
    
    .class-cell {
        color: var(--text-gray);
        font-size: 0.9rem;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 40px;
        color: var(--text-gray);
        font-size: 16px;
        background: #f8fafc;
        border-radius: 12px;
        border: 2px dashed #cbd5e1;
    }
    .empty-state h3 {
      color: var(--text-gray);
      font-size: 18px;
      margin-bottom: 8px;
    }

    .overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.4); backdrop-filter: blur(2px); z-index: 95; opacity: 0; visibility: hidden; transition: 0.3s; }
    .overlay.active { opacity: 1; visibility: visible; }
    .modal-close { position: absolute; top: 15px; right: 20px; font-size: 1.8rem; cursor: pointer; color: var(--text-gray); transition: 0.2s; border: none; background: none; }
    .modal-close:hover { color: var(--secondary-color); }
    .modal-title { text-align: center; color: var(--primary-color); margin-bottom: 1.8rem; font-size: 1.4rem; font-weight: 700; }
    

    @media (max-width: 992px) {
        .sidebar { transform: translateX(-100%); width: 280px; }
        .sidebar.active { transform: translateX(0); }
        .main-content { margin-left: 0; width: 100%; }
    }

    @media (max-width: 700px) {
        .timetable-header { flex-direction: column; gap: 10px; align-items: flex-start; }
        .timetable-table th,
        .timetable-table td { padding: 10px 8px; font-size: 0.85rem; }
        .timetable-container { padding: 16px; }
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
      <div style="font-size:12px; color:var(--text-gray);"><?php echo htmlspecialchars($employee_username); ?></div>
    </div>
    <div class="sidebar-menu">
      <a href="employee_dashboard.php" class="menu-item"> Dashboard</a>
      <a href="employee_profile.php" class="menu-item"> My Profile</a>
      <a href="employee_update_experiment.php" class="menu-item"> Experiments</a>
     <a href="employee_verify.php" class="menu-item"> Verification
      <?php if($pending_count > 0): ?><span style="margin-left:auto; background:var(--secondary-color); color:white; font-size:10px; font-weight:700; padding:2px 8px; border-radius:10px;"><?php echo $pending_count; ?></span><?php endif; ?>
      <a href="employee_schedule.php" class="menu-item"> Exams</a>
      <a href="timetable.php" class="menu-item active"> Timetable</a>
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
      <div class="page-title">My Teaching Schedule</div>
    
    <div class="timetable-container">
        <div class="timetable-header">
            <div class="timetable-title">
                <div class="timetable-icon">📅</div>
                <div class="timetable-info">
                    <h2>Teaching Schedule</h2>
                    <p><?php echo htmlspecialchars($employee_name); ?> - <?php echo htmlspecialchars($employee_role); ?></p>
                </div>
            </div>
        </div>
        
        <?php if (empty($employee_subjects)): ?>
          <div class="empty-state">
            <h3>No Subjects Assigned</h3>
            <p>You have not been assigned any teaching subjects yet. Please contact the administration.</p>
          </div>
        <?php else: ?>
          <!-- Subject Summary -->
          <div class="subject-summary">
            <h3>Assigned Subjects:</h3>
            <div class="subject-tags">
              <?php 
              $unique_subjects = [];
              foreach ($employee_subjects as $subject):
                $subject_key = $subject['subject'] . '|' . $subject['branch'] . '|' . $subject['section'] . '|' . $subject['semester'];
                if (!in_array($subject_key, $unique_subjects)):
                  $unique_subjects[] = $subject_key;
              ?>
                <span class="subject-tag">
                  <?php echo htmlspecialchars($subject['subject']); ?> 
                  (<?php echo htmlspecialchars($subject['branch']); ?> - 
                  Sec <?php echo htmlspecialchars($subject['section']); ?> - 
                  Sem <?php echo htmlspecialchars($subject['semester']); ?>)
                </span>
              <?php endif; endforeach; ?>
            </div>
          </div>
          
          <?php if (empty($timetable)): ?>
            <div class="empty-state">
              <h3>No Timetable Available</h3>
              <p>Your teaching timetable has not been scheduled yet.</p>
            </div>
          <?php else: ?>
            <table class="timetable-table">
              <thead>
                <tr>
                  <th>Day</th>
                  <th>Start Time</th>
                  <th>End Time</th>
                  <th>Subject</th>
                  <th>Class</th>
                </tr>
              </thead>
              <tbody>
                <?php 
                $current_day = '';
                foreach ($timetable as $slot): 
                  $show_day = ($slot['day_of_week'] !== $current_day);
                  $current_day = $slot['day_of_week'];
                  
                  // Determine badge class
                 
                ?>
                <?php if ($show_day): ?>
                <tr class="day-header-row">
                  <td colspan="5"><?php echo htmlspecialchars($slot['day_of_week']); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                  <td class="day-cell"><?php echo htmlspecialchars($slot['day_of_week']); ?></td>
                  <td class="time-cell"><?php echo date('h:i A', strtotime($slot['start_time'])); ?></td>
                  <td class="time-cell"><?php echo date('h:i A', strtotime($slot['end_time'])); ?></td>
                  <td class="subject-cell"><?php echo htmlspecialchars($slot['subject']); ?></td>
                  <td class="class-cell">
                    <?php echo htmlspecialchars($slot['branch']); ?> - 
                    Sec <?php echo htmlspecialchars($slot['section']); ?> - 
                    Sem <?php echo htmlspecialchars($slot['semester']); ?>
                  </td>
                 
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        <?php endif; ?>
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