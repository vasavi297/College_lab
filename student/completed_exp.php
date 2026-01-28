<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Get completed experiments (Verified submissions)
$query = "SELECT s.*, e.experiment_number, e.experiment_name, e.subject,
                 e.file_path,
                 s.verification_date, s.marks_obtained, s.feedback
          FROM submissions s
          JOIN experiments e ON s.experiment_id = e.Id
          WHERE s.student_id = ? 
            AND s.verification_status = 'Verified'
          ORDER BY s.verification_date DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$completed_experiments = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Completed Experiments - Sri Vasavi Engineering College</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/common.css">
<style>
    .completed-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        border-left: 4px solid #10b981;
    }
    
    .completed-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .experiment-title {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .checkmark {
        background: #10b981;
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        font-weight: bold;
    }
    
    .marks-badge {
        background: #d1fae5;
        color: #065f46;
        padding: 8px 20px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 16px;
    }
    
    .experiment-info {
        margin-bottom: 20px;
    }
    
    .info-row {
        display: flex;
        margin-bottom: 10px;
    }
    
    .info-label {
        width: 150px;
        color: #475569;
        font-weight: 600;
    }
    
    .info-value {
        color: #1e293b;
        flex: 1;
    }
    
    .feedback-section {
        background: #f0f9ff;
        padding: 20px;
        border-radius: 8px;
        margin-top: 20px;
        border: 1px solid #bae6fd;
    }
    
    .feedback-label {
        color: #0369a1;
        font-weight: 600;
        margin-bottom: 10px;
        display: block;
        font-size: 16px;
    }
    
    .feedback-text {
        color: #1e293b;
        white-space: pre-wrap;
        font-size: 15px;
        line-height: 1.6;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 40px;
        color: #64748b;
        font-size: 16px;
        background: #f8fafc;
        border-radius: 12px;
        border: 2px dashed #cbd5e1;
    }
    
    .empty-state h3 {
        color: #64748b;
        font-size: 18px;
        margin-bottom: 8px;
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
    <img src="../images/student.jpg" alt="Student Photo" class="student-photo">
  </header>

  <!-- TOP BAR -->
  <div class="topbar">
    <div>Welcome <strong><?php echo htmlspecialchars($_SESSION['name']); ?>...!</strong></div>
    <a href="logout.php" class="btn btn-primary">LOG OUT</a>
  </div>

  <!-- NAV BAR -->
  <nav class="navbar">
    <div class="nav-menu">
      <a href="updated_exp.php" class="nav-item">Updated Experiments</a>
      <a href="completed_exp.php" class="nav-item active">Completed Experiments</a>
      <a href="retake_exp.php" class="nav-item">Retake Experiments</a>
      <a href="profile.php" class="nav-item">Profile</a>
    </div>
  </nav>

  <!-- MAIN CONTENT -->
  <main class="main">
    <h1 class="page-title">Completed Experiments</h1>
    
    <?php if (empty($completed_experiments)): ?>
      <div class="empty-state">
        <h3>No Completed Experiments</h3>
        <p>You haven't completed any experiments yet. Submit experiments to see them here.</p>
      </div>
    <?php else: ?>
      <?php foreach ($completed_experiments as $exp): ?>
      <div class="completed-card">
        <div class="completed-header">
          <div class="experiment-title">
            <div class="checkmark">✓</div>
            <div>
              <h3 style="margin: 0;">
                Experiment <?php echo htmlspecialchars($exp['experiment_number']); ?>: 
                <?php echo htmlspecialchars($exp['experiment_name']); ?>
              </h3>
              <small style="color: #64748b;">
                Subject: <?php echo htmlspecialchars(ucfirst($exp['subject'])); ?>
              </small>
            </div>
          </div>
          <div class="marks-badge">
            Marks: <?php echo htmlspecialchars($exp['marks_obtained']); ?>/10
          </div>
        </div>
        
        <div class="experiment-info">
          <div class="info-row">
            <div class="info-label">Submitted On:</div>
            <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($exp['submitted_date'])); ?></div>
          </div>
          <div class="info-row">
            <div class="info-label">Verified On:</div>
            <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($exp['verification_date'])); ?></div>
          </div>
          <div class="info-row">
            <div class="info-label">Status:</div>
            <div class="info-value">
              <span style="color: #10b981; font-weight: 600;">✓ Completed</span>
            </div>
          </div>
        </div>
        
        <?php if (!empty($exp['feedback'])): ?>
        <div class="feedback-section">
          <span class="feedback-label">Feedback from Instructor:</span>
          <div class="feedback-text">
            <?php echo nl2br(htmlspecialchars($exp['feedback'])); ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </main>
</div>

<script>
// Add active class to current nav item
document.addEventListener('DOMContentLoaded', function() {
    const currentPage = window.location.pathname.split('/').pop();
    const navItems = document.querySelectorAll('.nav-item');
    
    navItems.forEach(item => {
        if (item.getAttribute('href') === currentPage) {
            item.classList.add('active');
        } else {
            item.classList.remove('active');
        }
    });
});
</script>

</body>
</html>
<?php $conn->close(); ?>