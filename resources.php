<?php
session_start();

// Check if user is logged in OR anonymous
if (!isset($_SESSION['user_id']) && !isset($_SESSION['anon_id'])) {
    header('Location: login.php');
    exit();
}

// Connect to the database
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'mindhaven';

$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
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
    $user_id = null;
}

// AI-powered content-based recommendations (only for logged-in users)
$recommendedResources = [];
if ($user_id) {
    try {
        // Get user's saved resources and their types/categories for recommendations
        $savedQuery = "
            SELECT r.type, r.title, r.description, r.tags
            FROM saved_resources sr
            JOIN resources r ON sr.resource_id = r.resourceID
            WHERE sr.user_id = ?
            LIMIT 10
        ";
        
        $stmt = $conn->prepare($savedQuery);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $savedTypes = [];
        $savedKeywords = [];
        $savedTags = [];
        
        while ($row = $result->fetch_assoc()) {
            // Collect types
            $savedTypes[] = $row['type'];
            
            // Extract keywords from title and description
            $text = strtolower($row['title'] . ' ' . $row['description']);
            $words = preg_split('/\s+/', $text);
            foreach ($words as $word) {
                $word = trim($word, '.,!?;:"()[]{}');
                if (strlen($word) > 4) { // Only meaningful words
                    $savedKeywords[] = $word;
                }
            }
            
            // Extract tags if available
            if (!empty($row['tags'])) {
                $tags = explode(',', $row['tags']);
                foreach ($tags as $tag) {
                    $savedTags[] = trim($tag);
                }
            }
        }
        
        $stmt->close();
        
        if (!empty($savedTypes) || !empty($savedTags)) {
            // Build recommendation query based on available data
            $conditions = [];
            $params = [];
            $types = '';
            
            // Use tags if available, otherwise fall back to types
            if (!empty($savedTags)) {
                // Count tag frequency and get top tags
                $tagFrequency = array_count_values($savedTags);
                arsort($tagFrequency);
                $topTags = array_slice(array_keys($tagFrequency), 0, 3);
                
                foreach ($topTags as $tag) {
                    $conditions[] = "r.tags LIKE ?";
                    $params[] = '%' . $tag . '%';
                    $types .= 's';
                }
            } else {
                // Fall back to type-based recommendations
                $typeFrequency = array_count_values($savedTypes);
                arsort($typeFrequency);
                $topType = array_key_first($typeFrequency);
                
                $conditions[] = "r.type = ?";
                $params[] = $topType;
                $types .= 's';
            }
            
            if (!empty($conditions)) {
                $recommendQuery = "
                    SELECT r.resourceID, r.title, r.description, r.link, r.type, r.dateAdded
                    FROM resources r
                    WHERE (" . implode(" OR ", $conditions) . ")
                    AND r.resourceID NOT IN (
                        SELECT sr.resource_id 
                        FROM saved_resources sr 
                        WHERE sr.user_id = ?
                    )
                    ORDER BY r.dateAdded DESC
                    LIMIT 6
                ";
                
                $params[] = $user_id;
                $types .= 'i';
                
                $stmt = $conn->prepare($recommendQuery);
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $recommendedResources = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmt->close();
            }
        }
    } catch (Exception $e) {
        // If there's any error with recommendations, just continue without them
        $recommendedResources = [];
    }
}

// Get selected type and search keyword from form
$selectedType = $_GET['type'] ?? '';
$searchKeyword = $_GET['search'] ?? '';
$searchKeywordLike = '%' . $searchKeyword . '%';

// Prepare SQL query with optional filters
if ($selectedType && $selectedType !== 'all') {
    $sql = "SELECT resourceID, title, description, link, type, dateAdded 
            FROM resources 
            WHERE type = ? AND (title LIKE ? OR description LIKE ?)
            ORDER BY dateAdded DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sss', $selectedType, $searchKeywordLike, $searchKeywordLike);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT resourceID, title, description, link, type, dateAdded 
            FROM resources 
            WHERE title LIKE ? OR description LIKE ?
           ORDER BY dateAdded DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $searchKeywordLike, $searchKeywordLike);
    $stmt->execute();
    $result = $stmt->get_result();
}

