<?php
require 'db.php';

if (!isset($_GET['token'])) {
    die("Invalid request.");
}

$token = $_GET['token'];

// 1. Check if token exists and is not expired
$stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    die("This reset link is invalid or has expired.");
}

// Token is valid — show password reset form
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password | MindHaven</title>
    <style>
        body { font-family: Arial; background-color: #f7f7f7; }
        .container {
            width: 400px; margin: 100px auto;
            background: #fff; padding: 30px;
            border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        input[type=password], button {
            width: 100%; padding: 10px; margin-top: 10px;
        }
        button {
            background-color: #4CAF50; color: white;
            border: none; cursor: pointer;
        }
        button:hover { background-color: #45a049; }
    </style>
</head>
<body>
<div class="container">
    <h2>Reset Your Password</h2>
    <form action="update_password.php" method="POST">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <label>Enter New Password:</label>
        <input type="password" name="new_password" required>

        <label>Confirm New Password:</label>
        <input type="password" name="confirm_password" required>

        <button type="submit">Update Password</button>
    </form>
</div>
</body>
</html>
