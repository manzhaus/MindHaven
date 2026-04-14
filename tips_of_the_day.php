<?php
session_start();
// No login required, accessible to all users

$tips = [
    "Take a few deep breaths to reduce stress.",
    "Try to get at least 7-8 hours of sleep every night.",
    "Stay connected with friends and family.",
    "Take breaks from screens to rest your eyes and mind.",
    "Practice gratitude by writing down 3 things you're thankful for.",
    "Exercise regularly to boost your mood.",
    "Limit your caffeine and sugar intake for better mental clarity.",
    "Spend some time outdoors in nature each day.",
    "Meditate or practice mindfulness for a few minutes daily.",
    "Reach out for help when you feel overwhelmed."
];

// Pick a random tip
$randomTip = $tips[array_rand($tips)];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tips of the Day - MindHaven</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* MindHaven Tips Styles */
        :root {
            --soft-blue: #6CA8D6;
            --light-teal: #2E8B57;
            --counsellor-accent: #4A90E2;
            --dark-gray: #2E2E2E;
            --medium-gray: #6E6E6E;
            --white: #ffffff;
            --light-blue: #E6F0FA;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f8fbff, #f0f8f5, #faf9ff);
            color: var(--dark-gray);
            line-height: 1.6;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
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

        .container {
            max-width: 700px;
            margin: 0 auto;
            padding: 2rem;
            position: relative;
            z-index: 1;
            text-align: center;
        }

        .tip-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 25px;
            padding: 3rem;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
            animation: fadeInUp 0.8s ease-out;
        }

        .tip-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, var(--soft-blue), var(--light-teal), var(--counsellor-accent));
            border-radius: 25px 25px 0 0;
        }

        h1 {
            font-size: 2.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--counsellor-accent), var(--soft-blue), var(--light-teal));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }

        h1 i {
            color: var(--soft-blue);
            font-size: 2.5rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .tip {
            font-size: 1.4rem;
            padding: 2rem;
            background: linear-gradient(135deg, rgba(108, 168, 214, 0.1), rgba(46, 139, 87, 0.05));
            border-radius: 20px;
            border: 2px solid rgba(108, 168, 214, 0.2);
            line-height: 1.7;
            margin-bottom: 2.5rem;
            position: relative;
            font-weight: 500;
            color: var(--dark-gray);
            box-shadow: inset 0 2px 10px rgba(108, 168, 214, 0.1);
        }

        .tip::before {
            content: '"';
            position: absolute;
            top: -10px;
            left: 20px;
            font-size: 4rem;
            color: var(--soft-blue);
            opacity: 0.3;
            font-family: serif;
        }

        .tip::after {
            content: '"';
            position: absolute;
            bottom: -30px;
            right: 20px;
            font-size: 4rem;
            color: var(--soft-blue);
            opacity: 0.3;
            font-family: serif;
        }

        .refresh-btn {
            background: linear-gradient(135deg, var(--light-teal), #228B22);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 6px 20px rgba(46, 139, 87, 0.3);
            margin-right: 1rem;
        }

        .refresh-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(46, 139, 87, 0.4);
        }

        .refresh-btn i {
            transition: transform 0.3s ease;
        }

        .refresh-btn:hover i {
            transform: rotate(180deg);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(135deg, var(--soft-blue), var(--counsellor-accent));
            color: white;
            padding: 1rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(108, 168, 214, 0.3);
        }

        .back-link:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(108, 168, 214, 0.4);
        }

        .back-link i {
            font-size: 1rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .tip-card {
                padding: 2rem;
            }

            h1 {
                font-size: 2.2rem;
                flex-direction: column;
                gap: 0.5rem;
            }

            h1 i {
                font-size: 2rem;
            }

            .tip {
                font-size: 1.2rem;
                padding: 1.5rem;
            }

            .refresh-btn, .back-link {
                padding: 0.8rem 1.5rem;
                font-size: 0.9rem;
                margin: 0.5rem;
                display: block;
                width: fit-content;
                margin-left: auto;
                margin-right: auto;
            }
        }

        @media (max-width: 480px) {
            h1 {
                font-size: 1.8rem;
            }

            .tip-card {
                padding: 1.5rem;
            }

            .tip {
                font-size: 1.1rem;
                padding: 1.2rem;
            }

            .refresh-btn, .back-link {
                width: 100%;
                justify-content: center;
                margin: 0.5rem 0;
            }
        }

        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .tip {
            animation: slideIn 0.6s ease-out 0.3s both;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="tip-card">
            <h1>
                <i class="fas fa-lightbulb"></i>
                Tip of the Day
            </h1>
            
            <div class="tip" id="tipText">
                <?= htmlspecialchars($randomTip) ?>
            </div>

            <button class="refresh-btn" onclick="getNewTip()">
                <i class="fas fa-sync-alt"></i>
                New Tip
            </button>

            <a href="dashboard.php" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>
    </div>

    <script>
        const tips = <?= json_encode($tips) ?>;
        let currentTipIndex = <?= array_search($randomTip, $tips) ?>;

        function getNewTip() {
            let newIndex;
            do {
                newIndex = Math.floor(Math.random() * tips.length);
            } while (newIndex === currentTipIndex && tips.length > 1);
            
            currentTipIndex = newIndex;
            const tipElement = document.getElementById('tipText');
            
            // Fade out
            tipElement.style.opacity = '0';
            tipElement.style.transform = 'scale(0.9)';
            
            setTimeout(() => {
                tipElement.textContent = tips[newIndex];
                // Fade in
                tipElement.style.opacity = '1';
                tipElement.style.transform = 'scale(1)';
            }, 300);
        }
    </script>
</body>
</html>