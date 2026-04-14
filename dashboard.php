<?php
session_start();

// Check if user is logged in OR anonymous
if (!isset($_SESSION['user_id']) && !isset($_SESSION['anon_id'])) {
    header('Location: login.php');
    exit();
}

// Connect to DB
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'mindhaven';
$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Prepare user data
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    $sql = "SELECT username, email FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        echo 'User not found.';
        exit();
    }

    $stmt->close();
} else {
    // Anonymous user
    $user = [
        'username' => 'Anonymous',
        'email' => 'Email Not Available'
    ];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MindHaven</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* MindHaven Dashboard - Mental Health Support App Styles */
        /* Using the exact color theme provided */

        /* Color Variables */
        :root {
            --soft-blue: #6CA8D6;
            --light-teal: #2E8B57; /* Changed from #A2D5C6 to darker sea green */
            --soft-lavender: #D9CFE8;
            --off-white: #F9F9F9;
            --muted-coral: #FFB6A0;
            --dark-gray: #2E2E2E;
            --medium-gray: #6E6E6E;
            --light-gray: #f0f0f0;
            --white: #ffffff;
            --shadow: rgba(0, 0, 0, 0.1);
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
            min-height: 100vh;
            padding: 0;
            margin: 0;
            display: flex;
        }

        /* Sidebar - Updated with calming colors */
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #f8fbff, #f0f8f5);
            color: var(--dark-gray);
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            overflow-y: auto;
            transition: transform 0.3s ease;
            z-index: 1000;
            box-shadow: 3px 0 15px rgba(108, 168, 214, 0.15);
            display: flex;
            flex-direction: column;
            border-right: 1px solid rgba(108, 168, 214, 0.2);
        }

        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%236CA8D6' fill-opacity='0.08' fill-rule='evenodd'/%3E%3C/svg%3E");
            opacity: 0.6;
            z-index: -1;
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(108, 168, 214, 0.2);
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, 0.5);
        }

        .sidebar-logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 1rem;
            display: block;
        }

        .sidebar-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            letter-spacing: 1px;
            color: var(--soft-blue);
        }

        .sidebar-subtitle {
            font-size: 0.9rem;
            color: var(--medium-gray);
            font-weight: 500;
        }

        /* User Profile in Sidebar */
        .sidebar-user {
            padding: 1.5rem;
            display: flex;
            align-items: center;
            border-bottom: 1px solid rgba(108, 168, 214, 0.15);
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, 0.3);
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--soft-blue), var(--light-teal));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: 600;
            margin-right: 1rem;
            border: 2px solid white;
            color: white;
            box-shadow: 0 2px 8px rgba(108, 168, 214, 0.3);
        }

        .user-info h4 {
            font-size: 1rem;
            margin-bottom: 0.2rem;
            color: var(--dark-gray);
            font-weight: 600;
        }

        .user-info p {
            font-size: 0.8rem;
            color: var(--medium-gray);
        }

        /* Navigation */
        .sidebar-nav {
            flex: 1;
            padding: 1rem 0;
            position: relative;
            z-index: 1;
            overflow-y: auto;
        }

        .nav-item {
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.8rem 1.5rem;
            color: var(--medium-gray);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            border-radius: 0 25px 25px 0;
            margin-right: 1rem;
        }

        .nav-link:hover {
            background-color: rgba(108, 168, 214, 0.1);
            color: var(--soft-blue);
            transform: translateX(5px);
        }

        .nav-link.active {
            background-color: rgba(108, 168, 214, 0.15);
            color: var(--soft-blue);
            font-weight: 600;
        }

        .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background-color: var(--soft-blue);
        }

        .nav-icon {
            width: 20px;
            margin-right: 1rem;
            text-align: center;
            font-size: 1.1rem;
        }

        .nav-text {
            font-size: 0.95rem;
        }

        /* Logout Section - Fixed positioning */
        .sidebar-footer {
            margin-top: auto;
            padding: 1.5rem;
            border-top: 1px solid rgba(108, 168, 214, 0.15);
            background: rgba(255, 255, 255, 0.4);
            position: relative;
            z-index: 1;
        }

        .logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 0.8rem;
            background: transparent;
            border: 1px solid var(--muted-coral);
            color: var(--muted-coral);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            font-weight: 500;
        }

        .logout-btn:hover {
            background-color: var(--muted-coral);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(255, 182, 160, 0.3);
        }

        .logout-btn i {
            margin-right: 0.5rem;
        }

        /* Main Content Area */
        .main-wrapper {
            flex: 1;
            margin-left: 280px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Mobile Sidebar Toggle */
        .sidebar-toggle {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1001;
            background: var(--soft-blue);
            color: white;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        /* Header - Updated with calming background */
        .header {
            background: linear-gradient(135deg, #f8fbff, #f0f8f5, #faf9ff);
            color: var(--dark-gray);
            padding: 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            border-bottom: 1px solid rgba(108, 168, 214, 0.1);
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%236CA8D6' fill-opacity='0.06' fill-rule='evenodd'/%3E%3C/svg%3E");
            opacity: 0.8;
            z-index: 0;
        }

        .welcome-text {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
            color: var(--dark-gray);
        }

        .tagline {
            font-size: 1.2rem;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
            color: var(--soft-blue);
            font-weight: 500;
        }

        .user-greeting {
            font-size: 1.1rem;
            margin-top: 1rem;
            position: relative;
            z-index: 1;
            color: var(--soft-blue);
            font-weight: 600;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            width: 100%;
        }

        .features-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 1rem;
        }

        .feature-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            height: 350px;
            display: flex;
            flex-direction: column;
        }

        .feature-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 8px;
        }

        .card-1::before {
            background-color: var(--soft-blue);
        }

        .card-2::before {
            background-color: var(--light-teal);
        }

        .card-3::before {
            background-color: var(--muted-coral);
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 2rem auto 1rem;
            font-size: 2rem;
            color: white;
            position: relative;
            z-index: 1;
        }

        .feature-icon::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: inherit;
            filter: blur(15px);
            opacity: 0.4;
            z-index: -1;
        }

        .icon-1 {
            background: linear-gradient(135deg, var(--soft-blue), #4A90E2);
        }

        .icon-2 {
            background: linear-gradient(135deg, var(--light-teal), #228B22); /* Updated gradient with darker green */
        }

        .icon-3 {
            background: linear-gradient(135deg, var(--muted-coral), #FF8A73);
        }

        .feature-title {
            font-size: 1.5rem;
            font-weight: 600;
            text-align: center;
            margin: 1rem 0;
            color: var(--dark-gray);
        }

        .feature-description {
            text-align: center;
            padding: 0 1.5rem;
            color: var(--medium-gray);
            margin-bottom: 1.5rem;
        }

        .feature-button {
            background: transparent;
            color: var(--dark-gray);
            border: 2px solid;
            border-radius: 30px;
            padding: 0.8rem 2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin: auto auto 2rem;
            display: block;
            text-decoration: none;
        }

        .button-1 {
            border-color: var(--soft-blue);
            color: var(--soft-blue);
        }

        .button-1:hover {
            background-color: var(--soft-blue);
            color: white;
        }

        .button-2 {
            border-color: var(--light-teal);
            color: var(--light-teal);
        }

        .button-2:hover {
            background-color: var(--light-teal);
            color: white;
        }

        .button-3 {
            border-color: var(--muted-coral);
            color: var(--muted-coral);
        }

        .button-3:hover {
            background-color: var(--muted-coral);
            color: white;
        }

        /* Pulse Animation - Updated for all buttons */
        @keyframes pulse-blue {
            0% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(108, 168, 214, 0.7);
            }
            
            70% {
                transform: scale(1.05);
                box-shadow: 0 0 0 10px rgba(108, 168, 214, 0);
            }
            
            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(108, 168, 214, 0);
            }
        }

        @keyframes pulse-teal {
            0% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(46, 139, 87, 0.7); /* Updated to match new darker green */
            }
            
            70% {
                transform: scale(1.05);
                box-shadow: 0 0 0 10px rgba(46, 139, 87, 0);
            }
            
            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(46, 139, 87, 0);
            }
        }

        @keyframes pulse-coral {
            0% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(255, 182, 160, 0.7);
            }
            
            70% {
                transform: scale(1.05);
                box-shadow: 0 0 0 10px rgba(255, 182, 160, 0);
            }
            
            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(255, 182, 160, 0);
            }
        }

        .pulse-blue {
            animation: pulse-blue 2s infinite;
        }

        .pulse-teal {
            animation: pulse-teal 2.5s infinite;
        }

        .pulse-coral {
            animation: pulse-coral 3s infinite;
        }

        /* Footer */
        .footer {
            background-color: white;
            padding: 1.5rem;
            text-align: center;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            margin-top: 2rem;
        }

        .footer-text {
            color: var(--medium-gray);
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
            }

            .sidebar-toggle {
                display: block;
            }

            .main-wrapper {
                margin-left: 0;
            }

            .features-container {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .header {
                padding: 1.5rem 1rem;
            }

            .welcome-text {
                font-size: 1.8rem;
            }

            .main-content {
                padding: 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
            }

            .main-content {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Sidebar Toggle -->
    <button class="sidebar-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <!-- Sidebar Header -->
        <div class="sidebar-header">
            <img src="assets/mindhavenlogo2.png" alt="MindHaven Logo" class="sidebar-logo">
            <h2 class="sidebar-title">MindHaven</h2>
            <p class="sidebar-subtitle">Mental Wellness Hub</p>
        </div>

        <!-- User Profile -->
        <div class="sidebar-user">
            <div class="user-avatar">
                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
            </div>
            <div class="user-info">
                <h4><?php echo htmlspecialchars($user['username']); ?></h4>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="sidebar-nav">
            <div class="nav-item">
                <a href="dashboard.php" class="nav-link active">
                    <i class="fas fa-home nav-icon"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="self_assessment.php" class="nav-link">
                    <i class="fas fa-clipboard-check nav-icon"></i>
                    <span class="nav-text">Self Assessment</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="chat.php" class="nav-link">
                    <i class="fas fa-comments nav-icon"></i>
                    <span class="nav-text">Chat Support</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="resources.php" class="nav-link">
                    <i class="fas fa-book-open nav-icon"></i>
                    <span class="nav-text">Resources</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="give_feedback.php" class="nav-link">
                    <i class="fas fa-comment-dots nav-icon"></i>
                    <span class="nav-text">Give Feedback</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="saved_resources.php" class="nav-link">
                    <i class="fas fa-bookmark nav-icon"></i>
                    <span class="nav-text">Saved Resources</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="tips_of_the_day.php" class="nav-link">
                    <i class="fas fa-lightbulb nav-icon"></i>
                    <span class="nav-text">Tips of the Day</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="contact_us.php" class="nav-link">
                    <i class="fas fa-envelope nav-icon"></i>
                    <span class="nav-text">Contact Us</span>
                </a>
            </div>
        </nav>

        <!-- Logout Section -->
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <!-- Main Content Wrapper -->
    <div class="main-wrapper">
        <!-- Header -->
        <header class="header">
            <h1 class="welcome-text">Welcome to MindHaven</h1>
            <p class="tagline">Your Safe Space for Mental Wellness</p>
            <p class="user-greeting">Hello, <?php echo htmlspecialchars($user['username']); ?>!</p>
        </header>

        <!-- Main Content -->
        <main class="main-content">
            <div class="features-container">
                <!-- Mental Health Self-Assessment -->
                <div class="feature-card card-1">
                    <div class="feature-icon icon-1">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                    <h2 class="feature-title">Mental Health Self-Assessment</h2>
                    <p class="feature-description">
                        Take our comprehensive assessment to understand your mental wellbeing and get personalized recommendations.
                    </p>
                    <a href="self_assessment.php" class="feature-button button-1 pulse-blue">Start Assessment</a>
                </div>

                <!-- Chat Support -->
                <div class="feature-card card-2">
                    <div class="feature-icon icon-2">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h2 class="feature-title">Chat Support</h2>
                    <p class="feature-description">
                        Connect with certified counsellor who can provide guidance and support for your mental health journey.
                    </p>
                    <a href="chat.php" class="feature-button button-2 pulse-teal">Chat with Counsellor</a>
                </div>

                <!-- Curated Resources -->
                <div class="feature-card card-3">
                    <div class="feature-icon icon-3">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <h2 class="feature-title">Curated Resources</h2>
                    <p class="feature-description">
                        Explore our collection of articles, videos, and exercises designed to support your mental wellness.
                    </p>
                    <a href="resources.php" class="feature-button button-3 pulse-coral">Browse Resources</a>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="footer">
            <p class="footer-text">&copy; 2025 MindHaven. All rights reserved.</p>
        </footer>
    </div>

    <script>
        // Sidebar toggle functionality
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.querySelector('.sidebar-toggle');
            
            if (window.innerWidth <= 992 && 
                !sidebar.contains(event.target) && 
                !toggle.contains(event.target) && 
                sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }
        });

        // Add hover effects and animations
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.feature-card');
            
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    cards.forEach(c => {
                        if (c !== card) {
                            c.style.opacity = '0.7';
                            c.style.transform = 'scale(0.95)';
                        }
                    });
                });
                
                card.addEventListener('mouseleave', function() {
                    cards.forEach(c => {
                        c.style.opacity = '1';
                        c.style.transform = '';
                    });
                });
            });

            // Handle window resize
            window.addEventListener('resize', function() {
                const sidebar = document.getElementById('sidebar');
                if (window.innerWidth > 992) {
                    sidebar.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>