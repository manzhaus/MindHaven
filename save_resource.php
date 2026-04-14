<?php
session_start();
header('Content-Type: text/plain');

if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to save resources.";
    exit();
}

$userID = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resourceID'])) {
    $resourceID = intval($_POST['resourceID']);

    $conn = new mysqli('localhost', 'root', '', 'mindhaven');
    if ($conn->connect_error) {
        echo "Connection failed.";
        exit();
    }

    $checkSql = "SELECT id FROM saved_resources WHERE user_id = ? AND resource_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param('ii', $userID, $resourceID);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows === 0) {
        $insertSql = "INSERT INTO saved_resources (user_id, resource_id) VALUES (?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param('ii', $userID, $resourceID);

        if ($insertStmt->execute()) {
            echo "Resource saved.";
        } else {
            echo "Error saving.";
        }

        $insertStmt->close();
    } else {
        echo "Already saved.";
    }

    $checkStmt->close();
    $conn->close();
} else {
    echo "Invalid request.";
}
