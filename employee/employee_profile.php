<?php
session_start();
if (!isset($_SESSION['employee_id']) || ($_SESSION['role'] ?? '') !== 'employee') {
    header('Location: employee_login.php');
    exit();
}

// Get employee details from database
include '../db_connect.php';
$employee_id = $_SESSION['employee_id'];

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Employee Profile - Sri Vasavi Engineering College</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../css/common.css">
  <link rel="stylesheet" href="../css/profile.css">
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
    <div>Welcome <strong><?php echo htmlspecialchars($name); ?>...!</strong></div>
    <a href="employee_logout.php" class="btn btn-primary">LOG OUT</a>
  </div>
  
  <!-- NAV BAR -->
  <nav class="navbar">
    <div class="nav-menu">
      <a href="employee_dashboard.php" class="nav-item">Dashboard</a>
       <a href="employee_profile.php" class="nav-item active">Profile</a>
      <a href="employee_update_experiment.php" class="nav-item">Update Experiments</a>
      <a href="employee_verify.php" class="nav-item">Verify Students</a>
      <a href="employee_schedule.php" class="nav-item">Schedule Exams</a>
     
    </div>
  </nav>

  <!-- MAIN CONTENT -->
  <main class="main">
    <div class="profile-card">
      <h2>Employee Profile</h2>
      <div class="profile-item">
        <span>Employee ID:</span>
        <span><?php echo htmlspecialchars($employee_id); ?></span>
      </div>
      <div class="profile-item">
        <span>Name:</span>
        <span><?php echo htmlspecialchars($name); ?></span>
      </div>
      <div class="profile-item">
        <span>Username:</span>
        <span><?php echo htmlspecialchars($username); ?></span>
      </div>
      <div class="profile-item">
        <span>Email:</span>
        <span><?php echo htmlspecialchars($email); ?></span>
      </div>
      
      <div class="profile-item">
        <span>Department:</span>
        <span><?php echo htmlspecialchars($department); ?></span>
      </div>
   
      <div class="profile-item">
        <span>Phone:</span>
        <span><?php echo htmlspecialchars($phone); ?></span>
      </div>
    </div>
  </main>

</div>

<script>
// Highlight active navigation item
document.addEventListener('DOMContentLoaded', function() {
    const currentPage = window.location.pathname.split('/').pop();
    const navItems = document.querySelectorAll('.nav-item');
    
    navItems.forEach(item => {
        const href = item.getAttribute('href');
        // Remove query string if present
        const cleanHref = href.split('?')[0];
        const cleanCurrent = currentPage.split('?')[0];
        
        if (cleanHref === cleanCurrent) {
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