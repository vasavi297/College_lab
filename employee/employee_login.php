<?php
session_start();
include '../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Check if employee exists
    $sql = "SELECT * FROM employees WHERE username = ? AND password = ? AND is_active = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $employee = $result->fetch_assoc();
        
        // Set session variables
        $_SESSION['employee_id'] = $employee['employee_id'];
        $_SESSION['employee_name'] = $employee['name'];
        $_SESSION['username'] = $employee['username'];
        $_SESSION['email'] = $employee['email'];
        $_SESSION['assigned_lab'] = $employee['lab_assigned'];
        $_SESSION['department'] = $employee['department'];
        $_SESSION['role'] = 'employee';
        
        // Update last login time
        $update_sql = "UPDATE employees SET last_login = NOW() WHERE employee_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $employee['employee_id']);
        $update_stmt->execute();
        
        header("Location: employee_dashboard.php");
        exit();
    } else {
        $error = "Invalid credentials or account inactive";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Employee Login - Sri Vasavi Engineering College</title>
  <link rel="icon" href="../images/vasavi.png" type="image/png">
  <style>
    .centered-container {
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
    }
    
    body {
      margin: 0;
      padding: 0;
      min-height: 100vh;
      font-family: 'Poppins', 'Segoe UI', Arial, sans-serif;
      background-image: url('../images/login.jpg');
      background-size: cover;
      background-repeat: no-repeat;
      background-position: center center;
      position: relative;
    }

    body::before {
      content: "";
      position: fixed;
      left: 0; top: 0; right: 0; bottom: 0;
      background: rgba(0, 0, 0, 0.25);
      z-index: 0;
    }

    .centered-container {
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      position: relative;
      z-index: 1;
    }

    .login-box {
      background: rgba(255, 255, 255, 0.85);
      border-radius: 28px;
      padding: 32px 38px 54px 38px;
      box-shadow: 0 8px 40px 0 rgba(0,0,0,0.19);
      backdrop-filter: blur(7px);
      min-width: 300px;
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    
    .login-heading {
      text-align: center;
      margin: 0 0 15px 0;
      color: #232e45;
      font-weight: 700;
      font-size: 1.5rem;
      letter-spacing: 0.02em;
    }
    
    .college-title {
      text-align: center;
      margin-bottom: 20px;
    }
    
    .college-title h1 {
      font-size: 1.2rem;
      color: #232e45;
      margin: 0 0 5px 0;
      font-weight: 700;
    }
    
    .college-title p {
      font-size: 0.9rem;
      color: #64748b;
      margin: 0;
      font-weight: 500;
    }

    .icon-container {
      width: 64px;
      height: 64px;
      margin-bottom: 10px;
      background: url('../images/icon.png') no-repeat center center;
      background-size: contain;
    }

    .login-box label {
      display: block;
      font-weight: 600;
      color: #232e45;
      margin-top: 16px;
      letter-spacing: 0.05em;
    }

    .input-box {
      margin-top: 6px;
      margin-bottom: 6px;
      width: 100%;
    }

    .input-box input {
      width: 100%;
      padding: 10px 16px;
      border: none;
      border-radius: 9px;
      background: #eef2f7;
      font-size: 1rem;
      margin-bottom: 4px;
      box-sizing: border-box;
      transition: box-shadow 0.2s;
    }

    .input-box input:focus {
      box-shadow: 0 0 0 2px #4f8cff33;
      outline: none;
    }

    .sign-in-btn {
      width: 100%;
      margin-top: 22px;
      background: linear-gradient(90deg, #375dff, #548cff);
      color: #fff;
      font-size: 1.07rem;
      font-weight: 700;
      border: none;
      padding: 12px;
      border-radius: 9px;
      cursor: pointer;
      letter-spacing: 0.05em;
      box-shadow: 0 2px 6px rgba(75, 122, 251, 0.16);
      transition: background 0.2s, box-shadow 0.2s;
    }
    
    .sign-in-btn:hover {
      background: linear-gradient(90deg, #548cff, #375dff);
      box-shadow: 0 4px 12px rgba(75, 122, 251, 0.24);
    }
    
    .error-message {
      background: rgba(239, 68, 68, 0.1);
      color: #ef4444;
      padding: 12px;
      border-radius: 9px;
      margin-bottom: 20px;
      text-align: center;
      width: 100%;
      font-size: 0.95rem;
      font-weight: 500;
      border: 1px solid rgba(239, 68, 68, 0.2);
    }
    
    .login-footer {
      text-align: center;
      margin-top: 25px;
      color: #64748b;
      font-size: 0.85rem;
      font-weight: 500;
      width: 100%;
    }
  </style>
</head>
<body>
  <div class="centered-container">
    <div class="login-box">
      
      
      <h2 class="login-heading">Employee Login</h2>
      
      <div class="logo">
        <img src="../images/vasavi.png" alt="Logo" style="width:80px;height:auto;">
      </div>
      
      <?php if (isset($error)): ?>
        <div class="error-message"><?php echo $error; ?></div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="input-box">
           Username:<br><br>
           <input type="text" name="username" placeholder="Enter your username" required autofocus>
        </div>
        <div class="input-box">
           Password:<br><br>
           <input type="password" name="password" placeholder="Enter your password" required>
        </div>
        <button type="submit" class="sign-in-btn">Login as Employee</button>
      </form>
      
     
    </div>
  </div>
</body>
</html>
<?php $conn->close(); ?>