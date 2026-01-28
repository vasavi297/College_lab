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

// Handle form submission for adding teacher
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_teacher'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $employee_id = $conn->real_escape_string($_POST['employee_id']);
    $email = $conn->real_escape_string($_POST['email']);
    $department = $conn->real_escape_string($_POST['department']);
    $lab_assigned = $conn->real_escape_string($_POST['lab_assigned']);
    $year = $conn->real_escape_string($_POST['year']);
    $semester = $conn->real_escape_string($_POST['semester']);
    
    // Check if employee_id already exists
    $check_sql = "SELECT id FROM teachers WHERE employee_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $employee_id);
    $check_stmt->execute();
    $check_stmt->store_result();
    
    if ($check_stmt->num_rows > 0) {
        $error_message = "Error: Employee ID '$employee_id' already exists!";
    } else {
        // Use prepared statements instead of string concatenation
        $stmt = $conn->prepare("INSERT INTO teachers (employee_id, NAME, email, department, lab_assigned, YEAR, semester) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $employee_id, $name, $email, $department, $lab_assigned, $year, $semester);
        
        if ($stmt->execute()) {
            $success_message = "Teacher added successfully!";
            // Redirect to clear form data
            header("Location: teachers.php?success=Teacher added successfully!");
            exit();
        } else {
            $error_message = "Error adding teacher: " . $stmt->error;
        }
        $stmt->close();
    }
    $check_stmt->close();
}

// Handle edit form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_teacher'])) {
    $id = $conn->real_escape_string($_POST['id']);
    $name = $conn->real_escape_string($_POST['name']);
    $employee_id = $conn->real_escape_string($_POST['employee_id']);
    $email = $conn->real_escape_string($_POST['email']);
    $department = $conn->real_escape_string($_POST['department']);
    $lab_assigned = $conn->real_escape_string($_POST['lab_assigned']);
    $year = $conn->real_escape_string($_POST['year']);
    $semester = $conn->real_escape_string($_POST['semester']);
    
    // Check if employee_id already exists (excluding current record)
    $check_sql = "SELECT id FROM teachers WHERE employee_id = ? AND id != ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("si", $employee_id, $id);
    $check_stmt->execute();
    $check_stmt->store_result();
    
    if ($check_stmt->num_rows > 0) {
        $error_message = "Error: Employee ID '$employee_id' already exists!";
    } else {
        // Use prepared statement for UPDATE
        $stmt = $conn->prepare("UPDATE teachers SET
                employee_id = ?,
                NAME = ?,
                email = ?,
                department = ?,
                lab_assigned = ?,
                YEAR = ?,
                semester = ?
                WHERE id = ?");
        $stmt->bind_param("sssssssi", $employee_id, $name, $email, $department, $lab_assigned, $year, $semester, $id);
        
        if ($stmt->execute()) {
            $success_message = "Teacher updated successfully!";
            // Redirect to teachers page to clear the edit form
            header("Location: teachers.php?success=Teacher updated successfully!");
            exit();
        } else {
            $error_message = "Error updating teacher: " . $stmt->error;
        }
        $stmt->close();
    }
    $check_stmt->close();
}

// Handle delete
if (isset($_GET['delete_id'])) {
    $delete_id = $conn->real_escape_string($_GET['delete_id']);
    $sql = "DELETE FROM teachers WHERE id = '$delete_id'";
    if ($conn->query($sql)) {
        $success_message = "Teacher deleted successfully!";
        header("Location: teachers.php?success=Teacher deleted successfully!");
        exit();
    } else {
        $error_message = "Error deleting teacher: " . $conn->error;
    }
}

// Fetch teacher data for editing
$edit_teacher = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $conn->real_escape_string($_GET['edit_id']);
    $result = $conn->query("SELECT * FROM teachers WHERE id = '$edit_id'");
    if ($result->num_rows > 0) {
        $edit_teacher = $result->fetch_assoc();
    }
}

