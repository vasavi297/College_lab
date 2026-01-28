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

// Check for success message from redirect
if (isset($_GET['success'])) {
    $success_message = $_GET['success'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if it's a delete operation
    if (isset($_POST['delete_id'])) {
        $delete_id = $conn->real_escape_string($_POST['delete_id']);
        $sql = "DELETE FROM students WHERE id = '$delete_id'";
        if ($conn->query($sql)) {
            $success_message = "Student deleted successfully!";
        } else {
            $error_message = "Error deleting student: " . $conn->error;
        }
    } else if (isset($_POST['update_student'])) {
        // Handle update operation
        $id = $conn->real_escape_string($_POST['id']);
        $name = $conn->real_escape_string($_POST['NAME']);
        $student_id = $conn->real_escape_string($_POST['student_id']);
        $email = $conn->real_escape_string($_POST['email']);
        $department = $conn->real_escape_string($_POST['department']);
        $section = $conn->real_escape_string($_POST['section']);
        $year = $conn->real_escape_string($_POST['YEAR']);
        $semester = $conn->real_escape_string($_POST['semester']);
        
        // Check if student_id already exists (excluding current record)
        $check_sql = "SELECT id FROM students WHERE student_id = ? AND id != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("si", $student_id, $id);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $error_message = "Error: Student ID '$student_id' already exists!";
        } else {
            $sql = "UPDATE students SET 
                    student_id = '$student_id', 
                    NAME = '$name', 
                    email = '$email', 
                    department = '$department', 
                    section = '$section', 
                    YEAR = '$year', 
                    semester = '$semester' 
                    WHERE id = '$id'";
            
            if ($conn->query($sql)) {
                $success_message = "Student updated successfully!";
                // Clear the edit_student variable and redirect to clear the form
                $edit_student = null;
                // Redirect to students page to clear the edit form
                header("Location: students.php?success=Student updated successfully!");
                exit();
            } else {
                $error_message = "Error updating student: " . $conn->error;
            }
        }
        $check_stmt->close();
    } else {
        // Insert operation with duplicate check
        $name = $conn->real_escape_string($_POST['NAME']);
        $student_id = $conn->real_escape_string($_POST['student_id']);
        $email = $conn->real_escape_string($_POST['email']);
        $department = $conn->real_escape_string($_POST['department']);
        $section = $conn->real_escape_string($_POST['section']);
        $year = $conn->real_escape_string($_POST['YEAR']);
        $semester = $conn->real_escape_string($_POST['semester']);
        
        // Check if student_id already exists
        $check_sql = "SELECT id FROM students WHERE student_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $student_id);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $error_message = "Error: Student ID '$student_id' already exists!";
        } else {
            // Use prepared statements instead of string concatenation
            $stmt = $conn->prepare("INSERT INTO students (student_id, NAME, email, department, section, YEAR, semester) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssii", $student_id, $name, $email, $department, $section, $year, $semester);
            
            if ($stmt->execute()) {
                $success_message = "Student added successfully!";
            } else {
                $error_message = "Error adding student: " . $stmt->error;
            }
            $stmt->close();
        }
        $check_stmt->close();
    }
}

// Fetch student data for editing
$edit_student = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $conn->real_escape_string($_GET['edit_id']);
    $result = $conn->query("SELECT * FROM students WHERE id = '$edit_id'");
    if ($result->num_rows > 0) {
        $edit_student = $result->fetch_assoc();
    }
}

if (isset($_GET['delete_id'])) {
    $delete_id = $conn->real_escape_string($_GET['delete_id']);
    $sql = "DELETE FROM students WHERE id = '$delete_id'";
    if ($conn->query($sql)) {
        $success_message = "Student deleted successfully!";
        header("Location: students.php?success=Student deleted successfully!");
        exit();
    } else {
        $error_message = "Error deleting student: " . $conn->error;
    }
}

