<?php
// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "college_lab";

$conn = new mysqli($host, $username, $password, $database);

// Get counts from database
$students_count = $conn->query("SELECT COUNT(*) as total FROM students")->fetch_assoc()['total'];
$teachers_count = $conn->query("SELECT COUNT(*) as total FROM teachers")->fetch_assoc()['total'];
$courses_count = $conn->query("SELECT COUNT(*) as total FROM courses")->fetch_assoc()['total'];
$announcements_count = $conn->query("SELECT COUNT(*) as total FROM announcements")->fetch_assoc()['total'];

// Get recent announcements (latest 5) - ordered by id DESC to get newest first
$recent_announcements = $conn->query("SELECT * FROM announcements ORDER BY id DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2>Admin Panel</h2>
            </div>
                <nav>
                    <a class="nav-item" href="dashboard.php" aria-current="page" style="display:block;padding:16px 20px;border-radius:6px;color:#ffffff;text-decoration:none;background:#345674;position:relative;">
                    <span style="position:absolute;left:0;top:0;bottom:0;width:4px;background:#5dade2;border-top-left-radius:6px;border-bottom-left-radius:6px;"></span> 📊 Dashboard</a>
                    <a class="nav-item" href="students.php" style="display:block;padding:16px 20px;border-radius:6px;color:#ecf0f1;text-decoration:none;background:transparent;" rel="noopener noreferrer">👨‍🎓 Students</a>
                    <a class="nav-item" href="teachers.php" style="display:block;padding:16px 20px;border-radius:6px;color:#ecf0f1;text-decoration:none;background:transparent;" rel="noopener noreferrer">👨‍🏫 Teachers</a>
                    <a class="nav-item" href="courses.php" style="display:block;padding:16px 20px;border-radius:6px;color:#ecf0f1;text-decoration:none;background:transparent;" rel="noopener noreferrer">📚 Courses</a>
                    <a class="nav-item" href="timetable.php" style="display:block;padding:16px 20px;border-radius:6px;color:#ecf0f1;text-decoration:none;background:transparent;" rel="noopener noreferrer">🗓️ Timetable</a>
                    <a class="nav-item" href="reports.php" style="display:block;padding:16px 20px;border-radius:6px;color:#ecf0f1;text-decoration:none;background:transparent;" rel="noopener noreferrer">📈 Reports</a>
                    <a class="nav-item" href="announcements.php" style="display:block;padding:16px 20px;border-radius:6px;color:#ecf0f1;text-decoration:none;background:transparent;" rel="noopener noreferrer">📢 Announcements</a>
                </nav>

        </aside>

        <main class="main-content" id="mainContent">
            <div class="topbar">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <button class="menu-toggle" id="menuToggle">☰</button>
                    <h1 id="pageTitle">Dashboard Overview</h1>
                </div>
                <div class="user-info">
                    <span>Admin User</span>
                    <div class="user-avatar">A</div>
                </div>
            </div>

            <div class="content">
                <section class="section active" id="dashboard">
                    <div class="stats-grid">
                        <div class="stat-card blue">
                            <h3>Total Students</h3>
                            <div class="value" id="totalStudents"><?php echo $students_count; ?></div>
                        </div>
                        <div class="stat-card green">
                            <h3>Total Teachers</h3>
                            <div class="value" id="totalTeachers"><?php echo $teachers_count; ?></div>
                        </div>
                        <div class="stat-card orange">
                            <h3>Total Courses</h3>
                            <div class="value" id="totalCourses"><?php echo $courses_count; ?></div>
                        </div>
                        <div class="stat-card purple">
                            <h3>Total Announcements</h3>
                            <div class="value" id="totalAnnouncements"><?php echo $announcements_count; ?></div>
                        </div>
                    </div>

                    <div class="form-card">
                        <h2 style="border-bottom: 2px solid #e0e0e0; padding-bottom: 10px; margin-bottom: 20px;">Recent Activity</h2>
                        
                        <?php if ($recent_announcements->num_rows > 0): ?>
                            <?php while($announcement = $recent_announcements->fetch_assoc()): ?>
                                <div style="background: #f8f9fa; border-left: 4px solid #3498db; padding: 15px; margin-bottom: 15px; border-radius: 4px;">
                                    <h4 style="margin: 0 0 5px 0; color: #2c3e50;">📢 <?php echo htmlspecialchars($announcement['title']); ?></h4>
                                    <p style="color: #7f8c8d; margin: 0 0 5px 0; font-size: 14px;">
                                        <?php 
                                        // Truncate content if too long for better display
                                        $content = htmlspecialchars($announcement['content']);
                                        if (strlen($content) > 200) {
                                            echo substr($content, 0, 200) . '...';
                                        } else {
                                            echo $content;
                                        }
                                        ?>
                                    </p>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div style="background: #f8f9fa; border-left: 4px solid #3498db; padding: 15px; margin-bottom: 15px; border-radius: 4px;">
                                <p style="color: #7f8c8d; margin: 0; font-style: italic;">Welcome to the Admin Dashboard. Use the sidebar to navigate through different sections.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script src="script.js"></script>
</body>
</html>