<?php
// announcements.php
session_start();
require_once '../db_connect.php';

// SESSION CHECK
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    // header('Location: index.php'); 
    // exit;
}

$display_name = 'Admin';
$username = htmlspecialchars($_SESSION['username'] ?? 'admin', ENT_QUOTES);

// Handle Form Submission
$message = '';

// Ensure announcements have unique IDs even if table lacks AUTO_INCREMENT
function normalizeAnnouncementIds($conn) {
    if (!$conn) { return; }

    // Find the current max id
    $maxId = 0;
    if ($res = $conn->query("SELECT COALESCE(MAX(id), 0) AS max_id FROM announcements")) {
        if ($row = $res->fetch_assoc()) {
            $maxId = (int)$row['max_id'];
        }
        $res->free();
    }

    // Pick rows missing an id and assign sequential ids based on created_at order
    if ($res = $conn->query("SELECT created_at, title FROM announcements WHERE id IS NULL OR id = 0 ORDER BY created_at ASC")) {
        while ($row = $res->fetch_assoc()) {
            $maxId += 1;
            $createdAt = $row['created_at'];
            $title = $row['title'];
            $stmt = $conn->prepare("UPDATE announcements SET id = ? WHERE (id IS NULL OR id = 0) AND created_at = ? AND title = ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param("iss", $maxId, $createdAt, $title);
                $stmt->execute();
                $stmt->close();
            }
        }
        $res->free();
    }
}

normalizeAnnouncementIds($conn);

// Handle Delete Announcement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_announcement') {
    $id = (int)$_POST['id'];
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM announcements WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $message = "<div class='alert alert-success'><i class='fa-solid fa-check'></i> Announcement deleted successfully!</div>";
                } else {
                    $message = "<div class='alert alert-error'>Announcement not found for deletion.</div>";
                }
            } else {
                $message = "<div class='alert alert-error'>Error deleting announcement: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }
    }
}

// Handle Edit Announcement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_announcement') {
    $id = (int)$_POST['id'];
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);

    if ($id > 0 && $title && $desc) {
        $stmt = $conn->prepare("UPDATE announcements SET title = ?, description = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("ssi", $title, $desc, $id);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $message = "<div class='alert alert-success'><i class='fa-solid fa-check'></i> Announcement updated successfully!</div>";
                } else {
                    $message = "<div class='alert alert-error'>No announcement updated. Please refresh and try again.</div>";
                }
            } else {
                $message = "<div class='alert alert-error'>Error updating announcement: " . $stmt->error . "</div>";
            }
            $stmt->close();
        }
    } else {
        $message = "<div class='alert alert-error'>Please fill all required fields.</div>";
    }
}

// Handle Add Announcement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_announcement') {
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);

    if ($title && $desc) {
        // Ensure we set an explicit ID in case the table lacks AUTO_INCREMENT
        $next_id = 1;
        if ($result = $conn->query("SELECT COALESCE(MAX(id), 0) + 1 AS next_id FROM announcements")) {
            if ($row = $result->fetch_assoc()) {
                $next_id = (int)$row['next_id'];
            }
            $result->free();
        }

        $stmt = $conn->prepare("INSERT INTO announcements (id, title, description) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("iss", $next_id, $title, $desc);
            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'><i class='fa-solid fa-check'></i> Announcement posted successfully!</div>";
            } else {
                $message = "<div class='alert alert-error'>Error: " . $stmt->error . "</div>";
            }
            $stmt->close();
        } else {
             $message = "<div class='alert alert-error'>Database Error: " . $conn->error . "</div>";
        }
    } else {
        $message = "<div class='alert alert-error'>Please fill all required fields.</div>";
    }
}

