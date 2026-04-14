<?php
session_start();
include 'db.php';

// Check if this is a file message or text message
$is_file = isset($_POST['file_name']) && isset($_POST['file_path']) && isset($_POST['file_type']);

$user_id = $_SESSION['user_id'] ?? null;
$anon_id = $_SESSION['anon_id'] ?? null;

if (!$user_id && !$anon_id) {
    http_response_code(400);
    echo json_encode(['error' => 'No user or anonymous ID']);
    exit;
}

$sender_type = 'user';

if ($is_file) {
    // Save file info into chat_files table
    $file_name = $_POST['file_name'];
    $file_path = $_POST['file_path'];
    $file_type = $_POST['file_type'];

    // Prepare and execute insert for chat_files
    $stmt = $conn->prepare("INSERT INTO chat_files (user_id, anon_id, file_name, file_path, file_type, uploaded_at) VALUES (?, ?, ?, ?, ?, NOW())");

    if ($user_id) {
        $stmt->bind_param("issss", $user_id, $anon_id, $file_name, $file_path, $file_type);
    } else {
        // for anon, bind null for user_id
        $null_user = null;
        $stmt->bind_param("issss", $null_user, $anon_id, $file_name, $file_path, $file_type);
    }

    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(['error' => 'File insert failed: ' . $stmt->error]);
        exit;
    }
    $file_id = $stmt->insert_id;
    $stmt->close();

    // Insert a corresponding message into chat_messages so file shows in history timeline
    // We'll save a special message like [file:<file_id>] so front-end can detect it
    $file_message = "[file:$file_id]";

    if ($user_id) {
        $stmt = $conn->prepare("INSERT INTO chat_messages (user_id, sender_type, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $sender_type, $file_message);
    } else {
        $stmt = $conn->prepare("INSERT INTO chat_messages (anon_id, sender_type, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $anon_id, $sender_type, $file_message);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'file_id' => $file_id]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'File message insert failed: ' . $stmt->error]);
    }
    $stmt->close();
    $conn->close();
    exit;

} else {
    // Save normal text message
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';

    if (empty($message)) {
        http_response_code(400);
        echo json_encode(['error' => 'Message cannot be empty']);
        exit;
    }

    if ($user_id) {
        $stmt = $conn->prepare("INSERT INTO chat_messages (user_id, sender_type, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $sender_type, $message);
    } else {
        $stmt = $conn->prepare("INSERT INTO chat_messages (anon_id, sender_type, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $anon_id, $sender_type, $message);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Database insert failed: ' . $stmt->error]);
    }
    $stmt->close();
    $conn->close();
}
?>
