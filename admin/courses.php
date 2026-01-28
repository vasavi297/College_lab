<?php
// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "college_lab";

$conn = new mysqli($host, $username, $password, $database);

// Check for success message from redirect
if (isset($_GET['success'])) {
    $success_message = $_GET['success'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_course'])) {
        // Handle update operation
        $id = $conn->real_escape_string($_POST['id']);
        $course_name = $conn->real_escape_string($_POST['course_name']);
        $course_id = $conn->real_escape_string($_POST['course_id']);
        $department = $conn->real_escape_string($_POST['department']);
        $credits = $conn->real_escape_string($_POST['credits']);
        $year = $conn->real_escape_string($_POST['year']);
        $semester = $conn->real_escape_string($_POST['semester']);
        
        $sql = "UPDATE courses SET 
                course_id = '$course_id', 
                course_name = '$course_name', 
                department = '$department', 
                credits = '$credits', 
                year = '$year', 
                semester = '$semester' 
                WHERE id = '$id'";
        
        if ($conn->query($sql)) {
            $success_message = "Course updated successfully!";
            // Clear the edit_course variable and redirect to clear the form
            $edit_course = null;
            // Redirect to courses page to clear the edit form
            header("Location: courses.php?success=Course updated successfully!");
            exit();
        } else {
            $error_message = "Error updating course: " . $conn->error;
        }
    } else {
        // Insert operation
        $course_name = $conn->real_escape_string($_POST['course_name']);
        $course_id = $conn->real_escape_string($_POST['course_id']);
        $department = $conn->real_escape_string($_POST['department']);
        $credits = $conn->real_escape_string($_POST['credits']);
        $year = $conn->real_escape_string($_POST['year']);
        $semester = $conn->real_escape_string($_POST['semester']);
        
        $sql = "INSERT INTO courses (course_id, course_name, department, credits, year, semester) 
                VALUES ('$course_id', '$course_name', '$department', '$credits', '$year', '$semester')";
        if ($conn->query($sql)) {
            $success_message = "Course added successfully!";
        } else {
            $error_message = "Error adding course: " . $conn->error;
        }
    }
}

// Fetch course data for editing
$edit_course = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $conn->real_escape_string($_GET['edit_id']);
    $result = $conn->query("SELECT * FROM courses WHERE id = '$edit_id'");
    if ($result->num_rows > 0) {
        $edit_course = $result->fetch_assoc();
    }
}

// Handle delete
if (isset($_GET['delete_id'])) {
    $delete_id = $conn->real_escape_string($_GET['delete_id']);
    $sql = "DELETE FROM courses WHERE id = '$delete_id'";
    if ($conn->query($sql)) {
        $success_message = "Course deleted successfully!";
        header("Location: courses.php?success=Course deleted successfully!");
        exit();
    } else {
        $error_message = "Error deleting course: " . $conn->error;
    }
}

