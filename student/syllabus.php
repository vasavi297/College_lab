<?php
session_start();
$student_name = $_SESSION['name'] ?? 'Student';
$roll_number = $_SESSION['roll_number'] ?? '';
$student_semester = $_SESSION['semester'] ?? '';
$semester_number = (function ($semester) {
  $map = [
    'I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4,
    'V' => 5, 'VI' => 6, 'VII' => 7, 'VIII' => 8
  ];
  if (isset($map[$semester])) {
    return $map[$semester];
  }
  if (is_numeric($semester)) {
    return (int)$semester;
  }
  return 1;
})($student_semester);
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
    <div>
      <div style="font-weight:700; color:var(--text-dark);">Welcome <?php echo htmlspecialchars($student_name); ?> !!</div>
      <div style="font-size:12px; color:var(--text-gray);">ID: <?php echo htmlspecialchars($roll_number); ?></div>
      <?php if ($semester_number <= 2): ?>
          <div style="font-size:11px; color:#15803d; background:#dcfce7; padding:3px 8px; border-radius:10px; margin-top:8px; display:inline-block;">
              BSH Phase
          </div>
      <?php else: ?>
          <div style="font-size:11px; color:#1d4ed8; background:#dbeafe; padding:3px 8px; border-radius:10px; margin-top:8px; display:inline-block;">
              Professional Phase
          </div>
      <?php endif; ?>
    </div>
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