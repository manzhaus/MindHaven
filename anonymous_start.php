<?php
session_start();

// If the user is already logged in with an account, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// If anonymous session already exists, skip assigning again
if (!isset($_SESSION['anon_id'])) {
    $_SESSION['anon_id'] = uniqid('anon_');
    $_SESSION['role'] = 'anonymous';
    $_SESSION['username'] = 'Anonymous'; // optional, for UI display
}

// Redirect to main dashboard
header('Location: dashboard.php');
exit;
