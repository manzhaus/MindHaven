<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT sender_type, message, timestamp FROM chat_messages WHERE user_id = ? ORDER BY timestamp ASC");
    $stmt->bind_param("i", $user_id);
} elseif (isset($_SESSION['anon_id'])) {
    $anon_id = $_SESSION['anon_id'];
    $stmt = $conn->prepare("SELECT sender_type, message, timestamp FROM chat_messages WHERE anon_id = ? ORDER BY timestamp ASC");
    $stmt->bind_param("s", $anon_id);
} else {
    echo json_encode([]);
    exit;
}

$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}
echo json_encode($messages);
?>
