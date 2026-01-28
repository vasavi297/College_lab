<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['employee_id']) || ($_SESSION['role'] ?? '') !== 'employee') {
    header('Location: employee_login.php');
    exit;
}

// NORMALIZATION FUNCTION - handles all subject variations
function normalizeSubject($subject) {
    $subject = trim(strtolower($subject));
    $subject = preg_replace('/[\s_]+/', '', $subject); // Remove all spaces and underscores
    return $subject;
}

$employee_id = $_SESSION['employee_id'];
$employee_name = $_SESSION['employee_name'];

// Get employee's assigned subject/lab
$employee_sql = "SELECT assigned_lab FROM employees WHERE employee_id = ?";
$employee_stmt = $conn->prepare($employee_sql);
$employee_stmt->bind_param("i", $employee_id);
$employee_stmt->execute();
$employee_result = $employee_stmt->get_result();
$employee_data = $employee_result->fetch_assoc();

$assigned_subject_raw = trim($employee_data['assigned_lab'] ?? '');
$assigned_normalized = normalizeSubject($assigned_subject_raw);
$employee_stmt->close();

// Get current week and year
$current_week = date('W');
$current_year = date('Y');
$current_date = date('Y-m-d');

// Handle form submission to enable experiment with custom timing
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $assigned_normalized) {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $experiment_id = $_POST['experiment_id'];
        $weekly_id = $_POST['weekly_id'] ?? 0;
        
        if ($action == 'enable') {
            // Get custom dates from form
            $start_date = $_POST['start_date'] ?? date('Y-m-d');
            $end_date = $_POST['end_date'] ?? date('Y-m-d', strtotime('+7 days'));
            
            // Calculate week number from start date
            $week_number = date('W', strtotime($start_date));
            $year = date('Y', strtotime($start_date));
            
            // Validate dates
            if (strtotime($start_date) > strtotime($end_date)) {
                $error = "Start date cannot be after end date.";
            } else {
                // Check if already enabled for same week/year
                $check_sql = "SELECT * FROM weekly_experiments 
                              WHERE experiment_id = ? 
                              AND week_number = ? 
                              AND year = ?
                              AND is_active = 1";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("iii", $experiment_id, $week_number, $year);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows == 0) {
                    $enable_sql = "INSERT INTO weekly_experiments 
                                  (experiment_id, week_number, year, enabled_date, 
                                   enabled_by, enabled_until) 
                                  VALUES (?, ?, ?, ?, ?, ?)";
                    $enable_stmt = $conn->prepare($enable_sql);
                    $enable_stmt->bind_param("iiisss", $experiment_id, $week_number, 
                                            $year, $start_date, $employee_id, $end_date);
                    
                    if ($enable_stmt->execute()) {
                        $success = "✅ Experiment enabled! Available from " . 
                                  date('d/m/Y', strtotime($start_date)) . " to " . 
                                  date('d/m/Y', strtotime($end_date));
                    } else {
                        $error = "Failed to enable experiment. Please try again.";
                    }
                    $enable_stmt->close();
                } else {
                    $error = "This experiment is already enabled for week $week_number of $year.";
                }
                $check_stmt->close();
            }
            
        } elseif ($action == 'disable') {
            // Disable experiment by weekly_id
            if ($weekly_id > 0) {
                $disable_sql = "UPDATE weekly_experiments 
                               SET is_active = 0 
                               WHERE weekly_id = ?";
                $disable_stmt = $conn->prepare($disable_sql);
                $disable_stmt->bind_param("i", $weekly_id);
                
                if ($disable_stmt->execute()) {
                    $success = "✅ Experiment disabled.";
                } else {
                    $error = "Failed to disable experiment. Please try again.";
                }
                $disable_stmt->close();
            }
        }
    }
}

