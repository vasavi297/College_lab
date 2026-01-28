<?php
session_start();
if (!isset($_SESSION['name'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Student Profile - Sri Vasavi Engineering College</title>
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
        <p>Pedatadepalli, Tadepalligudem </p>
      </div>
    </div>
    <img src="student.jpg" alt="Student Photo" class="student-photo">
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
      <a href="retake_exp.php" class="nav-item">Retake Experiments</a>
      <a href="profile.php" class="nav-item active">Profile</a>
    </div>
  </nav>

  <!-- MAIN CONTENT -->
  <main class="main">
    <div class="profile-card">
      <h2>Student Profile</h2>
      <div class="profile-item">
        <span>Name:</span>
        <span><?php echo htmlspecialchars($_SESSION['name']); ?></span>
      </div>
      <div class="profile-item">
        <span>Roll Number:</span>
        <span><?php echo htmlspecialchars($_SESSION['roll_number']); ?></span>
      </div>
      <div class="profile-item">
        <span>Branch:</span>
        <span><?php echo htmlspecialchars($_SESSION['branch']); ?></span>
      </div>
      <div class="profile-item">
        <span>Semester:</span>
        <span><?php echo htmlspecialchars($_SESSION['semester']); ?></span>
      </div>
      <div class="profile-item">
        <span>Email:</span>
        <span><?php echo htmlspecialchars($_SESSION['email']); ?></span>
      </div>
      <div class="profile-item">
        <span>Phone:</span>
        <span><?php echo htmlspecialchars($_SESSION['phone']); ?></span>
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