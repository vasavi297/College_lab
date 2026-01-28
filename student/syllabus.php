<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lab Syllabus - Sri Vasavi Engineering College</title>
<link href="https://fonts.googleapis.com/css?family=Poppins:400,600&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="css/main.css">

</head>
<body>

  <div class="header-container">
    <div class="header-content">
      <img src="college_logo.png" 
           alt="Sri Vasavi Engineering College Logo" class="college-logo" />
      <div class="header-text">
        <div class="college-name">SRI VASAVI ENGINEERING COLLEGE (AUTONOMOUS)</div>
        <div class="college-location">Pedatadepalli, Tadepalligudem - 534101, West Godavari District (AP)</div>
      </div>
      <img src="student.jpg" 
           alt="Student Profile Image" class="student-image" />
    </div>
  </div>
  
  <!-- Welcome Banner with Logout Button -->
  <div class="student-name-banner">
    <span>Welcome <?php echo isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : 'Student'; ?>...!</span>
    <a href="logout.php" class="logout-btn"> Logout</a>
  </div>

  <!-- Layout -->
  <div class="container">
    <!-- Sidebar -->
    <div class="sidebar">
      <a href="updated_exp.php">Updated Experiments</a>
      <a href="completed_exp.php">Completed Experiments</a>
      <a href="retake_exp.php">Retake Experiments</a>
     
      <a href="profile.php">Profile</a>
      <a href="syllabus.php">Lab Syllabus</a>
      <a href="practice.php">Practice Section</a>
    </div>

   <!-- Content -->
  <div class="content" id="content">
    
  </div>
</div>

<script>
const content = document.getElementById('content');

const syllabusData = {
  "Chemistry": [
    "Basics of Laboratory Safety",
    "Qualitative Analysis of Compounds",
    "Preparation of Solutions",
    "Titration Experiments"
  ],
  "Theory of Machines": [
    "Kinematics of Machines",
    "Gear Trains",
    "Friction and Brakes",
    "Balancing of Rotating Masses"
  ]
};

// Show subjects list
function showSubjects() {
  content.innerHTML = '<h2>Subjects</h2>';
  for (let subject in syllabusData) {
    const div = document.createElement('div');
    div.className = 'subject-card';
    div.textContent = subject;
    div.addEventListener('click', () => showSyllabus(subject));
    content.appendChild(div);
  }
}

// Show syllabus full page
function showSyllabus(subject) {
  content.innerHTML = `<h2>${subject} Syllabus</h2>`;
  
  const backBtn = document.createElement('div');
  backBtn.className = 'back-btn';
  backBtn.textContent = ' Back to Subjects';
  backBtn.addEventListener('click', showSubjects);
  content.appendChild(backBtn);

  const card = document.createElement('div');
  card.className = 'syllabus-card';

  syllabusData[subject].forEach(item => {
    const div = document.createElement('div');
    div.className = 'syllabus-item';
    div.textContent = "• " + item;
    card.appendChild(div);
  });

  content.appendChild(card);
}

// Automatically show subjects when page loads
window.addEventListener('DOMContentLoaded', showSubjects);

</script>

</body>
</html>