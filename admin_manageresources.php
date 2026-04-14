<?php
session_start();
include 'db.php';

// Only allow access if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$typeOptions = [
    'article',
    'video',
    'audio',
    'image',
    'pdf'
];

// Suggested tags - arranged alphabetically
$suggestedTags = [
    'anxiety',
    'breathing', 
    'burnout',
    'coping skills',
    'depression',
    'emotion',
    'focus',
    'gratitude',
    'loneliness',
    'meditation',
    'mental wellness',
    'mindfulness',
    'motivation',
    'panic attacks',
    'productivity',
    'relationships',
    'relaxation',
    'self-care',
    'self-esteem',
    'sleep',
    'stress'
];

// Handle file upload
function handleFileUpload($file, $type) {
    $uploadDir = 'uploads/';
    
    // Create uploads directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // More comprehensive file type checking
    $allowedTypes = [
        'pdf' => [
            'mime' => ['application/pdf'],
            'extensions' => ['pdf']
        ],
        'audio' => [
            'mime' => ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/x-wav', 'audio/wave'],
            'extensions' => ['mp3', 'wav']
        ],
        'video' => [
            'mime' => ['video/mp4', 'video/mpeg', 'video/quicktime', 'video/x-msvideo'],
            'extensions' => ['mp4', 'mpeg', 'mov', 'avi']
        ]
    ];
    
    $maxSizes = [
        'pdf' => 10 * 1024 * 1024,    // 10MB
        'audio' => 50 * 1024 * 1024,  // 50MB
        'video' => 100 * 1024 * 1024  // 100MB
    ];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'File upload error: ' . $file['error']];
    }
    
    // Get file extension
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Check if type is supported for file uploads
    if (!isset($allowedTypes[$type])) {
        return ['success' => false, 'error' => 'File uploads not supported for this resource type. Please use link instead.'];
    }
    
    // Check file extension
    if (!in_array($fileExtension, $allowedTypes[$type]['extensions'])) {
        $allowedExts = implode(', ', $allowedTypes[$type]['extensions']);
        return ['success' => false, 'error' => "Invalid file extension. Allowed extensions for {$type}: {$allowedExts}"];
    }
    
    // Check MIME type (more reliable than extension)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes[$type]['mime'])) {
        $allowedMimes = implode(', ', $allowedTypes[$type]['mime']);
        return ['success' => false, 'error' => "Invalid file type. Expected: {$allowedMimes}, Got: {$mimeType}"];
    }
    
    // Check file size
    if ($file['size'] > $maxSizes[$type]) {
        $maxSizeMB = $maxSizes[$type] / (1024 * 1024);
        return ['success' => false, 'error' => "File size exceeds {$maxSizeMB}MB limit."];
    }
    
    // Generate unique filename
    $fileName = uniqid() . '_' . time() . '.' . $fileExtension;
    $filePath = $uploadDir . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        return ['success' => true, 'path' => $filePath];
    } else {
        return ['success' => false, 'error' => 'Failed to save file to server.'];
    }
}

