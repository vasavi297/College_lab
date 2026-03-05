<?php
// reports.php
session_start();
require_once '../db_connect.php';

// --- AJAX HANDLER FOR REPORTS ---
if (isset($_GET['action']) && $_GET['action'] === 'get_report') {
    $type = $_GET['type'] ?? '';
    $date = $_GET['date'] ?? '';
    
    $data = [];
    $query = "";
    
    if ($conn) {
        $date_filter = !empty($date) ? "WHERE DATE(created_at) = '" . mysqli_real_escape_string($conn, $date) . "'" : "";
        
        switch ($type) {
            case 'Students List':
                $query = "SELECT student_id as id, name, roll_number as info, branch as department, created_at FROM students $date_filter ORDER BY created_at DESC";
                break;
            case 'Employees List':
                $query = "SELECT employee_id as id, name, role as info, department, created_at FROM employees $date_filter ORDER BY created_at DESC";
                break;
            case 'Lab Assignments List':
                $query = "SELECT id, subject as name, section as info, branch as department, NULL as created_at FROM employee_subjects ORDER BY subject, section";
                break;
            case 'Subjects List':
                // Subjects table has no id/created_at columns in schema, so skip date filtering and order by name
                $query = "SELECT subject as id, subject as name, department, semester as info, NULL as created_at FROM subjects ORDER BY subject ASC";
                break;
        }

        if ($query) {
            $result = $conn->query($query);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $data[] = $row;
                }
            }
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// SESSION CHECK
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    // header('Location: index.php'); 
    // exit;
}

