<?php
session_start();
include 'db.php';

// Check if user is logged in OR anonymous
if (!isset($_SESSION['user_id']) && !isset($_SESSION['anonymous_id'])) {
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

$score = null;
$interpretation = '';
$suggestions = '';

// Only process form if submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $questions = ['q1','q2','q3','q4','q5','q6','q7','q8','q9'];
    $score = 0;

    foreach ($questions as $q) {
        $score += isset($_POST[$q]) ? (int)$_POST[$q] : 0;
    }

    // Determine interpretation
    if ($score <= 4) {
        $interpretation = "Minimal depression";
        $suggestions = "<ul>
            <li>You're doing well! Continue your healthy lifestyle and habits.</li>
            <li>Stay socially connected and engage in activities you enjoy.</li>
            <li>Check out our daily mental wellness tips in the app.</li>
        </ul>";
    } elseif ($score <= 9) {
        $interpretation = "Mild depression";
        $suggestions = "<ul>
            <li>Consider self-help strategies like mindfulness or journaling.</li>
            <li>Explore light content from our curated resources section.</li>
            <li>If symptoms continue, monitor your mood and routines regularly.</li>
        </ul>";
    } elseif ($score <= 14) {
        $interpretation = "Moderate depression";
        $suggestions = "<ul>
            <li>Take time for structured self-care and reflection.</li>
            <li>Use the app's curated mental health resources for mood support.</li>
            <li>Try our anonymous chat support feature to share your feelings.</li>
        </ul>";
    } elseif ($score <= 19) {
        $interpretation = "Moderately severe depression";
        $suggestions = "<ul>
            <li>You may benefit from talking with a professional counsellor.</li>
            <li>Start by reaching out through the app's secure chat support.</li>
            <li>Browse our resources for managing mood and emotional balance.</li>
        </ul>";
    } else {
        $interpretation = "Severe depression";
        $suggestions = "<ul>
            <li>Your results suggest a high level of distress.</li>
            <li>We strongly recommend speaking to a licensed mental health professional.</li>
            <li>You can start a confidential conversation through our in-app support.</li>
            <li>Help is available. You are not alone.</li>
        </ul>";
    }

    // Save to history if logged in
    if (isset($_SESSION['user_id'])) {
        $userID = $_SESSION['user_id'];
        $stmt = $conn->prepare("INSERT INTO phq9_history (user_id, score, interpretation) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $userID, $score, $interpretation);
        $stmt->execute();
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
    <title>PHQ-9 Assessment - MindHaven</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* MindHaven PHQ-9 Assessment - Enhanced UI */
        /* Using the exact color theme from dashboard */

        /* Color Variables - Clinical Purple Theme */
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
    color: var(--medium-gray);
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
    color: var(--medium-gray);
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
    max-width: 900px;
    margin: 0 auto;
    padding: 3rem 2rem;
    width: 100%;
}

/* Assessment Container */
.assessment-container {
    background: white;
    border-radius: 20px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    position: relative;
    border: 1px solid rgba(139, 95, 191, 0.1);
}

.assessment-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 6px;
    background: linear-gradient(90deg, var(--primary-purple), #9B59B6);
}

.assessment-header {
    padding: 2.5rem 2.5rem 1.5rem;
    text-align: center;
    background: linear-gradient(135deg, rgba(139, 95, 191, 0.05), rgba(255, 140, 66, 0.05));
}

.assessment-icon {
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

.assessment-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: var(--deep-navy);
}

.assessment-description {
    font-size: 1.1rem;
    color: var(--medium-slate);
    line-height: 1.6;
    max-width: 600px;
    margin: 0 auto;
}

/* Progress Bar */
.progress-container {
    padding: 0 2.5rem 1.5rem;
    display: none;
}

.progress-bar {
    width: 100%;
    height: 8px;
    background-color: var(--light-silver);
    border-radius: 4px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--primary-purple), var(--accent-teal));
    width: 0%;
    transition: width 0.3s ease;
}

.progress-text {
    text-align: center;
    margin-top: 0.5rem;
    font-size: 0.9rem;
    color: var(--medium-slate);
}

/* Form Content */
.form-content {
    padding: 2rem 2.5rem 2.5rem;
}

.question-container {
    margin-bottom: 2.5rem;
    opacity: 0;
    transform: translateY(20px);
    animation: fadeInUp 0.6s ease forwards;
}

.question-container:nth-child(n) {
    animation-delay: calc(0.1s * var(--question-index, 0));
}

@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.question-label {
    display: block;
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    color: var(--deep-navy);
    line-height: 1.4;
}

