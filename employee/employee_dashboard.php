<?php
session_start();
include '../db_connect.php';

// SESSION CHECK
if (!isset($_SESSION['employee_id']) || ($_SESSION['role'] ?? '') !== 'employee') {
    header('Location: employee_login.php');
    exit;
}

$employee_id = $_SESSION['employee_id'];
$employee_name = $_SESSION['employee_name'];

// Get statistics for dashboard
$current_week_start = date('Y-m-d', strtotime('monday this week'));
$current_week_end = date('Y-m-d', strtotime('sunday this week'));

// Total students assigned to this employee (assuming you have a way to know which students are assigned)
// For now, count unique students who have submitted to this employee
$total_students_sql = "SELECT COUNT(DISTINCT student_id) as count FROM submissions WHERE employee_id = ?";
$stmt = $conn->prepare($total_students_sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$total_students = $stmt->get_result()->fetch_assoc()['count'];

// Completed this week (Verified submissions this week)
$completed_week_sql = "SELECT COUNT(*) as count FROM submissions 
                       WHERE employee_id = ? 
                       AND verification_status = 'Verified'
                       AND DATE(verification_date) BETWEEN ? AND ?";
$stmt = $conn->prepare($completed_week_sql);
$stmt->bind_param("iss", $employee_id, $current_week_start, $current_week_end);
$stmt->execute();
$completed_this_week = $stmt->get_result()->fetch_assoc()['count'];

// Pending submissions
$pending_sql = "SELECT COUNT(*) as count FROM submissions 
                WHERE employee_id = ? AND verification_status = 'Pending'";
$stmt = $conn->prepare($pending_sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$pending_count = $stmt->get_result()->fetch_assoc()['count'];

// Not completed (students with no submissions or all pending)
// This is a simplified version - you might need a more complex query based on your requirements
$not_completed = $total_students - ($completed_this_week + $pending_count);
if ($not_completed < 0) $not_completed = 0;

// Recent submissions for the table
$recent_sql = "SELECT s.*, stu.name as student_name, stu.roll_number 
               FROM submissions s 
               JOIN students stu ON s.student_id = stu.student_id 
               WHERE s.employee_id = ? 
               ORDER BY s.submitted_date DESC 
               LIMIT 5";
$stmt = $conn->prepare($recent_sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$recent_submissions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Employee Dashboard - Sri Vasavi Engineering College</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/common.css">
<style>
/* ================= DASHBOARD STATS ================= */
.dashboard-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    border-top: 4px solid;
    transition: transform 0.3s ease;
    text-align: center;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-card.total {
    border-color: #3b82f6;
}

.stat-card.completed {
    border-color: #10b981;
}

.stat-card.pending {
    border-color: #f59e0b;
}

.stat-card.not-completed {
    border-color: #ef4444;
}

.stat-number {
    font-size: 36px;
    font-weight: 700;
    margin-bottom: 10px;
}

.stat-card.total .stat-number { color: #3b82f6; }
.stat-card.completed .stat-number { color: #10b981; }
.stat-card.pending .stat-number { color: #f59e0b; }
.stat-card.not-completed .stat-number { color: #ef4444; }

.stat-label {
    font-size: 16px;
    color: #64748b;
    margin-bottom: 15px;
}

.stat-details {
    font-size: 14px;
    color: #94a3b8;
    margin-top: 10px;
}

/* ================= QUICK ACTIONS ================= */
.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 30px;
}

.quick-action {
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    padding: 25px 20px;
    text-align: center;
    text-decoration: none;
    color: #334155;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}

.quick-action:hover {
    border-color: #3b82f6;
    transform: translateY(-3px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

.quick-action-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #e0f2fe;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: #3b82f6;
}

.quick-action-title {
    font-weight: 600;
    font-size: 16px;
}

.quick-action-desc {
    font-size: 14px;
    color: #64748b;
}

/* ================= RECENT ACTIVITY ================= */
.recent-submissions {
    background: white;
    border-radius: 12px;
    padding: 25px;
    margin-top: 20px;
}

.recent-submissions h2 {
    margin-top: 0;
    margin-bottom: 20px;
    color: #1e293b;
    font-size: 20px;
}

.table-container {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.data-table th {
    background: #f8fafc;
    padding: 12px 16px;
    text-align: left;
    font-weight: 600;
    color: #475569;
    border-bottom: 2px solid #e2e8f0;
}

.data-table td {
    padding: 12px 16px;
    border-bottom: 1px solid #e2e8f0;
}

.data-table tr:hover {
    background: #f8fafc;
}

.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.status-badge.pending {
    background: #fef3c7;
    color: #92400e;
}

.status-badge.verified {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.retake {
    background: #fee2e2;
    color: #991b1b;
}

.action-btn {
    padding: 6px 12px;
    background: #3b82f6;
    color: white;
    border-radius: 6px;
    text-decoration: none;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
}

.action-btn:hover {
    background: #2563eb;
}

/* ================= WEEKLY SUMMARY ================= */
.weekly-summary {
    background: white;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 20px;
}

.week-range {
    color: #64748b;
    font-size: 14px;
    margin-bottom: 20px;
}

.week-range strong {
    color: #3b82f6;
}

/* ================= RESPONSIVE ================= */
@media(max-width:768px){
    .dashboard-stats {
        grid-template-columns: 1fr;
    }
    
    .quick-actions {
        grid-template-columns: 1fr;
    }
    
    .data-table {
        font-size: 12px;
    }
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
        <p>Pedatadepalli, Tadepalligudem </p>
      </div>
    </div>
    <div class="employee-info">
      <img src="employee.jpg" alt="Photo" class="student-photo">
    </div>
  </header>

  <div class="topbar">
    <div>Welcome <strong><?php echo htmlspecialchars($employee_name); ?>...!</strong></div>
    <a href="employee_logout.php" class="btn btn-primary">LOG OUT</a>
</div>
  <!-- NAV BAR -->
  <nav class="navbar">
    <div class="nav-menu">
      <a href="employee_dashboard.php" class="nav-item active">Dashboard</a>
      <a href="employee_profile.php" class="nav-item">Profile</a>
      <a href="employee_update_experiment.php" class="nav-item">Update Experiments</a>
      <a href="employee_verify.php" class="nav-item">Verify Students</a>
      <a href="employee_schedule.php" class="nav-item">Schedule Exams</a>
    </div>
  </nav>

  <!-- MAIN CONTENT -->
  <main class="main">
    <h1 class="page-title">Dashboard Overview</h1>
    
    <!-- Weekly Summary -->
    <div class="weekly-summary">
      <h2>This Week's Summary</h2>
      <div class="week-range">
        Week: <strong><?php echo date('F d', strtotime($current_week_start)); ?> - <?php echo date('F d, Y', strtotime($current_week_end)); ?></strong>
      </div>
      
      <!-- Stats Cards -->
      <div class="dashboard-stats">
        <div class="stat-card total">
          <div class="stat-number"><?php echo $total_students; ?></div>
          <div class="stat-label">Total Students Assigned</div>
          <div class="stat-details">All students under your supervision</div>
        </div>
        
        <div class="stat-card completed">
          <div class="stat-number"><?php echo $completed_this_week; ?></div>
          <div class="stat-label">Completed This Week</div>
          <div class="stat-details">Experiments verified this week</div>
        </div>
        
        <div class="stat-card pending">
          <div class="stat-number"><?php echo $pending_count; ?></div>
          <div class="stat-label">Pending Verification</div>
          <div class="stat-details">Awaiting your review</div>
        </div>
        
        <div class="stat-card not-completed">
          <div class="stat-number"><?php echo $not_completed; ?></div>
          <div class="stat-label">Not Completed</div>
          <div class="stat-details">Yet to submit or incomplete</div>
        </div>
      </div>
    </div>

    
    <!-- Recent Submissions -->
    <div class="recent-submissions">
      <h2>Recent Submissions</h2>
      <?php if (empty($recent_submissions)): ?>
        <p>No recent submissions found.</p>
      <?php else: ?>
        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th>Student</th>
                <th>Roll No</th>
                <th>Experiment</th>
                <th>Submitted On</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recent_submissions as $sub): ?>
              <tr>
                <td><?php echo htmlspecialchars($sub['student_name']); ?></td>
                <td><?php echo htmlspecialchars($sub['roll_number']); ?></td>
                <td>Exp <?php echo htmlspecialchars($sub['experiment_id']); ?></td>
                <td><?php echo date('d/m/Y', strtotime($sub['submitted_date'])); ?></td>
                <td>
                  <span class="status-badge <?php echo strtolower($sub['verification_status']); ?>">
                    <?php echo htmlspecialchars($sub['verification_status']); ?>
                  </span>
                </td>
                <td>
                  <a href="employee_verify.php?id=<?php echo $sub['submission_id']; ?>" class="action-btn">View</a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div style="text-align: center; margin-top: 20px;">
          <a href="employee_verify.php" class="btn btn-primary">View All Submissions</a>
        </div>
      <?php endif; ?>
    </div>

  </main>
</div>

</body>
</html>
<?php $conn->close(); ?>