<?php
function checkLoginModal() {
    if (!isset($_SESSION['user_id'])) {
        echo <<<HTML
        <style>
        /* Modal styles - MindHaven Theme Alert */
        #loginModal {
            display: block;
            position: fixed;
            z-index: 1000;
            left: 0; top: 0;
            width: 100%; height: 100%;
            background-color: rgba(46, 46, 46, 0.6);
            backdrop-filter: blur(4px);
        }
        #loginModal .modal-content {
            background: linear-gradient(135deg, #f8fbff, #f0f8f5);
            margin: 20% auto;
            padding: 30px 25px;
            width: 350px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 15px 35px rgba(108, 168, 214, 0.3);
            border: 1px solid rgba(108, 168, 214, 0.2);
            position: relative;
            animation: modalSlideIn 0.3s ease-out;
        }
        #loginModal .modal-content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(to right, #6CA8D6, #2E8B57);
            border-radius: 16px 16px 0 0;
        }
        #loginModal .modal-content p {
            color: #2E2E2E;
            font-size: 16px;
            margin-bottom: 25px;
            font-weight: 500;
            line-height: 1.5;
        }
        #loginModal button {
            margin: 8px;
            padding: 12px 24px;
            cursor: pointer;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            min-width: 100px;
        }
        #loginModal button:first-of-type {
            background-color: #6CA8D6;
            color: white;
        }
        #loginModal button:first-of-type:hover {
            background-color: #5A96C4;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(108, 168, 214, 0.4);
        }
        #loginModal button:last-of-type {
            background-color: transparent;
            color: #6E6E6E;
            border: 1px solid #D9CFE8;
        }
        #loginModal button:last-of-type:hover {
            background-color: #D9CFE8;
            color: #2E2E2E;
            transform: translateY(-2px);
        }
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-30px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        </style>
        <script>
        function handleCancel() {
            if (document.referrer && document.referrer !== window.location.href) {
                window.location.href = document.referrer;
            } else {
                window.location.href = 'dashboard.php';
            }
        }
        </script>
        <div id="loginModal">
            <div class="modal-content">
                <p>Please sign in to access this page.</p>
                <button onclick="window.location.href='login.php'">Login</button>
                <button onclick="handleCancel()">Cancel</button>
            </div>
        </div>
        HTML;
        return true;
    }
    return false;
}
?>