// Fetch students
$students = $conn->query("SELECT * FROM students ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Students</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
        }
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
            min-width: 60px;
        }
        .action-buttons .btn {
            background: #3498db;
            color: white;
        }
        .action-buttons .btn:hover {
            background: #2980b9;
        }
        .action-buttons .btn-danger {
            background: #e74c3c;
            color: white;
        }
        .action-buttons .btn-danger:hover {
            background: #c0392b;
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
                    <a class="nav-item" href="students.php" aria-current="page" style="display:block;padding:16px 20px;border-radius:6px;color:#ffffff;text-decoration:none;background:#345674;position:relative;">
                    <span style="position:absolute;left:0;top:0;bottom:0;width:4px;background:#5dade2;border-top-left-radius:6px;border-bottom-left-radius:6px;"></span> 👨‍🎓 Students</a>
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
                    <h1 id="pageTitle">Students</h1>
                </div>
                <div class="user-info">
                    <span>Admin User</span>
                    <div class="user-avatar">A</div>
                </div>
            </div>

            <div class="content">
                
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-error"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <section class="section active" id="students">
                    <div class="form-card">
                        <h2><?php echo isset($edit_student) ? 'Edit Student' : 'Add New Student'; ?></h2>
                        <form method="POST" action="">
                            <?php if (isset($edit_student)): ?>
                                <input type="hidden" name="id" value="<?php echo $edit_student['id']; ?>">
                            <?php endif; ?>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Student Name *</label>
                                    <input type="text" name="NAME" required value="<?php echo isset($edit_student) ? $edit_student['NAME'] : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label>Student ID *</label>
                                    <input type="text" name="student_id" required value="<?php echo isset($edit_student) ? $edit_student['student_id'] : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label>Email *</label>
                                    <input type="email" name="email" required value="<?php echo isset($edit_student) ? $edit_student['email'] : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label>Department *</label>
                                    <select name="department" required>
                                        <option value="">Select Department</option>
                                        <option value="CSE" <?php echo (isset($edit_student) && $edit_student['department'] == 'CSE') ? 'selected' : ''; ?>>Computer Science Engineering</option>
                                        <option value="ECE" <?php echo (isset($edit_student) && $edit_student['department'] == 'ECE') ? 'selected' : ''; ?>>Electronics & Communication</option>
                                        <option value="EEE" <?php echo (isset($edit_student) && $edit_student['department'] == 'EEE') ? 'selected' : ''; ?>>Electrical & Electronics</option>
                                        <option value="MECH" <?php echo (isset($edit_student) && $edit_student['department'] == 'MECH') ? 'selected' : ''; ?>>Mechanical Engineering</option>
                                        <option value="CIVIL" <?php echo (isset($edit_student) && $edit_student['department'] == 'CIVIL') ? 'selected' : ''; ?>>Civil Engineering</option>
                                        <option value="IT" <?php echo (isset($edit_student) && $edit_student['department'] == 'IT') ? 'selected' : ''; ?>>Information Technology</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Section *</label>
                                    <select name="section" required>
                                        <option value="">Select Section</option>
                                        <option value="A" <?php echo (isset($edit_student) && $edit_student['section'] == 'A') ? 'selected' : ''; ?>>Section A</option>
                                        <option value="B" <?php echo (isset($edit_student) && $edit_student['section'] == 'B') ? 'selected' : ''; ?>>Section B</option>
                                        <option value="C" <?php echo (isset($edit_student) && $edit_student['section'] == 'C') ? 'selected' : ''; ?>>Section C</option>
                                        <option value="D" <?php echo (isset($edit_student) && $edit_student['section'] == 'D') ? 'selected' : ''; ?>>Section D</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Year *</label>
                                    <select name="YEAR" required>
                                        <option value="">Select Year</option>
                                        <option value="1" <?php echo (isset($edit_student) && $edit_student['YEAR'] == '1') ? 'selected' : ''; ?>>1st Year</option>
                                        <option value="2" <?php echo (isset($edit_student) && $edit_student['YEAR'] == '2') ? 'selected' : ''; ?>>2nd Year</option>
                                        <option value="3" <?php echo (isset($edit_student) && $edit_student['YEAR'] == '3') ? 'selected' : ''; ?>>3rd Year</option>
                                        <option value="4" <?php echo (isset($edit_student) && $edit_student['YEAR'] == '4') ? 'selected' : ''; ?>>4th Year</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Semester *</label>
                                    <select name="semester" required>
                                        <option value="">Select Semester</option>
                                        <option value="1" <?php echo (isset($edit_student) && $edit_student['semester'] == '1') ? 'selected' : ''; ?>>Semester 1</option>
                                        <option value="2" <?php echo (isset($edit_student) && $edit_student['semester'] == '2') ? 'selected' : ''; ?>>Semester 2</option>
                                        <option value="3" <?php echo (isset($edit_student) && $edit_student['semester'] == '3') ? 'selected' : ''; ?>>Semester 3</option>
                                        <option value="4" <?php echo (isset($edit_student) && $edit_student['semester'] == '4') ? 'selected' : ''; ?>>Semester 4</option>
                                        <option value="5" <?php echo (isset($edit_student) && $edit_student['semester'] == '5') ? 'selected' : ''; ?>>Semester 5</option>
                                        <option value="6" <?php echo (isset($edit_student) && $edit_student['semester'] == '6') ? 'selected' : ''; ?>>Semester 6</option>
                                        <option value="7" <?php echo (isset($edit_student) && $edit_student['semester'] == '7') ? 'selected' : ''; ?>>Semester 7</option>
                                        <option value="8" <?php echo (isset($edit_student) && $edit_student['semester'] == '8') ? 'selected' : ''; ?>>Semester 8</option>
                                    </select>
                                </div>
                            </div>
                            <?php if (isset($edit_student)): ?>
                                <button type="submit" name="update_student" class="btn btn-primary">Update Student</button>
                                <button type="button" onclick="window.location.href='students.php'" class="btn">Cancel</button>
                            <?php else: ?>
                                <button type="submit" class="btn btn-primary">Add Student</button>
                            <?php endif; ?>
                        </form>
                    </div>

                    <div class="table-container">
                        <div class="table-header">
                            <h2>Student List</h2>
                            <input type="text" class="search-box" placeholder="Search students..." id="studentSearch">
                        </div>
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
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="studentTableBody">
                                <?php if ($students->num_rows > 0): ?>
                                    <?php while($student = $students->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $student['NAME']; ?></td>
                                            <td><?php echo $student['student_id']; ?></td>
                                            <td><?php echo $student['email']; ?></td>
                                            <td><?php echo $student['department']; ?></td>
                                            <td><?php echo $student['section']; ?></td>
                                            <td><?php echo $student['YEAR']; ?></td>
                                            <td><?php echo $student['semester']; ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="students.php?edit_id=<?php echo $student['id']; ?>" class="btn">Edit</a>
                                                    <a href="students.php?delete_id=<?php echo $student['id']; ?>" class="btn-danger" onclick="return confirm('Are you sure you want to delete this student?')">Delete</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="empty-state">No students added yet</td>
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