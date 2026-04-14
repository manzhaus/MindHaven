<?php
session_start();
include 'db.php';

// Only allow access if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Fetch full feedbacks
$result = $conn->query("SELECT id, user_id, name, email, message, rating, submitted_at FROM feedbacks ORDER BY submitted_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - View Feedbacks - MindHaven</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* MindHaven Admin Feedbacks Styles */
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
            --admin-accent: #4A90E2;
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
            max-width: 1600px;
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
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, var(--soft-blue), var(--light-teal), var(--admin-accent));
            border-radius: 20px 20px 0 0;
        }

        .header-content {
            text-align: center;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--admin-accent), var(--soft-blue), var(--light-teal));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }

        .page-title i {
            color: var(--soft-blue);
            font-size: 2rem;
        }

        /* Back Button at Bottom Left */
        .back-button-container {
            position: fixed;
            bottom: 2rem;
            left: 2rem;
            z-index: 1000;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 1.5rem;
            background: linear-gradient(135deg, var(--soft-blue), var(--admin-accent));
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(108, 168, 214, 0.4);
            font-size: 0.9rem;
        }

        .back-link:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(108, 168, 214, 0.5);
        }

        /* Table Container */
        .table-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            margin-bottom: 6rem;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            font-size: 0.9rem;
        }

        th {
            background: linear-gradient(135deg, var(--soft-blue), var(--admin-accent));
            color: white;
            padding: 1.2rem 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        th:first-child {
            border-radius: 10px 0 0 0;
        }

        th:last-child {
            border-radius: 0 10px 0 0;
        }

        td {
            padding: 1rem;
            border-bottom: 1px solid rgba(108, 168, 214, 0.1);
            vertical-align: top;
            transition: background-color 0.2s ease;
        }

        tr:hover td {
            background-color: rgba(108, 168, 214, 0.05);
        }

        tr:last-child td {
            border-bottom: none;
        }

        /* User ID Styling */
        .user-id {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.3rem 0.8rem;
            background: rgba(108, 168, 214, 0.1);
            border-radius: 20px;
            font-weight: 600;
            color: var(--soft-blue);
            font-size: 0.8rem;
        }

        .anonymous {
            background: rgba(255, 182, 160, 0.2);
            color: var(--muted-coral);
        }

        /* Name and Email Styling */
        .name-cell, .email-cell {
            font-weight: 500;
            color: var(--dark-gray);
        }

        .email-cell {
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            color: var(--medium-gray);
        }

        .not-provided {
            color: var(--medium-gray);
            font-style: italic;
            font-size: 0.85rem;
        }

        /* Message Styling */
        .message-cell {
            max-width: 350px;
            line-height: 1.5;
            color: var(--dark-gray);
            word-wrap: break-word;
        }

        /* Rating Styling */
        .rating-cell {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .stars {
            display: flex;
            gap: 2px;
        }

        .star {
            color: #ffd700;
            font-size: 0.9rem;
        }

        .star.empty {
            color: #ddd;
        }

        .rating-number {
            font-weight: 600;
            color: var(--medium-gray);
            font-size: 0.85rem;
        }

        /* Date Styling */
        .date-cell {
            color: var(--medium-gray);
            font-size: 0.85rem;
            white-space: nowrap;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
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

        /* Responsive Design */
        @media (max-width: 1400px) {
            .container {
                padding: 1rem;
            }
            
            .table-container {
                overflow-x: auto;
            }
            
            table {
                min-width: 1000px;
            }
        }

        @media (max-width: 768px) {
            .page-title {
                font-size: 2rem;
            }

            .table-container {
                padding: 1rem;
                margin-bottom: 5rem;
            }

            th, td {
                padding: 0.8rem 0.5rem;
            }

            .message-cell {
                max-width: 200px;
            }

            .back-button-container {
                bottom: 1rem;
                left: 1rem;
            }

            .back-link {
                padding: 0.8rem 1.2rem;
                font-size: 0.85rem;
            }
        }

        @media (max-width: 480px) {
            .page-title {
                font-size: 1.5rem;
                flex-direction: column;
                gap: 0.5rem;
            }

            .back-link {
                padding: 0.7rem 1rem;
                font-size: 0.8rem;
            }
        }

        /* Scrollbar Styling */
        .table-container::-webkit-scrollbar {
            height: 8px;
        }

        .table-container::-webkit-scrollbar-track {
            background: rgba(108, 168, 214, 0.1);
            border-radius: 10px;
        }

        .table-container::-webkit-scrollbar-thumb {
            background: var(--soft-blue);
            border-radius: 10px;
        }

        .table-container::-webkit-scrollbar-thumb:hover {
            background: var(--admin-accent);
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header Section -->
        <div class="header">
            <div class="header-content">
                <h1 class="page-title">
                    <i class="fas fa-comments"></i>
                    All Feedbacks
                </h1>
            </div>
        </div>

        <!-- Table Container -->
        <div class="table-container">
            <?php if ($result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th><i class="fas fa-user"></i> User</th>
                            <th><i class="fas fa-id-card"></i> Name</th>
                            <th><i class="fas fa-envelope"></i> Email</th>
                            <th><i class="fas fa-message"></i> Message</th>
                            <th><i class="fas fa-star"></i> Rating</th>
                            <th><i class="fas fa-calendar"></i> Submitted At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <span class="user-id <?= $row['user_id'] === null ? 'anonymous' : '' ?>">
                                    <i class="fas fa-<?= $row['user_id'] === null ? 'user-secret' : 'user' ?>"></i>
                                    <?= $row['user_id'] !== null ? 'User #' . $row['user_id'] : 'Anonymous' ?>
                                </span>
                            </td>
                            <td class="name-cell">
                                <?= $row['name'] ? htmlspecialchars($row['name']) : '<span class="not-provided">Not provided</span>' ?>
                            </td>
                            <td class="email-cell">
                                <?= $row['email'] ? htmlspecialchars($row['email']) : '<span class="not-provided">Not provided</span>' ?>
                            </td>
                            <td class="message-cell">
                                <?= nl2br(htmlspecialchars($row['message'])) ?>
                            </td>
                            <td class="rating-cell">
                                <div class="stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star star <?= $i <= $row['rating'] ? '' : 'empty' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="rating-number"><?= $row['rating'] ?>/5</span>
                            </td>
                            <td class="date-cell">
                                <i class="fas fa-clock"></i>
                                <?= date('M j, Y g:i A', strtotime($row['submitted_at'])) ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>No Feedbacks Yet</h3>
                    <p>No user feedbacks have been submitted yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Back Button - Fixed at Bottom Left -->
    <div class="back-button-container">
        <a href="admin_dashboard.php" class="back-link">
            <i class="fas fa-arrow-left"></i>
            Back to Dashboard
        </a>
    </div>

    <script>
        // Add smooth animations and interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Animate table rows on load
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach((row, index) => {
                row.style.opacity = '0';
                row.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    row.style.transition = 'all 0.5s ease';
                    row.style.opacity = '1';
                    row.style.transform = 'translateY(0)';
                }, index * 50);
            });

            // Add click animation to back button
            const backLink = document.querySelector('.back-link');
            if (backLink) {
                backLink.addEventListener('click', function(e) {
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 150);
                });
            }

            // Add hover effect to table rows
            rows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.005)';
                    this.style.boxShadow = '0 4px 15px rgba(0, 0, 0, 0.1)';
                });

                row.addEventListener('mouseleave', function() {
                    this.style.transform = '';
                    this.style.boxShadow = '';
                });
            });
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>