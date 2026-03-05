<?php
session_start();

// Handle Student Login POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login_type']) && $_POST['login_type'] == 'student') {
    require_once 'db_connect.php';
    
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $error_student = '';

    if (empty($username) || empty($password)) {
        $error_student = "Please enter username and password";
    } else {
        // Use prepared statements for security
        $stmt = $conn->prepare("SELECT student_id, name, roll_number, branch, semester, section, email, phone, linkedin, github 
                                FROM students 
                                WHERE username = ? AND password = ? LIMIT 1");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();

            // Store user data in session
            $_SESSION['student_id'] = $row['student_id'];
            $_SESSION['name']       = $row['name'];
            $_SESSION['roll_number']= $row['roll_number'];
            $_SESSION['branch']     = $row['branch'];
            $_SESSION['section']    = $row['section'];
            $_SESSION['semester']   = $row['semester'];
            $_SESSION['email']      = $row['email'];
            $_SESSION['phone']      = $row['phone'];
            $_SESSION['linkedin']   = $row['linkedin'];
            $_SESSION['github']     = $row['github'];
            $_SESSION['user_type']  = 'student';

            $stmt->close();
            $conn->close();
            
            header("Location: student/profile.php");
            exit();
        } else {
            $error_student = "Invalid username or password";
        }
        $stmt->close();
    }
    $conn->close();
}

