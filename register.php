<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($email) && !empty($password)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if email already exists
        $check_sql = "SELECT id FROM users WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $error = "Email already registered.";
        } else {
            // Insert new user
            $insert_sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("sss", $username, $email, $hashed_password);

            if ($insert_stmt->execute()) {
                header('Location: login.php');
                exit();
            } else {
                $error = "Registration failed. Please try again.";
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    } else {
        $error = "Please fill in all fields.";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - MindHaven</title>
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
            padding: 20px;
        }

        /* Register container */
        .register-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 400px;
            text-align: center;
            border-top: 4px solid var(--light-teal);
        }

        /* Logo styling */
        .register-container img {
            max-width: 120px;
            margin-bottom: 20px;
        }

        /* Headings */
        .register-container h1 {
            color: var(--dark-gray);
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: 600;
        }

        /* Form elements */
        form {
            text-align: left;
        }

        label {
            display: block;
            margin-bottom: 6px;
            color: var(--dark-gray);
            font-weight: 500;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: var(--light-teal);
            box-shadow: 0 0 0 3px rgba(162, 213, 198, 0.2);
        }

        /* Button styling */
        button[type="submit"] {
            background-color: var(--light-teal);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 12px 20px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s;
        }

        button[type="submit"]:hover {
            background-color: #8BC0B0;
            box-shadow: 0 2px 8px rgba(162, 213, 198, 0.3);
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

        /* Login link container */
        .register-container p {
            margin-top: 25px;
            color: var(--medium-gray);
            padding-top: 20px;
            border-top: 1px solid rgba(0,0,0,0.05);
        }

        /* Error messages */
        p[style*="color: red"] {
            background-color: rgba(255, 182, 160, 0.1);
            border-left: 4px solid var(--muted-coral);
            color: #D64545 !important;
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-size: 14px;
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

        /* Password strength indicator */
        .password-strength {
            height: 5px;
            margin-top: -15px;
            margin-bottom: 15px;
            border-radius: 3px;
            background-color: #e0e0e0;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            width: 0;
            transition: width 0.3s, background-color 0.3s;
        }

        /* Responsive adjustments */
        @media (max-width: 480px) {
            .register-container {
                padding: 20px;
            }
            
            .register-container h1 {
                font-size: 22px;
            }
            
            input[type="text"],
            input[type="email"],
            input[type="password"],
            button[type="submit"] {
                padding: 10px;
            }
        }
    </style>
    <script>
        // Simple password strength checker
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.querySelector('input[name="password"]');
            
            if (passwordInput) {
                // Create password strength elements
                const strengthDiv = document.createElement('div');
                strengthDiv.className = 'password-strength';
                const strengthBar = document.createElement('div');
                strengthBar.className = 'password-strength-bar';
                strengthDiv.appendChild(strengthBar);
                
                // Insert after password input
                passwordInput.parentNode.insertBefore(strengthDiv, passwordInput.nextSibling);
                
                // Update strength on input
                passwordInput.addEventListener('input', function() {
                    const value = passwordInput.value;
                    let strength = 0;
                    
                    if (value.length >= 8) strength += 25;
                    if (value.match(/[A-Z]/)) strength += 25;
                    if (value.match(/[0-9]/)) strength += 25;
                    if (value.match(/[^A-Za-z0-9]/)) strength += 25;
                    
                    strengthBar.style.width = strength + '%';
                    
                    if (strength <= 25) {
                        strengthBar.style.backgroundColor = '#FF6B6B';
                    } else if (strength <= 50) {
                        strengthBar.style.backgroundColor = '#FFD166';
                    } else if (strength <= 75) {
                        strengthBar.style.backgroundColor = '#06D6A0';
                    } else {
                        strengthBar.style.backgroundColor = '#118AB2';
                    }
                });
            }
        });
    </script>
</head>
<body>
    <div class="theme-accent"></div>
    <div class="register-container">
        <img src="assets/mindhavenlogo.png" alt="MindHaven Logo">
        <h1>Create an Account</h1>

        <?php
        if (!empty($error)) {
            echo '<p style="color: red;">' . htmlspecialchars($error) . '</p>';
        }
        ?>

        <form action="register.php" method="POST">
            <label for="username">Username:</label>
            <input type="text" name="username" required>

            <label for="email">Email:</label>
            <input type="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" name="password" required>
            <!-- Password strength indicator will be inserted here by JavaScript -->

            <button type="submit">Register</button>
        </form>

        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>