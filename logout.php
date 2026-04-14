<?php
session_start();

if (isset($_SESSION['anon_id'])) {
    $logFile = __DIR__ . '/anonymous_logs/' . $_SESSION['anon_id'] . '.txt';
    if (file_exists($logFile)) {
        unlink($logFile);
    }
    unset($_SESSION['anon_id']);
}

session_destroy();

header("Location: login.php");
exit;
?>
