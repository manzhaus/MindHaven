<?php
session_start();
$message = $_SESSION['message'] ?? null;
unset($_SESSION['message']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password | MindHaven</title>
    <style>
        body { font-family: Arial; background-color: #f7f7f7; }
        .container {
            width: 400px; margin: 100px auto;
            background: #fff; padding: 30px;
            border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        input[type=email], button {
            width: 100%; padding: 10px; margin-top: 10px;
        }
        button {
            background-color: #4CAF50; color: white;
            border: none; cursor: pointer;
        }
        button:hover { background-color: #45a049; }

        .alert {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #dff0d8;
            color: #3c763d;
            border-radius: 5px;
        }
        .error {
            background-color: #f2dede;
            color: #a94442;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Forgot Password</h2>

    <?php if ($message): ?>
        <div class="alert <?= $message['type'] === 'error' ? 'error' : '' ?>">
            <?= htmlspecialchars($message['text']) ?>
        </div>
    <?php endif; ?>

    <form action="send_reset_link.php" method="POST">
        <label>Enter your registered email:</label>
        <input type="email" name="email" required>
        <button type="submit">Send Reset Link</button>
    </form>
</div>
</body>
</html>
