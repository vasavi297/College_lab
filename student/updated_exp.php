<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['name'])) {
    header("Location: login.php");
    exit();
}

// Get student's branch from session (already set during login)
$student_branch = $_SESSION['branch']; 

$subject = isset($_GET['subject']) ? htmlspecialchars($_GET['subject']) : '';

// Get current week
$current_week = date('W');
$current_year = date('Y');

$experiments = [];
if (!empty($subject)) {
    // Check if student is allowed to access this subject
    $allowed = true;
    
    // Only MECH students can access theory_of_machines
    if ($subject == 'theory_of_machines' && $student_branch != 'MECH') {
        $allowed = false;
        $access_denied = true;
    }
    
    if ($allowed) {
        // Get experiments enabled for current week
        $query = "SELECT e.Id as experiment_id, e.experiment_number, e.experiment_name, e.file_path,
                         we.enabled_until,
                         CASE 
                             WHEN we.experiment_id IS NOT NULL THEN 'enabled'
                             ELSE 'disabled'
                         END as status
                  FROM experiments e
                  LEFT JOIN weekly_experiments we ON e.Id = we.experiment_id
                    AND we.week_number = ? 
                    AND we.year = ? 
                    AND we.is_active = 1
                    AND CURDATE() <= we.enabled_until
                  WHERE e.subject = ? AND e.is_active = 1
                  ORDER BY e.experiment_number";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iis", $current_week, $current_year, $subject);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $experiments[] = $row;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Updated Experiments - Sri Vasavi Engineering College</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/common.css">
<link rel="stylesheet" href="../css/experiment-listing.css">
<style>
    .start-btn {
        background: #1a347a;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.3s ease;
        min-width: 140px;
    }

    .start-btn:hover {
        background: #152a5e;
        transform: translateY(-2px);
    }

    .start-btn:disabled {
        background: #94a3b8;
        cursor: not-allowed;
        transform: none;
    }

    .start-btn.disabled {
        background: #cbd5e1;
        color: #64748b;
        cursor: not-allowed;
    }

    .experiment-status {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        margin-left: 10px;
    }

    .status-enabled {
        background: #d1fae5;
        color: #065f46;
    }

    .status-disabled {
        background: #f1f5f9;
        color: #64748b;
    }

    .week-info {
        background: #e0f2fe;
        padding: 12px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-size: 14px;
        color: #0369a1;
        border-left: 4px solid #3b82f6;
    }
    
    .subject-selection {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 20px;
        margin-top: 20px;
    }
    
    .subject-card {
        width: 300px;
        background: white;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 25px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
    }
    
    .subject-card:hover {
        border-color: #1a347a;
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    
    .subject-card h3 {
        color: #1a347a;
        margin-bottom: 10px;
        font-size: 20px;
    }
    
    .subject-card p {
        color: #64748b;
        font-size: 14px;
    }
    
    .access-denied {
        background: #fee2e2;
        color: #dc2626;
        padding: 30px;
        border-radius: 10px;
        text-align: center;
        margin: 30px auto;
        border: 1px solid #fecaca;
        max-width: 500px;
    }
    
    .access-denied h3 {
        color: #dc2626;
        margin-bottom: 15px;
    }
    
    .back-btn {
        background: #6b7280;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        margin-bottom: 20px;
    }
    
    .back-btn:hover {
        background: #4b5563;
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
    <img src="student.jpg" alt="Student Photo" class="student-photo">
  </header>

  <!-- TOP BAR -->
  <div class="topbar">
    <div>Welcome <strong><?php echo htmlspecialchars($_SESSION['name']); ?>...!</strong> </div>
    <a href="logout.php" class="btn btn-primary">LOG OUT</a>
  </div>

  <!-- NAV BAR -->
  <nav class="navbar">
    <div class="nav-menu">
      <a href="updated_exp.php" class="nav-item active">Updated Experiments </a>
      <a href="completed_exp.php" class="nav-item">Completed Experiments</a>
      <a href="retake_exp.php" class="nav-item">Retake Experiments</a>
      <a href="profile.php" class="nav-item">Profile</a>
    </div>
  </nav>

  <!-- MAIN CONTENT -->
<main class="main">
    <?php if (empty($subject)): ?>
        <!-- Subject Selection Page -->
        <h1 class="page-title">Select Subject</h1>
        <div class="week-info">
            Current Week: Week <?php echo $current_week; ?> (<?php echo date('F d') . ' - ' . date('F d, Y', strtotime('sunday this week')); ?>)
        </div>
        <div class="subject-selection">
            <!-- Always show Chemistry for ALL students -->
            <div class="subject-card" onclick="selectSubject('chemistry')">
                <h3>Chemistry</h3>
                <p>Click to view Chemistry experiments</p>
            </div>
            
            <!-- Show Theory of Machines ONLY for MECH students -->
            <?php if ($student_branch == 'MECH'): ?>
                <div class="subject-card" onclick="selectSubject('theory_of_machines')">
                    <h3>Theory of Machines</h3>
                    <p>Click to view Theory of Machines experiments</p>
                </div>
            <?php endif; ?>
        </div>
        
    <?php else: ?>
        <!-- Experiments Page -->
        <?php if (isset($access_denied)): ?>
            <div class="access-denied">
                <h3>Access Denied</h3>
                <p>You don't have permission to access <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $subject))); ?>.</p>
                <p>Only MECH students can access Theory of Machines.</p>
                <button class="start-btn" onclick="goBackToSubjects()" style="margin-top: 20px;">Back to Subjects</button>
            </div>
        <?php else: ?>
            <h1 class="page-title">Experiments for <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $subject))); ?></h1>
            <div class="week-info">
                Week <?php echo $current_week; ?>: Only enabled experiments are available for submission
            </div>
            <button class="back-btn" onclick="goBackToSubjects()">← Back to Subjects</button>
            
            <div id="experimentContainer">
                <?php if (empty($experiments)): ?>
                    <div class="no-experiments">No experiments available for this subject.</div>
                <?php else: ?>
                    <div class="experiment-list">
                        <?php foreach ($experiments as $exp): ?>
                            <div class="experiment-row">
                                <div class="experiment-item">
                                    <strong>Experiment <?php echo htmlspecialchars($exp['experiment_number']); ?>:</strong> 
                                    <?php echo htmlspecialchars($exp['experiment_name']); ?>
                                    <span class="experiment-status <?php echo $exp['status'] == 'enabled' ? 'status-enabled' : 'status-disabled'; ?>">
                                        <?php echo $exp['status'] == 'enabled' ? 'Available this week' : 'Not available'; ?>
                                    </span>
                                </div>
                                <?php if ($exp['status'] == 'enabled'): ?>
                                    <button class="start-btn" onclick="startExperiment('<?php echo htmlspecialchars($exp['file_path']); ?>')">
                                        Start Experiment
                                    </button>
                                <?php else: ?>
                                    <button class="start-btn disabled" disabled title="This experiment is not enabled for this week">
                                        Not Available
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</main>

</div>

<script>
function selectSubject(subject) {
    window.location.href = `updated_exp.php?subject=${subject}`;
}

function goBackToSubjects() {
    window.location.href = 'updated_exp.php';
}

function startExperiment(filePath) {
    // Navigate to the experiment file
    window.location.href = filePath;
}

// Keyboard navigation support
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        goBackToSubjects();
    }
});
</script>

</body>
</html>
<?php
// Close database connection
$conn->close();