// Get all experiments for the employee's assigned subject
if ($assigned_normalized) {
    $experiments_sql = "SELECT e.Id, e.subject, e.experiment_number, e.experiment_name,
                               we.weekly_id, we.is_active as is_enabled, 
                               we.enabled_date, we.enabled_until,
                               we.week_number, we.year
                        FROM experiments e
                        LEFT JOIN weekly_experiments we ON e.Id = we.experiment_id 
                            AND we.is_active = 1
                        WHERE e.is_active = 1 
                        AND LOWER(REPLACE(REPLACE(TRIM(e.subject), ' ', ''), '_', '')) = ?
                        ORDER BY e.experiment_number";
    $experiments_stmt = $conn->prepare($experiments_sql);
    $experiments_stmt->bind_param("s", $assigned_normalized);
    $experiments_stmt->execute();
    $experiments_result = $experiments_stmt->get_result();
    $all_experiments = $experiments_result->fetch_all(MYSQLI_ASSOC);
    $experiments_stmt->close();
} else {
    $all_experiments = [];
    $error = "No subject assigned to your account. Please contact administrator.";
}

// Count enabled experiments
$enabled_count = 0;
foreach ($all_experiments as $exp) {
    if (!empty($exp['is_enabled'])) {
        $enabled_count++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Update Experiments - Employee Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/common.css">
<style>
/* ================= UPDATE EXPERIMENT PAGE ================= */
.info-card {
    background: #e0f2fe;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 25px;
    border-left: 4px solid #3b82f6;
}

.info-card h3 {
    margin: 0 0 10px 0;
    color: #0369a1;
}

.info-card p {
    color: #64748b;
    font-size: 14px;
    margin-bottom: 5px;
}

/* Experiment List */
.experiment-section {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.experiment-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.experiment-item {
    padding: 20px;
    background: #f8fafc;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    transition: all 0.3s ease;
}

.experiment-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
}

.experiment-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
    flex-wrap: wrap;
    gap: 15px;
}

.experiment-title {
    font-weight: 600;
    color: #1e293b;
    font-size: 18px;
}

.experiment-subject {
    display: inline-block;
    background: #e0e7ff;
    color: #4f46e5;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    margin-right: 10px;
}

.experiment-number {
    color: #64748b;
    font-weight: 600;
}

.experiment-actions {
    display: flex;
    gap: 10px;
}

.action-btn {
    padding: 8px 20px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all 0.3s ease;
    min-width: 100px;
}

.enable-btn {
    background: #10b981;
    color: white;
}

.enable-btn:hover {
    background: #059669;
    transform: translateY(-2px);
}

.disable-btn {
    background: #ef4444;
    color: white;
}

.disable-btn:hover {
    background: #dc2626;
    transform: translateY(-2px);
}

/* Enable Form */
.enable-form {
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: 8px;
    padding: 20px;
    margin-top: 15px;
    display: none;
}

.date-inputs {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 15px;
}

.date-input {
    display: flex;
    flex-direction: column;
}

.date-input label {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 5px;
    color: #0369a1;
}

.date-input input {
    padding: 10px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 14px;
}

.form-buttons {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.cancel-form-btn {
    background: #64748b;
    color: white;
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}

.submit-btn {
    background: #3b82f6;
    color: white;
    padding: 8px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
}

/* Enabled Info */
.enabled-info {
    background: #d1fae5;
    border: 1px solid #a7f3d0;
    border-radius: 8px;
    padding: 15px;
    margin-top: 10px;
}

.enabled-badge {
    display: inline-block;
    background: #10b981;
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 10px;
}

.enabled-dates {
    font-size: 14px;
    color: #065f46;
}

.enabled-dates span {
    font-weight: 600;
}

.days-remaining {
    font-size: 13px;
    color: #059669;
    font-weight: 600;
    margin-top: 5px;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #94a3b8;
    font-style: italic;
    background: #f8fafc;
    border-radius: 8px;
    border: 2px dashed #cbd5e1;
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

/* Search Box */
.search-box {
    margin-bottom: 20px;
}

.search-box input {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 15px;
}

.search-box input:focus {
    outline: none;
    border-color: #3b82f6;
}

/* Confirmation Dialog */
.confirm-dialog {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.dialog-content {
    background: white;
    padding: 30px;
    border-radius: 12px;
    max-width: 400px;
    width: 90%;
    text-align: center;
}

.dialog-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 20px;
}

.cancel-btn {
    background: #64748b;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}

.confirm-btn {
    background: #ef4444;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
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
      <a href="employee_update_experiment.php" class="nav-item active">Update Experiments</a>
      <a href="employee_verify.php" class="nav-item">Verify Students</a>
      <a href="employee_schedule.php" class="nav-item">Schedule Exams</a>
    </div>
  </nav>

  <!-- MAIN CONTENT -->
  <main class="main">
   <div class="experiment-section">
      <h2 style="margin-top: 0; margin-bottom: 20px;">All Experiments</h2>
      
      <div class="search-box">
        <input type="text" id="searchInput" placeholder="Search experiments by name or number...">
      </div>
      
      <?php if (empty($all_experiments)): ?>
        <div class="empty-state">
          No experiments found for your assigned subject.<br>
          <?php if (!$assigned_normalized): ?>
            Your account has no assigned subject/lab. Contact administrator.
          <?php else: ?>
            No experiments available for "<?php echo htmlspecialchars($assigned_subject_raw); ?>".
          <?php endif; ?>
        </div>
      <?php else: ?>
        <div class="experiment-list" id="experimentList">
          <?php foreach ($all_experiments as $exp): 
            $is_enabled = !empty($exp['is_enabled']);
            $enabled_date = $exp['enabled_date'] ?? '';
            $enabled_until = $exp['enabled_until'] ?? '';
            $week_number = $exp['week_number'] ?? '';
            $year = $exp['year'] ?? '';
            
            if ($is_enabled && $enabled_until) {
                $days_left = floor((strtotime($enabled_until) - time()) / (60 * 60 * 24));
                if ($days_left > 0) {
                    $days_text = "$days_left days remaining";
                } elseif ($days_left == 0) {
                    $days_text = "Expires today";
                } else {
                    $days_text = "Expired " . abs($days_left) . " days ago";
                }
            }
          ?>
          <div class="experiment-item" data-name="<?php echo strtolower($exp['experiment_name']); ?>" data-number="<?php echo $exp['experiment_number']; ?>">
            <div class="experiment-header">
              <div>
                <div class="experiment-title">
                  <span class="experiment-subject"><?php echo htmlspecialchars(ucfirst($exp['subject'])); ?></span>
                  <span class="experiment-number">Exp <?php echo htmlspecialchars($exp['experiment_number']); ?>:</span>
                  <?php echo htmlspecialchars($exp['experiment_name']); ?>
                </div>
              </div>
              
              <div class="experiment-actions">
                <?php if (!$is_enabled): ?>
                  <button type="button" class="action-btn enable-btn" onclick="showEnableForm(<?php echo $exp['Id']; ?>)">
                    Set Timing
                  </button>
                <?php else: ?>
                  <form method="POST" style="margin: 0;">
                    <input type="hidden" name="action" value="disable">
                    <input type="hidden" name="weekly_id" value="<?php echo $exp['weekly_id']; ?>">
                    <input type="hidden" name="experiment_id" value="<?php echo $exp['Id']; ?>">
                    <button type="button" class="action-btn disable-btn" onclick="confirmDisable(this)">
                      Disable
                    </button>
                  </form>
                <?php endif; ?>
              </div>
            </div>
            
            <!-- Enable Form (Hidden by default) -->
            <div id="enableForm-<?php echo $exp['Id']; ?>" class="enable-form">
              <h4 style="margin-top: 0; color: #0369a1;">Set Availability Dates</h4>
              <form method="POST">
                <input type="hidden" name="action" value="enable">
                <input type="hidden" name="experiment_id" value="<?php echo $exp['Id']; ?>">
                
                <div class="date-inputs">
                  <div class="date-input">
                    <label for="start_date_<?php echo $exp['Id']; ?>">Start Date:</label>
                    <input type="date" id="start_date_<?php echo $exp['Id']; ?>" 
                           name="start_date" 
                           value="<?php echo date('Y-m-d'); ?>" 
                           min="<?php echo date('Y-m-d'); ?>" 
                           required>
                  </div>
                  <div class="date-input">
                    <label for="end_date_<?php echo $exp['Id']; ?>">End Date:</label>
                    <input type="date" id="end_date_<?php echo $exp['Id']; ?>" 
                           name="end_date" 
                           value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>" 
                           min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" 
                           required>
                  </div>
                </div>
                
                <div class="form-buttons">
                  <button type="button" class="cancel-form-btn" onclick="hideEnableForm(<?php echo $exp['Id']; ?>)">
                    Cancel
                  </button>
                  <button type="submit" class="submit-btn">
                    Enable Experiment
                  </button>
                </div>
              </form>
            </div>
            
            <?php if ($is_enabled): ?>
            <div class="enabled-info">
              <span class="enabled-badge">✓ Enabled</span>
              <div class="enabled-dates">
                <span>Week:</span> <?php echo $week_number; ?> of <?php echo $year; ?><br>
                <span>Available from:</span> <?php echo date('d/m/Y', strtotime($enabled_date)); ?><br>
                <span>Available until:</span> <?php echo date('d/m/Y', strtotime($enabled_until)); ?>
              </div>
              <div class="days-remaining">
                <?php echo $days_text ?? ''; ?>
              </div>
            </div>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </main>
</div>

<!-- Confirmation Dialog -->
<div id="confirmDialog" class="confirm-dialog">
  <div class="dialog-content">
    <h3 style="margin-top: 0; color: #1e293b;">Disable Experiment</h3>
    <p>Are you sure you want to disable this experiment?</p>
    <p style="font-size: 14px; color: #64748b;">Students will no longer be able to submit this experiment.</p>
    <div class="dialog-buttons">
      <button type="button" class="cancel-btn" onclick="hideConfirmDialog()">Cancel</button>
      <button type="button" class="confirm-btn" onclick="proceedDisable()">Yes, Disable</button>
    </div>
  </div>
</div>

<script>
// Search functionality
document.getElementById('searchInput').addEventListener('keyup', function() {
    const searchTerm = this.value.toLowerCase();
    const experimentItems = document.querySelectorAll('#experimentList .experiment-item');
    
    experimentItems.forEach(item => {
        const name = item.getAttribute('data-name');
        const number = item.getAttribute('data-number');
        
        if (name.includes(searchTerm) || number.includes(searchTerm)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});

// Show/hide enable form
function showEnableForm(experimentId) {
    // Hide all other forms first
    document.querySelectorAll('.enable-form').forEach(form => {
        form.style.display = 'none';
    });
    
    // Show the selected form
    const form = document.getElementById('enableForm-' + experimentId);
    if (form) {
        form.style.display = 'block';
        
        // Set minimum end date based on start date
        const startDateInput = form.querySelector('input[name="start_date"]');
        const endDateInput = form.querySelector('input[name="end_date"]');
        
        startDateInput.addEventListener('change', function() {
            const startDate = new Date(this.value);
            const nextDay = new Date(startDate);
            nextDay.setDate(nextDay.getDate() + 1);
            
            // Format YYYY-MM-DD
            const minDate = nextDay.toISOString().split('T')[0];
            endDateInput.min = minDate;
            
            // If current end date is before new minimum, update it
            if (endDateInput.value < minDate) {
                const defaultEnd = new Date(startDate);
                defaultEnd.setDate(defaultEnd.getDate() + 7);
                endDateInput.value = defaultEnd.toISOString().split('T')[0];
            }
        });
    }
}

function hideEnableForm(experimentId) {
    const form = document.getElementById('enableForm-' + experimentId);
    if (form) {
        form.style.display = 'none';
    }
}

// Confirm disable functionality
let disableFormToSubmit = null;

function confirmDisable(button) {
    disableFormToSubmit = button.closest('form');
    document.getElementById('confirmDialog').style.display = 'flex';
}

function hideConfirmDialog() {
    document.getElementById('confirmDialog').style.display = 'none';
    disableFormToSubmit = null;
}

function proceedDisable() {
    if (disableFormToSubmit) {
        disableFormToSubmit.submit();
    }
    hideConfirmDialog();
}
</script>

</body>
</html>
<?php $conn->close(); ?>