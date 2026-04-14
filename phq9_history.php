<?php
session_start();
require 'helpers.php';

if (checkLoginModal()) {
    exit(); // Stop here if user not logged in
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

    // Get PHQ-9 history
    $sql = "SELECT score, interpretation, date_taken FROM phq9_history WHERE user_id = ? ORDER BY date_taken ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $history = [];
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }
    $stmt->close();
} else {
    // Anonymous user
    $user = [
        'username' => 'Anonymous',
        'email' => 'Not Available'
    ];
    $history = []; // Anonymous users have no history
}

$conn->close();

$hasHistory = count($history) > 0;

$labels = [];
$scores = [];
$colors = [];
if ($hasHistory) {
    foreach ($history as $record) {
        $labels[] = date('M d, Y', strtotime($record['date_taken']));
        $score = (int)$record['score'];
        $scores[] = $score;

        if ($score <= 4) {
            $colors[] = '#27AE60'; // Minimal depression - green
        } elseif ($score <= 9) {
            $colors[] = '#8B5FBF'; // Mild depression - purple
        } elseif ($score <= 14) {
            $colors[] = '#FF8C42'; // Moderate depression - orange
        } elseif ($score <= 19) {
            $colors[] = '#F39C12'; // Moderately severe depression - amber
        } else {
            $colors[] = '#E74C3C'; // Severe depression - red
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHQ-9 History - MindHaven</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* MindHaven PHQ-9 History - Clinical Theme */
        
        /* Color Variables - Clinical Theme */
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

        /* Sidebar - Same as self-assessment */
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
            border-bottom: 1px solid rgba(139, 95, 191, 0.2);
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
            border-bottom: 1px solid rgba(139, 95, 191, 0.15);
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
            box-shadow: 0 2px 8px rgba(139, 95, 191, 0.3);
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
            border-top: 1px solid rgba(139, 95, 191, 0.15);
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
            border-bottom: 1px solid rgba(139, 95, 191, 0.1);
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
            max-width: 1000px;
            margin: 0 auto;
            padding: 3rem 2rem;
            width: 100%;
        }

        /* History Container */
        .history-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            position: relative;
            border: 1px solid rgba(139, 95, 191, 0.1);
        }

        .history-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, var(--primary-purple), #9B59B6);
        }

        .history-header {
            padding: 2.5rem 2.5rem 1.5rem;
            text-align: center;
            background: linear-gradient(135deg, rgba(139, 95, 191, 0.05), rgba(255, 140, 66, 0.05));
        }

        .history-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-purple), #9B59B6);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: white;
            box-shadow: 0 8px 25px rgba(139, 95, 191, 0.3);
        }

        .history-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--deep-navy);
        }

        .history-description {
            font-size: 1.1rem;
            color: var(--medium-slate);
            line-height: 1.6;
        }

        /* Chart Section */
        .chart-section {
            padding: 2rem 2.5rem;
            border-bottom: 1px solid rgba(139, 95, 191, 0.1);
        }

        .chart-container {
            width: 100%;
            height: 400px;
            margin: 1rem 0;
            background: rgba(139, 95, 191, 0.02);
            border-radius: 15px;
            padding: 1rem;
        }

        /* Table Section */
        .table-section {
            padding: 2rem 2.5rem;
        }

        .table-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--deep-navy);
            display: flex;
            align-items: center;
        }

        .table-title i {
            margin-right: 0.5rem;
            color: var(--primary-purple);
        }

        .history-table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .history-table th {
            background: linear-gradient(135deg, var(--primary-purple), #9B59B6);
            color: white;
            padding: 1rem 1.5rem;
            text-align: left;
            font-weight: 600;
            font-size: 1rem;
        }

        .history-table td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgba(139, 95, 191, 0.1);
            color: var(--deep-navy);
        }

        .history-table tr:nth-child(even) {
            background-color: rgba(139, 95, 191, 0.02);
        }

        .history-table tr:hover {
            background-color: rgba(139, 95, 191, 0.05);
            transform: scale(1.01);
            transition: all 0.2s ease;
        }

        .score-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            color: white;
        }

        .score-minimal { background-color: var(--clinical-green); }
        .score-mild { background-color: var(--primary-purple); }
        .score-moderate { background-color: var(--secondary-orange); }
        .score-severe { background-color: var(--warning-amber); }
        .score-critical { background-color: var(--error-crimson); }

        /* No History State */
        .no-history {
            padding: 4rem 2.5rem;
            text-align: center;
        }

        .no-history-icon {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(139, 95, 191, 0.1), rgba(255, 140, 66, 0.1));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            font-size: 3rem;
            color: var(--medium-slate);
        }

        .no-history-title {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--deep-navy);
        }

        .no-history-text {
            font-size: 1.1rem;
            color: var(--medium-slate);
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .start-assessment-btn {
            display: inline-flex;
            align-items: center;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, var(--primary-purple), #9B59B6);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(139, 95, 191, 0.3);
        }

        .start-assessment-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(139, 95, 191, 0.4);
        }

        .start-assessment-btn i {
            margin-right: 0.5rem;
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
            border: 1px solid rgba(139, 95, 191, 0.2);
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

            .chart-section, .table-section {
                padding: 1.5rem;
            }

            .history-header {
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

            .chart-container {
                height: 300px;
            }

            .history-table th,
            .history-table td {
                padding: 0.8rem;
                font-size: 0.9rem;
            }

            .page-title {
                font-size: 1.8rem;
            }

            .history-title {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .chart-section, .table-section {
                padding: 1rem;
            }

            .history-header {
                padding: 1.5rem 1rem;
            }

            .history-table {
                font-size: 0.8rem;
            }

            .history-table th,
            .history-table td {
                padding: 0.6rem 0.5rem;
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
            <h1 class="page-title">PHQ-9 Assessment History</h1>
            <p class="page-subtitle">Track your depression assessment progress over time</p>
        </header>

        <!-- Main Content -->
        <main class="main-content">
            <div class="history-container">
                <div class="history-header">
                    <div class="history-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h2 class="history-title">Your PHQ-9 Progress</h2>
                    <p class="history-description">
                        Monitor your mental health journey with detailed tracking of your PHQ-9 depression assessments
                    </p>
                </div>

                <?php if (!$hasHistory): ?>
                    <div class="no-history">
                        <div class="no-history-icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <h3 class="no-history-title">No Assessment History Found</h3>
                        <p class="no-history-text">
                            You haven't taken any PHQ-9 assessments yet. Start your mental health tracking journey by taking your first assessment.
                        </p>
                        <a href="phq9.php" class="start-assessment-btn">
                            <i class="fas fa-play"></i>
                            Take Your First PHQ-9 Assessment
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Chart Section -->
                    <div class="chart-section">
                        <div class="chart-container">
                            <canvas id="phq9Chart"></canvas>
                        </div>
                    </div>

                    <!-- Table Section -->
                    <div class="table-section">
                        <h3 class="table-title">
                            <i class="fas fa-table"></i>
                            Assessment History
                        </h3>
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Score</th>
                                    <th>Interpretation</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($history as $record): 
                                    $score = (int)$record['score'];
                                    $badgeClass = '';
                                    if ($score <= 4) $badgeClass = 'score-minimal';
                                    elseif ($score <= 9) $badgeClass = 'score-mild';
                                    elseif ($score <= 14) $badgeClass = 'score-moderate';
                                    elseif ($score <= 19) $badgeClass = 'score-severe';
                                    else $badgeClass = 'score-critical';
                                ?>
                                    <tr>
                                        <td><?= date('M d, Y', strtotime($record['date_taken'])) ?></td>
                                        <td>
                                            <span class="score-badge <?= $badgeClass ?>">
                                                <?= htmlspecialchars($record['score']) ?>/27
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars($record['interpretation']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <a href="self_assessment.php" class="back-link">
                <i class="fas fa-arrow-left back-icon"></i>
                Back to Assessment Options
            </a>
        </main>
    </div>

    <?php if ($hasHistory): ?>
    <script>
        // Chart.js configuration
        const ctx = document.getElementById('phq9Chart').getContext('2d');
        
        const data = {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                label: 'PHQ-9 Score',
                data: <?= json_encode($scores) ?>,
                fill: false,
                borderColor: '#8B5FBF',
                backgroundColor: 'rgba(139, 95, 191, 0.1)',
                tension: 0.4,
                pointBackgroundColor: <?= json_encode($colors) ?>,
                pointBorderColor: '#FFFFFF',
                pointBorderWidth: 3,
                pointRadius: 8,
                pointHoverRadius: 12,
                borderWidth: 3
            }]
        };

        const config = {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(139, 95, 191, 0.9)',
                        titleColor: '#FFFFFF',
                        bodyColor: '#FFFFFF',
                        borderColor: '#8B5FBF',
                        borderWidth: 2,
                        cornerRadius: 10,
                        displayColors: false,
                        callbacks: {
                            title: function(context) {
                                return 'Assessment Date: ' + context[0].label;
                            },
                            label: function(context) {
                                const score = context.parsed.y;
                                let interpretation = '';
                                if (score <= 4) interpretation = 'Minimal depression';
                                else if (score <= 9) interpretation = 'Mild depression';
                                else if (score <= 14) interpretation = 'Moderate depression';
                                else if (score <= 19) interpretation = 'Moderately severe depression';
                                else interpretation = 'Severe depression';
                                
                                return [
                                    'Score: ' + score + '/27',
                                    'Level: ' + interpretation
                                ];
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 27,
                        title: {
                            display: true,
                            text: 'PHQ-9 Score',
                            color: '#2C3E50',
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        },
                        ticks: {
                            stepSize: 3,
                            color: '#5D6D7E',
                            font: {
                                size: 12
                            }
                        },
                        grid: {
                            color: 'rgba(139, 95, 191, 0.1)',
                            borderColor: 'rgba(139, 95, 191, 0.2)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Assessment Date',
                            color: '#2C3E50',
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        },
                        ticks: {
                            color: '#5D6D7E',
                            font: {
                                size: 12
                            }
                        },
                        grid: {
                            color: 'rgba(139, 95, 191, 0.1)',
                            borderColor: 'rgba(139, 95, 191, 0.2)'
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        };

        new Chart(ctx, config);
    </script>
    <?php endif; ?>

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

        // Add animation to table rows
        document.addEventListener('DOMContentLoaded', function() {
            const tableRows = document.querySelectorAll('.history-table tbody tr');
            
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

            tableRows.forEach((row, index) => {
                row.style.opacity = '0';
                row.style.transform = 'translateY(20px)';
                row.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
                observer.observe(row);
            });
        });
    </script>
</body>
</html>
