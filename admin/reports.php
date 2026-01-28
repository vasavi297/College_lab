<?php
// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "college_lab";

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle report generation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate_report'])) {
    $report_type = $conn->real_escape_string($_POST['report_type']);
    $department = $conn->real_escape_string($_POST['department']);
    $section = $conn->real_escape_string($_POST['section']);
    
    // Generate report based on type
    switch($report_type) {
        case 'students':
            $sql = "SELECT * FROM students WHERE 1=1";
            if ($department) {
                $sql .= " AND department = '$department'";
            }
            if ($section) {
                $sql .= " AND section = '$section'";
            }
            $sql .= " ORDER BY NAME";
            $students = $conn->query($sql);
            break;
            
        case 'teachers':
            $sql = "SELECT * FROM teachers WHERE 1=1";
            if ($department) {
                $sql .= " AND department = '$department'";
            }
            $sql .= " ORDER BY NAME";
            $teachers = $conn->query($sql);
            break;
            
        case 'courses':
            $sql = "SELECT * FROM courses WHERE 1=1";
            if ($department) {
                $sql .= " AND department = '$department'";
            }
            $sql .= " ORDER BY course_name";
            $courses = $conn->query($sql);
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Reports</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .filter-section {
            margin-bottom: 20px;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2>Admin Panel</h2>
            </div>
            <nav>
                <a class="nav-item" href="dashboard.php" style="display:block;padding:16px 20px;border-radius:6px;color:#ecf0f1;text-decoration:none;background:transparent;" rel="noopener noreferrer">📊 Dashboard</a>
                <a class="nav-item" href="students.php" style="display:block;padding:16px 20px;border-radius:6px;color:#ecf0f1;text-decoration:none;background:transparent;" rel="noopener noreferrer">👨‍🎓 Students</a>
                <a class="nav-item" href="teachers.php" style="display:block;padding:16px 20px;border-radius:6px;color:#ecf0f1;text-decoration:none;background:transparent;" rel="noopener noreferrer">👨‍🏫 Teachers</a>
                <a class="nav-item" href="courses.php" style="display:block;padding:16px 20px;border-radius:6px;color:#ecf0f1;text-decoration:none;background:transparent;" rel="noopener noreferrer">📚 Courses</a>
                <a class="nav-item" href="timetable.php" style="display:block;padding:16px 20px;border-radius:6px;color:#ecf0f1;text-decoration:none;background:transparent;" rel="noopener noreferrer">🗓️ Timetable</a>
                <a class="nav-item" href="reports.php" aria-current="page" style="display:block;padding:16px 20px;border-radius:6px;color:#ffffff;text-decoration:none;background:#345674;position:relative;">
                    <span style="position:absolute;left:0;top:0;bottom:0;width:4px;background:#5dade2;border-top-left-radius:6px;border-bottom-left-radius:6px;"></span> 📈 Reports</a>
                <a class="nav-item" href="announcements.php" style="display:block;padding:16px 20px;border-radius:6px;color:#ecf0f1;text-decoration:none;background:transparent;" rel="noopener noreferrer">📢 Announcements</a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <div class="topbar">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <button class="menu-toggle" id="menuToggle">☰</button>
                    <h1 id="pageTitle">Reports</h1>
                </div>
                <div class="user-info">
                    <span>Admin User</span>
                    <div class="user-avatar">A</div>
                </div>
            </div>

            <div class="content">
                <section class="section active" id="reports">
                    <div class="form-card">
                        <h2>Generate Report</h2>
                        <form method="POST" action="">
                            <div class="filter-section">
                                <div class="filter-grid">
                                    <div class="form-group">
                                        <label>Select Report Type *</label>
                                        <select name="report_type" id="reportType" onchange="updateReportFilters()" required>
                                            <option value="">Choose a report</option>
                                            <option value="students" <?php echo (isset($report_type) && $report_type == 'students') ? 'selected' : ''; ?>>Student List</option>
                                            <option value="teachers" <?php echo (isset($report_type) && $report_type == 'teachers') ? 'selected' : ''; ?>>Teacher List</option>
                                            <option value="courses" <?php echo (isset($report_type) && $report_type == 'courses') ? 'selected' : ''; ?>>Course List</option>
                                        </select>
                                    </div>
                                    <div class="form-group" id="departmentFilter" style="display: none;">
                                        <label>Department</label>
                                        <select name="department" id="filterDepartment">
                                            <option value="">Select Department</option>
                                            <option value="CSE" <?php echo (isset($department) && $department == 'CSE') ? 'selected' : ''; ?>>Computer Science Engineering</option>
                                            <option value="ECE" <?php echo (isset($department) && $department == 'ECE') ? 'selected' : ''; ?>>Electronics & Communication</option>
                                            <option value="EEE" <?php echo (isset($department) && $department == 'EEE') ? 'selected' : ''; ?>>Electrical & Electronics</option>
                                            <option value="MECH" <?php echo (isset($department) && $department == 'MECH') ? 'selected' : ''; ?>>Mechanical Engineering</option>
                                            <option value="CIVIL" <?php echo (isset($department) && $department == 'CIVIL') ? 'selected' : ''; ?>>Civil Engineering</option>
                                            <option value="IT" <?php echo (isset($department) && $department == 'IT') ? 'selected' : ''; ?>>Information Technology</option>
                                        </select>
                                    </div>
                                    <div class="form-group" id="sectionFilter" style="display: none;">
                                        <label>Section</label>
                                        <select name="section" id="filterSection">
                                            <option value="">Select Section</option>
                                            <option value="A" <?php echo (isset($section) && $section == 'A') ? 'selected' : ''; ?>>Section A</option>
                                            <option value="B" <?php echo (isset($section) && $section == 'B') ? 'selected' : ''; ?>>Section B</option>
                                            <option value="C" <?php echo (isset($section) && $section == 'C') ? 'selected' : ''; ?>>Section C</option>
                                            <option value="D" <?php echo (isset($section) && $section == 'D') ? 'selected' : ''; ?>>Section D</option>
                                        </select>
                                    </div>
                                </div>
                                <button type="submit" name="generate_report" class="btn btn-primary">Generate Report</button>
                            </div>
                        </form>
                    </div>

                    <?php if (isset($report_type)): ?>
                    <div class="table-container" id="reportContainer">
                        <div class="table-header">
                            <h2 id="reportTitle">
                                <?php 
                                $titles = [
                                    'students' => 'Student List Report',
                                    'teachers' => 'Teacher List Report', 
                                    'courses' => 'Course List Report'
                                ];
                                echo $titles[$report_type];
                                if ($department) echo " - " . $department;
                                if ($section) echo " - Section " . $section;
                                ?>
                            </h2>
                        </div>
                        <div id="reportContent">
                            <?php if ($report_type == 'students' && isset($students)): ?>
                                <?php if ($students && $students->num_rows > 0): ?>
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Student ID</th>
                                                <th>Email</th>
                                                <th>Department</th>
                                                <th>Section</th>
                                                <th>Year</th>
                                                <th>Semester</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($student = $students->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($student['NAME'] ?? $student['name'] ?? '-'); ?></td>
                                                    <td><?php echo htmlspecialchars($student['student_id'] ?? '-'); ?></td>
                                                    <td><?php echo htmlspecialchars($student['email'] ?? '-'); ?></td>
                                                    <td><?php echo htmlspecialchars($student['department'] ?? '-'); ?></td>
                                                    <td><?php echo htmlspecialchars($student['section'] ?? '-'); ?></td>
                                                    <td><?php echo htmlspecialchars($student['YEAR'] ?? $student['year'] ?? '-'); ?></td>
                                                    <td><?php echo htmlspecialchars($student['semester'] ?? '-'); ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <tr><td colspan="7" class="empty-state">No students found</td></tr>
                                <?php endif; ?>
                                
                            <?php elseif ($report_type == 'teachers' && isset($teachers)): ?>
                                <?php if ($teachers && $teachers->num_rows > 0): ?>
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Employee ID</th>
                                                <th>Email</th>
                                                <th>Department</th>
                                                <th>Lab Assigned</th>
                                                <th>Year</th>
                                                <th>Semester</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($teacher = $teachers->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($teacher['NAME'] ?? $teacher['name'] ?? '-'); ?></td>
                                                    <td><?php echo htmlspecialchars($teacher['employee_id'] ?? '-'); ?></td>
                                                    <td><?php echo htmlspecialchars($teacher['email'] ?? '-'); ?></td>
                                                    <td><?php echo htmlspecialchars($teacher['department'] ?? '-'); ?></td>
                                                    <td><?php echo htmlspecialchars($teacher['lab_assigned'] ?? '-'); ?></td>
                                                    <td><?php echo htmlspecialchars($teacher['YEAR'] ?? $teacher['year'] ?? '-'); ?></td>
                                                    <td><?php echo htmlspecialchars($teacher['semester'] ?? '-'); ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <tr><td colspan="7" class="empty-state">No teachers found</td></tr>
                                <?php endif; ?>
                                
                            <?php elseif ($report_type == 'courses' && isset($courses)): ?>
                                <?php if ($courses && $courses->num_rows > 0): ?>
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Course Name</th>
                                                <th>Course ID</th>
                                                <th>Department</th>
                                                <th>Credits</th>
                                                <th>Year</th>
                                                <th>Semester</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($course = $courses->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($course['course_name'] ?? '-'); ?></td>
                                                    <td><?php echo htmlspecialchars($course['course_id'] ?? '-'); ?></td>
                                                    <td><?php echo htmlspecialchars($course['department'] ?? '-'); ?></td>
                                                    <td><?php echo htmlspecialchars($course['credits'] ?? '-'); ?></td>
                                                    <td><?php echo htmlspecialchars($course['YEAR'] ?? $course['year'] ?? '-'); ?></td>
                                                    <td><?php echo htmlspecialchars($course['semester'] ?? '-'); ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <tr><td colspan="6" class="empty-state">No courses found</td></tr>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </section>
            </div>
        </main>
    </div>

    <script src="script.js"></script>
    <script>
    function updateReportFilters() {
        const reportType = document.getElementById('reportType').value;
        const deptFilter = document.getElementById('departmentFilter');
        const sectionFilter = document.getElementById('sectionFilter');
        
        // Show department filter for all report types
        deptFilter.style.display = reportType ? 'block' : 'none';
        
        // Show section filter only for student reports
        sectionFilter.style.display = (reportType === 'students') ? 'block' : 'none';
    }

    // Initialize filters on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateReportFilters();
    });
    </script>
</body>
</html>