<?php
// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "college_lab";

$conn = new mysqli($host, $username, $password, $database);

// Check if timetable table exists, if not create it
$table_check = $conn->query("SHOW TABLES LIKE 'timetable'");
if ($table_check->num_rows == 0) {
    $create_table_sql = "CREATE TABLE timetable (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        day VARCHAR(20) NOT NULL,
        time_slot VARCHAR(50) NOT NULL,
        course VARCHAR(100) NOT NULL,
        teacher VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($create_table_sql)) {
        $success_message = "Timetable table created successfully!";
    } else {
        $error_message = "Error creating timetable table: " . $conn->error;
    }
}

// Handle form submission for add and edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $day = $conn->real_escape_string($_POST['day']);
    $time_slot = $conn->real_escape_string($_POST['time_slot']);
    $course = $conn->real_escape_string($_POST['course']);
    $teacher = $conn->real_escape_string($_POST['teacher']);
    
    if (isset($_POST['edit_id'])) {
        $edit_id = $conn->real_escape_string($_POST['edit_id']);
        $sql = "UPDATE timetable SET day='$day', time_slot='$time_slot', course='$course', teacher='$teacher' WHERE id='$edit_id'";
        if ($conn->query($sql)) {
            $success_message = "Timetable entry updated successfully!";
        } else {
            $error_message = "Error updating timetable entry: " . $conn->error;
        }
    } else {
        $sql = "INSERT INTO timetable (day, time_slot, course, teacher) 
                VALUES ('$day', '$time_slot', '$course', '$teacher')";
        if ($conn->query($sql)) {
            $success_message = "Timetable entry added successfully!";
        } else {
            $error_message = "Error adding timetable entry: " . $conn->error;
        }
    }
}

// Handle delete
if (isset($_GET['delete_id'])) {
    $delete_id = $conn->real_escape_string($_GET['delete_id']);
    $sql = "DELETE FROM timetable WHERE id = '$delete_id'";
    if ($conn->query($sql)) {
        $success_message = "Timetable entry deleted successfully!";
    } else {
        $error_message = "Error deleting timetable entry: " . $conn->error;
    }
}

// Fetch timetable entries
$timetable_entries = $conn->query("SELECT * FROM timetable ORDER BY 
    FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'),
    time_slot");

// Check if we're editing an entry
$edit_entry = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $conn->real_escape_string($_GET['edit_id']);
    $edit_result = $conn->query("SELECT * FROM timetable WHERE id = '$edit_id'");
    if ($edit_result->num_rows > 0) {
        $edit_entry = $edit_result->fetch_assoc();
    }
}

// For timetable display
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
$time_slots = ['09:30-10:30', '10:30-11:20', '11:20-12:10', '02:00-02:50', '03:40-04:30'];

