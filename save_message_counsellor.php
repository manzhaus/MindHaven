<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'counsellor') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$message = isset($_POST['message']) ? trim($_POST['message']) : '';
$sender_type = 'counsellor';
$user_id = isset($_POST['user_id']) && !empty($_POST['user_id']) ? intval($_POST['user_id']) : null;
$anon_id = isset($_POST['anon_id']) && !empty($_POST['anon_id']) ? $_POST['anon_id'] : null;

// Check if a file was uploaded and handle it
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['file']['tmp_name'];
    $fileName = $_FILES['file']['name'];
    // Sanitize file name
    $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9.\-_]/', '_', $fileName);
    $uploadFileDir = 'uploads/';
    $dest_path = $uploadFileDir . $fileName;

    if (move_uploaded_file($fileTmpPath, $dest_path)) {
        // Save the filename as message
        $message = $fileName;
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'File upload failed']);
        exit;
    }
}

// If message is empty and no file uploaded
if (!$message) {
    http_response_code(400);
    echo json_encode(['error' => 'Empty message']);
    exit;
}

if ($user_id) {
    $stmt = $conn->prepare("INSERT INTO chat_messages (user_id, sender_type, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $sender_type, $message);
} elseif ($anon_id) {
    $stmt = $conn->prepare("INSERT INTO chat_messages (anon_id, sender_type, message) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $anon_id, $sender_type, $message);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Missing user or anon ID']);
    exit;
}

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Database insert failed']);
}

$stmt->close();
$conn->close();
?>
