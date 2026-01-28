<?php
// Database connection (using your existing connection method)
$host = "localhost";
$username = "root";
$password = "";
$database = "college_lab"; // Your admin panel database

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if employee_lab_assignments table exists, create if not
$table_check = $conn->query("SHOW TABLES LIKE 'employee_lab_assignments'");
if ($table_check->num_rows == 0) {
    $create_table_sql = "CREATE TABLE employee_lab_assignments (
        assignment_id INT PRIMARY KEY AUTO_INCREMENT,
        employee_id INT NOT NULL,
        lab_subject VARCHAR(100) NOT NULL,
        section_name VARCHAR(10) NOT NULL,
        batch_type ENUM('FirstHalf', 'SecondHalf', 'Full') DEFAULT 'Full',
        max_capacity INT DEFAULT 30,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_assignment (employee_id, lab_subject, section_name, batch_type)
    )";
    
    if ($conn->query($create_table_sql)) {
        $success_message = "Employee lab assignments table created successfully!";
    } else {
        $error_message = "Error creating table: " . $conn->error;
    }
}

// Check for success message from redirect
if (isset($_GET['success'])) {
    $success_message = $_GET['success'];
}

// Handle form submission for lab assignment
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_assignment'])) {
        // Handle update operation
        $assignment_id = $conn->real_escape_string($_POST['assignment_id']);
        $employee_id = $conn->real_escape_string($_POST['employee_id']);
        $lab_subject = $conn->real_escape_string($_POST['lab_subject']);
        $section_name = $conn->real_escape_string($_POST['section_name']);
        $batch_type = $conn->real_escape_string($_POST['batch_type']);
        
        $sql = "UPDATE employee_lab_assignments SET 
                employee_id = '$employee_id', 
                lab_subject = '$lab_subject', 
                section_name = '$section_name', 
                batch_type = '$batch_type' 
                WHERE assignment_id = '$assignment_id'";
        
        if ($conn->query($sql)) {
            $success_message = "Lab assignment updated successfully!";
            header("Location: lab_assignments.php?success=Lab assignment updated successfully!");
            exit();
        } else {
            $error_message = "Error updating assignment: " . $conn->error;
        }
    } else {
        // Insert operation
        $employee_id = $conn->real_escape_string($_POST['employee_id']);
        $lab_subject = $conn->real_escape_string($_POST['lab_subject']);
        $section_name = $conn->real_escape_string($_POST['section_name']);
        $batch_type = $conn->real_escape_string($_POST['batch_type']);
        
        // Check if assignment already exists
        $check_sql = "SELECT assignment_id FROM employee_lab_assignments 
                     WHERE employee_id = '$employee_id' 
                     AND lab_subject = '$lab_subject' 
                     AND section_name = '$section_name' 
                     AND batch_type = '$batch_type'";
        $check_result = $conn->query($check_sql);
        
        if ($check_result->num_rows > 0) {
            $error_message = "Error: This assignment already exists!";
        } else {
            $sql = "INSERT INTO employee_lab_assignments (employee_id, lab_subject, section_name, batch_type) 
                    VALUES ('$employee_id', '$lab_subject', '$section_name', '$batch_type')";
            if ($conn->query($sql)) {
                $success_message = "Lab assignment added successfully!";
            } else {
                $error_message = "Error adding assignment: " . $conn->error;
            }
        }
    }
}

// Handle delete
if (isset($_GET['delete_id'])) {
    $delete_id = $conn->real_escape_string($_GET['delete_id']);
    $sql = "DELETE FROM employee_lab_assignments WHERE assignment_id = '$delete_id'";
    if ($conn->query($sql)) {
        $success_message = "Lab assignment deleted successfully!";
        header("Location: lab_assignments.php?success=Lab assignment deleted successfully!");
        exit();
    } else {
        $error_message = "Error deleting assignment: " . $conn->error;
    }
}

// Fetch assignment data for editing
$edit_assignment = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $conn->real_escape_string($_GET['edit_id']);
    $result = $conn->query("SELECT * FROM employee_lab_assignments WHERE assignment_id = '$edit_id'");
    if ($result->num_rows > 0) {
        $edit_assignment = $result->fetch_assoc();
    }
}