// Fetch teachers
$teachers = $conn->query("SELECT * FROM teachers");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Teachers</title>
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

        .action-buttons .btn-danger {
            background: #e74c3c;
            color: white;
        }

        .alert {
            padding: 12px 16px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-weight: 500;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
                <a class="nav-item" href="teachers.php" aria-current="page" style="display:block;padding:16px 20px;border-radius:6px;color:#ffffff;text-decoration:none;background:#345674;position:relative;">
                <span style="position:absolute;left:0;top:0;bottom:0;width:4px;background:#5dade2;border-top-left-radius:6px;border-bottom-left-radius:6px;"></span> 👨‍🏫 Teachers</a>
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
                    <h1 id="pageTitle">Teachers</h1>
                </div>
                <div class="user-info">
                    <span>Admin User</span>
                    <div class="user-avatar">A</div>
                </div>
            </div>

            <div class="content">
                <!-- Display Success/Error Messages -->
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success">
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-error">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <section class="section active" id="teachers">
                    <div class="form-card">
                        <h2><?php echo isset($edit_teacher) ? 'Edit Teacher' : 'Add New Teacher'; ?></h2>
                        <form method="POST" action="">
                            <?php if (isset($edit_teacher)): ?>
                                <input type="hidden" name="id" value="<?php echo $edit_teacher['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Teacher Name *</label>
                                    <input type="text" name="name" required 
                                        value="<?php echo isset($edit_teacher) ? $edit_teacher['NAME'] : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label>Employee ID *</label>
                                    <input type="text" name="employee_id" required 
                                        value="<?php echo isset($edit_teacher) ? $edit_teacher['employee_id'] : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label>Email *</label>
                                    <input type="email" name="email" required value="<?php echo isset($edit_teacher) ? $edit_teacher['email'] : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label>Department *</label>
                                    <select name="department" required>
                                        <option value="">Select Department</option>
                                        <option value="CSE" <?php echo (isset($edit_teacher) && $edit_teacher['department'] == 'CSE') ? 'selected' : ''; ?>>Computer Science Engineering</option>
                                        <option value="ECE" <?php echo (isset($edit_teacher) && $edit_teacher['department'] == 'ECE') ? 'selected' : ''; ?>>Electronics & Communication</option>
                                        <option value="EEE" <?php echo (isset($edit_teacher) && $edit_teacher['department'] == 'EEE') ? 'selected' : ''; ?>>Electrical & Electronics</option>
                                        <option value="MECH" <?php echo (isset($edit_teacher) && $edit_teacher['department'] == 'MECH') ? 'selected' : ''; ?>>Mechanical Engineering</option>
                                        <option value="CIVIL" <?php echo (isset($edit_teacher) && $edit_teacher['department'] == 'CIVIL') ? 'selected' : ''; ?>>Civil Engineering</option>
                                        <option value="IT" <?php echo (isset($edit_teacher) && $edit_teacher['department'] == 'IT') ? 'selected' : ''; ?>>Information Technology</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Lab Assigned</label>
                                    <input type="text" name="lab_assigned" placeholder="Enter lab name"
                                        value="<?php echo isset($edit_teacher) ? $edit_teacher['lab_assigned'] : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label>Year</label>
                                    <select name="year">
                                        <option value="">Select Year</option>
                                        <option value="1" <?php echo (isset($edit_teacher) && $edit_teacher['YEAR'] == '1') ? 'selected' : ''; ?>>1st Year</option>
                                        <option value="2" <?php echo (isset($edit_teacher) && $edit_teacher['YEAR'] == '2') ? 'selected' : ''; ?>>2nd Year</option>
                                        <option value="3" <?php echo (isset($edit_teacher) && $edit_teacher['YEAR'] == '3') ? 'selected' : ''; ?>>3rd Year</option>
                                        <option value="4" <?php echo (isset($edit_teacher) && $edit_teacher['YEAR'] == '4') ? 'selected' : ''; ?>>4th Year</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Semester</label>
                                    <select name="semester">
                                        <option value="">Select Semester</option>
                                        <option value="1" <?php echo (isset($edit_teacher) && $edit_teacher['semester'] == '1') ? 'selected' : ''; ?>>Semester 1</option>
                                        <option value="2" <?php echo (isset($edit_teacher) && $edit_teacher['semester'] == '2') ? 'selected' : ''; ?>>Semester 2</option>
                                        <option value="3" <?php echo (isset($edit_teacher) && $edit_teacher['semester'] == '3') ? 'selected' : ''; ?>>Semester 3</option>
                                        <option value="4" <?php echo (isset($edit_teacher) && $edit_teacher['semester'] == '4') ? 'selected' : ''; ?>>Semester 4</option>
                                        <option value="5" <?php echo (isset($edit_teacher) && $edit_teacher['semester'] == '5') ? 'selected' : ''; ?>>Semester 5</option>
                                        <option value="6" <?php echo (isset($edit_teacher) && $edit_teacher['semester'] == '6') ? 'selected' : ''; ?>>Semester 6</option>
                                        <option value="7" <?php echo (isset($edit_teacher) && $edit_teacher['semester'] == '7') ? 'selected' : ''; ?>>Semester 7</option>
                                        <option value="8" <?php echo (isset($edit_teacher) && $edit_teacher['semester'] == '8') ? 'selected' : ''; ?>>Semester 8</option>
                                    </select>
                                </div>
                            </div>

                            <?php if (isset($edit_teacher)): ?>
                                <button type="submit" name="update_teacher" class="btn btn-primary">Update Teacher</button>
                                <button type="button" onclick="window.location.href='teachers.php'" class="btn">Cancel</button>
                            <?php else: ?>
                                <button type="submit" name="add_teacher" class="btn btn-primary">Add Teacher</button>
                            <?php endif; ?>
                        </form>
                    </div>

                    <div class="table-container">
                        <div class="table-header">
                            <h2>Teacher List</h2>
                            <input type="text" class="search-box" placeholder="Search teachers..." id="teacherSearch">
                        </div>
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
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="teacherTableBody">
                                <?php if ($teachers->num_rows > 0): ?>
                                    <?php while($teacher = $teachers->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $teacher['NAME']; ?></td>
                                            <td><?php echo $teacher['employee_id']; ?></td>
                                            <td><?php echo $teacher['email']; ?></td>
                                            <td><?php echo $teacher['department']; ?></td>
                                            <td><?php echo $teacher['lab_assigned'] ?: '-'; ?></td>
                                            <td><?php echo $teacher['YEAR'] ?: '-'; ?></td>
                                            <td><?php echo $teacher['semester'] ?: '-'; ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="teachers.php?edit_id=<?php echo $teacher['id']; ?>" class="btn">Edit</a>
                                                    <a href="teachers.php?delete_id=<?php echo $teacher['id']; ?>" class="btn-danger" onclick="return confirm('Are you sure you want to delete this teacher?')">Delete</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="empty-state">No teachers added yet</td>
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
    <script>
        // Simple search functionality
        document.getElementById('teacherSearch').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#teacherTableBody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    </script>
</body>
</html>