<?php
session_start();
require_once 'database.php';

// Ha már be van jelentkezve, átirányítás a dashboardra
if(isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

// Jelszó hash létrehozása (első telepítéshez)
if(isset($_GET['setup']) && $_GET['setup'] === 'create_password') {
    $password = 'admin123';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $success = "Jelszó hash: " . $hash;
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Admin ellenőrzés
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_name'] = $admin['full_name'] ?? $admin['username'];
        $_SESSION['login_time'] = time();
        
        // Utolsó belépés frissítése
        $updateStmt = $pdo->prepare("UPDATE admins SET last_login = NOW(), last_ip = ? WHERE id = ?");
        $updateStmt->execute([$_SERVER['REMOTE_ADDR'], $admin['id']]);
        
        // Naplózás
        $logStmt = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $logStmt->execute([$admin['id'], 'login', 'Sikeres bejelentkezés', $_SERVER['REMOTE_ADDR']]);
        
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Hibás felhasználónév vagy jelszó!';
        
        // Sikertelen próbálkozás naplózása
        $logStmt = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, details, ip_address) VALUES (NULL, 'failed_login', ?, ?)");
        $logStmt->execute(["Sikertelen bejelentkezés: $username", $_SERVER['REMOTE_ADDR']]);
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kickstar Admin - Bejelentkezés</title>
    <link rel="stylesheet" href="style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header */
        header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        nav {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: #ff6b6b;
        }

        nav ul {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        nav ul li a {
            color: #2c3e50;
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        nav ul li a:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Animated background */
        .bg-bubbles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
        }

        .bg-bubbles li {
            position: absolute;
            list-style: none;
            display: block;
            width: 40px;
            height: 40px;
            background-color: rgba(255, 255, 255, 0.15);
            bottom: -160px;
            animation: square 25s infinite;
            transition-timing-function: linear;
            border-radius: 50%;
        }

        .bg-bubbles li:nth-child(1) {
            left: 10%;
            width: 80px;
            height: 80px;
            animation-delay: 0s;
        }

        .bg-bubbles li:nth-child(2) {
            left: 20%;
            width: 40px;
            height: 40px;
            animation-delay: 2s;
            animation-duration: 17s;
        }

        .bg-bubbles li:nth-child(3) {
            left: 25%;
            width: 120px;
            height: 120px;
            animation-delay: 4s;
        }

        .bg-bubbles li:nth-child(4) {
            left: 40%;
            width: 60px;
            height: 60px;
            animation-delay: 0s;
            animation-duration: 22s;
        }

        .bg-bubbles li:nth-child(5) {
            left: 70%;
            width: 50px;
            height: 50px;
            animation-delay: 0s;
        }

        .bg-bubbles li:nth-child(6) {
            left: 80%;
            width: 110px;
            height: 110px;
            animation-delay: 3s;
        }

        .bg-bubbles li:nth-child(7) {
            left: 32%;
            width: 150px;
            height: 150px;
            animation-delay: 7s;
        }

        .bg-bubbles li:nth-child(8) {
            left: 55%;
            width: 45px;
            height: 45px;
            animation-delay: 15s;
            animation-duration: 40s;
        }

        .bg-bubbles li:nth-child(9) {
            left: 15%;
            width: 35px;
            height: 35px;
            animation-delay: 2s;
            animation-duration: 40s;
        }

        .bg-bubbles li:nth-child(10) {
            left: 90%;
            width: 140px;
            height: 140px;
            animation-delay: 11s;
        }

        @keyframes square {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translateY(-1000px) rotate(720deg);
                opacity: 0;
            }
        }

        /* Main content - középre igazítás */
        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 80px 20px 20px;
            position: relative;
            z-index: 1;
        }

        /* Login container - KÖZÉPRE IGAZÍTVA */
        .login-wrapper {
            width: 100%;
            max-width: 450px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: slideIn 0.6s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Header */
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .login-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: rgba(255, 255, 255, 0.1);
            transform: rotate(45deg);
            animation: shine 3s infinite;
        }

        @keyframes shine {
            0% {
                transform: translateX(-100%) rotate(45deg);
            }
            100% {
                transform: translateX(100%) rotate(45deg);
            }
        }

        .login-header .logo {
            font-size: 4rem;
            margin-bottom: 15px;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        .login-header h1 {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .login-header p {
            opacity: 0.9;
            font-size: 0.9rem;
        }

        /* Body */
        .login-body {
            padding: 40px 30px;
        }

        /* Messages */
        .error-message {
            background: linear-gradient(135deg, #feb2b2 0%, #fc8181 100%);
            color: #fff;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: shake 0.5s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .success-message {
            background: linear-gradient(135deg, #9ae6b4 0%, #68d391 100%);
            color: #fff;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Form */
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #4a5568;
            font-weight: 500;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .form-group .input-icon {
            position: relative;
        }

        .form-group .input-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            font-size: 1.2rem;
            transition: color 0.3s ease;
            font-style: normal;
        }

        .form-group input {
            width: 100%;
            padding: 15px 15px 15px 50px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group input:focus + i {
            color: #667eea;
        }

        .form-group input::placeholder {
            color: #cbd5e0;
            font-size: 0.9rem;
        }

        /* Password visibility toggle */
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #a0aec0;
            transition: color 0.3s ease;
            font-size: 1.2rem;
        }

        .password-toggle:hover {
            color: #667eea;
        }

        /* Remember me and forgot password */
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #4a5568;
            font-size: 0.9rem;
            cursor: pointer;
        }

        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #667eea;
        }

        .forgot-password {
            color: #667eea;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .forgot-password:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        /* Login button */
        .login-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .login-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .login-btn:hover::before {
            width: 300px;
            height: 300px;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        /* Loading state */
        .login-btn.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .login-btn.loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin-left: -10px;
            margin-top: -10px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Footer */
        .login-footer {
            text-align: center;
            margin-top: 20px;
            color: gray;
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .login-footer a {
            color: gray;
            text-decoration: none;
            font-weight: 600;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-wrapper {
                padding: 10px;
            }
            
            .login-header {
                padding: 30px 20px;
            }
            
            .login-header .logo {
                font-size: 3rem;
            }
            
            .login-header h1 {
                font-size: 1.5rem;
            }
            
            .login-body {
                padding: 30px 20px;
            }
            
            .form-options {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }

            nav {
                flex-direction: column;
                gap: 10px;
            }

            nav ul {
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav>
            <div class="logo">Kickstar</div>
            <ul>
                <li><a href="index.php">Főoldal</a></li>
                <li><a href="products.php">Termékek</a></li>
                <li><a href="login.php">🛡️ Admin felület</a></li>
            </ul>
        </nav>
    </header>

    <!-- Animated background -->
    <ul class="bg-bubbles">
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
    </ul>

    <main>
        <div class="login-wrapper">
            <div class="login-card">
                <!-- Header -->
                <div class="login-header">
                    <div class="logo">👟</div>
                    <h1>Kickstar Admin</h1>
                    <p>Kérem jelentkezzen be a folytatáshoz</p>
                </div>

                <!-- Body -->
                <div class="login-body">
                    <?php if($error): ?>
                        <div class="error-message">
                            <span>⚠️</span>
                            <span><?php echo $error; ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if($success): ?>
                        <div class="success-message">
                            <span>✅</span>
                            <span><?php echo $success; ?></span>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" id="loginForm">
                        <div class="form-group">
                            <label>Felhasználónév</label>
                            <div class="input-icon">
                                <i>👤</i>
                                <input type="text" name="username" placeholder="pl.: admin" required autofocus>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Jelszó</label>
                            <div class="input-icon">
                                <i>🔒</i>
                                <input type="password" name="password" id="password" placeholder="••••••••" required>
                                <span class="password-toggle" onclick="togglePassword()">👁️</span>
                            </div>
                        </div>

                        <div class="form-options">
                            <label class="remember-me">
                                <input type="checkbox" name="remember"> 
                                <span>Emlékezz rám</span>
                            </label>
                            <a href="#" class="forgot-password" onclick="alert('Kérjük, vegye fel a kapcsolatot a rendszergazdával!')">Elfelejtett jelszó?</a>
                        </div>

                        <button type="submit" class="login-btn" id="loginBtn">Bejelentkezés</button>
                    </form>
                </div>
            </div>

            <!-- Footer -->
            <div class="login-footer">
                <p>&copy; 2024 Kickstar Admin. Minden jog fenntartva. | <a href="index.php">Vissza a webshopba</a></p>
            </div>
        </div>
    </main>

    <script>
        // Password visibility toggle
        function togglePassword() {
            const password = document.getElementById('password');
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            const toggle = document.querySelector('.password-toggle');
            toggle.textContent = type === 'password' ? '👁️' : '👁️‍🗨️';
        }

        // Loading animation on form submit
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('loginBtn');
            btn.classList.add('loading');
            btn.textContent = 'Bejelentkezés...';
        });

        // Add animation styles
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOutRight {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);

        // Prevent multiple form submissions
        let formSubmitted = false;
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            if (formSubmitted) {
                e.preventDefault();
                return;
            }
            formSubmitted = true;
        });

        // Auto-focus username field
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('input[name="username"]').focus();
        });

        // Enter key submits form
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !formSubmitted) {
                e.preventDefault();
                document.getElementById('loginForm').submit();
            }
        });
    </script>
</body>
</html>