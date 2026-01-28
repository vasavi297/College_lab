<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Get retake experiments
$query = "SELECT s.*, e.Id as experiment_db_id, e.experiment_number, e.experiment_name, e.subject,
                 s.verification_date, s.feedback, s.retake_count, s.can_retake_again,
                 s.employee_id 
          FROM submissions s
          JOIN experiments e ON s.experiment_id = e.Id
          WHERE s.student_id = ? 
            AND s.verification_status = 'Retake'
            AND s.can_retake_again = 1 
          ORDER BY s.verification_date DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$retake_experiments = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$subject_paths = [
    'chemistry' => 'chemistry',
    'theory_of_machines' => 'Theoryofmachines',  
    'physics' => 'physics'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Retake Experiments - Sri Vasavi Engineering College</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/common.css">
<style>
    .retake-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        border-left: 4px solid #ef4444;
    }
    
    .retake-header {
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
    
    .retake-icon {
        background: #ef4444;
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
    
    .retake-badge {
        background: #fee2e2;
        color: #991b1b;
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
        background: #fef2f2;
        padding: 20px;
        border-radius: 8px;
        margin-top: 20px;
        border: 1px solid #fecaca;
    }
    
    .feedback-label {
        color: #991b1b;
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
    
    .action-section {
        margin-top: 25px;
        padding-top: 20px;
        border-top: 1px solid #e2e8f0;
        text-align: center;
    }
    
    .resubmit-btn {
        background: #3b82f6;
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
        font-size: 16px;
    }
    
    .resubmit-btn:hover {
        background: #2563eb;
        transform: translateY(-2px);
    }
    
    .resubmit-btn.disabled {
        background: #94a3b8;
        cursor: not-allowed;
        opacity: 0.7;
    }
    
    .resubmit-btn.disabled:hover {
        background: #94a3b8;
        transform: none;
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
    
    .attempt-badge {
        background: #dc2626;
        color: white;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        margin-left: 8px;
        vertical-align: middle;
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
      <a href="completed_exp.php" class="nav-item">Completed Experiments</a>
      <a href="retake_exp.php" class="nav-item active">Retake Experiments</a>
      <a href="profile.php" class="nav-item">Profile</a>
    </div>
  </nav>

  <!-- MAIN CONTENT -->
  <main class="main">
    <h1 class="page-title">Retake Experiments</h1>
    
    <?php if (empty($retake_experiments)): ?>
      <div class="empty-state">
        <h3>No Retake Experiments</h3>
        <p>You don't have any experiments that need retaking.</p>
      </div>
    <?php else: ?>
      <?php foreach ($retake_experiments as $exp): ?>
      <div class="retake-card">
        <div class="retake-header">
          <div class="experiment-title">
            <div class="retake-icon">↻</div>
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
          <div class="retake-badge">
            Needs Retake
            <?php if (isset($exp['retake_count']) && $exp['retake_count'] > 0): ?>
                <span class="attempt-badge">Attempt #<?php echo ($exp['retake_count'] + 1); ?></span>
            <?php endif; ?>
          </div>
        </div>
        
        <div class="experiment-info">
          <div class="info-row">
            <div class="info-label">Submitted On:</div>
            <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($exp['submitted_date'])); ?></div>
          </div>
          <div class="info-row">
            <div class="info-label">Feedback Given:</div>
            <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($exp['verification_date'])); ?></div>
          </div>
          <div class="info-row">
            <div class="info-label">Status:</div>
            <div class="info-value">
              <span style="color: #ef4444; font-weight: 600;">↻ Retake Required</span>
            </div>
          </div>
          <?php if (isset($exp['retake_count']) && $exp['retake_count'] > 0): ?>
          <div class="info-row">
            <div class="info-label">Previous Attempts:</div>
            <div class="info-value">
              <span style="color: #dc2626; font-weight: 600;">
                <?php echo $exp['retake_count']; ?> time(s)
              </span>
            </div>
          </div>
          <?php endif; ?>
        </div>
        
        <?php if (!empty($exp['feedback'])): ?>
        <div class="feedback-section">
          <span class="feedback-label">Feedback from Instructor:</span>
          <div class="feedback-text">
            <?php echo nl2br(htmlspecialchars($exp['feedback'])); ?>
          </div>
        </div>
        <?php endif; ?>
        
        <div class="action-section">
          <?php 
          $safe_subject = isset($subject_paths[$exp['subject']]) ? $subject_paths[$exp['subject']] : 'chemistry';
          
          // Check if retake is still allowed
          $can_retake = ($exp['can_retake_again'] ?? 1) == 1;
          $current_attempt = ($exp['retake_count'] ?? 0) + 1;
          
          // Build URL with all necessary parameters
          if ($can_retake) {
              $experiment_url = "experiments/" . $safe_subject . "/exp" . $exp['experiment_number'] . ".php?" . 
                                "retake_id=" . $exp['submission_id'] . 
                                "&subject=" . urlencode($exp['subject']) . 
                                "&exp_number=" . $exp['experiment_number'] . 
                                "&emp_id=" . $exp['employee_id'] . 
                                "&retake_count=" . ($exp['retake_count'] ?? 0) . 
                                "&is_retake=1";
          }
          ?>
          
          <?php if ($can_retake): ?>
            <a href="<?php echo $experiment_url; ?>" class="resubmit-btn">
                ↻ Resubmit This Experiment (Attempt #<?php echo $current_attempt; ?>)
            </a>
            <div style="color: #64748b; font-size: 14px; margin-top: 12px;">
                Please review the feedback above and submit the experiment again.
            </div>
          <?php else: ?>
            <button class="resubmit-btn disabled" disabled>
                ↻ Already Submitted
            </button>
            <div style="color: #64748b; font-size: 14px; margin-top: 12px;">
                This retake has already been submitted and is waiting for verification.
            </div>
          <?php endif; ?>
        </div>
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
    
    // Show success message if any
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('retake_success')) {
        alert('Retake submitted successfully! It is now pending verification.');
        // Remove success parameter from URL
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
    }
});
</script>

</body>
</html>
<?php $conn->close(); ?>