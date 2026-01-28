<?php
// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "college_lab";

$conn = new mysqli($host, $username, $password, $database);

// Check if announcements table exists, if not create it
$table_check = $conn->query("SHOW TABLES LIKE 'announcements'");
if ($table_check->num_rows == 0) {
    $create_table_sql = "CREATE TABLE announcements (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($create_table_sql)) {
        $success_message = "Announcements table created successfully!";
    } else {
        $error_message = "Error creating announcements table: " . $conn->error;
    }
}

// Handle form submission for add and edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $content = $conn->real_escape_string($_POST['content']);
    
    if (isset($_POST['edit_id'])) {
        $edit_id = $conn->real_escape_string($_POST['edit_id']);
        $sql = "UPDATE announcements SET title='$title', content='$content' WHERE id='$edit_id'";
        if ($conn->query($sql)) {
            $success_message = "Announcement updated successfully!";
            // Redirect to clear the edit form
            header("Location: announcements.php?success=Announcement updated successfully!");
            exit();
        } else {
            $error_message = "Error updating announcement: " . $conn->error;
        }
    } else {
        $sql = "INSERT INTO announcements (title, content) VALUES ('$title', '$content')";
        if ($conn->query($sql)) {
            $success_message = "Announcement added successfully!";
            // Redirect to clear the form
            header("Location: announcements.php?success=Announcement added successfully!");
            exit();
        } else {
            $error_message = "Error adding announcement: " . $conn->error;
        }
    }
}

// Handle delete
if (isset($_GET['delete_id'])) {
    $delete_id = $conn->real_escape_string($_GET['delete_id']);
    $sql = "DELETE FROM announcements WHERE id = '$delete_id'";
    if ($conn->query($sql)) {
        $success_message = "Announcement deleted successfully!";
        header("Location: announcements.php?success=Announcement deleted successfully!");
        exit();
    } else {
        $error_message = "Error deleting announcement: " . $conn->error;
    }
}

// Fetch announcements
$announcements = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC");

// Check if we're editing an announcement
$edit_announcement = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $conn->real_escape_string($_GET['edit_id']);
    $edit_result = $conn->query("SELECT * FROM announcements WHERE id = '$edit_id'");
    if ($edit_result->num_rows > 0) {
        $edit_announcement = $edit_result->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Announcements</title>
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
                    <a class="nav-item" href="timetable.php" style="display:block;padding:16px 20px;border-radius:6px;color:#ecf0f1;text-decoration:none;background:transparent;" rel="noopener noreferrer">🗓️ Timetable</a>
                    <a class="nav-item" href="reports.php" style="display:block;padding:16px 20px;border-radius:6px;color:#ecf0f1;text-decoration:none;background:transparent;" rel="noopener noreferrer">📈 Reports</a>
                    <a class="nav-item" href="announcements.php" aria-current="page" style="display:block;padding:16px 20px;border-radius:6px;color:#ffffff;text-decoration:none;background:#345674;position:relative;">
                    <span style="position:absolute;left:0;top:0;bottom:0;width:4px;background:#5dade2;border-top-left-radius:6px;border-bottom-left-radius:6px;"></span> 📢 Announcements</a>
            </nav>
        </aside>

        <main class="main-content" id="mainContent">
            <div class="topbar">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <button class="menu-toggle" id="menuToggle">☰</button>
                    <h1 id="pageTitle">Announcements</h1>
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

                <section class="section active" id="announcements">
                    <div class="form-card">
                        <h2><?php echo $edit_announcement ? 'Edit Announcement' : 'Create New Announcement'; ?></h2>
                        <form method="POST" action="">
                            <?php if ($edit_announcement): ?>
                                <input type="hidden" name="edit_id" value="<?php echo $edit_announcement['id']; ?>">
                            <?php endif; ?>
                            <div class="form-group">
                                <label>Title *</label>
                                <input type="text" name="title" placeholder="Enter announcement title" required 
                                    value="<?php echo $edit_announcement ? $edit_announcement['title'] : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label>Description *</label>
                                <textarea name="content" placeholder="Enter announcement content" required style="min-height: 120px; resize: vertical;"><?php echo $edit_announcement ? $edit_announcement['content'] : ''; ?></textarea>
                            </div>
                            <div style="margin-top: 20px;">
                                <?php if ($edit_announcement): ?>
                                    <button type="submit" class="btn btn-primary">Update Announcement</button>
                                    <button type="button" onclick="window.location.href='announcements.php'" class="btn btn-secondary">Cancel</button>
                                <?php else: ?>
                                    <button type="submit" class="btn btn-primary">Post Announcement</button>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>

                    <div class="table-container">
                        <h2 style="margin-bottom: 20px;">Recent Announcements</h2>
                        <div class="announcement-list" id="announcementList">
                            <?php if ($announcements->num_rows > 0): ?>
                                <?php while($announcement = $announcements->fetch_assoc()): ?>
                                    <div class="announcement-item" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px; background: #f9f9f9;">
                                        <h3 style="margin: 0 0 10px 0;"><?php echo $announcement['title']; ?></h3>
                                        <p style="margin: 0 0 10px 0; white-space: pre-wrap;"><?php echo $announcement['content']; ?></p>
                                        <small style="color: #666;">Posted on: <?php echo date('M j, Y g:i A', strtotime($announcement['created_at'])); ?></small>
                                                <div class="action-buttons">
                                                    <a href="announcements.php?edit_id=<?php echo $announcement['id']; ?>" class="btn">Edit</a>
                                                    <a href="announcements.php?delete_id=<?php echo $announcement['id']; ?>" class="btn-danger" onclick="return confirm('Are you sure you want to delete this student?')">Delete</a>
                                                </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="empty-state">No announcements yet</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script src="script.js"></script>
</body>
</html>