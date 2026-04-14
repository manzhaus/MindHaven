<?php
session_start();
include 'db.php';

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

    $sql_user = "SELECT username, email FROM users WHERE id = ?";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bind_param('i', $user_id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();

    if ($result_user->num_rows > 0) {
        $user = $result_user->fetch_assoc();
    } else {
        echo 'User not found.';
        exit();
    }

    $stmt_user->close();
} else {
    // Anonymous user
    $user = [
        'username' => 'Anonymous',
        'email' => 'Not Available'
    ];
}

$userId = $_SESSION['user_id'] ?? null;
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $rating = intval($_POST['rating'] ?? 0);

    if (empty($message)) {
        $error = 'Please enter your feedback message.';
    } elseif ($rating < 1 || $rating > 5) {
        $error = 'Please provide a rating between 1 and 5 stars.';
    } else {
        $stmt = $conn->prepare("INSERT INTO feedbacks (user_id, name, email, message, rating) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isssi", $userId, $name, $email, $message, $rating);

        if ($stmt->execute()) {
            $success = 'Thank you for your feedback! Your input helps us improve MindHaven.';
            // Clear form fields after successful submission
            $name = $email = $message = '';
            $rating = 0;
        } else {
            $error = 'Failed to submit feedback. Please try again.';
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Give Feedback - MindHaven</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* MindHaven Feedback Page - Enhanced UI */
        /* Using the clinical theme with feedback-specific styling */

        /* Color Variables - Clinical Theme */
        :root {
            --primary-blue: #6CA8D6;
            --secondary-purple: #8B5FBF;
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
            --star-gold: #FFB400;
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

        /* Sidebar - Same as other pages */
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #e3f2fd, #fff8f0);
            color: var(--deep-navy);
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
            color: var(--primary-blue);
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
            background: linear-gradient(135deg, var(--primary-blue), var(--accent-teal));
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
            background-color: rgba(108, 168, 214, 0.1);
            color: var(--primary-blue);
            transform: translateX(5px);
        }

        .nav-link.active {
            background-color: rgba(108, 168, 214, 0.15);
            color: var(--primary-blue);
            font-weight: 600;
        }

        .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background-color: var(--primary-blue);
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
            border: 1px solid var(--secondary-purple);
            color: var(--secondary-purple);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            font-weight: 500;
        }

        .logout-btn:hover {
            background-color: var(--secondary-purple);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(139, 95, 191, 0.3);
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
            background: var(--primary-blue);
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
            background: linear-gradient(135deg, #e3f2fd, #fff8f0, #ffe5d9);
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
            background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%236CA8D6' fill-opacity='0.06' fill-rule='evenodd'/%3E%3C/svg%3E");
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
            color: var(--primary-blue);
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

        /* Feedback Container */
        .feedback-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            position: relative;
            border: 1px solid rgba(108, 168, 214, 0.1);
        }

        .feedback-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, var(--primary-blue), var(--accent-teal));
        }

        .feedback-header {
            padding: 2.5rem 2.5rem 1.5rem;
            text-align: center;
            background: linear-gradient(135deg, rgba(108, 168, 214, 0.05), rgba(32, 178, 170, 0.05));
        }

        .feedback-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-blue), var(--accent-teal));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: white;
            box-shadow: 0 8px 25px rgba(108, 168, 214, 0.3);
        }

        .feedback-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--deep-navy);
        }

        .feedback-description {
            font-size: 1.1rem;
            color: var(--medium-slate);
            line-height: 1.6;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Form Content */
        .form-content {
            padding: 2rem 2.5rem 2.5rem;
        }

        /* Messages */
        .message {
            margin-bottom: 2rem;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            animation: slideIn 0.5s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message.error {
            background: rgba(231, 76, 60, 0.1);
            color: var(--error-crimson);
            border: 1px solid rgba(231, 76, 60, 0.3);
        }

        .message.success {
            background: rgba(39, 174, 96, 0.1);
            color: var(--clinical-green);
            border: 1px solid rgba(39, 174, 96, 0.3);
        }

        .message i {
            font-size: 1.2rem;
        }

        /* Form Groups */
        .form-group {
            margin-bottom: 2rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.8rem;
            color: var(--deep-navy);
            font-size: 1rem;
        }

        .required {
            color: var(--error-crimson);
            margin-left: 0.3rem;
        }

        .form-input, .form-textarea {
            width: 100%;
            padding: 1rem 1.2rem;
            border: 2px solid rgba(108, 168, 214, 0.2);
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
            font-family: inherit;
            resize: vertical;
        }

        .form-input:focus, .form-textarea:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(108, 168, 214, 0.1);
        }

        .form-textarea {
            min-height: 120px;
            line-height: 1.6;
        }

        /* Star Rating */
        .rating-container {
            margin-bottom: 2rem;
        }

        .stars {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            margin-top: 1rem;
        }

        .stars input {
            display: none;
        }

        .stars label {
            font-size: 2.5rem;
            color: #ddd;
            cursor: pointer;
            transition: all 0.3s ease;
            user-select: none;
            position: relative;
        }

        .stars label:hover {
            transform: scale(1.1);
        }

        .stars input:checked ~ label,
        .stars label:hover,
        .stars label:hover ~ label {
            color: var(--star-gold);
            text-shadow: 0 0 10px rgba(255, 180, 0, 0.5);
        }

        .rating-text {
            text-align: center;
            margin-top: 1rem;
            font-size: 1rem;
            color: var(--medium-slate);
            font-weight: 500;
        }

        /* Submit Button */
        .submit-container {
            text-align: center;
            padding: 2rem 2.5rem;
            border-top: 1px solid rgba(108, 168, 214, 0.1);
            background: rgba(108, 168, 214, 0.02);
        }

        .submit-btn {
            background: linear-gradient(135deg, var(--primary-blue), #5a96c4);
            color: white;
            border: none;
            padding: 1rem 3rem;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(108, 168, 214, 0.3);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            gap: 0.8rem;
            margin: 0 auto;
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .submit-btn:hover::before {
            left: 100%;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(108, 168, 214, 0.4);
        }

        /* Back Button */
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
            color: var(--primary-blue);
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

            .form-content {
                padding: 1.5rem;
            }

            .feedback-header {
                padding: 2rem 1.5rem 1rem;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
            }

            .main-content {
                padding: 1.5rem 1rem;
            }

            .form-content {
                padding: 1rem;
            }

            .feedback-header {
                padding: 1.5rem 1rem;
            }

            .stars label {
                font-size: 2rem;
            }

            .page-title {
                font-size: 1.8rem;
            }

            .feedback-title {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .submit-btn {
                padding: 0.8rem 2rem;
                font-size: 1rem;
            }

            .stars {
                gap: 0.3rem;
            }

            .stars label {
                font-size: 1.8rem;
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
                <a href="give_feedback.php" class="nav-link active">
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
            <h1 class="page-title">Give Feedback</h1>
            <p class="page-subtitle">Help us improve MindHaven with your valuable feedback</p>
        </header>

        <!-- Main Content -->
        <main class="main-content">
            <div class="feedback-container">
                <div class="feedback-header">
                    <div class="feedback-icon">
                        <i class="fas fa-comment-dots"></i>
                    </div>
                    <h2 class="feedback-title">Share Your Experience</h2>
                    <p class="feedback-description">
                        Your feedback is invaluable to us. Help us understand how we can better support your mental wellness journey.
                    </p>
                </div>

                <div class="form-content">
                    <?php if ($error): ?>
                        <div class="message error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php elseif ($success): ?>
                        <div class="message success">
                            <i class="fas fa-check-circle"></i>
                            <?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>

                    <form action="give_feedback.php" method="POST" novalidate>
                        <div class="form-group">
                            <label for="name" class="form-label">
                                <i class="fas fa-user" style="margin-right: 0.5rem; color: var(--primary-blue);"></i>
                                Name (optional)
                            </label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                class="form-input"
                                value="<?= htmlspecialchars($name ?? '') ?>"
                                placeholder="Your name"
                            >
                        </div>

                        <div class="form-group">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope" style="margin-right: 0.5rem; color: var(--primary-blue);"></i>
                                Email (optional)
                            </label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-input"
                                value="<?= htmlspecialchars($email ?? '') ?>"
                                placeholder="Your email address"
                            >
                        </div>

                        <div class="form-group">
                            <label for="message" class="form-label">
                                <i class="fas fa-comment" style="margin-right: 0.5rem; color: var(--primary-blue);"></i>
                                Feedback Message
                                <span class="required">*</span>
                            </label>
                            <textarea
                                id="message"
                                name="message"
                                class="form-textarea"
                                required
                                placeholder="Share your thoughts, suggestions, or experiences with MindHaven..."
                            ><?= htmlspecialchars($message ?? '') ?></textarea>
                        </div>

                        <div class="rating-container">
                            <label class="form-label">
                                <i class="fas fa-star" style="margin-right: 0.5rem; color: var(--star-gold);"></i>
                                Overall Rating
                                <span class="required">*</span>
                            </label>
                            <div class="stars">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <input
                                        type="radio"
                                        id="star<?= $i ?>"
                                        name="rating"
                                        value="<?= $i ?>"
                                        <?= (isset($rating) && $rating == $i) ? 'checked' : '' ?>
                                        onchange="updateRatingText(<?= $i ?>)"
                                    />
                                    <label for="star<?= $i ?>">&#9733;</label>
                                <?php endfor; ?>
                            </div>
                            <div class="rating-text" id="ratingText">Please select a rating</div>
                        </div>

                        <div class="submit-container">
                            <button type="submit" class="submit-btn">
                                <i class="fas fa-paper-plane"></i>
                                Submit Feedback
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <a href="dashboard.php" class="back-link">
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

        // Rating text update
        function updateRatingText(rating) {
            const ratingTexts = {
                1: "Poor - Needs significant improvement",
                2: "Fair - Below expectations",
                3: "Good - Meets expectations",
                4: "Very Good - Exceeds expectations",
                5: "Excellent - Outstanding experience"
            };
            
            document.getElementById('ratingText').textContent = ratingTexts[rating];
        }

        // Initialize rating text if rating is already selected
        document.addEventListener('DOMContentLoaded', function() {
            const checkedRating = document.querySelector('input[name="rating"]:checked');
            if (checkedRating) {
                updateRatingText(parseInt(checkedRating.value));
            }

            // Add form validation feedback
            const form = document.querySelector('form');
            const messageTextarea = document.getElementById('message');
            const ratingInputs = document.querySelectorAll('input[name="rating"]');

            form.addEventListener('submit', function(e) {
                let isValid = true;

                // Check message
                if (!messageTextarea.value.trim()) {
                    messageTextarea.style.borderColor = 'var(--error-crimson)';
                    isValid = false;
                } else {
                    messageTextarea.style.borderColor = 'rgba(108, 168, 214, 0.2)';
                }

                // Check rating
                const ratingSelected = document.querySelector('input[name="rating"]:checked');
                if (!ratingSelected) {
                    document.querySelector('.stars').style.transform = 'scale(1.1)';
                    setTimeout(() => {
                        document.querySelector('.stars').style.transform = 'scale(1)';
                    }, 200);
                    isValid = false;
                }

                if (!isValid) {
                    e.preventDefault();
                }
            });

            // Remove error styling on input
            messageTextarea.addEventListener('input', function() {
                if (this.value.trim()) {
                    this.style.borderColor = 'rgba(108, 168, 214, 0.2)';
                }
            });
        });
    </script>
</body>
</html>