// Handle Employee Login POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login_type']) && $_POST['login_type'] == 'employee') {
    require_once 'db_connect.php';
    
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $error_employee = '';
    
    if (empty($username) || empty($password)) {
        $error_employee = "Username and password are required!";
    } else {
        // Check if employee exists
        $sql = "SELECT * FROM employees WHERE username = ? AND password = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $employee = $result->fetch_assoc();
            
            // Check if account is active
            if ($employee['is_active'] != 1) {
                $error_employee = "Your account is inactive. Please contact administrator.";
            } else {
                // Set session variables
                $_SESSION['employee_id'] = $employee['employee_id'];
                $_SESSION['employee_name'] = $employee['name'];
                $_SESSION['username'] = $employee['username'];
                $_SESSION['email'] = $employee['email'];
                $_SESSION['department'] = $employee['department'];
                $_SESSION['name'] = $employee['name'];
                $_SESSION['role'] = $employee['role'];
                $_SESSION['user_type'] = 'employee';
                
                // Update last login time
                $update_sql = "UPDATE employees SET last_login = NOW() WHERE employee_id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("i", $employee['employee_id']);
                $update_stmt->execute();
                
                // Redirect based on role
                if ($employee['role'] == 'admin') {
                    header("Location: admin/admin_dashboard.php");
                } else {
                    header("Location: employee/employee_dashboard.php");
                }
                exit();
            }
        } else {
            $error_employee = "Invalid username or password!";
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SVEC - Lab Record Digitalization Portal</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            min-height: 100vh;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
        }

        /* Hero Section */
        .hero {
            position: relative;
            width: 100%;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            padding: 50px 20px 20px 20px;
            margin: 0;
            margin-bottom: 0;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background-image: url('./images/svec-campus.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            filter: blur(5px) brightness(0.8);
            z-index: 1;
        }

        .hero::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background: linear-gradient(135deg, rgba(60, 80, 120, 0.35) 0%, rgba(80, 100, 150, 0.45) 100%);
            z-index: 2;
        }

        /* SVEC Logo */
        .logo-container {
            margin-bottom: -15px;
            z-index: 10;
            animation: slideDown 0.8s ease-out;
            display: flex;
            justify-content: center;
        }

        .svec-logo {
            width: 120px;
            height: 120px;
            object-fit: contain;
            filter: drop-shadow(0 4px 15px rgba(0, 0, 0, 0.3));
        }

        /* College Header */
        .college-header {
            text-align: center;
            margin-bottom: 20px;
            z-index: 10;
            animation: slideDown 0.8s ease-out 0.2s both;
        }

        .college-header h1 {
            font-size: 42px;
            color: rgba(255, 230, 5, 0.826);
            font-weight: 700;
            letter-spacing: 0.5px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            margin-bottom: 5px;
        }

        .college-header p {
            font-size: 16px;
            color: rgb(255, 255, 255);
            font-weight: 400;
            letter-spacing: 0.3px;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Login Container */
        .login-container {
            display: flex;
            gap: 90px;
            align-items: center;
            z-index: 10;
            max-width: 700px;
            width: 100%;
            animation: fadeInUp 1s ease-out 0.3s both;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Login Card */
        .login-card {
            flex: 1;
            background: white;
            border-radius: 16px;
            padding: 30px 25px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            min-height: 350px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Card Logo */
        .card-logo {
            text-align: center;
            margin-bottom: 25px;
        }

        .card-logo-circle {
            width: 80px;
            height: 80px;
            margin: 0 auto;
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
        }

        .card-logo-text {
            color: white;
            font-weight: bold;
            font-size: 24px;
        }

        .login-card h2 {
            font-size: 28px;
            color: #1e3a8a;
            margin-bottom: 25px;
            font-weight: 600;
            text-align: center;
        }

        /* Form Elements */
        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            color: #334155;
            font-weight: 500;
            margin-bottom: 7px;
            font-size: 14px;
            letter-spacing: 0.3px;
        }

        .form-group input {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #f8fafc;
            color: #333;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-group input::placeholder {
            color: #94a3b8;
        }

        .form-group input:focus {
            outline: none;
            background: white;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        /* Login Button */
        .login-btn {
            width: 100%;
            padding: 12px 20px;
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 15px;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.35);
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(37, 99, 235, 0.5);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        /* Top Right Button */
        .top-right-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 100;
            padding: 10px 24px;
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.35);
        }

        .top-right-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(37, 99, 235, 0.5);
        }

        .top-right-btn:active {
            transform: translateY(0);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .top-right-btn {
                padding: 8px 16px;
                font-size: 13px;
                top: 15px;
                right: 15px;
            }

            .svec-logo {
                width: 100px;
                height: 100px;
            }

            .college-header h1 {
                font-size: 32px;
            }

            .college-header p {
                font-size: 14px;
            }

            .college-header {
                margin-bottom: 35px;
            }

            .login-container {
                flex-direction: column;
                gap: 25px;
                max-width: 400px;
            }

            .login-card {
                padding: 30px 22px;
                min-height: 330px;
            }

            .login-card h2 {
                font-size: 24px;
                margin-bottom: 20px;
            }

            .card-logo-circle {
                width: 70px;
                height: 70px;
            }

            .card-logo-text {
                font-size: 20px;
            }
        }

        @media (max-width: 480px) {
            .top-right-btn {
                padding: 8px 14px;
                font-size: 12px;
                top: 12px;
                right: 12px;
            }

            .svec-logo {
                width: 80px;
                height: 80px;
            }

            .college-header h1 {
                font-size: 24px;
            }

            .college-header p {
                font-size: 12px;
            }

            .college-header {
                margin-bottom: 30px;
            }

            .login-card {
                padding: 30px 22px;
                min-height: auto;
                border-radius: 12px;
            }

            .login-card h2 {
                font-size: 20px;
                margin-bottom: 18px;
            }

            .form-group input {
                padding: 10px 12px;
                font-size: 13px;
            }

            .login-btn {
                padding: 11px 16px;
                font-size: 14px;
                margin-top: 12px;
            }
        }
    </style>
</head>

