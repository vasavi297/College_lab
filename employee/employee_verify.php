<?php
session_start();
include '../db_connect.php';

// Authentication
if (!isset($_SESSION['employee_id']) || ($_SESSION['role'] ?? '') !== 'employee') {
    header('Location: employee_login.php');
    exit;
}

$employee_id = $_SESSION['employee_id'];
$employee_name = $_SESSION['employee_name'];

// Handle form submission for verification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $submission_id = intval($_POST['submission_id']);
    $action = $_POST['action']; // 'verify' or 'retake'
    $marks = isset($_POST['marks']) ? floatval($_POST['marks']) : null;
    $feedback = trim($_POST['feedback'] ?? '');
   
// Validate feedback is required for both
if (empty($feedback)) {
    $error = "Feedback is required for both verification and retake.";
} 
// Validate marks only for verification, not for retake
else if ($action === 'verify' && ($marks === null || $marks < 0 || $marks > 10)) {
    $error = "Marks must be between 0 and 10 for verification.";
} else {
    // Update submission
    if ($action === 'verify') {
        $status = 'Verified';
        $sql = "UPDATE submissions 
                SET verification_status = ?, 
                    marks_obtained = ?, 
                    feedback = ?,
                    verification_date = NOW(),
                    can_retake_again = 0  -- No retake needed for verified submissions
                WHERE submission_id = ? AND employee_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdsii", $status, $marks, $feedback, $submission_id, $employee_id);
    } elseif ($action === 'retake') {
        $status = 'Retake';
        $sql = "UPDATE submissions 
                SET verification_status = ?, 
                    feedback = ?,
                    verification_date = NOW(),
                    marks_obtained = NULL,  -- Clear marks for retake
                    can_retake_again = 1,   -- CRITICAL: Allow student to retake
                    last_retake_date = NOW()
                WHERE submission_id = ? AND employee_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssii", $status, $feedback, $submission_id, $employee_id);
    }
    
    if ($stmt->execute()) {
        $success = "Submission " . ($action === 'verify' ? 'verified' : 'marked for retake') . " successfully!";
        // Force refresh to remove from pending list
        header("Location: employee_verify.php?status=" . ($status === 'Retake' ? 'Pending' : $status) . "&success=1");
        exit();
    } else {
        $error = "Failed to update submission: " . $stmt->error;
    }
    $stmt->close();
}
}

// Get filter status
$status = $_GET['status'] ?? 'Pending';
$view_id = $_GET['view'] ?? 0;

// Get submission counts for filter badges
$count_sql = "SELECT 
                SUM(CASE WHEN verification_status = 'Pending' THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN verification_status = 'Verified' THEN 1 ELSE 0 END) as verified_count,
                SUM(CASE WHEN verification_status = 'Retake' THEN 1 ELSE 0 END) as retake_count,
                COUNT(*) as total_count
              FROM submissions 
              WHERE employee_id = ?";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("i", $employee_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result()->fetch_assoc();
$count_stmt->close();

$pending_count = $count_result['pending_count'] ?? 0;
$verified_count = $count_result['verified_count'] ?? 0;
$retake_count = $count_result['retake_count'] ?? 0;
$total_count = $count_result['total_count'] ?? 0;

// Get submissions based on filter
if ($status === 'all') {
    $sql = "SELECT s.*, stu.name as student_name, stu.roll_number, stu.branch, stu.semester,
                   e.experiment_number, e.experiment_name, e.subject
            FROM submissions s
            JOIN students stu ON s.student_id = stu.student_id
            JOIN experiments e ON s.experiment_id = e.Id
            WHERE s.employee_id = ?
            ORDER BY s.submitted_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $employee_id);
} else {
    $sql = "SELECT s.*, stu.name as student_name, stu.roll_number, stu.branch, stu.semester,
                   e.experiment_number, e.experiment_name, e.subject
            FROM submissions s
            JOIN students stu ON s.student_id = stu.student_id
            JOIN experiments e ON s.experiment_id = e.Id
            WHERE s.employee_id = ? AND s.verification_status = ?
            ORDER BY s.submitted_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $employee_id, $status);
}

