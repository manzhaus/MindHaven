<?php
session_start();
include 'db.php';

// Only allow access to counsellor
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'counsellor') {
    header("Location: login.php");
    exit;
}

// Handle AJAX request for getting active users
if (isset($_GET['ajax']) && $_GET['ajax'] === 'get_users') {
    header('Content-Type: application/json');
    
    $sql = "    
        SELECT 
            IFNULL(u.username, CONCAT('Anonymous-', cm.anon_id)) AS display_name,
            cm.user_id,
            cm.anon_id
        FROM (
            SELECT DISTINCT user_id, anon_id
            FROM chat_messages
            WHERE sender_type = 'user'
        ) AS cm
        LEFT JOIN users u ON cm.user_id = u.id";
    
    $result = $conn->query($sql);
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = [
            'user_id' => $row['user_id'],
            'anon_id' => $row['anon_id'],
            'display_name' => $row['display_name']
        ];
    }
    
    echo json_encode(['success' => true, 'users' => $users]);
    exit;
}

// Handle AJAX request for getting unread message count
if (isset($_GET['ajax']) && $_GET['ajax'] === 'get_unread_count') {
    header('Content-Type: application/json');
    
    $count = 0;
    
    if (isset($_GET['user_id'])) {
        $user_id = intval($_GET['user_id']);
        $sql = "SELECT COUNT(*) as count FROM chat_messages 
                WHERE user_id = ? AND sender_type = 'user' AND counsellor_read = 0";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
    } else if (isset($_GET['anon_id'])) {
        $anon_id = $conn->real_escape_string($_GET['anon_id']);
        $sql = "SELECT COUNT(*) as count FROM chat_messages 
                WHERE anon_id = ? AND sender_type = 'user' AND counsellor_read = 0";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $anon_id);
    } else {
        echo json_encode(['success' => false, 'error' => 'Missing user identifier']);
        exit;
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $count = $row['count'];
    }
    
    echo json_encode(['success' => true, 'count' => $count]);
    exit;
}

// Get all users who have sent at least one message
$sql = "    
    SELECT 
        IFNULL(u.username, CONCAT('Anonymous-', cm.anon_id)) AS display_name,
        cm.user_id,
        cm.anon_id
    FROM (
        SELECT DISTINCT user_id, anon_id
        FROM chat_messages
        WHERE sender_type = 'user'
    ) AS cm
    LEFT JOIN users u ON cm.user_id = u.id";