.question-number {
    display: inline-block;
    width: 30px;
    height: 30px;
    background: linear-gradient(135deg, var(--primary-purple), #9B59B6);
    color: white;
    border-radius: 50%;
    text-align: center;
    line-height: 30px;
    font-weight: 700;
    margin-right: 1rem;
    font-size: 0.9rem;
}

.options-container {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.8rem;
    margin-left: 46px;
}

.option-item {
    position: relative;
}

.option-item input[type="radio"] {
    position: absolute;
    opacity: 0;
    cursor: pointer;
}

.option-label {
    display: flex;
    align-items: center;
    padding: 1rem 1.5rem;
    background: rgba(139, 95, 191, 0.05);
    border: 2px solid transparent;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
    color: var(--deep-navy);
}

.option-label:hover {
    background: rgba(139, 95, 191, 0.1);
    border-color: rgba(139, 95, 191, 0.3);
    transform: translateX(5px);
}

.option-item input[type="radio"]:checked + .option-label {
    background: rgba(139, 95, 191, 0.15);
    border-color: var(--primary-purple);
    color: var(--primary-purple);
    font-weight: 600;
}

.option-radio {
    width: 20px;
    height: 20px;
    border: 2px solid var(--medium-slate);
    border-radius: 50%;
    margin-right: 1rem;
    position: relative;
    transition: all 0.3s ease;
}

.option-item input[type="radio"]:checked + .option-label .option-radio {
    border-color: var(--primary-purple);
    background: var(--primary-purple);
}

.option-item input[type="radio"]:checked + .option-label .option-radio::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 8px;
    height: 8px;
    background: white;
    border-radius: 50%;
}

/* Submit Button */
.submit-container {
    text-align: center;
    padding: 2rem 2.5rem;
    border-top: 1px solid rgba(139, 95, 191, 0.1);
    background: rgba(139, 95, 191, 0.02);
}