// Get all unique resource types for dropdown
$typeQuery = "SELECT DISTINCT type FROM resources";
$typeResult = $conn->query($typeQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resources Library - MindHaven</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* MindHaven Resources Library - Enhanced UI */
        /* Using the clinical theme with resource-specific styling */
        
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
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            width: 100%;
        }

        /* Recommendations Section */
        .recommendations-section {
            background: linear-gradient(135deg, rgba(32, 178, 170, 0.05), rgba(139, 95, 191, 0.05));
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(32, 178, 170, 0.1);
            position: relative;
        }

        .recommendations-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--secondary-purple), var(--primary-teal));
            border-radius: 20px 20px 0 0;
        }

        .recommendations-title {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--deep-navy);
            display: flex;
            align-items: center;
        }

        .recommendations-title i {
            margin-right: 0.5rem;
            color: var(--secondary-purple);
        }

        .recommendations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 1.5rem;
        }

        .recommendation-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(139, 95, 191, 0.1);
            transition: all 0.3s ease;
        }

        .recommendation-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .recommendation-header {
            padding: 1rem;
            border-bottom: 1px solid rgba(32, 178, 170, 0.1);
        }

        .recommendation-type {
            display: inline-block;
            padding: 0.2rem 0.6rem;
            border-radius: 15px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .recommendation-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.3rem;
            color: var(--deep-navy);
            line-height: 1.3;
        }

        .recommendation-description {
            font-size: 0.85rem;
            color: var(--medium-slate);
            line-height: 1.4;
        }

        .recommendation-content {
            padding: 0;
        }

        .recommendation-footer {
            padding: 1rem;
            border-top: 1px solid rgba(32, 178, 170, 0.1);
            background: rgba(32, 178, 170, 0.02);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .recommendation-date {
            font-size: 0.75rem;
            color: var(--medium-slate);
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .recommendation-save-btn {
            padding: 0.5rem 1rem;
            background: linear-gradient(135deg, var(--clinical-green), #45a049);
            color: white;
            border: none;
            border-radius: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.85rem;
        }

        .recommendation-save-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(39, 174, 96, 0.3);
        }

        /* Recommendation Media Containers - Smaller versions */
        .rec-video-container {
            position: relative;
            width: 100%;
            height: 180px;
            background: linear-gradient(135deg, rgba(255, 140, 66, 0.1), rgba(32, 178, 170, 0.1));
        }

        .rec-video-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .rec-pdf-container {
            position: relative;
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, rgba(39, 174, 96, 0.1), rgba(32, 178, 170, 0.1));
        }

        .rec-pdf-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .rec-image-container {
            position: relative;
            width: 100%;
            height: 150px;
            overflow: hidden;
            background: linear-gradient(135deg, rgba(108, 168, 214, 0.1), rgba(32, 178, 170, 0.1));
        }

        .rec-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .recommendation-card:hover .rec-image-container img {
            transform: scale(1.05);
        }

        .rec-audio-container {
            padding: 1rem;
            background: linear-gradient(135deg, rgba(139, 95, 191, 0.05), rgba(32, 178, 170, 0.05));
            text-align: center;
        }

        .rec-audio-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--secondary-purple), var(--primary-teal));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.8rem;
            font-size: 1.2rem;
            color: white;
            box-shadow: 0 4px 15px rgba(139, 95, 191, 0.3);
        }

        .rec-audio-container audio {
            width: 100%;
            margin-top: 0.5rem;
        }

        .rec-link-container {
            padding: 1rem;
            background: linear-gradient(135deg, rgba(32, 178, 170, 0.05), rgba(139, 95, 191, 0.05));
            text-align: center;
        }

        .rec-link-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-teal), var(--secondary-purple));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.8rem;
            font-size: 1.2rem;
            color: white;
            box-shadow: 0 4px 15px rgba(32, 178, 170, 0.3);
        }

        .rec-visit-link {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            background: linear-gradient(135deg, var(--primary-teal), #17a2b8);
            color: white;
            text-decoration: none;
            border-radius: 20px;
            font-weight: 600;
            transition: all 0.3s ease;
            gap: 0.3rem;
            font-size: 0.8rem;
        }

        .rec-visit-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(32, 178, 170, 0.3);
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
            background: linear-gradient(90deg, var(--primary-teal), var(--secondary-purple));
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

        /* Resources Grid - Smaller cards */
        .resources-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .resource-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 1px solid rgba(32, 178, 170, 0.1);
            position: relative;
        }

        .resource-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
        }

        .resource-header {
            padding: 1rem;
            border-bottom: 1px solid rgba(32, 178, 170, 0.1);
            position: relative;
        }

        .resource-type {
            display: inline-block;
            padding: 0.2rem 0.6rem;
            border-radius: 15px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .type-video { background: rgba(255, 140, 66, 0.1); color: var(--accent-orange); }
        .type-article { background: rgba(32, 178, 170, 0.1); color: var(--primary-teal); }
        .type-audio { background: rgba(139, 95, 191, 0.1); color: var(--secondary-purple); }
        .type-pdf { background: rgba(39, 174, 96, 0.1); color: var(--clinical-green); }
        .type-image { background: rgba(108, 168, 214, 0.1); color: var(--soft-blue); }

        .resource-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--deep-navy);
            line-height: 1.3;
        }

        .resource-description {
            color: var(--medium-slate);
            line-height: 1.5;
            font-size: 0.85rem;
        }

        .resource-content {
            padding: 0;
        }

        /* Media Containers - Smaller versions */
        .media-container {
            position: relative;
            background: var(--light-silver);
            overflow: hidden;
        }

        .video-container {
            position: relative;
            width: 100%;
            height: 200px;
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
            height: 250px;
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
            height: 200px;
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
            padding: 1.5rem;
            background: linear-gradient(135deg, rgba(139, 95, 191, 0.05), rgba(32, 178, 170, 0.05));
            text-align: center;
        }

        .audio-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--secondary-purple), var(--primary-teal));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            color: white;
            box-shadow: 0 6px 20px rgba(139, 95, 191, 0.3);
        }

        .audio-container audio {
            width: 100%;
            margin-top: 0.8rem;
        }

        .link-container {
            padding: 1.5rem;
            background: linear-gradient(135deg, rgba(32, 178, 170, 0.05), rgba(139, 95, 191, 0.05));
            text-align: center;
        }

        .link-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-teal), var(--secondary-purple));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            color: white;
            box-shadow: 0 6px 20px rgba(32, 178, 170, 0.3);
        }

        .visit-link {
            display: inline-flex;
            align-items: center;
            padding: 0.6rem 1.2rem;
            background: linear-gradient(135deg, var(--primary-teal), #17a2b8);
            color: white;
            text-decoration: none;
            border-radius: 20px;
            font-weight: 600;
            transition: all 0.3s ease;
            gap: 0.4rem;
            font-size: 0.9rem;
        }

        .visit-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(32, 178, 170, 0.3);
        }

        /* Resource Footer */
        .resource-footer {
            padding: 1rem;
            border-top: 1px solid rgba(32, 178, 170, 0.1);
            background: rgba(32, 178, 170, 0.02);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.8rem;
        }

        .resource-date {
            font-size: 0.8rem;
            color: var(--medium-slate);
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .save-btn {
            padding: 0.5rem 1rem;
            background: linear-gradient(135deg, var(--clinical-green), #45a049);
            color: white;
            border: none;
            border-radius: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.85rem;
        }

        .save-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(39, 174, 96, 0.3);
        }

        .save-status {
            font-size: 0.8rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            transition: all 0.3s ease;
        }

        .save-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none !important;
        }

        .save-btn:disabled:hover {
            transform: none !important;
            box-shadow: none !important;
        }

        .recommendation-save-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none !important;
        }

        .recommendation-save-btn:disabled:hover {
            transform: none !important;
            box-shadow: none !important;
        }

        /* No Results State */
        .no-results {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }

        .no-results-icon {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(32, 178, 170, 0.1), rgba(139, 95, 191, 0.1));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            font-size: 3rem;
            color: var(--medium-slate);
        }

        .no-results-title {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--deep-navy);
        }

        .no-results-text {
            font-size: 1.1rem;
            color: var(--medium-slate);
            line-height: 1.6;
        }

        /* Back Button */
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
        @media (max-width: 1200px) {
            .resources-grid {
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            }
            
            .recommendations-grid {
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            }
        }

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
                padding: 1.5rem;
            }
            .resources-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 1rem;
            }
            .recommendations-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
            }
            .main-content {
                padding: 1rem;
            }
            .filter-form {
                grid-template-columns: 1fr;
            }
            .resource-footer, .recommendation-footer {
                flex-direction: column;
                align-items: stretch;
            }
            .page-title {
                font-size: 1.8rem;
            }
            .resources-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .filter-section, .recommendations-section {
                padding: 1.5rem;
            }
            .resource-header, .recommendation-header {
                padding: 0.8rem;
            }
            .audio-container, .link-container, .rec-audio-container, .rec-link-container {
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
                <a href="resources.php" class="nav-link active">
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
            <h1 class="page-title">Resources Library</h1>
            <p class="page-subtitle">Curated mental health resources to support your wellness journey</p>
        </header>

        <!-- Main Content -->
        <main class="main-content">
            <!-- AI-Powered Recommendations Section -->
            <?php if (!empty($recommendedResources)): ?>
                <div class="recommendations-section">
                    <h3 class="recommendations-title">
                        <i class="fas fa-magic"></i>
                        Recommended for You
                    </h3>
                    <div class="recommendations-grid">
                        <?php foreach ($recommendedResources as $res): ?>
                            <div class="recommendation-card">
                                <div class="recommendation-header">
                                    <span class="recommendation-type type-<?php echo strtolower($res['type']); ?>">
                                        <?php echo htmlspecialchars($res['type']); ?>
                                    </span>
                                    <h4 class="recommendation-title"><?= htmlspecialchars($res['title']) ?></h4>
                                    <p class="recommendation-description"><?= htmlspecialchars($res['description']) ?></p>
                                </div>
                                <div class="recommendation-content">
                                    <?php
                                    $link = htmlspecialchars($res['link']);
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
                                        <div class="rec-video-container">
                                            <iframe src="<?php echo $embedLink; ?>" allowfullscreen></iframe>
                                        </div>
                                    <?php elseif (str_ends_with($lowerLink, '.pdf')): ?>
                                        <div class="rec-pdf-container">
                                            <iframe src="<?php echo $link; ?>"></iframe>
                                        </div>
                                    <?php elseif (preg_match('/\.(jpg|jpeg|png|gif)$/i', $lowerLink)): ?>
                                        <div class="rec-image-container">
                                            <img src="<?php echo $link; ?>" alt="<?php echo htmlspecialchars($res['title']); ?>">
                                        </div>
                                    <?php elseif (preg_match('/\.(mp3|wav)$/i', $lowerLink)): ?>
                                        <div class="rec-audio-container">
                                            <div class="rec-audio-icon">
                                                <i class="fas fa-music"></i>
                                            </div>
                                            <audio controls>
                                                <source src="<?php echo $link; ?>">
                                                Your browser does not support the audio element.
                                            </audio>
                                        </div>
                                    <?php else: ?>
                                        <div class="rec-link-container">
                                            <div class="rec-link-icon">
                                                <i class="fas fa-external-link-alt"></i>
                                            </div>
                                            <a href="<?php echo $link; ?>" target="_blank" class="rec-visit-link">
                                                <i class="fas fa-arrow-right"></i>
                                                Visit Resource
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="recommendation-footer">
                                    <div class="recommendation-date">
                                        <i class="fas fa-calendar-alt"></i>
                                        <?php echo date('M j, Y', strtotime($res['dateAdded'])); ?>
                                    </div>
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                        <button onclick="saveResource(<?php echo $res['resourceID']; ?>)" class="recommendation-save-btn">
                                            <i class="fas fa-bookmark"></i>
                                            Save Resource
                                        </button>
                                        <span id="status-<?php echo $res['resourceID']; ?>" class="save-status"></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Filter Section -->
            <div class="filter-section">
                <h3 class="filter-title">
                    <i class="fas fa-filter"></i>
                    Filter Resources
                </h3>
                <form method="GET" action="resources.php" class="filter-form">
                    <div class="form-group">
                        <label for="type" class="form-label">Resource Type</label>
                        <select name="type" id="type" class="form-select">
                            <option value="all" <?php if ($selectedType === 'all') echo 'selected'; ?>>All Types</option>
                            <?php
                            // Reset the result pointer
                            $typeResult->data_seek(0);
                            while ($typeRow = $typeResult->fetch_assoc()):
                            ?>
                                <option value="<?php echo $typeRow['type']; ?>" <?php if ($selectedType == $typeRow['type']) echo 'selected'; ?>>
                                    <?php echo ucfirst($typeRow['type']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="search" class="form-label">Search Keywords</label>
                        <input type="text" name="search" id="search" class="form-input" placeholder="Enter keywords..." value="<?php echo htmlspecialchars($searchKeyword); ?>">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="filter-btn">
                            <i class="fas fa-search"></i>
                            Apply Filters
                        </button>
                    </div>
                </form>
            </div>

            <!-- Resources Grid -->
            <?php if ($result->num_rows > 0): ?>
                <div class="resources-grid">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="resource-card">
                            <div class="resource-header">
                                <span class="resource-type type-<?php echo strtolower($row['type']); ?>">
                                    <?php echo htmlspecialchars($row['type']); ?>
                                </span>
                                <h3 class="resource-title"><?php echo htmlspecialchars($row['title']); ?></h3>
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
                                <div class="resource-date">
                                    <i class="fas fa-calendar-alt"></i>
                                    <?php echo date('M j, Y', strtotime($row['dateAdded'])); ?>
                                </div>
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <button onclick="saveResource(<?php echo $row['resourceID']; ?>)" class="save-btn">
                                        <i class="fas fa-bookmark"></i>
                                        Save Resource
                                    </button>
                                    <span id="status-<?php echo $row['resourceID']; ?>" class="save-status"></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-results">
                    <div class="no-results-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3 class="no-results-title">No Resources Found</h3>
                    <p class="no-results-text">
                        We couldn't find any resources matching your search criteria. Try adjusting your filters or search terms.
                    </p>
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

        // Save resource functionality - Enhanced version
        function saveResource(resourceID) {
            // Find the button that was clicked
            const button = event.target.closest('button');
            const statusElement = document.getElementById('status-' + resourceID);
            
            // Disable button during request
            if (button) {
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            }
            
            const formData = new FormData();
            formData.append('resourceID', resourceID);
            
            fetch('save_resource.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.text())
            .then(response => {
                if (statusElement) {
                    statusElement.textContent = response.trim();
                    
                    // Style the status based on response
                    if (response.includes('Already saved')) {
                        statusElement.style.color = '#F39C12'; // Warning amber
                        statusElement.innerHTML = '<i class="fas fa-check-circle"></i> Already saved!';
                    } else if (response.includes('Resource saved')) {
                        statusElement.style.color = '#27AE60'; // Clinical green
                        statusElement.innerHTML = '<i class="fas fa-check-circle"></i> Resource saved!';
                    } else {
                        statusElement.style.color = '#E74C3C'; // Error crimson
                        statusElement.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + response;
                    }
                    
                    // Add success animation
                    statusElement.style.opacity = '0';
                    setTimeout(() => {
                        statusElement.style.opacity = '1';
                    }, 100);
                }
                
                // Re-enable button
                if (button) {
                    button.disabled = false;
                    if (response.includes('Already saved') || response.includes('Resource saved')) {
                        button.innerHTML = '<i class="fas fa-check"></i> Saved';
                        button.style.background = 'linear-gradient(135deg, #27AE60, #45a049)';
                    } else {
                        button.innerHTML = '<i class="fas fa-bookmark"></i> Save Resource';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (statusElement) {
                    statusElement.textContent = "Failed to save.";
                    statusElement.style.color = '#E74C3C';
                    statusElement.innerHTML = '<i class="fas fa-exclamation-circle"></i> Failed to save.';
                }
                
                // Re-enable button
                if (button) {
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-bookmark"></i> Save Resource';
                }
            });
        }

        // Add staggered animation to resource cards
        document.addEventListener('DOMContentLoaded', function() {
            const resourceCards = document.querySelectorAll('.resource-card, .recommendation-card');
            
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
        });
    </script>
</body>
</html>

<?php
if (isset($stmt)) $stmt->close();
$conn->close();
?>