$stmt->execute();
$submissions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get specific submission for viewing if requested
$submission_detail = null;
if ($view_id > 0) {
    $detail_sql = "SELECT s.*, stu.name as student_name, stu.roll_number, stu.branch, stu.semester,
                          e.experiment_number, e.experiment_name, e.subject
                   FROM submissions s
                   JOIN students stu ON s.student_id = stu.student_id
                   JOIN experiments e ON s.experiment_id = e.Id
                   WHERE s.submission_id = ? AND s.employee_id = ?";
    $detail_stmt = $conn->prepare($detail_sql);
    $detail_stmt->bind_param("ii", $view_id, $employee_id);
    $detail_stmt->execute();
    $result = $detail_stmt->get_result();
    $submission_detail = $result->fetch_assoc();
    $detail_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify Students - Employee Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/common.css">
    <style>
        /* Status Filter */
        .status-filter {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }
        
        .status-filter-btn {
            padding: 10px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            background: white;
            color: #64748b;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .status-filter-btn:hover {
    border-color: #2563eb;
    color: #2563eb;
}

.status-filter-btn.active {
    background: #2563eb;
    color: white;
    border-color: #2563eb;
}
        
        .filter-badge {
            background: #ef4444;
            color: white;
            font-size: 12px;
            padding: 2px 6px;
            border-radius: 10px;
            margin-left: 5px;
        }
        
        /* Submission Cards */
        .submission-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            cursor: pointer;
        }
        
        .submission-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .submission-card.pending {
            border-left: 4px solid #f59e0b;
        }
        
        .submission-card.verified {
            border-left: 4px solid #10b981;
        }
        
        .submission-card.retake {
            border-left: 4px solid #ef4444;
        }
        
        .submission-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .submission-info {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .info-item {
            padding: 10px;
            background: #f8fafc;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .info-item strong {
            color: #475569;
        }
        
        /* Buttons */
                /* Buttons */
       
        
        .btn-view {
            background: #2563eb;
            color: white;
            padding: 8px 16px;
            font-size: 14px;
        }
        
        .btn-view:hover {
            background: #1d4ed8;
        }
        
        .btn-verify {
            background: #10b981;
            color: white;
            padding: 8px 16px;
            font-size: 14px;
        }
        
        .btn-verify:hover {
            background: #059669;
        }
        
        .btn-retake {
            background: #ef4444;
            color: white;
            padding: 8px 16px;
            font-size: 14px;
        }
        
        .btn-retake:hover {
            background: #dc2626;
        }
        
       
        
        /* Modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.95);
            z-index: 10000;
            overflow-y: auto;
        }
        
        /* Status Badge */
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-verified {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-retake {
            background: #fee2e2;
            color: #991b1b;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 40px;
            color: #64748b;
            font-size: 16px;
            background: #f8fafc;
            border-radius: 12px;
            border: 2px dashed #cbd5e1;
        }
        
        /* Messages */
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
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
        
        /* Fullscreen Viewer Styles */
        .fullscreen-viewer {
            width: 100vw;
            height: 100vh;
            background: white;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 10001;
            display: flex;
            flex-direction: column;
        }
        
        .viewer-header {
            background: #2563eb;
            color: white;
            padding: 15px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .viewer-content {
            flex: 1;
            overflow-y: auto;
            padding: 25px;
            background: #f8fafc;
        }
        
        .submission-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .viewer-footer {
            background: #f8fafc;
            padding: 12px 25px;
            border-top: 1px solid #e2e8f0;
            font-size: 0.85rem;
            color: #64748b;
            display: flex;
            justify-content: space-between;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .submission-header {
                flex-direction: column;
                gap: 10px;
            }
            
            .submission-info {
                grid-template-columns: 1fr;
            }
            
            .status-filter {
                flex-direction: column;
            }
            
            .status-filter-btn {
                width: 100%;
                text-align: center;
            }
            
            .viewer-header {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }
            
            .viewer-footer {
                flex-direction: column;
                gap: 10px;
            }
        }
        
        /* Submission HTML Styling */
        .student-submission-html {
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }
        
        .student-submission-html table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        .student-submission-html th,
        .student-submission-html td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }
        
        .student-submission-html .header-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        
        .student-submission-html h2 {
            text-align: center;
            margin-top: 0;
            margin-bottom: 20px;
        }
        
        .student-submission-html h3,
        .student-submission-html h4 {
            margin-top: 20px;
            margin-bottom: 10px;
        }
        
        .student-submission-html p {
            margin: 10px 0;
        }
    </style>
</head>
<body>
<div class="page">
    <!-- Header -->
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

    <!-- Top Bar -->
 <div class="topbar">
    <div>Welcome <strong><?php echo htmlspecialchars($employee_name); ?>...!</strong></div>
    <a href="employee_logout.php" class="btn btn-primary">LOG OUT</a>
  </div>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-menu">
            <a href="employee_dashboard.php" class="nav-item">Dashboard</a>
            <a href="employee_profile.php" class="nav-item">Profile</a>
            <a href="employee_update_experiment.php" class="nav-item">Update Experiments</a>
            <a href="employee_verify.php" class="nav-item active">Verify Students</a>
            <a href="employee_schedule.php" class="nav-item">Schedule Exams</a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main">
        <h1 class="page-title">Verify Student Submissions</h1>
        
        <!-- Messages -->
        <?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
            <div class="message success">Submission updated successfully!</div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="message success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Status Filter -->
        <div class="status-filter">
            <a href="?status=Pending" class="status-filter-btn <?php echo $status == 'Pending' ? 'active' : ''; ?>">
                Pending 
                <span class="filter-badge"><?php echo $pending_count; ?></span>
            </a>
            <a href="?status=Verified" class="status-filter-btn <?php echo $status == 'Verified' ? 'active' : ''; ?>">
                Verified 
                <span class="filter-badge"><?php echo $verified_count; ?></span>
            </a>
            <a href="?status=Retake" class="status-filter-btn <?php echo $status == 'Retake' ? 'active' : ''; ?>">
                Retake 
                <span class="filter-badge"><?php echo $retake_count; ?></span>
            </a>
            <a href="?status=all" class="status-filter-btn <?php echo $status == 'all' ? 'active' : ''; ?>">
                All 
                <span class="filter-badge"><?php echo $total_count; ?></span>
            </a>
        </div>

        <!-- Submissions List -->
        <?php if (empty($submissions)): ?>
            <div class="empty-state">
                <h3>No Submissions Found</h3>
                <p>There are no <?php echo $status == 'all' ? '' : $status . ' '; ?>submissions assigned to you.</p>
            </div>
        <?php else: ?>
            <?php foreach ($submissions as $sub): ?>
            <div class="submission-card <?php echo strtolower($sub['verification_status']); ?>"
                 onclick="window.location.href='?view=<?php echo $sub['submission_id']; ?>&status=<?php echo $status; ?>'">
                
                <div class="submission-header">
                    <div>
                        <h3 style="margin: 0 0 5px 0;"><?php echo htmlspecialchars($sub['experiment_number'] . ': ' . $sub['experiment_name']); ?></h3>
                        <small style="color: #64748b;">
                            <?php echo htmlspecialchars($sub['subject']); ?> | 
                            Submitted: <?php echo date('d/m/Y H:i', strtotime($sub['submitted_date'])); ?>
                        </small>
                    </div>
                    <span class="status-badge status-<?php echo strtolower($sub['verification_status']); ?>">
                        <?php echo htmlspecialchars($sub['verification_status']); ?>
                    </span>
                </div>
                
                <div class="submission-info">
                    <div class="info-item">
                        <strong>Student:</strong> <?php echo htmlspecialchars($sub['student_name']); ?>
                    </div>
                    <div class="info-item">
                        <strong>Roll No:</strong> <?php echo htmlspecialchars($sub['roll_number']); ?>
                    </div>
                    <div class="info-item">
                        <strong>Branch:</strong> <?php echo htmlspecialchars($sub['branch']); ?>
                    </div>
                    <?php if ($sub['marks_obtained']): ?>
                    <div class="info-item">
                        <strong>Marks:</strong> <?php echo $sub['marks_obtained']; ?>/10
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($sub['verification_status'] == 'Pending'): ?>
                <div style="margin-top: 10px; color: #f59e0b; font-size: 14px; font-weight: 600;">
                    ⚠️ Click to view and verify this submission
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</div>

<!-- Fullscreen Submission Viewer -->
<?php if ($submission_detail): ?>
<div class="fullscreen-viewer" id="fullscreenViewer">
    <!-- Header -->
    <div class="viewer-header">
        <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
            <h2 style="margin: 0; font-size: 1.5rem; color: white;">Submission Viewer</h2>
            <div style="display: flex; gap: 15px; font-size: 0.9rem; flex-wrap: wrap;">
                <span><strong>Student:</strong> <?php echo htmlspecialchars($submission_detail['student_name']); ?></span>
                <span><strong>Experiment:</strong> <?php echo htmlspecialchars($submission_detail['experiment_number'] . ': ' . $submission_detail['experiment_name']); ?></span>
                <span><strong>Status:</strong> 
                    <span class="status-badge status-<?php echo strtolower($submission_detail['verification_status']); ?>">
                        <?php echo htmlspecialchars($submission_detail['verification_status']); ?>
                    </span>
                </span>
            </div>
        </div>
        
        <div style="display: flex; gap: 10px; align-items: center;">
            <!-- Verification Controls -->
            <?php if ($submission_detail['verification_status'] == 'Pending'): ?>
            <div style="display: flex; gap: 10px; margin-right: 20px;">
                <button onclick="showVerificationForm()" style="background: #10b981; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 5px;">
                    <span>✓</span> Verify
                </button>
                <button onclick="showRetakeForm()" style="background: #ef4444; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 5px;">
                    <span>↻</span> Retake
                </button>
            </div>
            <?php endif; ?>
            <?php if (isset($submission_detail['retake_count']) && $submission_detail['retake_count'] > 0): ?>
<div style="margin-bottom: 15px; padding: 10px; background: #fef3c7; border-radius: 6px; border-left: 4px solid #f59e0b;">
    <strong>Retake Information:</strong> 
    This is attempt #<?php echo ($submission_detail['retake_count'] + 1); ?>.
    Previous attempts: <?php echo $submission_detail['retake_count']; ?>.
</div>
<?php endif; ?>
            
            <a href="?status=<?php echo $status; ?>" 
               style="background: rgba(255,255,255,0.2); color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 600; border: 1px solid rgba(255,255,255,0.3);">
               Close (ESC)
            </a>
        </div>
    </div>
<!-- Content -->
<div class="viewer-content">
    <div class="submission-container">
        <div class="student-submission-html">
            <?php 
            // Get and process the submission HTML
            $html_content = $submission_detail['submission_data'] ?? '<p style="color: #64748b; text-align: center; padding: 40px;">No submission content found.</p>';
            
            // Fix the newline issue - replace literal \n with actual line breaks
            $html_content = str_replace('\n', "\n", $html_content);
            $html_content = str_replace("\\n", "\n", $html_content);
            
            // Also decode any other escaped characters
            $html_content = stripslashes($html_content);
            
            // Decode HTML entities
            $html_content = html_entity_decode($html_content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            
            // Fix image paths to point to shared images folder
            $html_content = str_replace('src="../../images/', 'src="../images/', $html_content);
            $html_content = str_replace("src='../../images/", "src='../images/", $html_content);
            
            // Output the HTML
            echo $html_content;
            ?>
        </div>
    </div>
</div>
    
    <!-- Footer -->
    <div class="viewer-footer">
        <div>
            
            <strong>Roll No:</strong> <?php echo htmlspecialchars($submission_detail['roll_number']); ?> | 
            <strong>Branch:</strong> <?php echo htmlspecialchars($submission_detail['branch']); ?> | 
            <strong>Submitted:</strong> <?php echo date('d/m/Y H:i', strtotime($submission_detail['submitted_date'])); ?>
            <?php if ($submission_detail['verification_date']): ?>
            | <strong>Verified:</strong> <?php echo date('d/m/Y H:i', strtotime($submission_detail['verification_date'])); ?>
            <?php endif; ?>
        </div>
        <div>
            <strong>Use:</strong> ESC to close | Ctrl+Mouse Wheel to zoom
        </div>
    </div>
</div>

<!-- Verification Form Modal -->
<div id="verificationModal" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.8); z-index: 10002; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; padding: 30px; width: 90%; max-width: 500px; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
        <h3 style="margin-top: 0; color: #3b82f6; border-bottom: 1px solid #e2e8f0; padding-bottom: 15px;">
            <span id="modalTitle">Verify Submission</span>
        </h3>
        
        <form method="POST" id="verifyForm">
            <input type="hidden" name="submission_id" value="<?php echo $submission_detail['submission_id']; ?>">
            <input type="hidden" name="action" id="actionType" value="verify">
            
            <div id="marksSection" style="margin-bottom: 20px;">
                <label for="modalMarks" style="display: block; margin-bottom: 8px; font-weight: 600; color: #3b82f6;">Marks (0-10)</label>
                <input type="number" id="modalMarks" name="marks" min="0" max="10" step="0.5" 
                       placeholder="Enter marks" required
                       style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 1rem;">
                <div style="color: #64748b; font-size: 0.85rem; margin-top: 5px;">
                    Enter marks between 0 and 10. Decimal points allowed.
                </div>
            </div>
            
            <div style="margin-bottom: 25px;">
                <label for="modalFeedback" style="display: block; margin-bottom: 8px; font-weight: 600; color: #3b82f6;">Feedback / Comments</label>
                <textarea id="modalFeedback" name="feedback" rows="5" 
                          placeholder="Provide constructive feedback to the student..."
                          style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-family: inherit; font-size: 0.95rem;"><?php 
                          echo htmlspecialchars($submission_detail['feedback'] ?? ''); ?></textarea>
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="hideVerificationModal()" 
                        style="background: #64748b; color: white; border: none; padding: 12px 24px; border-radius: 6px; cursor: pointer; font-weight: 600;">
                    Cancel
                </button>
                <button type="submit" id="submitBtn"
                        style="background: #10b981; color: white; border: none; padding: 12px 24px; border-radius: 6px; cursor: pointer; font-weight: 600;">
                    Submit Verification
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Close viewer with ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (document.getElementById('verificationModal').style.display === 'block') {
            hideVerificationModal();
        } else {
            window.location.href = '?status=<?php echo $status; ?>';
        }
    }
    
    // Zoom with Ctrl + Mouse Wheel
    if (e.ctrlKey && (e.key === '+' || e.key === '-')) {
        e.preventDefault();
        const container = document.querySelector('.submission-container');
        let currentScale = parseFloat(container.style.transform?.replace('scale(', '')?.replace(')', '')) || 1;
        
        if (e.key === '+') {
            currentScale = Math.min(currentScale + 0.1, 2);
        } else if (e.key === '-') {
            currentScale = Math.max(currentScale - 0.1, 0.5);
        }
        
        container.style.transform = `scale(${currentScale})`;
        container.style.transformOrigin = 'top left';
    }
});