// Handle add resource
if (isset($_POST['add_resource'])) {
    $title = $_POST['title'] ?? '';
    $type = $_POST['type'] ?? '';
    $link = $_POST['link'] ?? '';
    $description = $_POST['description'] ?? '';
    $inputType = $_POST['input_type'] ?? 'link';
    $tags = isset($_POST['tags']) ? implode(',', $_POST['tags']) : '';
    
    if ($title && $type) {
        if ($inputType === 'file' && isset($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE) {
            // Handle file upload
            $uploadResult = handleFileUpload($_FILES['file'], $type);
            if ($uploadResult['success']) {
                $link = $uploadResult['path'];
            } else {
                $error = $uploadResult['error'];
            }
        } elseif ($inputType === 'link' && !$link) {
            $error = "Please provide a link.";
        } elseif ($inputType === 'file' && (!isset($_FILES['file']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE)) {
            $error = "Please select a file to upload.";
        }
        
        if (!isset($error) && $link) {
            $stmt = $conn->prepare("INSERT INTO resources (title, type, link, description, dateAdded, tags) VALUES (?, ?, ?, ?, NOW(), ?)");
            $stmt->bind_param("sssss", $title, $type, $link, $description, $tags);
            $stmt->execute();
            $stmt->close();
            header("Location: admin_manageresources.php");
            exit();
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}

// Handle edit resource (show form pre-filled)
$editResource = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT resourceID, title, type, link, description, tags FROM resources WHERE resourceID = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $editResource = $result->fetch_assoc();
    $stmt->close();
}

// Handle update resource
if (isset($_POST['update_resource'])) {
    $id = intval($_POST['resourceID']);
    $title = $_POST['title'] ?? '';
    $type = $_POST['type'] ?? '';
    $link = $_POST['link'] ?? '';
    $description = $_POST['description'] ?? '';
    $inputType = $_POST['input_type'] ?? 'link';
    $tags = isset($_POST['tags']) ? implode(',', $_POST['tags']) : '';
    
    if ($title && $type) {
        if ($inputType === 'file' && isset($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE) {
            // Handle file upload for update
            $uploadResult = handleFileUpload($_FILES['file'], $type);
            if ($uploadResult['success']) {
                // Delete old file if it exists and is a local file
                $oldLink = $editResource['link'];
                if ($oldLink && strpos($oldLink, 'uploads/') === 0 && file_exists($oldLink)) {
                    unlink($oldLink);
                }
                $link = $uploadResult['path'];
            } else {
                $error = $uploadResult['error'];
            }
        } elseif ($inputType === 'link' && !$link) {
            $error = "Please provide a link.";
        }
        
        if (!isset($error) && $link) {
            $stmt = $conn->prepare("UPDATE resources SET title = ?, type = ?, link = ?, description = ?, tags = ? WHERE resourceID = ?");
            $stmt->bind_param("sssssi", $title, $type, $link, $description, $tags, $id);
            $stmt->execute();
            $stmt->close();
            header("Location: admin_manageresources.php");
            exit();
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}

// Handle deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Get the resource to check if we need to delete a file
    $stmt = $conn->prepare("SELECT link FROM resources WHERE resourceID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $resource = $result->fetch_assoc();
    $stmt->close();
    
    if ($resource) {
        // Delete file if it's a local upload
        if ($resource['link'] && strpos($resource['link'], 'uploads/') === 0 && file_exists($resource['link'])) {
            unlink($resource['link']);
        }
        
        // Delete from database
        $stmt = $conn->prepare("DELETE FROM resources WHERE resourceID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
    
    header("Location: admin_manageresources.php");
    exit();
}

// Fetch all resources - make sure to handle NULL tags
$result = $conn->query("SELECT resourceID, title, type, link, description, COALESCE(tags, '') as tags FROM resources ORDER BY dateAdded DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Resources - MindHaven</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* MindHaven Admin Resource Management Styles */
        /* Using the same color theme as main dashboard */

        /* Color Variables */
        :root {
            --soft-blue: #6CA8D6;
            --light-teal: #2E8B57;
            --soft-lavender: #D9CFE8;
            --off-white: #F9F9F9;
            --muted-coral: #FFB6A0;
            --dark-gray: #2E2E2E;
            --medium-gray: #6E6E6E;
            --light-gray: #f0f0f0;
            --white: #ffffff;
            --shadow: rgba(0, 0, 0, 0.1);
            --admin-accent: #4A90E2;
            --success-green: #28a745;
            --warning-orange: #ffc107;
            --danger-red: #dc3545;
        }

        /* Base styles and reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f8fbff, #f0f8f5, #faf9ff);
            color: var(--dark-gray);
            line-height: 1.6;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        /* Background Pattern */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%236CA8D6' fill-opacity='0.04' fill-rule='evenodd'/%3E%3C/svg%3E");
            opacity: 0.6;
            z-index: -1;
        }

        /* Container */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            position: relative;
            z-index: 1;
        }

        /* Header Section */
        .header {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, var(--soft-blue), var(--light-teal), var(--admin-accent));
            border-radius: 20px 20px 0 0;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--admin-accent), var(--soft-blue), var(--light-teal));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            text-align: center;
        }

        .page-title i {
            color: var(--soft-blue);
            font-size: 2rem;
        }

        /* Back Button at Bottom Left */
        .back-button-container {
            position: fixed;
            bottom: 2rem;
            left: 2rem;
            z-index: 1000;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 1.5rem;
            background: linear-gradient(135deg, var(--soft-blue), var(--admin-accent));
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(108, 168, 214, 0.4);
            font-size: 0.9rem;
        }

        .back-link:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(108, 168, 214, 0.5);
        }

        /* Error Message */
        .error {
            background: rgba(220, 53, 69, 0.1);
            color: var(--danger-red);
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            border: 1px solid rgba(220, 53, 69, 0.2);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Form Container */
        .form-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .form-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark-gray);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-title i {
            color: var(--soft-blue);
        }

        /* Input Type Toggle */
        .input-type-toggle {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding: 0.5rem;
            background: rgba(108, 168, 214, 0.1);
            border-radius: 10px;
        }

        .toggle-option {
            flex: 1;
            padding: 0.8rem 1rem;
            border: none;
            border-radius: 8px;
            background: transparent;
            color: var(--medium-gray);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .toggle-option.active {
            background: var(--soft-blue);
            color: white;
            box-shadow: 0 2px 8px rgba(108, 168, 214, 0.3);
        }

        /* Form Styles */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        label {
            font-weight: 600;
            color: var(--dark-gray);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        label i {
            color: var(--soft-blue);
            font-size: 0.9rem;
        }

        input[type="text"], 
        input[type="url"], 
        input[type="file"],
        select, 
        textarea {
            padding: 0.8rem;
            border: 2px solid rgba(108, 168, 214, 0.2);
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
        }

        input[type="text"]:focus, 
        input[type="url"]:focus, 
        input[type="file"]:focus,
        select:focus, 
        textarea:focus {
            outline: none;
            border-color: var(--soft-blue);
            box-shadow: 0 0 0 3px rgba(108, 168, 214, 0.1);
            background: white;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        /* File Upload Styling */
        .file-upload-area {
            border: 2px dashed rgba(108, 168, 214, 0.3);
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            background: rgba(108, 168, 214, 0.05);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .file-upload-area:hover {
            border-color: var(--soft-blue);
            background: rgba(108, 168, 214, 0.1);
        }

        .file-upload-area.dragover {
            border-color: var(--soft-blue);
            background: rgba(108, 168, 214, 0.15);
        }

        .file-upload-icon {
            font-size: 3rem;
            color: var(--soft-blue);
            margin-bottom: 1rem;
        }

        .file-upload-text {
            color: var(--medium-gray);
            margin-bottom: 0.5rem;
        }

        .file-upload-hint {
            font-size: 0.8rem;
            color: var(--medium-gray);
        }

        input[type="file"] {
            display: none;
        }

        /* Tags Section Styling */
        .tags-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.5rem;
            max-height: 200px;
            overflow-y: auto;
            padding: 1rem;
            border: 2px solid rgba(108, 168, 214, 0.2);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.8);
        }

        .tag-checkbox-item {
            display: flex;
            align-items: center;
            padding: 0.3rem;
            border-radius: 5px;
            transition: background-color 0.2s ease;
        }

        .tag-checkbox-item:hover {
            background-color: rgba(108, 168, 214, 0.1);
        }

        .tag-checkbox-item input[type="checkbox"] {
            margin-right: 0.5rem;
            transform: scale(1.1);
        }

        .tag-checkbox-item label {
            font-weight: normal;
            font-size: 0.9rem;
            margin-bottom: 0;
            cursor: pointer;
            flex: 1;
        }

        /* Type Badge */
        .type-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .type-article { background: rgba(108, 168, 214, 0.2); color: var(--soft-blue); }
        .type-video { background: rgba(255, 182, 160, 0.2); color: var(--muted-coral); }
        .type-audio { background: rgba(46, 139, 87, 0.2); color: var(--light-teal); }
        .type-image { background: rgba(217, 207, 232, 0.4); color: #8B5A96; }
        .type-pdf { background: rgba(255, 193, 7, 0.2); color: #B8860B; }

        /* Tag Badges in Table - Complete set with proper colors */
        .tag-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            margin: 0.1rem;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
            color: white;
            text-transform: lowercase;
            background-color: var(--soft-blue); /* Default fallback color */
        }

        /* Individual tag colors - all tags covered */
        .tag-anxiety { background-color: #e74c3c !important; }
        .tag-breathing { background-color: #2980b9 !important; }
        .tag-burnout { background-color: #f39c12 !important; color: #2c3e50 !important; }
        .tag-coping-skills { background-color: #7f8c8d !important; }
        .tag-depression { background-color: #3498db !important; }
        .tag-emotion { background-color: #d35400 !important; }
        .tag-focus { background-color: #95a5a6 !important; }
        .tag-gratitude { background-color: #2ecc71 !important; }
        .tag-loneliness { background-color: #1abc9c !important; }
        .tag-meditation { background-color: #8e44ad !important; }
        .tag-mental-wellness { background-color: #c0392b !important; }
        .tag-mindfulness { background-color: #27ae60 !important; }
        .tag-motivation { background-color: #e67e22 !important; }
        .tag-panic-attacks { background-color: #c0392b !important; }
        .tag-productivity { background-color: #f1c40f !important; color: #2c3e50 !important; }
        .tag-relationships { background-color: #d35400 !important; }
        .tag-relaxation { background-color: #16a085 !important; }
        .tag-self-care { background-color: #3498db !important; }
        .tag-self-esteem { background-color: #f39c12 !important; color: #2c3e50 !important; }
        .tag-sleep { background-color: #34495e !important; }
        .tag-stress { background-color: #9b59b6 !important; }

        /* Buttons */
        .btn {
            padding: 0.8rem 2rem;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--soft-blue), var(--admin-accent));
            color: white;
            box-shadow: 0 4px 15px rgba(108, 168, 214, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(108, 168, 214, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--light-teal), #228B22);
            color: white;
            box-shadow: 0 4px 15px rgba(46, 139, 87, 0.3);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(46, 139, 87, 0.4);
        }

        .btn-secondary {
            background: rgba(108, 168, 214, 0.1);
            color: var(--soft-blue);
            border: 2px solid var(--soft-blue);
        }

        .btn-secondary:hover {
            background: var(--soft-blue);
            color: white;
        }

        .btn-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        /* Table Container */
        .table-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            margin-bottom: 6rem;
        }

        .table-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark-gray);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .table-title i {
            color: var(--soft-blue);
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            font-size: 0.9rem;
        }

        th {
            background: linear-gradient(135deg, var(--soft-blue), var(--admin-accent));
            color: white;
            padding: 1.2rem 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
        }

        th:first-child {
            border-radius: 10px 0 0 0;
        }

        th:last-child {
            border-radius: 0 10px 0 0;
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid rgba(108, 168, 214, 0.1);
            vertical-align: top;
            transition: background-color 0.2s ease;
        }

        tr:hover td {
            background-color: rgba(108, 168, 214, 0.05);
        }

        tr:last-child td {
            border-bottom: none;
        }

        /* Action Links */
        .action-links {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .action-link {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
        }

        .action-edit {
            background: rgba(108, 168, 214, 0.1);
            color: var(--soft-blue);
        }

        .action-edit:hover {
            background: var(--soft-blue);
            color: white;
        }

        .action-delete {
            background: rgba(220, 53, 69, 0.1);
            color: var(--danger-red);
        }

        .action-delete:hover {
            background: var(--danger-red);
            color: white;
        }

        .action-view {
            background: rgba(46, 139, 87, 0.1);
            color: var(--light-teal);
        }

        .action-view:hover {
            background: var(--light-teal);
            color: white;
        }

        /* Description Cell */
        .description-cell {
            max-width: 300px;
            line-height: 1.4;
        }

        /* Tags Cell */
        .tags-cell {
            max-width: 250px;
            line-height: 1.4;
        }

        /* Hidden/Shown based on input type */
        .link-input, .file-input {
            display: none;
        }

        .link-input.active, .file-input.active {
            display: flex;
            flex-direction: column;
        }

        /* File type restrictions notice */
        .file-restrictions {
            background: rgba(255, 193, 7, 0.1);
            border: 1px solid rgba(255, 193, 7, 0.3);
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
            font-size: 0.9rem;
            color: var(--medium-gray);
        }

        .file-restrictions strong {
            color: var(--dark-gray);
        }

        /* No Tags Message */
        .no-tags {
            color: var(--medium-gray);
            font-style: italic;
            font-size: 0.8rem;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .container {
                padding: 1rem;
            }
            
            .table-container {
                overflow-x: auto;
            }
            
            table {
                min-width: 900px;
            }
        }

        @media (max-width: 768px) {
            .page-title {
                font-size: 2rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .btn-actions {
                flex-direction: column;
            }

            .back-button-container {
                bottom: 1rem;
                left: 1rem;
            }

            .table-container {
                margin-bottom: 5rem;
            }

            .input-type-toggle {
                flex-direction: column;
                gap: 0.5rem;
            }

            .tags-container {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .page-title {
                font-size: 1.5rem;
                flex-direction: column;
                gap: 0.5rem;
            }

            .action-links {
                flex-direction: column;
                gap: 0.3rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header Section -->
        <div class="header">
            <h1 class="page-title">
                <i class="fas fa-cogs"></i>
                Resource Management
            </h1>
        </div>

        <!-- Error Message -->
        <?php if (!empty($error)): ?>
            <div class="error">
                <i class="fas fa-exclamation-triangle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Form Container -->
        <div class="form-container">
            <?php
            function displayTagsCheckboxes($suggestedTags, $selectedTags = []) {
                echo '<div class="form-group full-width">';
                echo '<label><i class="fas fa-tags"></i> Tags (Select multiple):</label>';
                echo '<div class="tags-container">';
                foreach ($suggestedTags as $tag) {
                    $tagId = str_replace(' ', '-', $tag);
                    $isChecked = in_array($tag, $selectedTags);
                    echo '<div class="tag-checkbox-item">';
                    echo '<input type="checkbox" id="tag-' . htmlspecialchars($tagId) . '" name="tags[]" value="' . htmlspecialchars($tag) . '" ' . ($isChecked ? 'checked' : '') . '>';
                    echo '<label for="tag-' . htmlspecialchars($tagId) . '">' . htmlspecialchars($tag) . '</label>';
                    echo '</div>';
                }
                echo '</div>';
                echo '</div>';
            }
            ?>
            <?php if ($editResource): ?>
                <h2 class="form-title">
                    <i class="fas fa-edit"></i>
                    Edit Resource
                </h2>
                <form method="POST" action="admin_manageresources.php" enctype="multipart/form-data">
                    <input type="hidden" name="resourceID" value="<?= $editResource['resourceID'] ?>">
                    
                    <!-- Input Type Toggle -->
                    <div class="input-type-toggle">
                        <button type="button" class="toggle-option active" data-type="link">
                            <i class="fas fa-link"></i>
                            Add Link
                        </button>
                        <button type="button" class="toggle-option" data-type="file">
                            <i class="fas fa-upload"></i>
                            Upload File
                        </button>
                    </div>
                    <input type="hidden" name="input_type" id="input_type" value="link">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="title">
                                <i class="fas fa-heading"></i>
                                Title:
                            </label>
                            <input type="text" name="title" required value="<?= htmlspecialchars($editResource['title']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="type">
                                <i class="fas fa-tag"></i>
                                Type:
                            </label>
                            <select name="type" required>
                                <option value="">-- Select Type --</option>
                                <?php foreach ($typeOptions as $option): ?>
                                    <option value="<?= htmlspecialchars($option) ?>" <?= $editResource['type'] === $option ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($option) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php
                        // Handle existing tags properly - split by comma and trim whitespace
                        $existingTags = !empty($editResource['tags']) ? array_map('trim', explode(',', $editResource['tags'])) : [];
                        displayTagsCheckboxes($suggestedTags, $existingTags);
                        ?>
                        <div class="form-group full-width link-input active">
                            <label for="link">
                                <i class="fas fa-link"></i>
                                Link:
                            </label>
                            <input type="url" name="link" value="<?= htmlspecialchars($editResource['link']) ?>">
                        </div>
                        <div class="form-group full-width file-input">
                            <label for="file">
                                <i class="fas fa-upload"></i>
                                Upload File:
                            </label>
                            <div class="file-upload-area" onclick="document.getElementById('file').click()">
                                <div class="file-upload-icon">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>
                                <div class="file-upload-text">Click to select file or drag and drop</div>
                                <div class="file-upload-hint">PDF (10MB), MP3/WAV (50MB), MP4 (100MB)</div>
                            </div>
                            <input type="file" name="file" id="file" accept=".pdf,.mp3,.wav,.mp4">
                            <div class="file-restrictions">
                                <strong>File Upload Restrictions:</strong><br>
                                • <strong>PDF:</strong> Only for PDF resource type<br>
                                • <strong>Audio (MP3/WAV):</strong> Only for Audio resource type<br>
                                • <strong>Video (MP4):</strong> Only for Video resource type<br>
                                • <strong>Article/Image:</strong> Use link option instead
                            </div>
                        </div>
                        <div class="form-group full-width">
                            <label for="description">
                                <i class="fas fa-align-left"></i>
                                Description:
                            </label>
                            <textarea name="description" rows="4"><?= htmlspecialchars($editResource['description']) ?></textarea>
                        </div>
                    </div>
                    <div class="btn-actions">
                        <button type="submit" name="update_resource" class="btn btn-success">
                            <i class="fas fa-save"></i>
                            Update Resource
                        </button>
                        <a href="admin_manageresources.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i>
                            Cancel
                        </a>
                    </div>
                </form>
            <?php else: ?>
                <h2 class="form-title">
                    <i class="fas fa-plus"></i>
                    Add New Resource
                </h2>
                <form method="POST" action="admin_manageresources.php" enctype="multipart/form-data">
                    <!-- Input Type Toggle -->
                    <div class="input-type-toggle">
                        <button type="button" class="toggle-option active" data-type="link">
                            <i class="fas fa-link"></i>
                            Add Link
                        </button>
                        <button type="button" class="toggle-option" data-type="file">
                            <i class="fas fa-upload"></i>
                            Upload File
                        </button>
                    </div>
                    <input type="hidden" name="input_type" id="input_type" value="link">
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="title">
                                <i class="fas fa-heading"></i>
                                Title:
                            </label>
                            <input type="text" name="title" required>
                        </div>
                        <div class="form-group">
                            <label for="type">
                                <i class="fas fa-tag"></i>
                                Type:
                            </label>
                            <select name="type" required>
                                <option value="">-- Select Type --</option>
                                <?php foreach ($typeOptions as $option): ?>
                                    <option value="<?= htmlspecialchars($option) ?>"><?= htmlspecialchars($option) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php displayTagsCheckboxes($suggestedTags); ?>
                        <div class="form-group full-width link-input active">
                            <label for="link">
                                <i class="fas fa-link"></i>
                                Link:
                            </label>
                            <input type="url" name="link">
                        </div>
                        <div class="form-group full-width file-input">
                            <label for="file">
                                <i class="fas fa-upload"></i>
                                Upload File:
                            </label>
                            <div class="file-upload-area" onclick="document.getElementById('file').click()">
                                <div class="file-upload-icon">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>
                                <div class="file-upload-text">Click to select file or drag and drop</div>
                                <div class="file-upload-hint">PDF (10MB), MP3/WAV (50MB), MP4 (100MB)</div>
                            </div>
                            <input type="file" name="file" id="file" accept=".pdf,.mp3,.wav,.mp4">
                            <div class="file-restrictions">
                                <strong>File Upload Restrictions:</strong><br>
                                • <strong>PDF:</strong> Only for PDF resource type<br>
                                • <strong>Audio (MP3/WAV):</strong> Only for Audio resource type<br>
                                • <strong>Video (MP4):</strong> Only for Video resource type<br>
                                • <strong>Article/Image:</strong> Use link option instead
                            </div>
                        </div>
                        <div class="form-group full-width">
                            <label for="description">
                                <i class="fas fa-align-left"></i>
                                Description:
                            </label>
                            <textarea name="description" rows="4"></textarea>
                        </div>
                    </div>
                    <div class="btn-actions">
                        <button type="submit" name="add_resource" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Add Resource
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>

        <!-- Table Container -->
        <div class="table-container">
            <h2 class="table-title">
                <i class="fas fa-list"></i>
                Existing Resources
            </h2>
            
            <table>
                <thead>
                    <tr>
                        <th><i class="fas fa-heading"></i> Title</th>
                        <th><i class="fas fa-tag"></i> Type</th>
                        <th><i class="fas fa-link"></i> Link</th>
                        <th><i class="fas fa-align-left"></i> Description</th>
                        <th><i class="fas fa-tags"></i> Tags</th>
                        <th><i class="fas fa-cog"></i> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td>
                            <span class="type-badge type-<?= htmlspecialchars($row['type']) ?>">
                                <i class="fas fa-<?= $row['type'] === 'video' ? 'play' : ($row['type'] === 'audio' ? 'volume-up' : ($row['type'] === 'image' ? 'image' : ($row['type'] === 'pdf' ? 'file-pdf' : 'newspaper'))) ?>"></i>
                                <?= htmlspecialchars($row['type']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?= htmlspecialchars($row['link']) ?>" target="_blank" class="action-link action-view">
                                <i class="fas fa-external-link-alt"></i>
                                View
                            </a>
                        </td>
                        <td class="description-cell"><?= nl2br(htmlspecialchars($row['description'])) ?></td>
                        <td class="tags-cell">
                            <?php
                            if (!empty($row['tags']) && trim($row['tags']) !== '') {
                                $tags = array_map('trim', explode(',', $row['tags']));
                                foreach ($tags as $tag) {
                                    if (!empty($tag)) {
                                        // Convert tag to CSS class name - handle spaces and special characters
                                        $tagClass = 'tag-' . strtolower(str_replace(' ', '-', $tag));
                                        echo '<span class="tag-badge ' . htmlspecialchars($tagClass) . '">' . htmlspecialchars($tag) . '</span>';
                                    }
                                }
                            } else {
                                echo '<span class="no-tags">No tags</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <div class="action-links">
                                <a href="?edit=<?= $row['resourceID'] ?>" class="action-link action-edit">
                                    <i class="fas fa-edit"></i>
                                    Edit
                                </a>
                                <a href="?delete=<?= $row['resourceID'] ?>" class="action-link action-delete" onclick="return confirm('Are you sure you want to delete this resource?');">
                                    <i class="fas fa-trash"></i>
                                    Delete
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Back Button - Fixed at Bottom Left -->
    <div class="back-button-container">
        <a href="admin_dashboard.php" class="back-link">
            <i class="fas fa-arrow-left"></i>
            Back to Dashboard
        </a>
    </div>

    <script>
        // Toggle between link and file input
        document.addEventListener('DOMContentLoaded', function() {
            const toggleButtons = document.querySelectorAll('.toggle-option');
            const inputTypeField = document.getElementById('input_type');
            const linkInputs = document.querySelectorAll('.link-input');
            const fileInputs = document.querySelectorAll('.file-input');

            toggleButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const type = this.dataset.type;
                    
                    // Update active state
                    toggleButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Update hidden field
                    inputTypeField.value = type;
                    
                    // Show/hide inputs
                    if (type === 'link') {
                        linkInputs.forEach(input => input.classList.add('active'));
                        fileInputs.forEach(input => input.classList.remove('active'));
                    } else {
                        linkInputs.forEach(input => input.classList.remove('active'));
                        fileInputs.forEach(input => input.classList.add('active'));
                    }
                });
            });

            // File upload drag and drop
            const fileUploadArea = document.querySelector('.file-upload-area');
            const fileInput = document.getElementById('file');

            if (fileUploadArea && fileInput) {
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    fileUploadArea.addEventListener(eventName, preventDefaults, false);
                });

                function preventDefaults(e) {
                    e.preventDefault();
                    e.stopPropagation();
                }

                ['dragenter', 'dragover'].forEach(eventName => {
                    fileUploadArea.addEventListener(eventName, highlight, false);
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    fileUploadArea.addEventListener(eventName, unhighlight, false);
                });

                function highlight(e) {
                    fileUploadArea.classList.add('dragover');
                }

                function unhighlight(e) {
                    fileUploadArea.classList.remove('dragover');
                }

                fileUploadArea.addEventListener('drop', handleDrop, false);

                function handleDrop(e) {
                    const dt = e.dataTransfer;
                    const files = dt.files;
                    fileInput.files = files;
                    updateFileUploadText(files[0]);
                }

                fileInput.addEventListener('change', function() {
                    if (this.files.length > 0) {
                        updateFileUploadText(this.files[0]);
                    }
                });

                function updateFileUploadText(file) {
                    const textElement = fileUploadArea.querySelector('.file-upload-text');
                    if (file) {
                        textElement.textContent = `Selected: ${file.name}`;
                    }
                }
            }

            // Add smooth animations and interactions
            const containers = document.querySelectorAll('.form-container, .table-container');
            containers.forEach((container, index) => {
                container.style.opacity = '0';
                container.style.transform = 'translateY(30px)';
                setTimeout(() => {
                    container.style.transition = 'all 0.6s ease';
                    container.style.opacity = '1';
                    container.style.transform = 'translateY(0)';
                }, index * 200);
            });

            // Animate table rows
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach((row, index) => {
                row.style.opacity = '0';
                row.style.transform = 'translateX(-20px)';
                setTimeout(() => {
                    row.style.transition = 'all 0.4s ease';
                    row.style.opacity = '1';
                    row.style.transform = 'translateX(0)';
                }, 800 + (index * 100));
            });

            // Add hover effects to table rows
            rows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.01)';
                    this.style.boxShadow = '0 4px 15px rgba(0, 0, 0, 0.1)';
                });
                row.addEventListener('mouseleave', function() {
                    this.style.transform = '';
                    this.style.boxShadow = '';
                });
            });

            // Add click animation to buttons
            const buttons = document.querySelectorAll('.btn, .back-link');
            buttons.forEach(button => {
                button.addEventListener('click', function(e) {
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 150);
                });
            });
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>