$display_name = 'Admin';
$username = htmlspecialchars($_SESSION['username'] ?? '', ENT_QUOTES);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>Reports | Admin</title>
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
    .form-card, .filter-card {
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
    .form-group { margin-bottom: 5px; }
    .form-group label { display: block; font-size: 12px; font-weight: 700; color: var(--text-gray); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
    .form-group select, .form-group input { width: 100%; padding: 10px 12px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 14px; color: var(--text-dark); transition: 0.2s; background: #fff; }
    .form-group select:focus, .form-group input:focus { outline: none; border-color: var(--accent-color); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
    
    .btn { padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s; border: none; font-size: 14px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; }
    .btn-primary { background: var(--accent-color); color: white; }
    .btn-primary:hover { background: var(--primary-color); }
    .btn-danger { background: #fee2e2; color: #b91c1c; }
    .btn-danger:hover { background: #fecaca; }
    .btn-success { background: #dcfce7; color: #15803d; }
    .btn-success:hover { background: #bbf7d0; }
    
    /* TABLE STYLES */
    .table-responsive { width: 100%; overflow-x: auto; background: var(--white); border-radius: var(--radius-lg); box-shadow: var(--shadow-card); border: 1px solid #f1f5f9; }
    table { width: 100%; border-collapse: separate; border-spacing: 0; }
    th { text-align: left; padding: 18px 24px; background: #f8fafc; color: var(--text-gray); font-size: 12px; font-weight: 700; text-transform: uppercase; border-bottom: 1px solid #e2e8f0; }
    td { padding: 16px 24px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: var(--text-dark); vertical-align: middle; }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: #f8fafc; }
    .empty-state { text-align: center; padding: 40px; color: var(--text-gray); }

    .status-badge { padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; background: #f1f5f9; color: var(--text-gray); }

    @media (max-width: 992px) {
        .sidebar { transform: translateX(-100%); width: 280px; }
        .sidebar.active { transform: translateX(0); }
        .main-content { margin-left: 0; width: 100%; }
        .overlay.active { opacity: 1; visibility: visible; }
    }

    @media (max-width: 768px) {
        .top-header { padding: 0 20px; }
        .dashboard-container { padding: 20px; }
        .form-card, .filter-card { padding: 20px; }
        .page-title { font-size: 20px; }
        
        .header-branding h1 { font-size: 14px; }
        .header-branding p { font-size: 9px; }
    }

    @media (max-width: 600px) {
        .header-branding { display: flex; flex-direction: column; }
        .header-branding h1 { font-size: 12px; line-height: 1.2; }
        .header-right .info-text { display: none; }
        
        .dashboard-container { padding: 15px; }
        .form-card, .filter-card { padding: 15px; }
        
        .header-logout-btn span { display: none; }
        .header-logout-btn { padding: 8px; }
        
        .form-grid { grid-template-columns: 1fr; }
        form > div[style*="display:flex"] { flex-direction: column; }
        .btn { width: 100%; }
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
            <a href="reports.php" class="menu-item active"> Reports</a>
             <a href="admin_control_pdf.php" class="menu-item"> Downloads</a>
            <a href="announcements.php" class="menu-item"> Announcements</a>
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
            <div class="page-title">Analytics & Reports</div>
            
            <div class="form-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid #f1f5f9; padding-bottom:15px;">
                    <h3 style="margin:0; color:var(--primary-color);">Generate Report</h3>
                </div>

                <form id="reportForm">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Report Type <span style="color:#dc2626">*</span></label>
                            <select id="reportType" required>
                                <option value="">Select report type</option>
                                <option value="Students List">Students List</option>
                                <option value="Employees List">Employees List</option>
                                <option value="Lab Assignments List">Lab Assignments List</option>
                                <option value="Subjects List">Subjects List</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Target Date</label>
                            <input type="date" id="reportDate_field">
                            <small style="color:var(--text-gray); font-size:11px;">Optional: Shows records added on this date</small>
                        </div>
                    </div>
                    
                    <div style="margin-top:24px; display:flex; gap:12px;">
                        <button class="btn btn-primary" type="button" onclick="generateReport()"><i class="fa-solid fa-chart-pie"></i> Generate Report</button>
                        <button class="btn btn-success" type="button" onclick="exportReport()" id="exportBtn" style="display:none;"><i class="fa-solid fa-download"></i> Export CSV</button>
                        <button class="btn btn-danger" type="button" onclick="clearForm()"><i class="fa-solid fa-rotate-left"></i> Reset</button>
                    </div>
                </form>
            </div>

            <div class="table-responsive" style="display:none;" id="reportContainer">
                <div style="padding: 16px 24px; border-bottom: 1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center;">
                    <h3 style="margin:0;" id="reportTitle">Report Results</h3>
                    <span id="reportDate" style="font-size:12px; color:var(--text-gray);"></span>
                </div>
                <div id="reportContent"></div>
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

        let currentReportData = [];
        let currentReportType = '';

        async function generateReport(){
          const type = document.getElementById('reportType').value;
          const date = document.getElementById('reportDate_field').value;
          
          if(!type) {
            alert('Please select a report type');
            return;
          }
          
          // Show report container and loading state
          document.getElementById('reportContainer').style.display = 'block';
          document.getElementById('reportContent').innerHTML = '<div style="padding:40px;text-align:center;color:var(--text-gray);"><i class="fa-solid fa-spinner fa-spin" style="font-size:24px;margin-bottom:10px;"></i><br>Fetching data...</div>';
          
          // Set report title and footer date
          document.getElementById('reportTitle').textContent = type + (date ? ' (' + date + ')' : '');
          const now = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
          document.getElementById('reportDate').textContent = 'Generated on: ' + now;

          try {
            const response = await fetch(`reports.php?action=get_report&type=${encodeURIComponent(type)}&date=${encodeURIComponent(date)}`);
            const data = await response.json();
            
            currentReportData = data;
            currentReportType = type;
            
            displayReportData(type, data, date);
            
            document.getElementById('exportBtn').style.display = 'inline-flex';
          } catch (error) {
            console.error('Error fetching report:', error);
            document.getElementById('reportContent').innerHTML = '<div style="color:#b91c1c;padding:20px;text-align:center;">Error loading data. Please try again.</div>';
          }
        }

        function displayReportData(type, data, dateFilter) {
            const contentDiv = document.getElementById('reportContent');
            
            if (!data || data.length === 0) {
                contentDiv.innerHTML = `<div class="empty-state">No records found ${dateFilter ? 'for ' + dateFilter : 'yet'}.</div>`;
                return;
            }

            let headers = '';
            let rows = '';

            // Table Header based on type
            switch(type) {
                case 'Students List':
                    headers = '<th>ID</th><th>Name</th><th>Roll Number</th><th>Branch</th><th>Date Added</th>';
                    break;
                case 'Employees List':
                    headers = '<th>ID</th><th>Name</th><th>Role</th><th>Department</th><th>Date Added</th>';
                    break;
                case 'Lab Assignments List':
                    headers = '<th>ID</th><th>Lab/Subject</th><th>Section</th><th>Branch</th>';
                    break;
                case 'Subjects List':
                    headers = '<th>ID</th><th>Subject Name</th><th>Department</th><th>Semester</th><th>Date Added</th>';
                    break;
            }

            data.forEach(item => {
              if (type === 'Lab Assignments List') {
                rows += `<tr>
                  <td>${item.id}</td>
                  <td style="font-weight:600;">${item.name}</td>
                  <td>${item.info}</td>
                  <td>${item.department}</td>
                </tr>`;
              } else {
                rows += `<tr>
                  <td>${item.id}</td>
                  <td style="font-weight:600;">${item.name}</td>
                  <td>${item.info}</td>
                  <td>${item.department}</td>
                  <td>${item.created_at || 'N/A'}</td>
                </tr>`;
              }
            });

            contentDiv.innerHTML = `
                <table>
                    <thead><tr>${headers}</tr></thead>
                    <tbody>${rows}</tbody>
                </table>
            `;
        }

        function exportReport() {
          if (!currentReportData || currentReportData.length === 0) {
            alert('Please generate a report first');
            return;
          }
          
          let headers = [];
          switch(currentReportType) {
            case 'Students List': headers = ['ID', 'Name', 'Roll Number', 'Branch', 'Date Added']; break;
            case 'Employees List': headers = ['ID', 'Name', 'Role', 'Department', 'Date Added']; break;
            case 'Lab Assignments List': headers = ['ID', 'Lab/Subject', 'Section', 'Branch']; break;
            case 'Subjects List': headers = ['ID', 'Subject Name', 'Department', 'Semester', 'Date Added']; break;
          }

          const csvRows = [];
          csvRows.push(headers.join(','));

          currentReportData.forEach(item => {
            let values;
            if (currentReportType === 'Lab Assignments List') {
              values = [
                item.id,
                `"${item.name}"`,
                `"${item.info}"`,
                `"${item.department}"`
              ];
            } else {
              values = [
                item.id,
                `"${item.name}"`,
                `"${item.info}"`,
                `"${item.department}"`,
                `"${item.created_at || ''}"`
              ];
            }
            csvRows.push(values.join(','));
          });

          const csvString = csvRows.join('\n');
          const blob = new Blob([csvString], { type: 'text/csv' });
          const url = window.URL.createObjectURL(blob);
          const a = document.createElement('a');
          a.setAttribute('hidden', '');
          a.setAttribute('href', url);
          a.setAttribute('download', `${currentReportType.replace(/\s+/g, '_')}_${new Date().toISOString().split('T')[0]}.csv`);
          document.body.appendChild(a);
          a.click();
          document.body.removeChild(a);
        }

        function clearForm() {
          document.getElementById('reportForm').reset();
          document.getElementById('reportContainer').style.display = 'none';
          document.getElementById('exportBtn').style.display = 'none';
          currentReportData = [];
          currentReportType = '';
        }
    </script>
</body>
</html>