.submit-btn {
    background: linear-gradient(135deg, var(--primary-purple), #9B59B6);
    color: white;
    border: none;
    padding: 1rem 3rem;
    border-radius: 50px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 8px 25px rgba(139, 95, 191, 0.3);
    position: relative;
    overflow: hidden;
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
    box-shadow: 0 12px 35px rgba(139, 95, 191, 0.4);
}

.submit-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

/* Results Section */
.results-container {
    padding: 2.5rem;
    text-align: center;
}

.results-icon {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 2rem;
    font-size: 2.5rem;
    color: white;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.results-icon.minimal {
    background: linear-gradient(135deg, var(--clinical-green), #45a049);
}

.results-icon.mild {
    background: linear-gradient(135deg, var(--primary-purple), #9B59B6);
}

.results-icon.moderate {
    background: linear-gradient(135deg, var(--secondary-orange), #E67E22);
}

.results-icon.severe {
    background: linear-gradient(135deg, var(--error-crimson), #d32f2f);
}

.score-display {
    font-size: 3rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: var(--deep-navy);
}

.interpretation {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 2rem;
    color: var(--primary-purple);
}

.suggestions {
    background: rgba(139, 95, 191, 0.05);
    border-radius: 15px;
    padding: 2rem;
    margin: 2rem 0;
    text-align: left;
}

.suggestions h3 {
    color: var(--deep-navy);
    margin-bottom: 1rem;
    font-size: 1.3rem;
}

.suggestions li {
    padding: 0.8rem 0;
    border-bottom: 1px solid rgba(139, 95, 191, 0.1);
    position: relative;
    padding-left: 2rem;
    color: var(--medium-slate);
    line-height: 1.6;
}

.suggestions li::before {
    content: '✓';
    position: absolute;
    left: 0;
    top: 0.8rem;
    color: var(--clinical-green);
    font-weight: bold;
}

.back-link {
    display: inline-flex;
    align-items: center;
    margin-top: 2rem;
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

    .form-content {
        padding: 1.5rem;
    }

    .assessment-header {
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

    .assessment-header {
        padding: 1.5rem 1rem;
    }

    .options-container {
        margin-left: 0;
    }

    .question-label {
        font-size: 1rem;
    }

    .page-title {
        font-size: 1.8rem;
    }

    .assessment-title {
        font-size: 1.5rem;
    }
}

@media (max-width: 480px) {
    .submit-btn {
        padding: 0.8rem 2rem;
        font-size: 1rem;
    }

    .score-display {
        font-size: 2.5rem;
    }

    .interpretation {
        font-size: 1.3rem;
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
            <h1 class="page-title">PHQ-9 Depression Assessment</h1>
            <p class="page-subtitle">Patient Health Questionnaire - 9 Questions</p>
        </header>

        <!-- Main Content -->
        <main class="main-content">
            <div class="assessment-container">
                <?php if ($score === null): ?>
                    <!-- Assessment Form -->
                    <div class="assessment-header">
                        <div class="assessment-icon">
                            <i class="fas fa-brain"></i>
                        </div>
                        <h2 class="assessment-title">Depression Screening</h2>
                        <p class="assessment-description">
                            Over the last 2 weeks, how often have you been bothered by any of the following problems? 
                            Please select the most appropriate response for each question.
                        </p>
                    </div>

                    <div class="progress-container" id="progressContainer">
                        <div class="progress-bar">
                            <div class="progress-fill" id="progressFill"></div>
                        </div>
                        <div class="progress-text" id="progressText">Question 0 of 9</div>
                    </div>

                    <form method="POST" id="assessmentForm">
                        <div class="form-content">
                            <?php
                            $questionsText = [
                                "Little interest or pleasure in doing things?",
                                "Feeling down, depressed, or hopeless?",
                                "Trouble falling or staying asleep, or sleeping too much?",
                                "Feeling tired or having little energy?",
                                "Poor appetite or overeating?",
                                "Feeling bad about yourself — or that you are a failure or have let yourself or your family down?",
                                "Trouble concentrating on things, such as reading the newspaper or watching television?",
                                "Moving or speaking so slowly that other people could have noticed? Or so fidgety or restless that you have been moving a lot more than usual?",
                                "Thoughts that you would be better off dead, or thoughts of hurting yourself in some way?"
                            ];

                            $options = [
                                0 => "Not at all",
                                1 => "Several days",
                                2 => "More than half the days",
                                3 => "Nearly every day"
                            ];

                            foreach ($questionsText as $index => $text):
                                $qName = "q" . ($index + 1);
                            ?>
                                <div class="question-container" style="--question-index: <?= $index ?>">
                                    <label class="question-label">
                                        <span class="question-number"><?= $index + 1 ?></span>
                                        <?= $text ?>
                                    </label>
                                    <div class="options-container">
                                        <?php foreach ($options as $value => $label): ?>
                                            <div class="option-item">
                                                <input type="radio" name="<?= $qName ?>" value="<?= $value ?>" id="<?= $qName ?>_<?= $value ?>" required>
                                                <label for="<?= $qName ?>_<?= $value ?>" class="option-label">
                                                    <div class="option-radio"></div>
                                                    <?= $label ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="submit-container">
                            <button type="submit" class="submit-btn" id="submitBtn">
                                <i class="fas fa-check-circle" style="margin-right: 0.5rem;"></i>
                                Complete Assessment
                            </button>
                        </div>
                    </form>
                <?php else: ?>
                    <!-- Results Display -->
                    <div class="results-container">
                        <div class="results-icon <?= strtolower(str_replace(' ', '', explode(' ', $interpretation)[0])) ?>">
                            <?php
                            if ($score <= 4) echo '<i class="fas fa-smile"></i>';
                            elseif ($score <= 9) echo '<i class="fas fa-meh"></i>';
                            elseif ($score <= 14) echo '<i class="fas fa-frown"></i>';
                            elseif ($score <= 19) echo '<i class="fas fa-sad-tear"></i>';
                            else echo '<i class="fas fa-exclamation-triangle"></i>';
                            ?>
                        </div>
                        
                        <div class="score-display"><?= $score ?>/27</div>
                        <div class="interpretation"><?= $interpretation ?></div>
                        
                        <div class="suggestions">
                            <h3><i class="fas fa-lightbulb" style="margin-right: 0.5rem; color: var(--primary-purple);"></i>Recommendations</h3>
                            <?= $suggestions ?>
                        </div>

                        <a href="self_assessment.php" class="back-link">
                            <i class="fas fa-arrow-left back-icon"></i>
                            Back to Assessment Options
                        </a>
                    </div>
                <?php endif; ?>
            </div>
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

        // Progress tracking and form enhancement
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('assessmentForm');
            const progressContainer = document.getElementById('progressContainer');
            const progressFill = document.getElementById('progressFill');
            const progressText = document.getElementById('progressText');
            const submitBtn = document.getElementById('submitBtn');

            if (form) {
                const totalQuestions = 9;
                let answeredQuestions = 0;

                // Show progress bar
                if (progressContainer) {
                    progressContainer.style.display = 'block';
                }

                // Track progress
                function updateProgress() {
                    answeredQuestions = 0;
                    for (let i = 1; i <= totalQuestions; i++) {
                        const radios = document.querySelectorAll(`input[name="q${i}"]:checked`);
                        if (radios.length > 0) {
                            answeredQuestions++;
                        }
                    }

                    const progress = (answeredQuestions / totalQuestions) * 100;
                    if (progressFill) progressFill.style.width = progress + '%';
                    if (progressText) progressText.textContent = `Question ${answeredQuestions} of ${totalQuestions}`;

                    // Enable submit button when all questions are answered
                    if (submitBtn) {
                        submitBtn.disabled = answeredQuestions < totalQuestions;
                    }
                }

                // Add event listeners to all radio buttons
                const radioButtons = document.querySelectorAll('input[type="radio"]');
                radioButtons.forEach(radio => {
                    radio.addEventListener('change', updateProgress);
                });

                // Initial progress update
                updateProgress();

                // Form submission enhancement
                form.addEventListener('submit', function(e) {
                    if (submitBtn) {
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin" style="margin-right: 0.5rem;"></i>Processing...';
                        submitBtn.disabled = true;
                    }
                });
            }

            // Smooth scroll to next question on selection
            const radioInputs = document.querySelectorAll('input[type="radio"]');
            radioInputs.forEach((radio, index) => {
                radio.addEventListener('change', function() {
                    const currentQuestion = this.closest('.question-container');
                    const nextQuestion = currentQuestion.nextElementSibling;
                    
                    if (nextQuestion && nextQuestion.classList.contains('question-container')) {
                        setTimeout(() => {
                            nextQuestion.scrollIntoView({ 
                                behavior: 'smooth', 
                                block: 'center' 
                            });
                        }, 300);
                    }
                });
            });
        });
    </script>
</body>
</html>
