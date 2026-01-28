<?php
session_start();

// Handle login POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Include database connection
    require_once '../db_connect.php';
    
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Use prepared statements for security
    $stmt = $conn->prepare("SELECT * FROM students WHERE username=? AND password=? LIMIT 1");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();

        // Store user data in session
        $_SESSION['student_id'] = $row['student_id'];
        $_SESSION['name'] = $row['name'];
        $_SESSION['roll_number'] = $row['roll_number'];
        $_SESSION['branch'] = $row['branch'];
        $_SESSION['semester'] = $row['semester'];
        $_SESSION['email'] = $row['email'];
        $_SESSION['phone'] = $row['phone'];

        $stmt->close();
        $conn->close();
        
        header("Location: profile.php");
        exit();
    } else {
        $error = "Invalid username or password";
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Student Login - College Project</title>
  <link rel="icon" href="../images/vasavi.png" type="image/png">
  <link rel="stylesheet" href="style.css">
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

    .logo {
      width: 80px;
      height: auto;
      margin-bottom: 20px;
    }

    /* ORIGINAL STYLE FOR LABELS - Keeping the <br> tags styling */
    .input-box {
      margin-top: 6px;
      margin-bottom: 6px;
      width: 100%;
    }

    .input-box label {
      font-weight: 600;
      color: #232e45;
      letter-spacing: 0.05em;
      display: inline-block;
      margin-bottom: 8px; /* Space between label and input */
    }

    .input-box input {
      width: 100%;
      padding: 10px 16px;
      border: none;
      border-radius: 9px;
      background: #eef2f7;
      font-size: 1rem;
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
      color: #ff4757;
      background: #ff475710;
      padding: 10px;
      border-radius: 8px;
      margin-top: 15px;
      text-align: center;
      font-weight: 500;
      width: 100%;
      box-sizing: border-box;
    }
  </style>
</head>
<body>
  <div class="centered-container">
    <div class="login-box">
      <h2 class="login-heading">Student Login</h2>
      
      <div class="logo">
        <img src="../images/vasavi.png" alt="College Logo" style="width:80px;height:auto;">
      </div>

      <!-- Display error message if login failed -->
      <?php if (isset($error)): ?>
        <div class="error-message">
          <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <!-- Login form - now submits to itself -->
      <form id="loginForm" method="post" action="">
        <div class="input-box">
          <!-- Keeping the ORIGINAL structure with <br> tags -->
          Username:<br><br>
          <input type="text" id="username" name="username" placeholder="Enter your username" required>
        </div>
        
        <div class="input-box">
          <!-- Keeping the ORIGINAL structure with <br> tags -->
          Password:<br><br>
          <input type="password" id="password" name="password" placeholder="Enter your password" required>
        </div>
        
        <button type="submit" class="sign-in-btn">Log In</button>
      </form>
    </div>
  </div>

  <!-- Optional JavaScript -->
  <script>
    document.getElementById('loginForm').addEventListener('submit', function(e) {
      // Basic client-side validation (optional)
      const username = document.getElementById('username').value.trim();
      const password = document.getElementById('password').value.trim();
      
      if (!username || !password) {
        e.preventDefault();
        alert('Please fill in both username and password');
      }
    });
  </script>
</body>
</html>