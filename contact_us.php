<?php
session_start();
// No login required, accessible by all users
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - MindHaven</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* MindHaven Contact Us Styles */
        :root {
            --soft-blue: #6CA8D6;
            --light-teal: #2E8B57;
            --counsellor-accent: #4A90E2;
            --dark-gray: #2E2E2E;
            --medium-gray: #6E6E6E;
            --white: #ffffff;
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
            margin: 50px auto;
            padding: 2rem;
            position: relative;
            z-index: 1;
        }

        .contact-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        .contact-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, var(--soft-blue), var(--light-teal), var(--counsellor-accent));
            border-radius: 20px 20px 0 0;
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--counsellor-accent), var(--soft-blue), var(--light-teal));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-align: center;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }

        h1 i {
            color: var(--soft-blue);
            font-size: 2rem;
        }

        .contact-info {
            font-size: 1.1rem;
            line-height: 1.8;
            margin-bottom: 2rem;
        }

        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: rgba(108, 168, 214, 0.05);
            border-radius: 15px;
            transition: all 0.3s ease;
            border-left: 4px solid var(--soft-blue);
        }

        .contact-item:hover {
            background: rgba(108, 168, 214, 0.1);
            transform: translateX(5px);
        }

        .contact-item i {
            font-size: 1.5rem;
            color: var(--soft-blue);
            margin-right: 1rem;
            width: 30px;
            text-align: center;
        }

        .contact-item strong {
            color: var(--dark-gray);
            margin-right: 0.5rem;
            font-weight: 600;
        }

        .contact-item a {
            color: var(--counsellor-accent);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .contact-item a:hover {
            color: var(--soft-blue);
            text-decoration: underline;
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
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(108, 168, 214, 0.3);
            margin: 0 auto;
            display: flex;
            justify-content: center;
            width: fit-content;
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
                margin: 20px auto;
                padding: 1rem;
            }

            .contact-card {
                padding: 2rem;
            }

            h1 {
                font-size: 2rem;
                flex-direction: column;
                gap: 0.5rem;
            }

            h1 i {
                font-size: 1.5rem;
            }

            .contact-info {
                font-size: 1rem;
            }

            .contact-item {
                flex-direction: column;
                text-align: center;
                gap: 0.5rem;
            }

            .contact-item i {
                margin-right: 0;
                margin-bottom: 0.5rem;
            }

            .back-link {
                padding: 0.8rem 1.5rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            h1 {
                font-size: 1.8rem;
            }

            .contact-card {
                padding: 1.5rem;
            }

            .contact-item {
                padding: 0.8rem;
            }
        }

        /* Animation */
        .contact-item {
            opacity: 0;
            transform: translateY(20px);
            animation: slideIn 0.6s ease forwards;
        }

        .contact-item:nth-child(1) { animation-delay: 0.1s; }
        .contact-item:nth-child(2) { animation-delay: 0.2s; }
        .contact-item:nth-child(3) { animation-delay: 0.3s; }

        @keyframes slideIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="contact-card">
            <h1>
                <i class="fas fa-envelope"></i>
                Contact Us
            </h1>

            <div class="contact-info">
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <strong>Email:</strong>
                        <a href="mailto:lurhkaf030224@gmail.com">lurhkaf030224@gmail.com</a>
                    </div>
                </div>

                <div class="contact-item">
                    <i class="fas fa-phone"></i>
                    <div>
                        <strong>Telephone:</strong>
                        <a href="tel:+601111924815">011-11924815</a>
                    </div>
                </div>

                <div class="contact-item">
                    <i class="fab fa-linkedin"></i>
                    <div>
                        <strong>LinkedIn:</strong>
                        <a href="https://www.linkedin.com/in/muhammad-fakhrul-iman-959b36331/" target="_blank" rel="noopener noreferrer">Muhammad Fakhrul Iman</a>
                    </div>
                </div>
            </div>

            <a href="dashboard.php" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>
    </div>
</body>
</html>