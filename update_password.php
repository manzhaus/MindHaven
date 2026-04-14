<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
    if ($new_password !== $confirm_password) {
        die("Passwords do not match.");
    }

    // 1. Validate token again
    $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        die("Invalid or expired token.");
    }

    $stmt->bind_result($email);
    $stmt->fetch();

    // 2. Hash the new password
    $hashed = password_hash($new_password, PASSWORD_DEFAULT);

    // 3. Update user password
    $update = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $update->bind_param("ss", $hashed, $email);
    $update->execute();

    // 4. Remove the used token
    $delete = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
    $delete->bind_param("s", $email);
    $delete->execute();

    echo "Password has been updated successfully. You can now <a href='login.php'>log in</a>.";
}
?>