// Show verification form
// Show verification form
function showVerificationForm() {
    document.getElementById('actionType').value = 'verify';
    document.getElementById('modalTitle').textContent = 'Verify Submission';
    document.getElementById('marksSection').style.display = 'block';
    document.getElementById('modalMarks').required = true;  // Add this line
    document.getElementById('submitBtn').style.background = '#10b981';
    document.getElementById('submitBtn').textContent = 'Submit Verification';
    document.getElementById('verificationModal').style.display = 'flex';
    document.getElementById('modalMarks').focus();
}

// Show retake form
function showRetakeForm() {
    document.getElementById('actionType').value = 'retake';
    document.getElementById('modalTitle').textContent = 'Mark for Retake';
    document.getElementById('marksSection').style.display = 'none';  // Hide marks
    document.getElementById('modalMarks').required = false;  // Marks not required
    document.getElementById('submitBtn').style.background = '#ef4444';
    document.getElementById('submitBtn').textContent = 'Mark for Retake';
    document.getElementById('verificationModal').style.display = 'flex';
    document.getElementById('modalFeedback').focus();
}



// Hide verification modal
function hideVerificationModal() {
    document.getElementById('verificationModal').style.display = 'none';
}

// Form submission with confirmation
document.getElementById('verifyForm').addEventListener('submit', function(e) {
    const actionType = document.getElementById('actionType').value;
    let confirmMessage = '';
    
    if (actionType === 'verify') {
        const marks = document.getElementById('modalMarks').value;
        if (!marks || marks < 0 || marks > 10) {
            alert('Please enter valid marks between 0 and 10');
            e.preventDefault();
            return;
        }
        confirmMessage = 'Mark this submission as verified with ' + marks + ' marks?';
    } else {
        confirmMessage = 'Ask student to retake this experiment?';
    }
    
    if (!confirm(confirmMessage)) {
        e.preventDefault();
    }
});

// Close when clicking outside verification modal
document.getElementById('verificationModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideVerificationModal();
    }
});

// Reset zoom on page load
document.addEventListener('DOMContentLoaded', function() {
    const container = document.querySelector('.submission-container');
    if (container) {
        container.style.transform = 'scale(1)';
    }
});
</script>
<?php endif; ?>

<script>
// Add click event to prevent card clicks when clicking on links
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.submission-card a, .submission-card button').forEach(el => {
        el.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
    
    // Show success message from URL parameter
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('success') === '1') {
        setTimeout(() => {
            const messages = document.querySelectorAll('.message.success');
            if (messages.length > 0) {
                messages[0].style.display = 'none';
            }
        }, 3000);
    }
});
</script>

</body>
</html>
<?php $conn->close(); ?> 