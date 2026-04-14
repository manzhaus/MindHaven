<?php
session_start();
include 'db.php';

// Only counsellor allowed
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'counsellor') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : null;
$anon_id = isset($_POST['anon_id']) ? $_POST['anon_id'] : null;

if (!isset($_FILES['file'])) {
    echo json_encode(['success' => false, 'error' => 'No file uploaded']);
    exit;
}

$uploadDir = __DIR__ . '/chat_files/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$file = $_FILES['file'];
// Sanitize and unique filename
$filename = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename($file['name']));
$targetFile = $uploadDir . $filename;

if (move_uploaded_file($file['tmp_name'], $targetFile)) {
    // Insert into chat_messages with type 'file'
    $stmt = $conn->prepare("INSERT INTO chat_messages (user_id, anon_id, sender_type, message, type, timestamp) VALUES (?, ?, 'counsellor', ?, 'file', NOW())");
    $stmt->bind_param("iss", $user_id, $anon_id, $filename);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => true, 'filename' => $filename]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to save file']);
}