$timetable_entries_display = $conn->query("SELECT * FROM timetable ORDER BY 
    FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'),
    time_slot");
$entries = [];
while($entry = $timetable_entries_display->fetch_assoc()) {
    $entries[$entry['day']][$entry['time_slot']] = $entry;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Timetable</title>
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
                <a class="nav-item" href="courses.php" style="display:block;padding:16px 20px;border-radius:6px;color:#ecf0f1;text-decoration:none;background:transparent;" rel="noopener noreferrer">📚 Courses</a>
                <a class="nav-item" href="timetable.php" aria-current="page" style="display:block;padding:16px 20px;border-radius:6px;color:#ffffff;text-decoration:none;background:#345674;position:relative;">
                    <span style="position:absolute;left:0;top:0;bottom:0;width:4px;background:#5dade2;border-top-left-radius:6px;border-bottom-left-radius:6px;"></span> 🗓️ Timetable
                </a>
                <a class="nav-item" href="reports.php" style="display:block;padding:16px 20px;border-radius:6px;color:#ecf0f1;text-decoration:none;background:transparent;" rel="noopener noreferrer">📈 Reports</a>
                <a class="nav-item" href="announcements.php" style="display:block;padding:16px 20px;border-radius:6px;color:#ecf0f1;text-decoration:none;background:transparent;" rel="noopener noreferrer">📢 Announcements</a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <div class="topbar">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <button class="menu-toggle" id="menuToggle">☰</button>
                    <h1 id="pageTitle">Timetable</h1>
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

                <!-- Add/Edit Form -->
                <div class="form-card">
                    <h2><?php echo $edit_entry ? 'Edit Timetable Entry' : 'Add Timetable Entry'; ?></h2>
                    <form method="POST" action="">
                        <?php if ($edit_entry): ?>
                            <input type="hidden" name="edit_id" value="<?php echo $edit_entry['id']; ?>">
                        <?php endif; ?>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Day *</label>
                                <select name="day" required>
                                    <option value="">Select Day</option>
                                    <option value="Monday" <?php echo ($edit_entry && $edit_entry['day'] == 'Monday') ? 'selected' : ''; ?>>Monday</option>
                                    <option value="Tuesday" <?php echo ($edit_entry && $edit_entry['day'] == 'Tuesday') ? 'selected' : ''; ?>>Tuesday</option>
                                    <option value="Wednesday" <?php echo ($edit_entry && $edit_entry['day'] == 'Wednesday') ? 'selected' : ''; ?>>Wednesday</option>
                                    <option value="Thursday" <?php echo ($edit_entry && $edit_entry['day'] == 'Thursday') ? 'selected' : ''; ?>>Thursday</option>
                                    <option value="Friday" <?php echo ($edit_entry && $edit_entry['day'] == 'Friday') ? 'selected' : ''; ?>>Friday</option>
                                    <option value="Saturday" <?php echo ($edit_entry && $edit_entry['day'] == 'Saturday') ? 'selected' : ''; ?>>Saturday</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Time *</label>
                                <select name="time_slot" required>
                                    <option value="">Select Time</option>
                                    <option value="09:30-10:30" <?php echo ($edit_entry && $edit_entry['time_slot'] == '09:30-10:30') ? 'selected' : ''; ?>>09:30-10:30</option>
                                    <option value="10:30-11:20" <?php echo ($edit_entry && $edit_entry['time_slot'] == '10:30-11:20') ? 'selected' : ''; ?>>10:30-11:20</option>
                                    <option value="11:20-12:10" <?php echo ($edit_entry && $edit_entry['time_slot'] == '11:20-12:10') ? 'selected' : ''; ?>>11:20-12:10</option>
                                    <option value="02:00-02:50" <?php echo ($edit_entry && $edit_entry['time_slot'] == '02:00-02:50') ? 'selected' : ''; ?>>02:00-02:50</option>
                                    <option value="03:40-04:30" <?php echo ($edit_entry && $edit_entry['time_slot'] == '03:40-04:30') ? 'selected' : ''; ?>>03:40-04:30</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Course *</label>
                                <input type="text" name="course" placeholder="Enter course name" required 
                                    value="<?php echo $edit_entry ? $edit_entry['course'] : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label>Teacher *</label>
                                <input type="text" name="teacher" placeholder="Enter teacher name" required 
                                    value="<?php echo $edit_entry ? $edit_entry['teacher'] : ''; ?>">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <?php echo $edit_entry ? 'Update Entry' : 'Add Entry'; ?>
                        </button>
                        <?php if ($edit_entry): ?>
                            <button type="button" onclick="window.location.href='students.php'" class="btn">Cancel</button>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- Weekly Timetable Display -->
                <div class="table-container">
                    <div class="table-header">
                        <h2>Weekly Timetable</h2>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Time/Day</th>
                                <?php foreach($days as $day): ?>
                                    <th><?php echo $day; ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($time_slots as $time): ?>
                                <tr>
                                    <td style="background: #f8f9fa; font-weight: bold;"><?php echo $time; ?></td>
                                    <?php foreach($days as $day): ?>
                                        <td style="min-height: 60px;">
                                            <?php if(isset($entries[$day][$time])): ?>
                                                <div style="background: #3498db; color: white; padding: 8px; border-radius: 4px;">
                                                    <strong><?php echo $entries[$day][$time]['course']; ?></strong><br>
                                                    <small><?php echo $entries[$day][$time]['teacher']; ?></small>
                                                </div>
                                            <?php else: ?>
                                                <div style="color: #7f8c8d; font-style: italic;">-</div>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- All Timetable Entries Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h2>All Timetable Entries</h2>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Day</th>
                                <th>Time</th>
                                <th>Course</th>
                                <th>Teacher</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($timetable_entries->num_rows > 0): ?>
                                <?php while($entry = $timetable_entries->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $entry['day']; ?></td>
                                        <td><?php echo $entry['time_slot']; ?></td>
                                        <td><?php echo $entry['course']; ?></td>
                                        <td><?php echo $entry['teacher']; ?></td>
                                        
                                        <td>
                                                <div class="action-buttons">
                                                    <a href="timetable.php?edit_id=<?php echo $entry['id']; ?>" class="btn">Edit</a>
                                                    <a href="timetable.php?delete_id=<?php echo $entry['id']; ?>" class="btn-danger" onclick="return confirm('Are you sure you want to delete this student?')">Delete</a>
                                                </div>
                                            </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="empty-state">No timetable entries yet</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script src="script.js"></script>
</body>
</html>