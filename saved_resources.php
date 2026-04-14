<?php
session_start();
require 'helpers.php';

if (checkLoginModal()) {
    exit(); // Stop here if user not logged in
}

$userID = $_SESSION['user_id'];
$conn = new mysqli('localhost', 'root', '', 'mindhaven');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user info for sidebar
$sql_user = "SELECT username, email FROM users WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param('i', $userID);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

if ($result_user->num_rows > 0) {
    $user = $result_user->fetch_assoc();
} else {
    echo 'User not found.';
    exit();
}

$stmt_user->close();

// Get selected type and search keyword from form
$selectedType = $_GET['type'] ?? '';
$searchKeyword = $_GET['search'] ?? '';
$searchKeywordLike = '%' . $searchKeyword . '%';

// Prepare SQL query with optional filters
if ($selectedType && $selectedType !== 'all') {
    $sql = "SELECT r.resourceID, r.title, r.description, r.link, r.type, r.dateAdded
            FROM saved_resources s
            JOIN resources r ON s.resource_id = r.resourceID
            WHERE s.user_id = ? AND r.type = ? AND (r.title LIKE ? OR r.description LIKE ?)
            ORDER BY r.dateAdded DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('isss', $userID, $selectedType, $searchKeywordLike, $searchKeywordLike);
} else {
    $sql = "SELECT r.resourceID, r.title, r.description, r.link, r.type, r.dateAdded
            FROM saved_resources s
            JOIN resources r ON s.resource_id = r.resourceID
            WHERE s.user_id = ? AND (r.title LIKE ? OR r.description LIKE ?)
            ORDER BY r.dateAdded DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iss', $userID, $searchKeywordLike, $searchKeywordLike);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saved Resources - MindHaven</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* MindHaven Saved Resources - Enhanced UI */
        /* Using the clinical theme with saved resources specific styling */

        /* Color Variables - Clinical Theme */
        :root {
            --primary-teal: #20B2AA;
            --secondary-purple: #8B5FBF;
            --accent-orange: #FF8C42;
            --warm-cream: #FFF8F0;
            --soft-peach: #FFE5D9;
            --deep-navy: #2C3E50;
            --medium-slate: #5D6D7E;
            --light-silver: #F8F9FA;
            --pure-white: #FFFFFF;
            --clinical-green: #27AE60;
            --warning-amber: #F39C12;
            --error-crimson: #E74C3C;
            --soft-blue: #6CA8D6;
            --bookmark-gold: #FFD700;
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
            background: linear-gradient(135deg, #e0f7fa, #fff8f0);
            color: var(--deep-navy);
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            overflow-y: auto;
            transition: transform 0.3s ease;
            z-index: 1000;
            box-shadow: 3px 0 15px rgba(32, 178, 170, 0.15);
            display: flex;
            flex-direction: column;
            border-right: 1px solid rgba(32, 178, 170, 0.2);
        }

        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%2320B2AA' fill-opacity='0.08' fill-rule='evenodd'/%3E%3C/svg%3E");
            opacity: 0.6;
            z-index: -1;
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(32, 178, 170, 0.2);
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
            color: var(--primary-teal);
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
            border-bottom: 1px solid rgba(32, 178, 170, 0.15);
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, 0.3);
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-teal), var(--secondary-purple));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: 600;
            margin-right: 1rem;
            border: 2px solid white;
            color: white;
            box-shadow: 0 2px 8px rgba(32, 178, 170, 0.3);
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
            background-color: rgba(32, 178, 170, 0.1);
            color: var(--primary-teal);
            transform: translateX(5px);
        }

        .nav-link.active {
            background-color: rgba(32, 178, 170, 0.15);
            color: var(--primary-teal);
            font-weight: 600;
        }

        .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background-color: var(--primary-teal);
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
            border-top: 1px solid rgba(32, 178, 170, 0.15);
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
            border: 1px solid var(--accent-orange);
            color: var(--accent-orange);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            font-weight: 500;
        }

        .logout-btn:hover {
            background-color: var(--accent-orange);
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
            background: var(--primary-teal);
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
            background: linear-gradient(135deg, #e0f7fa, #fff8f0, #ffe5d9);
            color: var(--deep-navy);
            padding: 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            border-bottom: 1px solid rgba(32, 178, 170, 0.1);
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%2320B2AA' fill-opacity='0.06' fill-rule='evenodd'/%3E%3C/svg%3E");
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
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }

        .bookmark-icon {
            color: var(--bookmark-gold);
            font-size: 2rem;
            animation: bookmarkPulse 2s infinite;
        }

        @keyframes bookmarkPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .page-subtitle {
            font-size: 1.2rem;
            position: relative;
            z-index: 1;
            color: var(--primary-teal);
            font-weight: 500;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            max-width: 1200px;
            margin: 0 auto;
            padding: 3rem 2rem;
            width: 100%;
        }

        /* Filter Section */
        .filter-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(32, 178, 170, 0.1);
            position: relative;
        }

        .filter-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--bookmark-gold), var(--primary-teal));
            border-radius: 20px 20px 0 0;
        }

        .filter-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--deep-navy);
            display: flex;
            align-items: center;
        }

        .filter-title i {
            margin-right: 0.5rem;
            color: var(--primary-teal);
        }

        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--deep-navy);
            font-size: 0.9rem;
        }

        .form-input, .form-select {
            padding: 0.8rem 1rem;
            border: 2px solid rgba(32, 178, 170, 0.2);
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary-teal);
            box-shadow: 0 0 0 3px rgba(32, 178, 170, 0.1);
        }

        .filter-btn {
            padding: 0.8rem 2rem;
            background: linear-gradient(135deg, var(--primary-teal), #17a2b8);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .filter-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(32, 178, 170, 0.3);
        }

        /* Stats Section */
        .stats-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(32, 178, 170, 0.1);
            position: relative;
        }

        .stats-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--bookmark-gold), var(--primary-teal));
            border-radius: 20px 20px 0 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
        }

        .stat-card {
            text-align: center;
            padding: 1.5rem;
            background: linear-gradient(135deg, rgba(32, 178, 170, 0.05), rgba(255, 215, 0, 0.05));
            border-radius: 15px;
            border: 1px solid rgba(32, 178, 170, 0.1);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-teal);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1rem;
            color: var(--medium-slate);
            font-weight: 500;
        }

        /* Resources Grid */
        .resources-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .resource-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 1px solid rgba(32, 178, 170, 0.1);
            position: relative;
        }

        .resource-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
        }

        .resource-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--bookmark-gold), var(--primary-teal));
        }

        .resource-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(32, 178, 170, 0.1);
            position: relative;
        }

        .resource-type {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .type-video { background: rgba(255, 140, 66, 0.1); color: var(--accent-orange); }
        .type-article { background: rgba(32, 178, 170, 0.1); color: var(--primary-teal); }
        .type-audio { background: rgba(139, 95, 191, 0.1); color: var(--secondary-purple); }
        .type-pdf { background: rgba(39, 174, 96, 0.1); color: var(--clinical-green); }
        .type-image { background: rgba(108, 168, 214, 0.1); color: var(--soft-blue); }

        .resource-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 0.8rem;
            color: var(--deep-navy);
            line-height: 1.3;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .saved-badge {
            color: var(--bookmark-gold);
            font-size: 1rem;
            animation: savedPulse 2s infinite;
        }

        @keyframes savedPulse {
            0%, 100% { opacity: 0.7; }
            50% { opacity: 1; }
        }

        .resource-description {
            color: var(--medium-slate);
            line-height: 1.6;
            font-size: 0.95rem;
        }

        .resource-content {
            padding: 0;
        }

        /* Media Containers */
        .media-container {
            position: relative;
            background: var(--light-silver);
            overflow: hidden;
        }

        .video-container {
            position: relative;
            width: 100%;
            height: 250px;
            background: linear-gradient(135deg, rgba(255, 140, 66, 0.1), rgba(32, 178, 170, 0.1));
        }

        .video-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .pdf-container {
            position: relative;
            width: 100%;
            height: 400px;
            background: linear-gradient(135deg, rgba(39, 174, 96, 0.1), rgba(32, 178, 170, 0.1));
        }

        .pdf-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .image-container {
            position: relative;
            width: 100%;
            max-height: 300px;
            overflow: hidden;
            background: linear-gradient(135deg, rgba(108, 168, 214, 0.1), rgba(32, 178, 170, 0.1));
        }

        .image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .resource-card:hover .image-container img {
            transform: scale(1.05);
        }

        .audio-container {
            padding: 2rem;
            background: linear-gradient(135deg, rgba(139, 95, 191, 0.05), rgba(32, 178, 170, 0.05));
            text-align: center;
        }

        .audio-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--secondary-purple), var(--primary-teal));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: white;
            box-shadow: 0 8px 25px rgba(139, 95, 191, 0.3);
        }

        .audio-container audio {
            width: 100%;
            margin-top: 1rem;
        }

        .link-container {
            padding: 2rem;
            background: linear-gradient(135deg, rgba(32, 178, 170, 0.05), rgba(139, 95, 191, 0.05));
            text-align: center;
        }

        .link-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-teal), var(--secondary-purple));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: white;
            box-shadow: 0 8px 25px rgba(32, 178, 170, 0.3);
        }

        .visit-link {
            display: inline-flex;
            align-items: center;
            padding: 0.8rem 1.5rem;
            background: linear-gradient(135deg, var(--primary-teal), #17a2b8);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            gap: 0.5rem;
        }

        .visit-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(32, 178, 170, 0.3);
        }

        /* Resource Footer */
        .resource-footer {
            padding: 1.5rem;
            border-top: 1px solid rgba(32, 178, 170, 0.1);
            background: rgba(32, 178, 170, 0.02);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .resource-dates {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
        }

        .resource-date {
            font-size: 0.85rem;
            color: var(--medium-slate);
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .saved-date {
            color: var(--bookmark-gold);
            font-weight: 600;
        }

        .unsave-btn {
            padding: 0.6rem 1.2rem;
            background: linear-gradient(135deg, var(--error-crimson), #c0392b);
            color: white;
            border: none;
            border-radius: 20px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .unsave-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(231, 76, 60, 0.3);
        }

        /* No Resources State */
        .no-resources {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }

        .no-resources-icon {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.1), rgba(32, 178, 170, 0.1));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            font-size: 3rem;
            color: var(--bookmark-gold);
        }

        .no-resources-title {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--deep-navy);
        }

        .no-resources-text {
            font-size: 1.1rem;
            color: var(--medium-slate);
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .browse-resources-btn {
            display: inline-flex;
            align-items: center;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, var(--primary-teal), #17a2b8);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            gap: 0.5rem;
        }

        .browse-resources-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(32, 178, 170, 0.3);
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
            border: 1px solid rgba(32, 178, 170, 0.2);
            backdrop-filter: blur(10px);
        }

        .back-link:hover {
            background: white;
            color: var(--primary-teal);
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

            .resources-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
            }

            .main-content {
                padding: 1.5rem 1rem;
            }

            .resource-footer {
                flex-direction: column;
                align-items: stretch;
            }

            .page-title {
                font-size: 1.8rem;
                flex-direction: column;
                gap: 0.5rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .resource-header {
                padding: 1rem;
            }

            .audio-container, .link-container {
                padding: 1.5rem;
            }

            .stats-section {
                padding: 1.5rem;
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
            <img src="assets/mindhavenlogo.png" alt="MindHaven Logo" class="sidebar-logo">
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
                <a href="give_feedback.php" class="nav-link">
                    <i class="fas fa-comment-dots nav-icon"></i>
                    <span class="nav-text">Give Feedback</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="saved_resources.php" class="nav-link active">
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
            <h1 class="page-title">
                <i class="fas fa-bookmark bookmark-icon"></i>
                Saved Resources
            </h1>
            <p class="page-subtitle">Your personal collection of mental wellness resources</p>
        </header>

        <!-- Filter Section -->
        <div class="filter-section">
            <h3 class="filter-title">
                <i class="fas fa-filter"></i>
                Filter Saved Resources
            </h3>
            <form method="GET" action="saved_resources.php" class="filter-form">
                <div class="form-group">
                    <label for="type" class="form-label">Resource Type</label>
                    <select name="type" id="type" class="form-select">
                        <option value="all" <?php if (($selectedType ?? '') === 'all') echo 'selected'; ?>>All Types</option>
                        <?php 
                        // Get unique types from saved resources
                        $typeQuery = "SELECT DISTINCT r.type FROM saved_resources s JOIN resources r ON s.resource_id = r.resourceID WHERE s.user_id = ?";
                        $typeStmt = $conn->prepare($typeQuery);
                        $typeStmt->bind_param('i', $userID);
                        $typeStmt->execute();
                        $typeResult = $typeStmt->get_result();
                        while ($typeRow = $typeResult->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $typeRow['type']; ?>" <?php if (($selectedType ?? '') == $typeRow['type']) echo 'selected'; ?>>
                                <?php echo ucfirst($typeRow['type']); ?>
                            </option>
                        <?php endwhile; 
                        $typeStmt->close();
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="search" class="form-label">Search Keywords</label>
                    <input type="text" name="search" id="search" class="form-input" placeholder="Enter keywords..." value="<?php echo htmlspecialchars($searchKeyword ?? ''); ?>">
                </div>

                <div class="form-group">
                    <button type="submit" class="filter-btn">
                        <i class="fas fa-search"></i>
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>

        <!-- Stats Section -->
        <div class="stats-section">
            <div class="stats-grid" style="grid-template-columns: 1fr;">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $result->num_rows; ?></div>
                    <div class="stat-label">Saved Resources</div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Resources Grid -->
            <?php 
            $result->data_seek(0);
            if ($result->num_rows > 0): 
            ?>
                <div class="resources-grid">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="resource-card">
                            <div class="resource-header">
                                <span class="resource-type type-<?php echo strtolower($row['type']); ?>">
                                    <?php echo htmlspecialchars($row['type']); ?>
                                </span>
                                <h3 class="resource-title">
                                    <?php echo htmlspecialchars($row['title']); ?>
                                    <i class="fas fa-bookmark saved-badge"></i>
                                </h3>
                                <p class="resource-description"><?php echo htmlspecialchars($row['description']); ?></p>
                            </div>

                            <div class="resource-content">
                                <?php
                                $link = htmlspecialchars($row['link']);
                                $lowerLink = strtolower($link);

                                if (strpos($link, 'youtube.com/embed') !== false || strpos($link, 'youtube.com/watch') !== false):
                                    // Handle YouTube embed
                                    if (strpos($link, 'watch?v=') !== false) {
                                        parse_str(parse_url($link, PHP_URL_QUERY), $urlParams);
                                        $videoID = $urlParams['v'] ?? '';
                                        $embedLink = "https://www.youtube.com/embed/" . $videoID;
                                    } else {
                                        $embedLink = $link;
                                    }
                                ?>
                                    <div class="media-container video-container">
                                        <iframe src="<?php echo $embedLink; ?>" allowfullscreen></iframe>
                                    </div>
                                <?php elseif (str_ends_with($lowerLink, '.pdf')): ?>
                                    <div class="media-container pdf-container">
                                        <iframe src="<?php echo $link; ?>"></iframe>
                                    </div>
                                <?php elseif (preg_match('/\.(jpg|jpeg|png|gif)$/i', $lowerLink)): ?>
                                    <div class="media-container image-container">
                                        <img src="<?php echo $link; ?>" alt="<?php echo htmlspecialchars($row['title']); ?>">
                                    </div>
                                <?php elseif (preg_match('/\.(mp3|wav)$/i', $lowerLink)): ?>
                                    <div class="audio-container">
                                        <div class="audio-icon">
                                            <i class="fas fa-music"></i>
                                        </div>
                                        <audio controls>
                                            <source src="<?php echo $link; ?>">
                                            Your browser does not support the audio element.
                                        </audio>
                                    </div>
                                <?php else: ?>
                                    <div class="link-container">
                                        <div class="link-icon">
                                            <i class="fas fa-external-link-alt"></i>
                                        </div>
                                        <a href="<?php echo $link; ?>" target="_blank" class="visit-link">
                                            <i class="fas fa-arrow-right"></i>
                                            Visit Resource
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="resource-footer">
                                <div class="resource-dates">
                                    <div class="resource-date">
                                        <i class="fas fa-calendar-alt"></i>
                                        Added: <?php echo date('M j, Y', strtotime($row['dateAdded'])); ?>
                                    </div>
                                </div>

                                <form method="POST" action="unsave_resource.php" style="margin: 0;">
                                    <input type="hidden" name="resourceID" value="<?php echo $row['resourceID']; ?>">
                                    <button type="submit" class="unsave-btn" onclick="return confirm('Are you sure you want to remove this resource from your saved collection?')">
                                        <i class="fas fa-trash-alt"></i>
                                        Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-resources">
                    <div class="no-resources-icon">
                        <i class="fas fa-bookmark"></i>
                    </div>
                    <h3 class="no-resources-title">No Saved Resources Yet</h3>
                    <p class="no-resources-text">
                        You haven't saved any resources to your collection yet. Browse our resource library to discover helpful content for your mental wellness journey.
                    </p>
                    <a href="resources.php" class="browse-resources-btn">
                        <i class="fas fa-search"></i>
                        Browse Resources
                    </a>
                </div>
            <?php endif; ?>

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

        // Add staggered animation to resource cards
        document.addEventListener('DOMContentLoaded', function() {
            const resourceCards = document.querySelectorAll('.resource-card');
            
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

            resourceCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
                observer.observe(card);
            });

            // Add confirmation for unsave buttons
            const unsaveButtons = document.querySelectorAll('.unsave-btn');
            unsaveButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if (!confirm('Are you sure you want to remove this resource from your saved collection?')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
