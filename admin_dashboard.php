<?php
session_start();

// Only allow access if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MindHaven</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* MindHaven Admin Dashboard Styles */
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            position: relative;
            z-index: 1;
        }

        /* Header Section */
        .header {
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
            padding: 3rem 2rem;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
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

        .admin-badge {
            display: inline-flex;
            align-items: center;
            background: linear-gradient(135deg, var(--admin-accent), var(--soft-blue));
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 50px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 1rem;
            box-shadow: 0 4px 15px rgba(74, 144, 226, 0.3);
        }

        .admin-badge i {
            margin-right: 0.5rem;
            font-size: 1rem;
        }

        .welcome-title {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, var(--admin-accent), var(--soft-blue), var(--light-teal));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
            letter-spacing: -1px;
        }

        .welcome-subtitle {
            font-size: 1.2rem;
            color: var(--medium-gray);
            font-weight: 500;
            margin-top: 0.5rem;
        }

        /* Admin Menu */
        .admin-menu {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .menu-item {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 2.5rem;
            text-decoration: none;
            color: var(--dark-gray);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .menu-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, transparent, rgba(255, 255, 255, 0.1));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .menu-item:hover::before {
            opacity: 1;
        }

        .menu-item:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .menu-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 1;
        }

        .menu-icon::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: inherit;
            filter: blur(20px);
            opacity: 0.4;
            z-index: -1;
        }

        .icon-resources {
            background: linear-gradient(135deg, var(--soft-blue), #4A90E2);
        }

        .icon-feedback {
            background: linear-gradient(135deg, var(--light-teal), #228B22);
        }

        .icon-logout {
            background: linear-gradient(135deg, var(--muted-coral), #FF8A73);
        }

        .menu-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.8rem;
            color: var(--dark-gray);
        }

        .menu-description {
            font-size: 1rem;
            color: var(--medium-gray);
            line-height: 1.5;
        }

        /* Special styling for logout - make it smaller */
        .logout-container {
            display: flex;
            justify-content: center;
            margin-top: 3rem;
            margin-bottom: 2rem;
        }

        .logout-item {
            border: 2px solid var(--muted-coral);
            background: rgba(255, 182, 160, 0.1);
            padding: 1rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            color: var(--muted-coral);
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            box-shadow: 0 4px 15px rgba(255, 182, 160, 0.2);
        }

        .logout-item:hover {
            background: var(--muted-coral);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 182, 160, 0.4);
        }

        .logout-item i {
            margin-right: 0.5rem;
            font-size: 1rem;
        }

        /* Pulse animations */
        @keyframes pulse-blue {
            0% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(108, 168, 214, 0.7);
            }
            70% {
                transform: scale(1.05);
                box-shadow: 0 0 0 10px rgba(108, 168, 214, 0);
            }
            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(108, 168, 214, 0);
            }
        }

        @keyframes pulse-teal {
            0% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(46, 139, 87, 0.7);
            }
            70% {
                transform: scale(1.05);
                box-shadow: 0 0 0 10px rgba(46, 139, 87, 0);
            }
            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(46, 139, 87, 0);
            }
        }

        .pulse-blue {
            animation: pulse-blue 2s infinite;
        }

        .pulse-teal {
            animation: pulse-teal 2.5s infinite;
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 2rem;
            color: var(--medium-gray);
            font-size: 0.9rem;
            background: rgba(255, 255, 255, 0.7);
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .header {
                padding: 2rem 1rem;
                margin-bottom: 2rem;
            }

            .welcome-title {
                font-size: 2rem;
            }

            .admin-menu {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .menu-item {
                padding: 2rem;
            }

            .menu-icon {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }

            .menu-title {
                font-size: 1.3rem;
            }
        }

        @media (max-width: 480px) {
            .welcome-title {
                font-size: 1.8rem;
            }

            .menu-item {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header Section -->
        <div class="header">
            <div class="admin-badge">
                <i class="fas fa-shield-alt"></i>
                Administrator Access
            </div>
            <h1 class="welcome-title">Welcome, Admin</h1>
            <p class="welcome-subtitle">MindHaven Administrative Dashboard</p>
        </div>

        <!-- Admin Menu -->
        <div class="admin-menu">
            <a href="admin_manageresources.php" class="menu-item">
                <div class="menu-icon icon-resources pulse-blue">
                    <i class="fas fa-cogs"></i>
                </div>
                <h2 class="menu-title">Resource Management</h2>
                <p class="menu-description">Manage and organize mental health resources, articles, and educational content for users.</p>
            </a>

            <a href="admin_feedbacks.php" class="menu-item">
                <div class="menu-icon icon-feedback pulse-teal">
                    <i class="fas fa-comments"></i>
                </div>
                <h2 class="menu-title">View Feedbacks</h2>
                <p class="menu-description">Review user feedback, suggestions, and testimonials to improve the platform experience.</p>
            </a>
        </div>

        <!-- Logout Button -->
        <div class="logout-container">
            <a href="logout.php" class="logout-item">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>&copy; 2025 MindHaven Administrative Panel. All rights reserved.</p>
        </div>
    </div>

    <script>
        // Add smooth hover effects and interactions
        document.addEventListener('DOMContentLoaded', function() {
            const menuItems = document.querySelectorAll('.menu-item:not(.logout-item)');
            
            menuItems.forEach(item => {
                item.addEventListener('mouseenter', function() {
                    menuItems.forEach(otherItem => {
                        if (otherItem !== item) {
                            otherItem.style.opacity = '0.7';
                            otherItem.style.transform = 'scale(0.95)';
                        }
                    });
                });
                
                item.addEventListener('mouseleave', function() {
                    menuItems.forEach(otherItem => {
                        otherItem.style.opacity = '1';
                        otherItem.style.transform = '';
                    });
                });
            });

            // Add click animation
            const allMenuItems = document.querySelectorAll('.menu-item');
            allMenuItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    // Create ripple effect
                    const ripple = document.createElement('div');
                    const rect = item.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.cssText = `
                        position: absolute;
                        width: ${size}px;
                        height: ${size}px;
                        left: ${x}px;
                        top: ${y}px;
                        background: rgba(255, 255, 255, 0.5);
                        border-radius: 50%;
                        transform: scale(0);
                        animation: ripple 0.6s ease-out;
                        pointer-events: none;
                        z-index: 10;
                    `;
                    
                    item.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
        });

        // Add CSS for ripple animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(2);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>