// Fetch Announcements
$announcements = [];
if ($conn) {
    $result = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC, id DESC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $announcements[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Announcements | Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    :root {
        --primary-color: #1e3a8a;
        --primary-light: #eff6ff;
        --accent-color: #2563eb;
        --secondary-color: #dc2626;
        --text-dark: #0f172a;
        --text-gray: #64748b;
        --bg-body: #f1f5f9;
        --white: #ffffff;
        
        --sidebar-width: 290px;
        --header-height: 74px;
        
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow-card: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        --radius-lg: 16px;
        --radius-md: 12px;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
    
    html, body {
        height: 100%;
        width: 100%;
        overflow: hidden;
        background-color: var(--bg-body);
        color: var(--text-dark);
    }
    
    a { text-decoration: none; color: inherit; }

    /* ================= SIDEBAR ================= */
    .sidebar {
        width: var(--sidebar-width);
        background: var(--white);
        height: 100vh;
        position: fixed;
        left: 0; top: 0; z-index: 100;
        border-right: 1px solid #e2e8f0;
        display: flex; flex-direction: column;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 10px 0 30px rgba(0,0,0,0.03);
    }
    .sidebar.closed { transform: translateX(-100%); }

    .sidebar-brand { height: 90px; display: flex; align-items: center; justify-content: space-between; padding: 0 24px; border-bottom: 1px solid #f1f5f9; flex-shrink: 0; }
    .brand-wrapper { display: flex; align-items: center; gap: 12px; }
    .brand-wrapper img { height: 52px; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1)); }
    .brand-text { display: flex; flex-direction: column; line-height: 1.1; }
    .brand-title { font-size: 24px; font-weight: 800; color: var(--primary-color); letter-spacing: -0.5px; }
    .brand-subtitle { font-size: 10px; text-transform: uppercase; color: var(--text-gray); font-weight: 600; letter-spacing: 1px; }

    .close-btn { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-gray); transition: 0.2s; }
    .close-btn:hover { background: #f1f5f9; color: var(--secondary-color); }

    .sidebar-user { padding: 24px; text-align: center; background: linear-gradient(to bottom, #ffffff, #f8fafc); border-bottom: 1px solid #e2e8f0; flex-shrink: 0; }
    .sidebar-user img { width: 70px; height: 70px; border-radius: 50%; object-fit: cover; border: 4px solid var(--white); box-shadow: var(--shadow-card); margin-bottom: 10px; }

    .sidebar-menu { padding: 20px 16px; flex: 1; overflow-y: auto; }
    .menu-item { display: flex; align-items: center; padding: 14px 18px; margin-bottom: 6px; border-radius: var(--radius-md); color: var(--text-gray); font-weight: 500; font-size: 14px; transition: all 0.2s; border: 1px solid transparent; }
    .menu-item i { width: 26px; font-size: 18px; margin-right: 12px; color: #94a3b8; transition: 0.2s; }
    .menu-item:hover { background-color: #f8fafc; color: var(--primary-color); border-color: #e2e8f0; }
    .menu-item:hover i { color: var(--primary-color); }
    .menu-item.active { background: linear-gradient(45deg, var(--primary-color), #2563eb); color: var(--white); box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3); }
    .menu-item.active i { color: var(--white); }

    .logout-container { padding: 20px; border-top: 1px solid #f1f5f9; flex-shrink: 0; }
    .logout-btn { display: flex; justify-content: center; align-items: center; width: 100%; padding: 12px; border-radius: var(--radius-md); background-color: #fef2f2; color: var(--secondary-color); font-weight: 600; font-size: 14px; transition: 0.2s; border: 1px solid #fee2e2; }
    .logout-btn:hover { background-color: #fee2e2; transform: translateY(-1px); }

    /* ================= MAIN CONTENT ================= */
    .main-content { 
        margin-left: var(--sidebar-width);
        width: calc(100% - var(--sidebar-width));
        height: 100vh;
        overflow-y: auto;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .main-content.full-width { margin-left: 0; width: 100%; }

    .top-header { 
        height: var(--header-height); 
        background: rgba(255, 255, 255, 0.9); 
        backdrop-filter: blur(10px); 
        display: flex; justify-content: space-between; align-items: center; 
        padding: 0 32px; 
        position: sticky; top: 0; z-index: 90; 
        border-bottom: 1px solid #e2e8f0; 
    }
    .header-branding h1 { font-size: 18px; font-weight: 800; color: var(--primary-color); }
    .header-branding p { font-size: 11px; color: var(--text-gray); font-weight: 600; letter-spacing: 0.5px; }
    .toggle-btn { font-size: 20px; cursor: pointer; padding: 8px; border-radius: 8px; border: none; background: transparent; margin-right: 15px; color: var(--text-dark); }
    
    .dashboard-container { padding: 32px; max-width: 1400px; margin: 0 auto; }
    .overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.4); backdrop-filter: blur(2px); z-index: 95; opacity: 0; visibility: hidden; transition: 0.3s; }

    /* --- PAGE SPECIFIC --- */
    .page-title { font-size: 24px; font-weight: 800; color: var(--text-dark); margin-bottom: 24px; }

    /* FORM STYLES */
    .form-card {
        background: var(--white);
        padding: 28px;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-card);
        border: 1px solid #f1f5f9;
        margin-bottom: 30px;
    }
    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        align-items: end;
    }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; font-size: 12px; font-weight: 700; color: var(--text-gray); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
    .form-group select, .form-group input, .form-group textarea { width: 100%; padding: 10px 12px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 14px; color: var(--text-dark); transition: 0.2s; background: #fff; font-family: inherit; }
    .form-group select:focus, .form-group input:focus, .form-group textarea:focus { outline: none; border-color: var(--accent-color); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
    
    .btn { padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s; border: none; font-size: 14px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; }
    .btn-primary { background: var(--accent-color); color: white; }
    .btn-primary:hover { background: var(--primary-color); }
    .btn-danger { background: #fee2e2; color: #b91c1c; }
    .btn-danger:hover { background: #fecaca; }
    .btn-edit { background: #dcfce7; color: #15803d; }
    
    /* ANNOUNCEMENT LIST */
    .list-card {
        background: var(--white);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-card);
        border: 1px solid #f1f5f9;
        overflow: hidden;
    }
    .announcement-item { padding: 20px 28px; border-bottom: 1px solid #f1f5f9; transition: background 0.2s; }
    .announcement-item:last-child { border-bottom: none; }
    .announcement-item:hover { background: #f8fafc; }
    
    .item-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px; }
    .item-title { font-size: 16px; font-weight: 700; color: var(--text-dark); margin-bottom: 4px; }
    .item-date { font-size: 12px; font-weight: 600; color: var(--text-gray); display:flex; align-items:center; gap:6px; }
    .item-desc { font-size: 14px; color: #475569; line-height: 1.6; white-space: pre-wrap; }
    
    .empty-state { text-align: center; padding: 40px; color: var(--text-gray); }

    /* ALERTS */
    .alert { padding: 16px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; display: flex; align-items: center; gap: 10px; }
    .alert-success { background: #f0fdf4; color: #15803d; border: 1px solid #dcfce7; }
    .alert-error { background: #fef2f2; color: #b91c1c; border: 1px solid #fee2e2; }

    @media (max-width: 992px) {
        .sidebar { transform: translateX(-100%); width: 280px; }
        .sidebar.active { transform: translateX(0); }
        .main-content { margin-left: 0; width: 100%; }
        .overlay.active { opacity: 1; visibility: visible; }
    }

    @media (max-width: 768px) {
        .top-header { padding: 0 20px; }
        .dashboard-container { padding: 20px; }
        .form-card { padding: 20px; }
        .page-title { font-size: 20px; }
        
        .header-branding h1 { font-size: 14px; }
        .header-branding p { font-size: 9px; }
        
        .announcement-item { padding: 15px 20px; }
    }

    @media (max-width: 600px) {
        .header-branding { display: flex; flex-direction: column; }
        .header-branding h1 { font-size: 12px; line-height: 1.2; }
        .header-right .info-text { display: none; }
        
        .dashboard-container { padding: 15px; }
        .form-card { padding: 15px; }
        
        .header-logout-btn span { display: none; }
        .header-logout-btn { padding: 8px; }
        
        .item-header { flex-direction: column; gap: 10px; }
        .item-header > div:last-child { align-self: flex-start; }
    }
</style>
</head>
<body>

    <div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

    <nav class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-wrapper">
                <img src="../images/vasavi.png" alt="Logo">
                <div class="brand-text">
                    <span class="brand-title">SVEC</span>
                    <span class="brand-subtitle">Administration</span>
                </div>
            </div>
            <div class="close-btn" onclick="toggleSidebar()"><i class="fa-solid fa-xmark"></i></div>
        </div>
        <div class="sidebar-user">
            <img src="https://ui-avatars.com/api/?name=Admin&background=1e3a8a&color=fff&size=128">
            <div style="font-weight:700; color:var(--text-dark);">Administrator</div>
            <div style="font-size:12px; color:var(--text-gray);">admin</div>
        </div>
        <div class="sidebar-menu">
            <a href="admin_dashboard.php" class="menu-item"> Dashboard</a>
            <a href="students.php" class="menu-item"> Students</a>
            <a href="employees.php" class="menu-item"> Employees</a>
            <a href="subjects.php" class="menu-item"> Subjects</a>
            <a href="timetable.php" class="menu-item"> Timetable</a>
            <a href="reports.php" class="menu-item"> Reports</a>
             <a href="admin_control_pdf.php" class="menu-item"> Downloads</a>
            <a href="announcements.php" class="menu-item active"> Announcements</a>
        </div>
        <div class="logout-container">
            <a href="logout.php" class="logout-btn"><i class="fa-solid fa-arrow-right-from-bracket" style="margin-right:8px;"></i> Logout</a>
        </div>
    </nav>

    <main class="main-content" id="mainContent">
        <header class="top-header">
            <div style="display:flex; align-items:center;">
                <button class="toggle-btn" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
                <div class="header-branding">
                    <h1>SRI VASAVI ENGINEERING COLLEGE</h1>
                </div>
            </div>
            <div class="header-right" style="display:flex; align-items:center; gap:15px;">
                <div class="info-text" style="text-align:right;">
                    <div style="font-size:14px; font-weight:700; color:var(--text-dark);">Administrator</div>
                    <div style="font-size:11px; color:var(--text-gray); font-weight:600;">Admin</div>
                </div>
                <img src="https://ui-avatars.com/api/?name=Admin&background=1e3a8a&color=fff" style="width:42px; height:42px; border-radius:50%; border:2px solid #e2e8f0;">
                <a href="logout.php" class="header-logout-btn" style="display: flex; align-items: center; gap: 6px; padding: 8px 14px; background: #fef2f2; color: var(--secondary-color); border: 1px solid #fecaca; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none; transition: all 0.2s;"><i class="fa-solid fa-arrow-right-from-bracket"></i> <span>LOG OUT</span></a>
            </div>
        </header>

        <div class="dashboard-container">
            <div class="page-title">Campus Announcements</div>
            
            <?= $message ?>

            <div class="form-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid #f1f5f9; padding-bottom:15px;">
                    <h3 id="formTitle" style="margin:0; color:var(--primary-color);">Create Announcement</h3>
                </div>

                <form method="post" action="announcements.php" id="announcementForm">
                    <input type="hidden" name="action" id="formAction" value="add_announcement">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="form-group">
                        <label>Title <span style="color:#dc2626">*</span></label>
                        <input name="title" id="form_title" required placeholder="Announcement Title">
                    </div>
                    
                    <div class="form-group">
                        <label>Description <span style="color:#dc2626">*</span></label>
                        <textarea name="description" id="form_description" rows="4" required placeholder="Announcement Details"></textarea>
                    </div>
                    
                    <div style="margin-top:24px; display:flex; gap:12px;">
                        <button class="btn btn-primary" type="submit" id="submitBtn"><i class="fa-solid fa-bullhorn"></i> Post Announcement</button>
                        <button class="btn btn-danger" type="button" onclick="cancelEdit()" id="cancelBtn" style="display:none;"><i class="fa-solid fa-xmark"></i> Cancel</button>
                    </div>
                </form>
            </div>

            <div class="page-title" style="font-size:18px; margin-bottom:15px;">Recent Announcements</div>
            
            <div class="list-card">
                <?php if (count($announcements) > 0): ?>
                  <?php foreach ($announcements as $a): ?>
                    <div class="announcement-item">
                        <div class="item-header">
                            <div>
                                <div class="item-title"><?= htmlspecialchars($a['title']) ?></div>
                                <div class="item-date"><i class="fa-regular fa-calendar" style="font-size:11px;"></i> <?= date('M d, Y • h:i A', strtotime($a['created_at'])) ?></div>
                            </div>
                            <div style="display:flex; gap:8px;">
                                <button 
                                    class="btn btn-edit" 
                                    style="padding:6px 12px; font-size:12px;"
                                    data-id="<?= $a['id'] ?>"
                                    data-title="<?= htmlspecialchars($a['title'], ENT_QUOTES) ?>"
                                    data-desc="<?= htmlspecialchars(base64_encode($a['description']), ENT_QUOTES) ?>"
                                    onclick="editAnnouncement(this)">
                                    <i class="fa-solid fa-pen"></i> Edit
                                </button>
                                <form method="post" action="announcements.php" style="margin:0;" onsubmit="return confirm('Are you sure you want to delete this announcement?');">
                                    <input type="hidden" name="action" value="delete_announcement">
                                    <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                    <button type="submit" class="btn btn-danger" style="padding:6px 12px; font-size:12px;">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="item-desc"><?= nl2br(htmlspecialchars($a['description'])) ?></div>
                    </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <div class="empty-state">No announcements posted yet.</div>
                <?php endif; ?>
            </div>
            
        </div>
    </main>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const overlay = document.getElementById('overlay');
            if (window.innerWidth > 992) {
                sidebar.classList.toggle('closed');
                mainContent.classList.toggle('full-width');
            } else {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
            }
        }

        function editAnnouncement(button) {
            const id = button.dataset.id;
            const title = button.dataset.title || '';
            const desc = button.dataset.desc ? atob(button.dataset.desc) : '';
            
            document.getElementById('formTitle').textContent = 'Edit Announcement';
            document.getElementById('formAction').value = 'edit_announcement';
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '<i class="fa-solid fa-check"></i> Update Announcement';
            document.getElementById('cancelBtn').style.display = 'inline-flex';
            
            document.getElementById('edit_id').value = id;
            document.getElementById('form_title').value = title;
            document.getElementById('form_description').value = desc;
            
            document.querySelector('.form-card').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        function cancelEdit() {
            document.getElementById('formTitle').textContent = 'Create Announcement';
            document.getElementById('formAction').value = 'add_announcement';
            document.getElementById('submitBtn').innerHTML = '<i class="fa-solid fa-bullhorn"></i> Post Announcement';
            document.getElementById('cancelBtn').style.display = 'none';
            
            document.getElementById('announcementForm').reset();
        }
    </script>
</body>
</html>