<body>

    <!-- Top Right Button -->
    <button class="top-right-btn" onclick="handleEcap()">ecap</button>

    <!-- Hero Section -->
    <section class="hero">
        <!-- College Header -->
        <div class="college-header">
            <!-- SVEC Logo -->
            <div class="logo-container">
                <img src="./images/svec-logo.png" alt="SVEC Logo" class="svec-logo">
            </div>
            <h1>Sri Vasavi Engineering College(Autonomous)</h1>
            <p>Lab Record Digitalization Portal</p>
        </div>

        <!-- Login Container -->
        <div class="login-container">
            <!-- Employee Login Card -->
            <div class="login-card">
                <div>
                    <h2>Employee Login</h2>
                    <?php if (isset($error_employee) && !empty($error_employee)): ?>
                    <div
                        style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 12px; border-radius: 9px; margin-bottom: 20px; text-align: center; width: 100%; font-size: 0.95rem; font-weight: 500; border: 1px solid rgba(239, 68, 68, 0.2);">
                        <?php echo htmlspecialchars($error_employee); ?>
                    </div>
                    <?php endif; ?>
                    <form method="POST" action="">
                        <input type="hidden" name="login_type" value="employee">
                        <div class="form-group">
                            <label for="employee-username">Username:</label>
                            <input type="text" id="employee-username" name="username" placeholder="Enter your username"
                                required>
                        </div>
                        <div class="form-group">
                            <label for="employee-password">Password:</label>
                            <input type="password" id="employee-password" name="password"
                                placeholder="Enter your password" required>
                        </div>
                        <button type="submit" class="login-btn">Log In</button>
                    </form>
                </div>
            </div>

            <!-- Student Login Card -->
            <div class="login-card">
                <div>
                    <h2>Student Login</h2>
                    <?php if (isset($error_student) && !empty($error_student)): ?>
                    <div
                        style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 12px; border-radius: 9px; margin-bottom: 20px; text-align: center; width: 100%; font-size: 0.95rem; font-weight: 500; border: 1px solid rgba(239, 68, 68, 0.2);">
                        <?php echo htmlspecialchars($error_student); ?>
                    </div>
                    <?php endif; ?>
                    <form method="POST" action="">
                        <input type="hidden" name="login_type" value="student">
                        <div class="form-group">
                            <label for="student-username">Username:</label>
                            <input type="text" id="student-username" name="username" placeholder="Enter your username"
                                required>
                        </div>
                        <div class="form-group">
                            <label for="student-password">Password:</label>
                            <input type="password" id="student-password" name="password"
                                placeholder="Enter your password" required>
                        </div>
                        <button type="submit" class="login-btn">Log In</button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- About College Section -->
    <section
        style="background-color: rgba(30, 58, 138, 0.95); color: #fff; padding: 80px 40px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
        <div style="max-width: 1200px; margin: 0 auto;">
            <h2
                style="color: rgba(255, 230, 5, 0.826); font-size: 48px; text-align: center; margin-bottom: 60px; font-weight: 700;">
                About College</h2>

            <p style="color: #fff; font-size: 18px; line-height: 1.8; text-align: justify;">
                Sri Vasavi Engineering College is a leading institution committed to excellence in engineering education
                and innovation. The college provides a serene learning environment supported by modern infrastructure
                and advanced laboratories. With a highly qualified faculty and industry-aligned curriculum, it fosters
                technical expertise and ethical values among students. The institution emphasizes research, creativity,
                and real-world problem-solving through projects and industry collaboration. Sri Vasavi Engineering
                College is dedicated to shaping competent professionals who contribute positively to society and the
                global technological landscape.
            </p>
        </div>
    </section>

    <script>
        function handleEcap() {
            window.open('http://sves.org.in/ecap/', '_blank');
        }
    </script>

    <!-- Footer Section -->
    <footer
        style="background-color: rgba(30, 58, 138, 0.95); color: #fff; padding: 30px 40px 15px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; position: relative; z-index: 100; border-top: 2px solid rgba(255, 255, 255, 0.1);">
        <div
            style="max-width: 1400px; margin: 0 auto; display: grid; grid-template-columns: 1fr 2fr 1fr; gap: 40px; margin-bottom: 20px; align-items: start;">
            <!-- College Info Section (Left) -->
            <div style="display: flex; flex-direction: column; height: 100%;">
                <div>
                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                        <img src="./images/svec-logo.png" alt="SVEC Logo"
                            style="width: 60px; height: 60px; object-fit: contain;">
                        <div style="text-align: left;">
                            <h3 style="color: rgba(255, 230, 5, 0.826); font-size: 22px; margin: 0; line-height: 1.2;">
                                Sri Vasavi</h3>
                            <h3 style="color: rgba(255, 230, 5, 0.826); font-size: 18px; margin: 0; line-height: 1.2;">
                                Engineering College</h3>
                        </div>
                    </div>
                    <p style="color: #ccc; line-height: 1.6; margin-bottom: 15px; font-size: 15px; text-align: left;">
                        A premier institution fostering engineering excellence, innovation, and holistic development
                        through
                        modern infrastructure and industry-aligned education.</p>
                </div>
                <div style="display: flex; gap: 15px; justify-content: flex-start; margin-top: 10px;">
                    <a href="#" style="color: #1DA1F2; font-size: 20px; transition: opacity 0.3s;"
                        onmouseover="this.style.opacity='0.7'" onmouseout="this.style.opacity='1'">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" />
                        </svg>
                    </a>
                    <a href="#" style="color: #1877F2; font-size: 20px; transition: opacity 0.3s;"
                        onmouseover="this.style.opacity='0.7'" onmouseout="this.style.opacity='1'">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                        </svg>
                    </a>
                    <a href="#" style="color: #0A66C2; font-size: 20px; transition: opacity 0.3s;"
                        onmouseover="this.style.opacity='0.7'" onmouseout="this.style.opacity='1'">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                        </svg>
                    </a>
                    <a href="#" style="color: #E4405F; font-size: 20px; transition: opacity 0.3s;"
                        onmouseover="this.style.opacity='0.7'" onmouseout="this.style.opacity='1'">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Departments Section (Middle) -->
            <div style="display: flex; flex-direction: column; height: 100%; justify-content: center; padding: 0 20px;">
                <h2
                    style="color: rgba(255, 230, 5, 0.826); font-size: 22px; margin-bottom: 25px; font-weight: 600; text-align: center;">
                    Departments</h2>
                <div
                    style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; justify-content: center; align-items: start;">
                    <!-- Departments Column 1 -->
                    <div>
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            <li style="margin-bottom: 14px; display: flex; align-items: flex-start;">
                                <div
                                    style="width: 6px; height: 6px; background-color: rgba(255, 230, 5, 0.826); border-radius: 50%; margin-top: 8px; margin-right: 12px; flex-shrink: 0;">
                                </div>
                                <span
                                    style="color: #ccc; font-size: 15px; font-weight: 500; display: block; text-align: left; line-height: 1.4;">
                                    CSE (Computer Science & Engineering)</span>
                            </li>
                            <li style="margin-bottom: 14px; display: flex; align-items: flex-start;">
                                <div
                                    style="width: 6px; height: 6px; background-color: rgba(255, 230, 5, 0.826); border-radius: 50%; margin-top: 8px; margin-right: 12px; flex-shrink: 0;">
                                </div>
                                <span
                                    style="color: #ccc; font-size: 15px; font-weight: 500; display: block; text-align: left; line-height: 1.4;">
                                    CST (Computer Science & Technology)</span>
                            </li>
                            <li style="margin-bottom: 14px; display: flex; align-items: flex-start;">
                                <div
                                    style="width: 6px; height: 6px; background-color: rgba(255, 230, 5, 0.826); border-radius: 50%; margin-top: 8px; margin-right: 12px; flex-shrink: 0;">
                                </div>
                                <span
                                    style="color: #ccc; font-size: 15px; font-weight: 500; display: block; text-align: left; line-height: 1.4;">
                                    CAI (Computer Applications & Informatics)</span>
                            </li>
                            <li style="margin-bottom: 14px; display: flex; align-items: flex-start;">
                                <div
                                    style="width: 6px; height: 6px; background-color: rgba(255, 230, 5, 0.826); border-radius: 50%; margin-top: 8px; margin-right: 12px; flex-shrink: 0;">
                                </div>
                                <span
                                    style="color: #ccc; font-size: 15px; font-weight: 500; display: block; text-align: left; line-height: 1.4;">
                                    AIML (Artificial Intelligence & Machine Learning)</span>
                            </li>
                            <li style="margin-bottom: 14px; display: flex; align-items: flex-start;">
                                <div
                                    style="width: 6px; height: 6px; background-color: rgba(255, 230, 5, 0.826); border-radius: 50%; margin-top: 8px; margin-right: 12px; flex-shrink: 0;">
                                </div>
                                <span
                                    style="color: #ccc; font-size: 15px; font-weight: 500; display: block; text-align: left; line-height: 1.4;">
                                    CDS (Computer & Data Science)</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Departments Column 2 -->
                    <div>
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            <li style="margin-bottom: 14px; display: flex; align-items: flex-start;">
                                <div
                                    style="width: 6px; height: 6px; background-color: rgba(255, 230, 5, 0.826); border-radius: 50%; margin-top: 8px; margin-right: 12px; flex-shrink: 0;">
                                </div>
                                <span
                                    style="color: #ccc; font-size: 15px; font-weight: 500; display: block; text-align: left; line-height: 1.4;">
                                    ECE (Electronics & Communication Engineering)</span>
                            </li>
                            <li style="margin-bottom: 14px; display: flex; align-items: flex-start;">
                                <div
                                    style="width: 6px; height: 6px; background-color: rgba(255, 230, 5, 0.826); border-radius: 50%; margin-top: 8px; margin-right: 12px; flex-shrink: 0;">
                                </div>
                                <span
                                    style="color: #ccc; font-size: 15px; font-weight: 500; display: block; text-align: left; line-height: 1.4;">
                                    ECT (Electronics & Computer Technology)</span>
                            </li>
                            <li style="margin-bottom: 14px; display: flex; align-items: flex-start;">
                                <div
                                    style="width: 6px; height: 6px; background-color: rgba(255, 230, 5, 0.826); border-radius: 50%; margin-top: 8px; margin-right: 12px; flex-shrink: 0;">
                                </div>
                                <span
                                    style="color: #ccc; font-size: 15px; font-weight: 500; display: block; text-align: left; line-height: 1.4;">
                                    EEE (Electrical & Electronics Engineering)</span>
                            </li>
                            <li style="margin-bottom: 14px; display: flex; align-items: flex-start;">
                                <div
                                    style="width: 6px; height: 6px; background-color: rgba(255, 230, 5, 0.826); border-radius: 50%; margin-top: 8px; margin-right: 12px; flex-shrink: 0;">
                                </div>
                                <span
                                    style="color: #ccc; font-size: 15px; font-weight: 500; display: block; text-align: left; line-height: 1.4;">
                                    ME (Mechanical Engineering)</span>
                            </li>
                            <li style="margin-bottom: 14px; display: flex; align-items: flex-start;">
                                <div
                                    style="width: 6px; height: 6px; background-color: rgba(255, 230, 5, 0.826); border-radius: 50%; margin-top: 8px; margin-right: 12px; flex-shrink: 0;">
                                </div>
                                <span
                                    style="color: #ccc; font-size: 15px; font-weight: 500; display: block; text-align: left; line-height: 1.4;">
                                    CE (Civil Engineering)</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Contact Info (Right) -->
            <div style="display: flex; flex-direction: column; height: 100%; justify-content: space-between;">
                <div>
                    <h3
                        style="color: rgba(255, 230, 5, 0.826); font-size: 22px; margin-bottom: 25px; font-weight: 600; text-align: right;">
                        Contact Info</h3>
                    <div style="color: #ccc; line-height: 1.8; margin: 0; font-size: 15px; text-align: right;">
                        <p
                            style="margin: 0 0 10px 0; display: flex; align-items: flex-start; justify-content: flex-end;">
                            <span style="flex-shrink: 0; margin-left: 10px;">📍</span>
                            <span style="text-align: right;">
                                Sri Vasavi Engineering College<br>
                                Pedatadepalli, Tadepalligudem<br>
                                534101 West Godavari District<br>
                                Andhra Pradesh.
                            </span>
                        </p>
                        <p style="margin: 0 0 10px 0; display: flex; align-items: center; justify-content: flex-end;">
                            <span style="text-align: right;">
                                +918818284322<br>
                                <span style="font-size: 13px; color: #aaa;">(9:30AM - 4:30PM)</span>
                            </span>
                        </p>
                        <p style="margin: 0; display: flex; align-items: center; justify-content: flex-end;">
                            <span style="text-align: right;">
                                principal@srivasaviengg.ac.in
                            </span>
                        </p>
                    </div>
                </div>
                <div style="margin-top: auto; padding-top: 20px;">
                    <!-- Optional: Add any additional content here if needed -->
                </div>
            </div>
        </div>

        <!-- Lab Matrix Credit -->
        <div
            style="text-align: center; ">
            <p style="color: #ffffff; font-size: 16px; font-weight: 600; ">A Mark by</p>
            <div>
                <a href="Contributors/contributors.html" target="_blank" style="display: inline-block; margin-bottom: 10px;">
                    <img src="./images/image.png" alt="Lab Matrix" style="height: 140px; object-fit: contain;">
                </a>
                <p style="color: #ffffff; font-size: 14px; font-weight: 500; ">© 2026 Sri Vasavi Engineering
                College. All Rights Reserved.</p>
            </div>
            
        </div>
    </footer>
</body>

</html>