// Fetch all assignments with employee details
// IMPORTANT: Check if you have 'employees' table or 'teachers' table
$assignments = $conn->query("
    SELECT ela.*, e.name as employee_name, e.department 
    FROM employee_lab_assignments ela
    JOIN employees e ON ela.employee_id = e.employee_id
    ORDER BY ela.lab_subject, ela.section_name
");

// Fetch employees for dropdown
$employees = $conn->query("SELECT employee_id, name, department FROM employees ORDER BY name");

// Fetch available lab subjects (you can modify this based on your actual subjects)
$lab_subjects = ['Physics Lab', 'Chemistry Lab', 'Computer Lab', 'Electronics Lab', 'Mechanical Lab'];

// If employees query fails, try teachers table instead
if (!$employees) {
    // Try to fetch from teachers table if employees table doesn't exist
    $employees = $conn->query("SELECT id as employee_id, NAME as name, department FROM teachers ORDER BY NAME");
    
    // Also update the assignments query
    $assignments = $conn->query("
        SELECT ela.*, t.NAME as employee_name, t.department 
        FROM employee_lab_assignments ela
        JOIN teachers t ON ela.employee_id = t.id
        ORDER BY ela.lab_subject, ela.section_name
    ");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Lab Assignments</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../admin/style.css">
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
        .info-box {
            background: #e8f4fc;
            border-left: 4px solid #3498db;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
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
                <a class="nav-item" href="dashboard.php">📊 Dashboard</a>
                <a class="nav-item" href="students.php">👨‍🎓 Students</a>
                <a class="nav-item" href="teachers.php">👨‍🏫 Teachers</a>
                <a class="nav-item" href="lab_assignments.php" aria-current="page" style="background:#345674;color:#ffffff;position:relative;">
                    <span style="position:absolute;left:0;top:0;bottom:0;width:4px;background:#5dade2;"></span> 🧪 Lab Assignments
                </a>
                <a class="nav-item" href="courses.php">📚 Courses</a>
                <a class="nav-item" href="timetable.php">🗓️ Timetable</a>
                <a class="nav-item" href="reports.php">📈 Reports</a>
                <a class="nav-item" href="announcements.php">📢 Announcements</a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <div class="topbar">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <button class="menu-toggle" id="menuToggle">☰</button>
                    <h1 id="pageTitle">Lab Assignments</h1>
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

                <div class="info-box">
                    <strong>How it works:</strong> Assign employees to specific labs, sections, and batches. 
                    Students will be automatically routed to the correct employee based on their section and batch.
                </div>

                <section class="section active" id="labAssignments">
                    <div class="form-card">
                        <h2><?php echo isset($edit_assignment) ? 'Edit Lab Assignment' : 'Add New Lab Assignment'; ?></h2>
                        <form method="POST" action="">
                            <?php if (isset($edit_assignment)): ?>
                                <input type="hidden" name="assignment_id" value="<?php echo $edit_assignment['assignment_id']; ?>">
                                <input type="hidden" name="update_assignment" value="1">
                            <?php endif; ?>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Employee *</label>
                                    <select name="employee_id" required>
                                        <option value="">Select Employee</option>
                                        <?php 
                                        if ($employees && $employees->num_rows > 0):
                                            while($employee = $employees->fetch_assoc()): ?>
                                                <option value="<?php echo $employee['employee_id']; ?>"
                                                    <?php echo (isset($edit_assignment) && $edit_assignment['employee_id'] == $employee['employee_id']) ? 'selected' : ''; ?>>
                                                    <?php echo $employee['name'] . ' (' . $employee['department'] . ')'; ?>
                                                </option>
                                            <?php endwhile;
                                        else: ?>
                                            <option value="">No employees found</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Lab Subject *</label>
                                    <select name="lab_subject" id="labSubjectSelect" required>
                                        <option value="">Select Lab Subject</option>
                                        <?php foreach($lab_subjects as $subject): ?>
                                            <option value="<?php echo $subject; ?>"
                                                <?php echo (isset($edit_assignment) && $edit_assignment['lab_subject'] == $subject) ? 'selected' : ''; ?>>
                                                <?php echo $subject; ?>
                                            </option>
                                        <?php endforeach; ?>
                                        <option value="Other" <?php echo (isset($edit_assignment) && !in_array($edit_assignment['lab_subject'], $lab_subjects)) ? 'selected' : ''; ?>>Other (Specify)</option>
                                    </select>
                                </div>
                                <?php if (isset($edit_assignment) && !in_array($edit_assignment['lab_subject'], $lab_subjects)): ?>
                                <div class="form-group" id="customSubjectGroup">
                                    <label>Custom Lab Subject *</label>
                                    <input type="text" name="custom_lab_subject" value="<?php echo $edit_assignment['lab_subject']; ?>" required>
                                </div>
                                <?php endif; ?>
                                <div class="form-group">
                                    <label>Section *</label>
                                    <select name="section_name" required>
                                        <option value="">Select Section</option>
                                        <option value="A" <?php echo (isset($edit_assignment) && $edit_assignment['section_name'] == 'A') ? 'selected' : ''; ?>>Section A</option>
                                        <option value="B" <?php echo (isset($edit_assignment) && $edit_assignment['section_name'] == 'B') ? 'selected' : ''; ?>>Section B</option>
                                        <option value="C" <?php echo (isset($edit_assignment) && $edit_assignment['section_name'] == 'C') ? 'selected' : ''; ?>>Section C</option>
                                        <option value="D" <?php echo (isset($edit_assignment) && $edit_assignment['section_name'] == 'D') ? 'selected' : ''; ?>>Section D</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Batch Type *</label>
                                    <select name="batch_type" required>
                                        <option value="">Select Batch</option>
                                        <option value="FirstHalf" <?php echo (isset($edit_assignment) && $edit_assignment['batch_type'] == 'FirstHalf') ? 'selected' : ''; ?>>First Half</option>
                                        <option value="SecondHalf" <?php echo (isset($edit_assignment) && $edit_assignment['batch_type'] == 'SecondHalf') ? 'selected' : ''; ?>>Second Half</option>
                                        <option value="Full" <?php echo (isset($edit_assignment) && $edit_assignment['batch_type'] == 'Full') ? 'selected' : ''; ?>>Full Batch</option>
                                    </select>
                                </div>
                            </div>
                            <?php if (isset($edit_assignment)): ?>
                                <button type="submit" name="update_assignment" class="btn btn-primary">Update Assignment</button>
                                <button type="button" onclick="window.location.href='lab_assignments.php'" class="btn">Cancel</button>
                            <?php else: ?>
                                <button type="submit" class="btn btn-primary">Add Assignment</button>
                            <?php endif; ?>
                        </form>
                    </div>

                    <div class="table-container">
                        <div class="table-header">
                            <h2>Current Lab Assignments</h2>
                            <input type="text" class="search-box" placeholder="Search assignments..." id="assignmentSearch">
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Department</th>
                                    <th>Lab Subject</th>
                                    <th>Section</th>
                                    <th>Batch Type</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="assignmentTableBody">
                                <?php 
                                if ($assignments && $assignments->num_rows > 0): 
                                    $assignments->data_seek(0);
                                    while($assignment = $assignments->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $assignment['employee_name']; ?></td>
                                            <td><?php echo $assignment['department']; ?></td>
                                            <td><?php echo $assignment['lab_subject']; ?></td>
                                            <td><?php echo $assignment['section_name']; ?></td>
                                            <td>
                                                <?php 
                                                $batch_labels = [
                                                    'FirstHalf' => 'First Half',
                                                    'SecondHalf' => 'Second Half', 
                                                    'Full' => 'Full Batch'
                                                ];
                                                echo $batch_labels[$assignment['batch_type']] ?? $assignment['batch_type'];
                                                ?>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="lab_assignments.php?edit_id=<?php echo $assignment['assignment_id']; ?>" class="btn">Edit</a>
                                                    <a href="lab_assignments.php?delete_id=<?php echo $assignment['assignment_id']; ?>" class="btn-danger" onclick="return confirm('Are you sure you want to delete this assignment?')">Delete</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="empty-state">No lab assignments yet. Add one above.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script src="../admin/script.js"></script>
    <script>
        // Simple search functionality
        document.getElementById('assignmentSearch').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#assignmentTableBody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Handle custom lab subject input
        document.getElementById('labSubjectSelect').addEventListener('change', function() {
            const formGrid = this.closest('.form-grid');
            const existingCustom = document.getElementById('customSubjectGroup');
            
            if (this.value === 'Other') {
                if (!existingCustom) {
                    const customField = document.createElement('div');
                    customField.className = 'form-group';
                    customField.id = 'customSubjectGroup';
                    customField.innerHTML = `
                        <label>Custom Lab Subject *</label>
                        <input type="text" name="custom_lab_subject" required>
                    `;
                    formGrid.appendChild(customField);
                }
            } else if (existingCustom) {
                existingCustom.remove();
            }
        });
    </script>
</body>
</html>