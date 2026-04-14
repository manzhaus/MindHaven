<?php
session_start();
include 'db.php';

// If form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Check if email exists   
    $sql = "SELECT id, username, password, role FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result(); 
    
    if ($stmt->num_rows == 1) {
        $stmt->bind_result($id, $username, $hashed_password, $role);
        $stmt->fetch();
        
        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;
            
            if ($role === 'admin') {
                header('Location: admin_dashboard.php');
            } elseif ($role === 'counsellor') {
                header('Location: counsellor_dashboard.php');
            } else {
                header('Location: dashboard.php');
            }
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "Email not found.";
    }
    
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MindHaven</title>
    <style>
        /* MindHaven - Mental Health Support App Styles */
        /* Using the exact color theme provided */
        /* Color Variables */
        :root {
            --soft-blue: #6CA8D6;
            --light-teal: #A2D5C6;
            --soft-lavender: #D9CFE8;
            --off-white: #F9F9F9;
            --muted-coral: #FFB6A0;
            --dark-gray: #2E2E2E;
            --medium-gray: #6E6E6E;
        }

        /* Base styles and reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--off-white);
            color: var(--dark-gray);
            line-height: 1.6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 10px;
        }

        /* Login container */
        .login-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 25px;
            width: 100%;
            max-width: 380px;
            text-align: center;
            border-top: 4px solid var(--soft-blue);
        }

        /* Logo styling */
        .login-container img {
            max-width: 100px;
            margin-bottom: 15px;
        }

        /* Headings */
        .login-container h1 {
            color: var(--dark-gray);
            margin-bottom: 15px;
            font-size: 22px;
            font-weight: 600;
        }

        /* Form elements */
        form {
            text-align: left;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: var(--dark-gray);
            font-weight: 500;
            font-size: 14px;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            font-size: 15px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: var(--soft-blue);
            box-shadow: 0 0 0 3px rgba(108, 168, 214, 0.2);
        }

        /* Button styling */
        button[type="submit"] {
            background-color: var(--soft-blue);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 10px 20px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s;
        }

        button[type="submit"]:hover {
            background-color: #5A96C4;
            box-shadow: 0 2px 8px rgba(108, 168, 214, 0.3);
        }

        /* Links */
        a {
            color: var(--soft-blue);
            text-decoration: none;
            transition: color 0.3s;
        }

        a:hover {
            color: #5A96C4;
            text-decoration: underline;
        }

        /* Forgot password link - smaller */
        .forgot-password {
            font-size: 12px;
            margin-top: 8px;
            margin-bottom: 15px;
        }

        /* Register link container */
        .login-container p {
            margin-top: 15px;
            color: var(--medium-gray);
            padding-top: 15px;
            border-top: 1px solid rgba(0,0,0,0.05);
            font-size: 14px;
        }

        /* Error messages */
        p[style*="color: red"] {
            background-color: rgba(255, 182, 160, 0.1);
            border-left: 4px solid var(--muted-coral);
            color: #D64545 !important;
            padding: 8px 12px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-size: 13px;
            text-align: left;
        }

        /* Additional theme elements */
        .theme-accent {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(to right, 
                var(--soft-blue) 0%, 
                var(--soft-blue) 33%, 
                var(--light-teal) 33%, 
                var(--light-teal) 66%, 
                var(--soft-lavender) 66%, 
                var(--soft-lavender) 100%);
        }

        /* Form divider with accent colors */
        .form-divider {
            display: flex;
            align-items: center;
            margin: 12px 0;
            color: var(--medium-gray);
            font-size: 13px;
        }

        .form-divider::before,
        .form-divider::after {
            content: "";
            flex: 1;
            height: 1px;
        }

        .form-divider::before {
            background-color: var(--light-teal);
            margin-right: 10px;
        }

        .form-divider::after {
            background-color: var(--soft-lavender);
            margin-left: 10px;
        }

        /* Anonymous button */
        .anonymous-btn {
            display: inline-block;
            text-align: center;
            background-color: #00695C;
            color: #fff;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 500;
            margin-top: 8px;
            width: 100%;
            box-sizing: border-box;
        }

        /* Responsive adjustments */
        @media (max-width: 480px) {
            .login-container {
                padding: 20px;
                margin: 5px;
            }
            
            .login-container h1 {
                font-size: 20px;
            }
            
            input[type="email"],
            input[type="password"],
            button[type="submit"] {
                padding: 9px;
            }
        }
    </style>
</head>
<body>
    <div class="theme-accent"></div>
    <div class="login-container">
        <img src="assets/mindhavenlogo.png" alt="MindHaven Logo">
        <h1>Login to MindHaven</h1>
        <?php
        if (!empty($error)) {
            echo '<p style="color: red;">' . htmlspecialchars($error) . '</p>';
        }
        ?>
        <form action="login.php" method="POST">
            <label for="email">Email:</label>
            <input type="email" name="email" required>
            <label for="password">Password:</label>
            <input type="password" name="password" required>
            <button type="submit">Login</button>
        </form>
        <p class="forgot-password"><a href="forgot_password.php">Forgot your password?</a></p>
        <div class="form-divider">or</div>
        <a href="anonymous_start.php" class="anonymous-btn">Continue as Anonymous</a>
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>
</html>
