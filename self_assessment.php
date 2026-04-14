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
        'email' => 'Not Available'
    ];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Self Assessment - MindHaven</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* MindHaven Self Assessment - Enhanced UI */
        /* Using the exact color theme from dashboard */

        /* Color Variables - New Clinical Theme */
        :root {
            --primary-purple: #8B5FBF;
            --secondary-orange: #FF8C42;
            --accent-teal: #20B2AA;
            --warm-cream: #FFF8F0;
            --soft-peach: #FFE5D9;
            --deep-navy: #2C3E50;
            --medium-slate: #5D6D7E;
            --light-silver: #F8F9FA;
            --pure-white: #FFFFFF;
            --clinical-green: #27AE60;
            --warning-amber: #F39C12;
            --error-crimson: #E74C3C;
        }

        /* Base styles and reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--warm-cream);
            color: var(--deep-navy);
            line-height: 1.6;
            min-height: 100vh;
            padding: 0;
            margin: 0;
            display: flex;
        }

        /* Sidebar - Same as dashboard */
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #f5f3ff, #fff8f0);
            color: var(--deep-navy);
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            overflow-y: auto;
            transition: transform 0.3s ease;
            z-index: 1000;
            box-shadow: 3px 0 15px rgba(139, 95, 191, 0.15);
            display: flex;
            flex-direction: column;
            border-right: 1px solid rgba(139, 95, 191, 0.2);
        }

        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%238B5FBF' fill-opacity='0.08' fill-rule='evenodd'/%3E%3C/svg%3E");
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
            color: var(--primary-purple);
        }

        .sidebar-subtitle {
            font-size: 0.9rem;
            color: var(--medium-slate);
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
            background: linear-gradient(135deg, var(--primary-purple), var(--accent-teal));
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
            color: var(--deep-navy);
            font-weight: 600;
        }

        .user-info p {
            font-size: 0.8rem;
            color: var(--medium-slate);
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
            color: var(--medium-slate);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            border-radius: 0 25px 25px 0;
            margin-right: 1rem;
        }

        .nav-link:hover {
            background-color: rgba(139, 95, 191, 0.1);
            color: var(--primary-purple);
            transform: translateX(5px);
        }

        .nav-link.active {
            background-color: rgba(139, 95, 191, 0.15);
            color: var(--primary-purple);
            font-weight: 600;
        }

        .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background-color: var(--primary-purple);
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

        /* Logout Section */
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
            border: 1px solid var(--secondary-orange);
            color: var(--secondary-orange);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            font-weight: 500;
        }

        .logout-btn:hover {
            background-color: var(--secondary-orange);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(255, 140, 66, 0.3);
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
            background: var(--primary-purple);
            color: white;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #f5f3ff, #fff8f0, #ffe5d9);
            color: var(--deep-navy);
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
            background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%238B5FBF' fill-opacity='0.06' fill-rule='evenodd'/%3E%3C/svg%3E");
            opacity: 0.8;
            z-index: 0;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
            color: var(--deep-navy);
        }

        .page-subtitle {
            font-size: 1.2rem;
            position: relative;
            z-index: 1;
            color: var(--primary-purple);
            font-weight: 500;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            max-width: 800px;
            margin: 0 auto;
            padding: 3rem 2rem;
            width: 100%;
        }

        .assessments-container {
            display: flex;
            flex-direction: column;
            gap: 2rem;
            margin-top: 1rem;
        }

        .assessment-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            transition: all 0.4s ease;
            position: relative;
            border: 1px solid rgba(108, 168, 214, 0.1);
        }

        .assessment-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.12);
        }

        .assessment-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
        }

        .assessment-card:nth-child(1)::before {
            background: linear-gradient(90deg, var(--primary-purple), #9B59B6);
        }

        .assessment-card:nth-child(2)::before {
            background: linear-gradient(90deg, var(--secondary-orange), #E67E22);
        }

        .card-content {
            padding: 2.5rem;
            position: relative;
        }

        .assessment-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
            color: white;
            position: relative;
        }

        .assessment-card:nth-child(1) .assessment-icon {
            background: linear-gradient(135deg, var(--primary-purple), #9B59B6);
        }

        .assessment-card:nth-child(2) .assessment-icon {
            background: linear-gradient(135deg, var(--secondary-orange), #E67E22);
        }

        .assessment-icon::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: inherit;
            filter: blur(15px);
            opacity: 0.3;
            z-index: -1;
        }

        .assessment-title {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--deep-navy);
            line-height: 1.3;
        }

        .assessment-description {
            font-size: 1.1rem;
            color: var(--medium-slate);
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .button-group {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .assessment-btn {
            display: inline-flex;
            align-items: center;
            padding: 0.9rem 1.8rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border: 2px solid;
        }

        .assessment-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .assessment-btn:hover::before {
            left: 100%;
        }

        .primary-btn {
            background-color: var(--primary-purple);
            color: white;
            border-color: var(--primary-purple);
        }

        .primary-btn:hover {
            background-color: #9B59B6;
            border-color: #9B59B6;
            box-shadow: 0 8px 20px rgba(139, 95, 191, 0.3);
        }

        .secondary-btn {
            background-color: transparent;
            color: var(--accent-teal);
            border-color: var(--accent-teal);
        }

        .secondary-btn:hover {
            background-color: var(--accent-teal);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(32, 178, 170, 0.3);
        }

        .assessment-card:nth-child(2) .primary-btn {
            background-color: var(--secondary-orange);
            border-color: var(--secondary-orange);
        }

        .assessment-card:nth-child(2) .primary-btn:hover {
            background-color: #E67E22;
            border-color: #E67E22;
            box-shadow: 0 8px 20px rgba(255, 140, 66, 0.3);
        }

        .assessment-card:nth-child(2) .secondary-btn {
            color: var(--primary-purple);
            border-color: var(--primary-purple);
        }

        .assessment-card:nth-child(2) .secondary-btn:hover {
            background-color: var(--primary-purple);
            color: white;
            box-shadow: 0 8px 20px rgba(139, 95, 191, 0.3);
        }

        .btn-icon {
            margin-right: 0.5rem;
            font-size: 0.9rem;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            margin-top: 3rem;
            padding: 1rem 2rem;
            background: rgba(255, 255, 255, 0.8);
            color: var(--medium-slate);
            text-decoration: none;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 1px solid rgba(108, 168, 214, 0.2);
            backdrop-filter: blur(10px);
        }

        .back-link:hover {
            background: white;
            color: var(--primary-purple);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .back-icon {
            margin-right: 0.5rem;
            font-size: 0.9rem;
        }

        /* Responsive Design */
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

            .header {
                padding: 1.5rem 1rem;
            }

            .page-title {
                font-size: 2rem;
            }

            .main-content {
                padding: 2rem 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
            }

            .main-content {
                padding: 1.5rem 1rem;
            }

            .card-content {
                padding: 2rem 1.5rem;
            }

            .button-group {
                flex-direction: column;
            }

            .assessment-btn {
                justify-content: center;
            }

            .page-title {
                font-size: 1.8rem;
            }

            .assessment-title {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .card-content {
                padding: 1.5rem 1rem;
            }

            .assessment-title {
                font-size: 1.3rem;
            }

            .assessment-description {
                font-size: 1rem;
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
                <a href="dashboard.php" class="nav-link">
                    <i class="fas fa-home nav-icon"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="self_assessment.php" class="nav-link active">
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
            <h1 class="page-title">Self Assessment</h1>
            <p class="page-subtitle">Choose a mental health assessment to understand your wellbeing</p>
        </header>

        <!-- Main Content -->
        <main class="main-content">
            <div class="assessments-container">
                <!-- PHQ-9 Depression Test -->
                <div class="assessment-card">
                    <div class="card-content">
                        <div class="assessment-icon">
                            <i class="fas fa-brain"></i>
                        </div>
                        <h2 class="assessment-title">PHQ-9 Depression Test</h2>
                        <p class="assessment-description">
                            Assess your symptoms of depression with this standard tool used by healthcare professionals worldwide. This comprehensive questionnaire helps identify depression severity and track progress over time.
                        </p>
                        <div class="button-group">
                            <a href="phq9.php" class="assessment-btn primary-btn">
                                <i class="fas fa-play btn-icon"></i>
                                Start PHQ-9
                            </a>
                            <a href="phq9_history.php" class="assessment-btn secondary-btn">
                                <i class="fas fa-history btn-icon"></i>
                                View PHQ-9 History
                            </a>
                        </div>
                    </div>
                </div>

                <!-- GAD-7 Anxiety Test -->
                <div class="assessment-card">
                    <div class="card-content">
                        <div class="assessment-icon">
                            <i class="fas fa-heart-pulse"></i>
                        </div>
                        <h2 class="assessment-title">GAD-7 Anxiety Test</h2>
                        <p class="assessment-description">
                            Measure your level of anxiety using this widely used assessment tool. The GAD-7 helps identify generalized anxiety disorder and provides insights into your anxiety levels and patterns.
                        </p>
                        <div class="button-group">
                            <a href="gad7.php" class="assessment-btn primary-btn">
                                <i class="fas fa-play btn-icon"></i>
                                Start GAD-7
                            </a>
                            <a href="gad7_history.php" class="assessment-btn secondary-btn">
                                <i class="fas fa-history btn-icon"></i>
                                View GAD-7 History
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <a class="back-link" href="dashboard.php">
                <i class="fas fa-arrow-left back-icon"></i>
                Back to Dashboard
            </a>
        </main>
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

        // Handle window resize
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            if (window.innerWidth > 992) {
                sidebar.classList.remove('active');
            }
        });

        // Add subtle animations on scroll
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.assessment-card');
            
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
                observer.observe(card);
            });
        });
    </script>
</body>
</html>
