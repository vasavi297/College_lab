<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['employee_id']) || ($_SESSION['role'] ?? '') !== 'employee') {
    header('Location: employee_login.php');
    exit;
}

$employee_id = $_SESSION['employee_id'];
$employee_name = $_SESSION['employee_name'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $exam_date = $_POST['exam_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $subject = $_POST['subject'];
    $section = $_POST['section'];
    $instructions = $_POST['instructions'];
    
    $sql = "INSERT INTO lab_schedules (employee_id, exam_date, start_time, end_time, subject, section, instructions) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssss", $employee_id, $exam_date, $start_time, $end_time, $subject, $section, $instructions);
    
    if ($stmt->execute()) {
        $success = "Lab exam scheduled successfully!";
    } else {
        $error = "Failed to schedule exam. Please try again.";
    }
}

// Get upcoming schedules
$schedules_sql = "SELECT * FROM lab_schedules WHERE employee_id = ? AND exam_date >= CURDATE() ORDER BY exam_date, start_time";
$schedules_stmt = $conn->prepare($schedules_sql);
$schedules_stmt->bind_param("i", $employee_id);
$schedules_stmt->execute();
$schedules = $schedules_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Schedule Exams - Employee Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/common.css">
<style>
/* ================= SCHEDULE PAGE ================= */
.schedule-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
}

@media(max-width:992px){
    .schedule-container {
        grid-template-columns: 1fr;
    }
}

.schedule-form, .upcoming-schedules {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #334155;
    font-size: 15px;
}

.form-group input[type="date"],
.form-group input[type="time"],
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 15px;
    font-family: 'Poppins', sans-serif;
    transition: all 0.3s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.time-group {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.form-group textarea {
    resize: vertical;
    min-height: 120px;
}

.btn-schedule {
    background: #3b82f6;
    color: white;
    border: none;
    padding: 14px 30px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    width: 100%;
    margin-top: 10px;
}

.btn-schedule:hover {
    background: #2563eb;
    transform: translateY(-2px);
}

/* Upcoming Schedules */
.schedule-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
    margin-top: 20px;
}

.schedule-item {
    padding: 20px;
    background: #f8fafc;
    border-radius: 8px;
    border-left: 4px solid #3b82f6;
}

.schedule-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.schedule-date {
    background: #3b82f6;
    color: white;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
}

.schedule-subject {
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 5px;
}

.schedule-details {
    color: #64748b;
    font-size: 14px;
    margin: 5px 0;
}

.schedule-section {
    display: inline-block;
    background: #e0f2fe;
    color: #0369a1;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    margin-top: 10px;
}

.empty-schedules {
    text-align: center;
    padding: 40px 20px;
    color: #94a3b8;
    font-style: italic;
}

.message {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-weight: 500;
}

.message.success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}

.message.error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fecaca;
}
</style>
</head>
<body>

<div class="page">

  <!-- HEADER -->
  <header class="header">
    <div class="header-left">
      <img src="../images/vasavi.png" class="logo" alt="College Logo">
      <div>
        <h1>SRI VASAVI ENGINEERING COLLEGE (AUTONOMOUS)</h1>
        <p>Pedatadepalli, Tadepalligudem</p>
      </div>
    </div>
    <div class="employee-info">
      <img src="employee.jpg" alt="Photo" class="student-photo">
    </div>
  </header>

  <!-- TOP BAR -->
  <div class="topbar">
    <div>Welcome <strong><?php echo htmlspecialchars($employee_name); ?>...!</strong></div>
    <a href="employee_logout.php" class="btn btn-primary">LOG OUT</a>
  </div>

  <!-- NAV BAR -->
  <nav class="navbar">
    <div class="nav-menu">
      <a href="employee_dashboard.php" class="nav-item">Dashboard</a>
      <a href="employee_profile.php" class="nav-item">Profile</a>
      <a href="employee_update_experiment.php" class="nav-item">Update Experiments</a>
      <a href="employee_verify.php" class="nav-item">Verify Students</a>
      <a href="employee_schedule.php" class="nav-item active">Schedule Exams</a>
    </div>
  </nav>

  <!-- MAIN CONTENT -->
  <main class="main">
    <h1 class="page-title">Schedule Lab Exams</h1>
    
    <?php if (isset($success)): ?>
      <div class="message success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
      <div class="message error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="schedule-container">
      <!-- Schedule Form -->
      <div class="schedule-form">
        <h2 style="margin-top: 0; margin-bottom: 25px;">New Lab Exam Schedule</h2>
        
        <form method="POST" action="">
          <div class="form-group">
            <label>Exam Date</label>
            <input type="date" name="exam_date" required min="<?php echo date('Y-m-d'); ?>">
          </div>
          
          <div class="form-group">
            <label>Exam Time</label>
            <div class="time-group">
              <div>
                <label style="font-size: 13px;">Start Time</label>
                <input type="time" name="start_time" required>
              </div>
              <div>
                <label style="font-size: 13px;">End Time</label>
                <input type="time" name="end_time" required>
              </div>
            </div>
          </div>
          
          <div class="form-group">
            <label>Subject</label>
            <select name="subject" required>
              <option value="">Select Subject</option>
              <option value="chemistry">Chemistry Lab</option>
              <option value="theory_of_machines">Theory of Machines Lab</option>
              <option value="physics">Physics Lab</option>
              <option value="computer_science">Computer Science Lab</option>
            </select>
          </div>
          
          <div class="form-group">
            <label>Section</label>
            <select name="section" required>
              <option value="A">Section A</option>
              <option value="B">Section B</option>
              <option value="C">Section C</option>
              <option value="D">Section D</option>
            </select>
          </div>
          
          <div class="form-group">
            <label>Instructions (Optional)</label>
            <textarea name="instructions" placeholder="Enter exam instructions for students..."></textarea>
          </div>
          
          <button type="submit" class="btn-schedule">Schedule Exam</button>
        </form>
      </div>
      
      <!-- Upcoming Schedules -->
      <div class="upcoming-schedules">
        <h2 style="margin-top: 0; margin-bottom: 25px;">Your Upcoming Schedules</h2>
        
        <?php if (empty($schedules)): ?>
          <div class="empty-schedules">
            No upcoming lab exams scheduled.
          </div>
        <?php else: ?>
          <div class="schedule-list">
            <?php foreach ($schedules as $schedule): ?>
            <div class="schedule-item">
              <div class="schedule-header">
                <div class="schedule-date">
                  <?php echo date('d M, Y', strtotime($schedule['exam_date'])); ?>
                </div>
                <span class="schedule-section">Section <?php echo htmlspecialchars($schedule['section']); ?></span>
              </div>
              
              <div class="schedule-subject">
                <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $schedule['subject']))); ?> Lab Exam
              </div>
              
              <div class="schedule-details">
                <strong>Time:</strong> <?php echo date('h:i A', strtotime($schedule['start_time'])); ?> - 
                <?php echo date('h:i A', strtotime($schedule['end_time'])); ?>
              </div>
              
              <?php if (!empty($schedule['instructions'])): ?>
              <div class="schedule-details">
                <strong>Instructions:</strong> <?php echo htmlspecialchars($schedule['instructions']); ?>
              </div>
              <?php endif; ?>
              
              <div class="schedule-details">
                <strong>Scheduled on:</strong> <?php echo date('d/m/Y', strtotime($schedule['created_at'])); ?>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </main>
</div>

</body>
</html>
<?php $conn->close(); ?>