$result = $conn->query($sql);
$users = [];
while ($row = $result->fetch_assoc()) {
    $id = $row['user_id'] ?? $row['anon_id'];
    $users[] = [
        'id' => $id,
        'user_id' => $row['user_id'],
        'anon_id' => $row['anon_id'],
        'display_name' => $row['display_name'],
        'channel' => $row['user_id'] ? "chat_user_" . $row['user_id'] : "chat_anon_" . $row['anon_id']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Counsellor Dashboard - MindHaven</title>
    <script src="https://cdn.ably.io/lib/ably.min-1.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* MindHaven Counsellor Dashboard Styles */
        /* Using the same color theme as main dashboard */
        
        /* Color Variables */
        :root {
            --soft-blue: #6CA8D6;
            --light-teal: #2E8B57;
            --soft-lavender: #D9CFE8;
            --off-white: #F9F9F9;
            --muted-coral: #FFB6A0;
            --dark-gray: #2E2E2E;
            --medium-gray: #6E6E6E;
            --light-gray: #f0f0f0;
            --white: #ffffff;
            --shadow: rgba(0, 0, 0, 0.1);
            --counsellor-accent: #4A90E2;
            --success-green: #28a745;
            --warning-orange: #ffc107;
            --danger-red: #dc3545;
        }

        /* Base styles and reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f8fbff, #f0f8f5, #faf9ff);
            color: var(--dark-gray);
            line-height: 1.6;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
            padding-bottom: 6rem; /* Space for logout button */
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

        /* Container */
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem;
            position: relative;
            z-index: 1;
        }

        /* Header Section */
        .header {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            text-align: center;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, var(--soft-blue), var(--light-teal), var(--counsellor-accent));
            border-radius: 20px 20px 0 0;
        }

        .header-title {
            font-size: 2.2rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--counsellor-accent), var(--soft-blue), var(--light-teal));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }

        .header-title i {
            color: var(--soft-blue);
            font-size: 1.8rem;
        }

        .counsellor-badge {
            display: inline-flex;
            align-items: center;
            background: linear-gradient(135deg, var(--light-teal), #228B22);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-top: 1rem;
            box-shadow: 0 4px 15px rgba(46, 139, 87, 0.3);
        }

        .counsellor-badge i {
            margin-right: 0.5rem;
            font-size: 0.9rem;
        }

        /* Logout Button at Bottom */
        .logout-container {
            position: fixed;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
        }

        .logout-btn {
            background: linear-gradient(135deg, var(--muted-coral), #FF8A73);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 6px 25px rgba(255, 182, 160, 0.4);
            font-size: 1rem;
        }

        .logout-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(255, 182, 160, 0.5);
        }

        /* Chat List Container */
        .chat-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .chat-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-gray);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .chat-title i {
            color: var(--soft-blue);
        }

        /* User List */
        .user-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .user-list li {
            padding: 0;
            border-bottom: 1px solid rgba(108, 168, 214, 0.1);
            position: relative;
            transition: all 0.3s ease;
            border-radius: 10px;
            margin-bottom: 0.5rem;
        }

        .user-list li:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .user-list li:hover {
            background-color: rgba(108, 168, 214, 0.05);
            transform: translateX(5px);
        }

        .user-list a {
            text-decoration: none;
            color: var(--dark-gray);
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            gap: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border-radius: 10px;
        }

        .user-list a:hover {
            color: var(--soft-blue);
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--soft-blue), var(--counsellor-accent));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            box-shadow: 0 4px 15px rgba(108, 168, 214, 0.3);
        }

        .user-avatar.anonymous {
            background: linear-gradient(135deg, var(--muted-coral), #FF8A73);
            box-shadow: 0 4px 15px rgba(255, 182, 160, 0.3);
        }

        .user-info {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.2rem;
        }

        .user-status {
            font-size: 0.85rem;
            color: var(--medium-gray);
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .status-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--success-green);
        }

        /* Badge */
        .badge {
            background: linear-gradient(135deg, var(--danger-red), #c82333);
            color: white;
            padding: 0.3rem 0.8rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 20px;
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: translateY(-50%) scale(1);
                box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
            }
            50% {
                transform: translateY(-50%) scale(1.05);
                box-shadow: 0 4px 15px rgba(220, 53, 69, 0.5);
            }
            100% {
                transform: translateY(-50%) scale(1);
                box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
            }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: var(--medium-gray);
        }

        .empty-state i {
            font-size: 3rem;
            color: var(--soft-blue);
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--dark-gray);
        }

        .empty-state p {
            font-size: 1rem;
            line-height: 1.5;
        }

        /* New user animation */
        .new-user {
            animation: slideInFromRight 0.5s ease-out;
        }

        @keyframes slideInFromRight {
            from {
                opacity: 0;
                transform: translateX(100px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            .header {
                padding: 1.5rem;
            }
            .header-title {
                font-size: 1.8rem;
                flex-direction: column;
                gap: 0.5rem;
            }
            .chat-container {
                padding: 1.5rem;
            }
            .user-list a {
                padding: 1rem;
            }
            .user-avatar {
                width: 40px;
                height: 40px;
                font-size: 1rem;
            }
            .logout-container {
                bottom: 1rem;
            }
            .logout-btn {
                padding: 0.8rem 1.5rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .header-title {
                font-size: 1.5rem;
            }
            .user-list a {
                flex-direction: column;
                text-align: center;
                gap: 0.5rem;
            }
            .user-info {
                align-items: center;
            }
            .badge {
                position: static;
                transform: none;
                margin-top: 0.5rem;
            }
            .logout-btn {
                padding: 0.7rem 1.2rem;
                font-size: 0.85rem;
            }
        }

        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(108, 168, 214, 0.3);
            border-radius: 50%;
            border-top-color: var(--soft-blue);
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header Section -->
        <div class="header">
            <h2 class="header-title">
                <i class="fas fa-comments"></i>
                Active Conversations
            </h2>
            <span class="counsellor-badge">
                <i class="fas fa-user-md"></i>
                Counsellor
            </span>
        </div>

        <!-- Chat Container -->
        <div class="chat-container">
            <h3 class="chat-title">
                <i class="fas fa-users"></i>
                Chat Sessions
            </h3>
            
            <ul class="user-list" id="userList">
                <?php if (count($users) > 0): ?>
                    <?php foreach ($users as $u): ?>
                        <li id="user-<?= htmlspecialchars($u['channel']) ?>">
                            <a href="counsellor_chat.php?<?= $u['user_id'] ? 'user_id=' . $u['user_id'] : 'anon_id=' . $u['anon_id'] ?>">
                                <div class="user-avatar <?= $u['user_id'] ? '' : 'anonymous' ?>">
                                    <?php if ($u['user_id']): ?>
                                        <i class="fas fa-user"></i>
                                    <?php else: ?>
                                        <i class="fas fa-user-secret"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="user-info">
                                    <div class="user-name"><?= htmlspecialchars($u['display_name']) ?></div>
                                    <div class="user-status">
                                        <span class="status-indicator"></span>
                                        <?= $u['user_id'] ? 'Registered User' : 'Anonymous User' ?>
                                    </div>
                                </div>
                            </a>
                            <span class="badge" style="display: none;">0</span>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li id="empty-state">
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <h3>No Active Conversations</h3>
                            <p>No users have started a chat yet. New conversations will appear here automatically.</p>
                        </div>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <!-- Logout Button at Bottom -->
    <div class="logout-container">
        <form method="POST" action="logout.php">
            <button class="logout-btn" type="submit">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </button>
        </form>
    </div>

    <script>
        const userChannels = <?= json_encode(array_column($users, 'channel')) ?>;
        const channelMap = <?= json_encode(array_combine(
            array_column($users, 'channel'),
            array_column($users, 'display_name')
        )) ?>;
        const badgeMap = {};
        const msgCount = {};
        const activeUsers = new Set(userChannels);

        // Add loading animation
        document.addEventListener('DOMContentLoaded', function() {
            // Animate chat items on load
            const chatItems = document.querySelectorAll('.user-list li');
            chatItems.forEach((item, index) => {
                if (!item.querySelector('.empty-state')) {
                    item.style.opacity = '0';
                    item.style.transform = 'translateY(20px)';
                    setTimeout(() => {
                        item.style.transition = 'all 0.5s ease';
                        item.style.opacity = '1';
                        item.style.transform = 'translateY(0)';
                    }, index * 100);
                }
            });
        });

        // Function to add new user to the list
        function addNewUser(channelName, displayName, isAnonymous, anonId, userId) {
            console.log('Adding new user:', { channelName, displayName, isAnonymous, anonId, userId });
            
            const userList = document.getElementById('userList');
            const emptyState = document.getElementById('empty-state');
            
            // Remove empty state if it exists
            if (emptyState) {
                emptyState.remove();
            }

            // Create new user list item
            const li = document.createElement('li');
            li.id = 'user-' + channelName;
            li.className = 'new-user';
            
            const href = isAnonymous ? `counsellor_chat.php?anon_id=${anonId}` : `counsellor_chat.php?user_id=${userId}`;
            const avatarClass = isAnonymous ? 'user-avatar anonymous' : 'user-avatar';
            const icon = isAnonymous ? 'fas fa-user-secret' : 'fas fa-user';
            const statusText = isAnonymous ? 'Anonymous User' : 'Registered User';

            li.innerHTML = `
                <a href="${href}">
                    <div class="${avatarClass}">
                        <i class="${icon}"></i>
                    </div>
                    <div class="user-info">
                        <div class="user-name">${displayName}</div>
                        <div class="user-status">
                            <span class="status-indicator"></span>
                            ${statusText}
                        </div>
                    </div>
                </a>
                <span class="badge" style="display: none;">0</span>
            `;

            userList.appendChild(li);
            activeUsers.add(channelName);

            // Get initial unread message count for this user
            fetch(`counsellor_dashboard.php?ajax=get_unread_count&${isAnonymous ? 'anon_id=' + anonId : 'user_id=' + userId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.count > 0) {
                        msgCount[channelName] = data.count;
                        const badge = li.querySelector('.badge');
                        badge.textContent = data.count;
                        badge.style.display = 'inline-block';
                    } else {
                        msgCount[channelName] = 0;
                    }
                })
                .catch(error => {
                    console.error('Error getting unread count:', error);
                    msgCount[channelName] = 0;
                });
            
            console.log('User added successfully');
        }

        // Function to remove user from the list
        function removeUser(channelName) {
            console.log('Removing user:', channelName);
            
            const userElement = document.getElementById('user-' + channelName);
            if (userElement) {
                userElement.style.transition = 'all 0.3s ease';
                userElement.style.opacity = '0';
                userElement.style.transform = 'translateX(-100px)';
                
                setTimeout(() => {
                    userElement.remove();
                    activeUsers.delete(channelName);
                    delete msgCount[channelName];
                    
                    // Show empty state if no users left
                    const userList = document.getElementById('userList');
                    if (userList.children.length === 0) {
                        const emptyLi = document.createElement('li');
                        emptyLi.id = 'empty-state';
                        emptyLi.innerHTML = `
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <h3>No Active Conversations</h3>
                                <p>No users have started a chat yet. New conversations will appear here automatically.</p>
                            </div>
                        `;
                        userList.appendChild(emptyLi);
                    }
                }, 300);
            }
        }

        // Polling function to check for new users
        function checkForNewUsers() {
            fetch('counsellor_dashboard.php?ajax=get_users')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        data.users.forEach(user => {
                            const channelName = user.user_id ? `chat_user_${user.user_id}` : `chat_anon_${user.anon_id}`;
                            
                            if (!activeUsers.has(channelName)) {
                                console.log('Found new user via polling:', user);
                                addNewUser(
                                    channelName,
                                    user.display_name,
                                    !user.user_id,
                                    user.anon_id,
                                    user.user_id
                                );
                                
                                // Subscribe to the new user's channel
                                if (window.ably) {
                                    const newChannel = window.ably.channels.get(channelName);
                                    // Don't reset msgCount here - it's already set by addNewUser
                                    
                                    newChannel.subscribe('chat', chatMessage => {
                                        if (chatMessage.data.sender_type === 'user') {
                                            msgCount[channelName]++;
                                            const li = document.getElementById('user-' + channelName);
                                            if (li) {
                                                const badge = li.querySelector('.badge');
                                                badge.textContent = msgCount[channelName];
                                                badge.style.display = 'inline-block';
                                            }
                                        }
                                    });
                                }
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Error checking for new users:', error);
                });
        }

        // Fetch Ably Token and setup real-time functionality
        fetch('ably_token.php')
            .then(res => res.json())
            .then(token => {
                const ably = new Ably.Realtime({ token: token.token });
                window.ably = ably; // Store globally for polling function
                
                console.log('Ably connected successfully');
                
                // Subscribe to existing user channels
                userChannels.forEach(channelName => {
                    const channel = ably.channels.get(channelName);
                    msgCount[channelName] = 0;
                    
                    // Subscribe to chat messages
                    channel.subscribe('chat', message => {
                        if (message.data.sender_type === 'user') {
                            msgCount[channelName]++;
                            const li = document.getElementById('user-' + channelName);
                            if (li) {
                                const badge = li.querySelector('.badge');
                                badge.textContent = msgCount[channelName];
                                badge.style.display = 'inline-block';
                            }
                        }
                    });
                });

                // Subscribe to counsellor notifications channel for new users
                const notificationChannel = ably.channels.get('counsellor_notifications');
                
                notificationChannel.subscribe('new_user', message => {
                    console.log('Received new_user notification:', message.data);
                    const { channelName, displayName, isAnonymous, anonId, userId } = message.data;
                    
                    if (!activeUsers.has(channelName)) {
                        addNewUser(channelName, displayName, isAnonymous, anonId, userId);
                        
                        // Subscribe to the new user's channel
                        const newChannel = ably.channels.get(channelName);
                        // Don't reset msgCount here - it's already set by addNewUser
                        
                        newChannel.subscribe('chat', chatMessage => {
                            if (chatMessage.data.sender_type === 'user') {
                                msgCount[channelName]++;
                                const li = document.getElementById('user-' + channelName);
                                if (li) {
                                    const badge = li.querySelector('.badge');
                                    badge.textContent = msgCount[channelName];
                                    badge.style.display = 'inline-block';
                                }
                            }
                        });
                    }
                });

                notificationChannel.subscribe('user_disconnected', message => {
                    console.log('Received user_disconnected notification:', message.data);
                    const { channelName } = message.data;
                    removeUser(channelName);
                });

                // Start polling as backup
                setInterval(checkForNewUsers, 5000); // Check every 5 seconds
            })
            .catch(error => {
                console.error('Error connecting to chat:', error);
                // If Ably fails, rely on polling only
                setInterval(checkForNewUsers, 3000); // Check every 3 seconds
            });
    </script>
</body>
</html>
