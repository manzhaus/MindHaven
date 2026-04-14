<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_POST['resourceID'])) {
    header("Location: saved_resources.php");
    exit();
}

$userID = $_SESSION['user_id'];
$resourceID = $_POST['resourceID'];

$conn = new mysqli('localhost', 'root', '', 'mindhaven');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "DELETE FROM saved_resources WHERE user_id = ? AND resource_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $userID, $resourceID);
$stmt->execute();

$stmt->close();
$conn->close();

header("Location: saved_resources.php");
exit();
?>
