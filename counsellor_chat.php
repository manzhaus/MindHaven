<?php
session_start();
include 'db.php';

// Check if logged in as counsellor
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'counsellor') {
    header('Location: login.php');
    exit;
}

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;
$anon_id = isset($_GET['anon_id']) ? $_GET['anon_id'] : null;

if (!$user_id && !$anon_id) {
    echo "No chat specified.";
    exit;
}

// Determine channel and name
if ($user_id) {
    $channelName = "chat_user_" . $user_id;
    $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($chatUserName);
    $stmt->fetch();
    $stmt->close();
} else {
    $channelName = "chat_anon_" . $anon_id;
    $chatUserName = "Anonymous-" . htmlspecialchars($anon_id);
}

// Fetch chat history
if ($user_id) {
    $stmt = $conn->prepare("SELECT sender_type, message, timestamp FROM chat_messages WHERE user_id = ? ORDER BY timestamp ASC LIMIT 50");
    $stmt->bind_param("i", $user_id);
} else {
    $stmt = $conn->prepare("SELECT sender_type, message, timestamp FROM chat_messages WHERE anon_id = ? ORDER BY timestamp ASC LIMIT 50");
    $stmt->bind_param("s", $anon_id);
}

$stmt->execute();
$result = $stmt->get_result();
$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with <?= htmlspecialchars($chatUserName) ?> - MindHaven</title>
    <script src="https://cdn.ably.io/lib/ably.min-1.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* MindHaven Enhanced Chat Styles */
        :root {
            --soft-blue: #6CA8D6;
            --light-teal: #2E8B57;
            --counsellor-accent: #4A90E2;
            --dark-gray: #2E2E2E;
            --medium-gray: #6E6E6E;
            --white: #ffffff;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f8fbff, #f0f8f5, #faf9ff);
            margin: 0;
            padding: 0;
            color: var(--dark-gray);
        }

        /* Background Pattern */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%236CA8D6' fill-opacity='0.04' fill-rule='evenodd'/%3E%3C/svg%3E");
            opacity: 0.6;
            z-index: -1;
        }

        .chat-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
            max-width: 1000px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 20px;
            box-sizing: border-box;
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            z-index: 1;
        }

        h2 {
            color: var(--dark-gray);
            margin: 10px 0;
            font-size: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        #messages {
            flex: 1;
            overflow-y: auto;
            border: 2px solid rgba(108, 168, 214, 0.2);
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 15px;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(5px);
        }

        #messages p {
            margin: 8px 0;
            padding: 8px 12px;
            border-radius: 12px;
            max-width: 60%;
            width: fit-content;
            word-wrap: break-word;
            line-height: 1.4;
            font-size: 0.9rem;
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        #messages p[data-sender="user"] {
            background: linear-gradient(135deg, var(--soft-blue), var(--counsellor-accent));
            color: white;
            margin-right: auto;
            border-bottom-left-radius: 4px;
        }

        #messages p[data-sender="counsellor"] {
            background: linear-gradient(135deg, var(--light-teal), #228B22);
            color: white;
            margin-left: auto;
            border-bottom-right-radius: 4px;
        }

        .chat-input {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        #messageInput {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid rgba(108, 168, 214, 0.2);
            border-radius: 25px;
            font-size: 0.9rem;
            background: rgba(255, 255, 255, 0.9);
            transition: all 0.3s ease;
            outline: none;
        }

        #messageInput:focus {
            border-color: var(--soft-blue);
            box-shadow: 0 0 0 3px rgba(108, 168, 214, 0.1);
            background: white;
        }

        #sendBtn {
            padding: 12px 20px;
            background: linear-gradient(135deg, var(--soft-blue), var(--counsellor-accent));
            color: white;
            border: none;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 4px 15px rgba(108, 168, 214, 0.3);
        }

        #sendBtn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(108, 168, 214, 0.4);
        }

        .sender-user {
             color: rgba(255, 255, 255, 0.9);
            font-weight: 600;
            font-size: 0.8rem;
            display: block;
            margin-bottom: 3px;
        }

        .sender-counsellor {
             color: rgba(255, 255, 255, 0.9);
            font-weight: 600;
            font-size: 0.8rem;
            display: block;
            margin-bottom: 3px;
        }

        .timestamp {
             font-size: 0.7rem;
             color: rgba(255, 255, 255, 0.7);
             display: block;
             text-align: right;
            margin-top: 4px;
        }

        .back-btn {
            margin-bottom: 10px;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, var(--soft-blue), var(--counsellor-accent));
            color: white;
            padding: 10px 16px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(108, 168, 214, 0.3);
            width: fit-content;
        }

        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(108, 168, 214, 0.4);
        }

        /* Scrollbar Styling */
        #messages::-webkit-scrollbar {
            width: 8px;
        }

        #messages::-webkit-scrollbar-track {
            background: rgba(108, 168, 214, 0.1);
            border-radius: 10px;
        }

        #messages::-webkit-scrollbar-thumb {
            background: var(--soft-blue);
            border-radius: 10px;
        }

        #messages::-webkit-scrollbar-thumb:hover {
            background: var(--counsellor-accent);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .chat-container {
                padding: 15px;
            }

            #messages p {
                max-width: 75%;
                padding: 6px 10px;
                font-size: 0.85rem;
            }

            #messageInput {
                padding: 10px 14px;
                font-size: 0.85rem;
            }

            #sendBtn {
                padding: 10px 16px;
            }
        }
    </style>