// Fetch courses
$courses = $conn->query("SELECT * FROM courses");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Courses</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
     <style>
        .action-buttons {
    display: flex;
    gap: 8px;
    justify-content: center;}

    .action-buttons .btn, .action-buttons .btn-danger {
    text-decoration: none;
    display: inline-block;
    padding: 6px 12px;
    border-radius: 4px;
    font-weight: 500;
    text-align: center;
    border: none;
    cursor: pointer;
    font-size: 13px;
    min-width: 60px;}

    .action-buttons .btn {
    background: #3498db;
    color: white;}

    .action-buttons .btn-danger {
    background: #e74c3c;
    color: white;}
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
                    <a class="nav-item" href="courses.php" aria-current="page" style="display:block;padding:16px 20px;border-radius:6px;color:#ffffff;text-decoration:none;background:#345674;position:relative;">
                    <span style="position:absolute;left:0;top:0;bottom:0;width:4px;background:#5dade2;border-top-left-radius:6px;border-bottom-left-radius:6px;"></span> 📚 Courses</a>
                    <a class="nav-item" href="timetable.php" style="display:block;padding:16px 20px;border-radius:6px;color:#ecf0f1;text-decoration:none;background:transparent;" rel="noopener noreferrer">🗓️ Timetable</a>
                    <a class="nav-item" href="reports.php" style="display:block;padding:16px 20px;border-radius:6px;color:#ecf0f1;text-decoration:none;background:transparent;" rel="noopener noreferrer">📈 Reports</a>
                    <a class="nav-item" href="announcements.php" style="display:block;padding:16px 20px;border-radius:6px;color:#ecf0f1;text-decoration:none;background:transparent;" rel="noopener noreferrer">📢 Announcements</a>
            
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <div class="topbar">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <button class="menu-toggle" id="menuToggle">☰</button>
                    <h1 id="pageTitle">Courses</h1>
                </div>
                <div class="user-info">
                    <span>Admin User</span>
                    <div class="user-avatar">A</div>
                </div>
            </div>

            <div class="content">
                <!-- Success/Error Messages -->
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-error"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <section class="section active" id="courses">
                    <div class="form-card">
                        <h2><?php echo isset($edit_course) ? 'Edit Course' : 'Add New Course'; ?></h2>
                        <form method="POST" action="">
                            <?php if (isset($edit_course)): ?>
                                <input type="hidden" name="id" value="<?php echo $edit_course['id']; ?>">
                            <?php endif; ?>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Course Name *</label>
                                    <input type="text" name="course_name" required value="<?php echo isset($edit_course) ? $edit_course['course_name'] : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label>Course ID *</label>
                                    <input type="text" name="course_id" required value="<?php echo isset($edit_course) ? $edit_course['course_id'] : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label>Department *</label>
                                    <select name="department" required>
                                        <option value="">Select Department</option>
                                        <option value="CSE" <?php echo (isset($edit_course) && $edit_course['department'] == 'CSE') ? 'selected' : ''; ?>>Computer Science Engineering</option>
                                        <option value="ECE" <?php echo (isset($edit_course) && $edit_course['department'] == 'ECE') ? 'selected' : ''; ?>>Electronics & Communication</option>
                                        <option value="EEE" <?php echo (isset($edit_course) && $edit_course['department'] == 'EEE') ? 'selected' : ''; ?>>Electrical & Electronics</option>
                                        <option value="MECH" <?php echo (isset($edit_course) && $edit_course['department'] == 'MECH') ? 'selected' : ''; ?>>Mechanical Engineering</option>
                                        <option value="CIVIL" <?php echo (isset($edit_course) && $edit_course['department'] == 'CIVIL') ? 'selected' : ''; ?>>Civil Engineering</option>
                                        <option value="IT" <?php echo (isset($edit_course) && $edit_course['department'] == 'IT') ? 'selected' : ''; ?>>Information Technology</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Credits *</label>
                                    <input type="number" name="credits" min="1" max="10" required value="<?php echo isset($edit_course) ? $edit_course['credits'] : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label>Year *</label>
                                    <select name="year" required>
                                        <option value="">Select Year</option>
                                        <option value="1" <?php echo (isset($edit_course) && $edit_course['year'] == '1') ? 'selected' : ''; ?>>1st Year</option>
                                        <option value="2" <?php echo (isset($edit_course) && $edit_course['year'] == '2') ? 'selected' : ''; ?>>2nd Year</option>
                                        <option value="3" <?php echo (isset($edit_course) && $edit_course['year'] == '3') ? 'selected' : ''; ?>>3rd Year</option>
                                        <option value="4" <?php echo (isset($edit_course) && $edit_course['year'] == '4') ? 'selected' : ''; ?>>4th Year</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Semester *</label>
                                    <select name="semester" required>
                                        <option value="">Select Semester</option>
                                        <option value="1" <?php echo (isset($edit_course) && $edit_course['semester'] == '1') ? 'selected' : ''; ?>>Semester 1</option>
                                        <option value="2" <?php echo (isset($edit_course) && $edit_course['semester'] == '2') ? 'selected' : ''; ?>>Semester 2</option>
                                        <option value="3" <?php echo (isset($edit_course) && $edit_course['semester'] == '3') ? 'selected' : ''; ?>>Semester 3</option>
                                        <option value="4" <?php echo (isset($edit_course) && $edit_course['semester'] == '4') ? 'selected' : ''; ?>>Semester 4</option>
                                        <option value="5" <?php echo (isset($edit_course) && $edit_course['semester'] == '5') ? 'selected' : ''; ?>>Semester 5</option>
                                        <option value="6" <?php echo (isset($edit_course) && $edit_course['semester'] == '6') ? 'selected' : ''; ?>>Semester 6</option>
                                        <option value="7" <?php echo (isset($edit_course) && $edit_course['semester'] == '7') ? 'selected' : ''; ?>>Semester 7</option>
                                        <option value="8" <?php echo (isset($edit_course) && $edit_course['semester'] == '8') ? 'selected' : ''; ?>>Semester 8</option>
                                    </select>
                                </div>
                            </div>


                            <?php if (isset($edit_course)): ?>
                                <button type="submit" name="update_course" class="btn btn-primary">Update Course</button>
                                <button type="button" onclick="window.location.href='courses.php'" class="btn">Cancel</button>
                            <?php else: ?>
                                <button type="submit" class="btn btn-primary">Add Course</button>
                            <?php endif; ?>
                        </form>
                    </div>

                    <div class="table-container">
                        <div class="table-header">
                            <h2>Course List</h2>
                            <input type="text" class="search-box" placeholder="Search courses..." id="courseSearch">
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Course Name</th>
                                    <th>Course ID</th>
                                    <th>Department</th>
                                    <th>Credits</th>
                                    <th>Year</th>
                                    <th>Semester</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="courseTableBody">
                                <?php if ($courses->num_rows > 0): ?>
                                    <?php while($course = $courses->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $course['course_name']; ?></td>
                                            <td><?php echo $course['course_id']; ?></td>
                                            <td><?php echo $course['department']; ?></td>
                                            <td><?php echo $course['credits']; ?></td>
                                            <td><?php echo $course['year']; ?></td>
                                            <td><?php echo $course['semester']; ?></td>
                                            
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="courses.php?edit_id=<?php echo $course['id']; ?>" class="btn">Edit</a>
                                                    <a href="courses.php?delete_id=<?php echo $course['id']; ?>" class="btn-danger" onclick="return confirm('Are you sure you want to delete this student?')">Delete</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="empty-state">No courses added yet</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script src="script.js"></script>
</body>
</html>