</head>
<body>
<div class="chat-container">
    <a href="counsellor_dashboard.php" class="back-btn">
        <i class="fas fa-arrow-left"></i>
        Back
    </a>
    <h2>
        <i class="fas fa-comments"></i>
        Chat with <?= htmlspecialchars($chatUserName) ?>
    </h2>
    <div id="messages">
<?php
function looksLikeFile($message) {
    $extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'mp3', 'mp4', 'zip', 'rar'];
    $path_info = pathinfo($message);
    return isset($path_info['extension']) && in_array(strtolower($path_info['extension']), $extensions);
}
?>

<?php foreach ($messages as $i => $m): ?>
    <p class="msg" data-sender="<?= htmlspecialchars($m['sender_type']) ?>">
        <span class="<?= $m['sender_type'] === 'user' ? 'sender-user' : 'sender-counsellor' ?>">
            <?= $m['sender_type'] === 'user' ? htmlspecialchars($chatUserName) : 'Counsellor' ?>:
        </span>
        <?php if (looksLikeFile($m['message'])): ?>
            <a href="chat_files/<?= htmlspecialchars($m['message']) ?>" target="_blank" rel="noopener noreferrer">
                <?= htmlspecialchars($m['message']) ?>
            </a>
        <?php else: ?>
            <?= nl2br(htmlspecialchars($m['message'])) ?>
        <?php endif; ?>
        <span class="timestamp"><?= htmlspecialchars($m['timestamp']) ?></span>
    </p>
<?php endforeach; ?>

    </div>
    <div class="chat-input">
        <input type="text" id="messageInput" placeholder="Type a message..." />
        <button id="sendBtn">
            <i class="fas fa-paper-plane"></i>
            Send
        </button>
    </div>
</div>

<script>
    const channelName = <?= json_encode($channelName) ?>;
    const userId = <?= $user_id ?? 'null' ?>;
    const anonId = <?= $anon_id ? json_encode($anon_id) : 'null' ?>;
    const msgBox = document.getElementById('messages');
    const input = document.getElementById('messageInput');

    // Scroll to first unread user message
    const allMsgs = document.querySelectorAll('#messages .msg');
    for (let i = 0; i < allMsgs.length; i++) {
        if (allMsgs[i].dataset.sender === 'user') {
            allMsgs[i].scrollIntoView({ behavior: 'smooth' });
            break;
        }
    }

    fetch('ably_token.php')
        .then(res => res.json())
        .then(token => {
            const ably = new Ably.Realtime({ token: token.token });
            const channel = ably.channels.get(channelName);

            // Subscribe to new messages
function looksLikeFile(filename) {
    const extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'mp3', 'mp4', 'zip', 'rar'];
    const ext = filename.split('.').pop().toLowerCase();
    return extensions.includes(ext);
}

channel.subscribe('chat', message => {
    const p = document.createElement('p');
    const senderType = message.data.sender_type;
    const text = message.data.text;
    const className = senderType === 'user' ? 'sender-user' : 'sender-counsellor';
    const name = senderType === 'user' ? <?= json_encode($chatUserName) ?> : 'You';
    const isFile = looksLikeFile(text);

    p.className = 'msg';
    p.setAttribute('data-sender', senderType);

    if (isFile) {
        const fileUrl = 'chat_files/' + encodeURIComponent(text);
        p.innerHTML = `<span class="${className}">${name}:</span> <a href="${fileUrl}" target="_blank" rel="noopener noreferrer">${text}</a><br><span class="timestamp">${new Date().toLocaleString()}</span>`;
    } else {
        p.innerHTML = `<span class="${className}">${name}:</span> ${text}<br><span class="timestamp">${new Date().toLocaleString()}</span>`;
    }

    msgBox.appendChild(p);
    msgBox.scrollTop = msgBox.scrollHeight;
});


            document.getElementById('sendBtn').onclick = () => {
                const text = input.value.trim();
                if (text !== '') {
                    const data = {
                        sender_type: 'counsellor',
                        text: text
                    };

                    // Publish to Ably
                    channel.publish('chat', data);
                    input.value = '';

                    // Save to database
                    fetch('save_message_counsellor.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            user_id: userId,
                            anon_id: anonId,
                            message: text,
                            sender_type: 'counsellor'
                        })
                    });
                }
            };

            // Enter key to send
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    document.getElementById('sendBtn').click();
                }
            });
        });

    // Auto-focus input
    input.focus();
</script>
</body>
</html>
