<?php
session_start();
$host = 'localhost';
$db   = 'car_sales';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && $password === $user['password']) {
            unset($user['password']);
            $_SESSION['user'] = $user;
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    } else {
        $error = 'Please enter both username and password.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | QMSL</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap');

        :root {
            --black-solid: #0a0a0a;
            --white-solid: #ffffff;
            --black-text: #111111;
            --white-text: #ffffff;
            --secondary-black: rgba(0,0,0,0.55);
            --secondary-white: rgba(255,255,255,0.60);
            --tertiary-black: rgba(0,0,0,0.30);
            --tertiary-white: rgba(255,255,255,0.35);
            --border-black: rgba(0,0,0,0.08);
            --border-white: rgba(255,255,255,0.10);
            --input-bg-black: rgba(255,255,255,0.06);
            --input-bg-white: rgba(0,0,0,0.03);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            font-size: 16px;
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', 'Helvetica Neue', 'Segoe UI', sans-serif;
            background-color: var(--black-solid);
            color: var(--white-text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            letter-spacing: 0.02em;
        }

        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: rgba(10, 10, 10, 0.75);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-white);
            z-index: 1000;
            padding: 18px 0;
            transition: all 0.2s ease;
        }
        .nav-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        .logo {
            font-size: 1.6rem;
            font-weight: 600;
            letter-spacing: 0.12em;
            color: #ffffff;
            text-decoration: none;
            transition: opacity 0.2s;
        }
        .logo:hover { opacity: 0.75; }
        .nav-links {
            display: flex;
            gap: 2.2rem;
            list-style: none;
            flex-wrap: wrap;
        }
        .nav-links a {
            color: rgba(255, 255, 255, 0.75);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            letter-spacing: 0.08em;
            transition: color 0.2s;
        }
        .nav-links a:hover { color: #ffffff; }

        .main-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 140px 32px 60px;
            width: 100%;
        }

        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 32px;
            width: 100%;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border-black);
            border-radius: 16px;
            padding: 48px;
            max-width: 460px;
            margin: 0 auto;
            width: 100%;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
            color: var(--black-text);
        }

        .card-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .card-header h1 {
            font-size: 1.8rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            color: var(--black-text);
            margin-bottom: 6px;
        }
        .card-header .subtitle {
            font-size: 0.9rem;
            color: var(--secondary-black);
            letter-spacing: 0.05em;
        }

        .form-group {
            margin-bottom: 24px;
        }
        .form-group label {
            display: block;
            font-weight: 500;
            font-size: 0.8rem;
            letter-spacing: 0.06em;
            color: var(--secondary-black);
            margin-bottom: 8px;
        }
        .form-group input {
            width: 100%;
            padding: 14px 16px;
            font-size: 0.95rem;
            font-family: inherit;
            background: var(--input-bg-white);
            border: 1px solid var(--border-black);
            border-radius: 8px;
            color: var(--black-text);
            outline: none;
            transition: border 0.2s, background 0.2s;
        }
        .form-group input:focus {
            border-color: rgba(0,0,0,0.3);
            background: rgba(0,0,0,0.02);
        }
        .form-group input.input-error {
            border-color: rgba(0,0,0,0.2);
            background: rgba(0,0,0,0.02);
        }
        .error-message {
            font-size: 0.75rem;
            color: var(--secondary-black);
            margin-top: 6px;
            min-height: 18px;
            letter-spacing: 0.03em;
            transition: opacity 0.2s;
        }

        .btn-submit {
            width: 100%;
            padding: 14px 24px;
            background: var(--black-solid);
            color: var(--white-text);
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.2s;
            margin-top: 8px;
        }
        .btn-submit:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        .btn-submit:active {
            transform: translateY(0);
        }

        .success-msg {
            background: rgba(0,0,0,0.03);
            border: 1px solid var(--border-black);
            border-radius: 8px;
            padding: 14px 18px;
            margin-bottom: 28px;
            text-align: center;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--black-text);
            letter-spacing: 0.04em;
            display: none;
        }
        .success-msg.show {
            display: block;
        }

        .card-footer {
            text-align: center;
            margin-top: 28px;
            font-size: 0.85rem;
            color: var(--secondary-black);
            letter-spacing: 0.04em;
        }
        .card-footer a {
            color: var(--black-text);
            text-decoration: none;
            font-weight: 600;
            letter-spacing: 0.06em;
            border-bottom: 1px solid var(--secondary-black);
            transition: opacity 0.2s;
        }
        .card-footer a:hover {
            opacity: 0.7;
        }

        .footer {
            background-color: #0a0a0a;
            padding: 48px 0;
            text-align: center;
            border-top: 1px solid var(--border-white);
        }
        .footer p {
            color: rgba(255,255,255,0.5);
            font-size: 0.8rem;
            letter-spacing: 0.1em;
        }

        @media (max-width: 1024px) {
            .car-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 22px;
            }
            .search-hero {
                padding: 48px 0 56px;
                min-height: auto;
            }
            .search-hero-title {
                font-size: 2.2rem;
            }
            .search-panel {
                padding: 24px 24px;
                gap: 16px;
            }
        }

        @media (max-width: 768px) {
            body {
                padding-top: 70px;
            }
            .navbar {
                padding: 14px 0;
            }
            .nav-container {
                padding: 0 20px;
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            .logo {
                font-size: 1.5rem;
            }
            .nav-links {
                gap: 1.5rem;
            }
            .nav-links a {
                font-size: 0.8rem;
            }

            .search-hero {
                padding: 40px 0 50px;
                min-height: auto;
            }
            .search-hero-title {
                font-size: 1.8rem;
            }
            .search-hero-subtitle {
                font-size: 0.9rem;
                margin-bottom: 24px;
            }
            .search-panel {
                padding: 20px 18px;
                gap: 14px;
            }
            .search-row {
                flex-direction: column;
                gap: 12px;
            }
            .search-field {
                min-width: 100%;
            }
            .search-actions {
                flex-direction: row;
                width: 100%;
                flex-wrap: wrap;
            }
            .search-actions button {
                flex: 1;
                white-space: normal;
                text-align: center;
            }
            .car-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 18px;
            }
            .container {
                padding: 0 20px;
            }
            .results-section {
                padding: 40px 0 50px;
            }
            .results-header {
                margin-bottom: 24px;
            }
            .empty-state {
                padding: 60px 20px;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 600px) {
            .car-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            .search-hero-title {
                font-size: 1.6rem;
            }
            .search-panel {
                padding: 16px 14px;
                gap: 12px;
            }
            .search-field input {
                padding: 12px 14px;
                font-size: 0.9rem;
            }
            .btn-primary-dark,
            .btn-ghost-dark {
                padding: 12px 16px;
                font-size: 0.8rem;
                letter-spacing: 0.06em;
            }
            .card-body {
                padding: 16px 18px 18px;
                gap: 6px;
            }
            .car-title {
                font-size: 1rem;
            }
            .price {
                font-size: 1.1rem;
            }
            .modal-panel {
                max-width: 100%;
                margin: 0 8px;
            }
            .modal-body {
                padding: 20px 18px 24px;
            }
            .modal-title {
                font-size: 1.3rem;
            }
            .modal-specs {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            .modal-price-large {
                font-size: 1.5rem;
            }
            .modal-close {
                top: 12px;
                right: 12px;
                width: 34px;
                height: 34px;
                font-size: 1rem;
            }
        }

        @media (max-width: 480px) {
            body {
                padding-top: 64px;
            }
            .container {
                padding: 0 14px;
            }
            .navbar {
                padding: 10px 0;
            }
            .nav-container {
                padding: 0 14px;
                gap: 8px;
            }
            .logo {
                font-size: 1.3rem;
                letter-spacing: 0.08em;
            }
            .nav-links {
                gap: 1rem;
                flex-wrap: wrap;
            }
            .nav-links a {
                font-size: 0.7rem;
                letter-spacing: 0.05em;
            }

            .search-hero {
                padding: 32px 0 40px;
            }
            .search-hero-title {
                font-size: 1.5rem;
                letter-spacing: 0.05em;
            }
            .search-hero-subtitle {
                font-size: 0.8rem;
                margin-bottom: 20px;
            }
            .search-panel {
                padding: 14px 12px;
                gap: 10px;
            }
            .search-field label {
                font-size: 0.65rem;
            }
            .search-field input {
                padding: 10px 12px;
                font-size: 0.8rem;
            }
            .search-actions {
                gap: 8px;
            }
            .btn-primary-dark,
            .btn-ghost-dark {
                padding: 10px 14px;
                font-size: 0.75rem;
            }

            .results-section {
                padding: 30px 0 40px;
            }
            .results-count {
                font-size: 0.95rem;
            }
            .car-grid {
                grid-template-columns: 1fr;
                gap: 14px;
            }
            .card-body {
                padding: 14px 14px 16px;
                gap: 4px;
            }
            .car-title {
                font-size: 0.95rem;
            }
            .car-meta {
                font-size: 0.7rem;
                gap: 10px;
            }
            .car-meta span+span::before {
                left: -7px;
            }
            .car-details {
                font-size: 0.7rem;
            }
            .price {
                font-size: 1rem;
            }
            .location {
                font-size: 0.7rem;
            }

            .modal-overlay {
                padding: 12px;
            }
            .modal-panel {
                max-height: 85vh;
            }
            .modal-img {
                aspect-ratio: 4/3;
            }
            .modal-body {
                padding: 16px 14px 20px;
            }
            .modal-title {
                font-size: 1.2rem;
            }
            .modal-subtitle {
                font-size: 0.75rem;
                margin-bottom: 14px;
            }
            .modal-specs {
                grid-template-columns: 1fr;
                gap: 8px;
                padding: 14px 0;
                margin-bottom: 18px;
            }
            .modal-spec-label {
                font-size: 0.6rem;
            }
            .modal-spec-value {
                font-size: 0.85rem;
            }
            .modal-price-large {
                font-size: 1.3rem;
                margin-bottom: 14px;
            }
            .modal-description {
                font-size: 0.8rem;
                line-height: 1.6;
            }
            .modal-contact {
                font-size: 0.75rem;
            }
            .modal-close {
                top: 10px;
                right: 10px;
                width: 30px;
                height: 30px;
                font-size: 0.9rem;
            }
            .empty-state {
                padding: 40px 16px;
                font-size: 0.8rem;
            }
        }

        @media (max-width: 360px) {
            .search-hero-title {
                font-size: 1.3rem;
            }
            .search-field input {
                padding: 8px 10px;
                font-size: 0.75rem;
            }
            .btn-primary-dark,
            .btn-ghost-dark {
                padding: 8px 10px;
                font-size: 0.7rem;
                letter-spacing: 0.04em;
            }
            .car-title {
                font-size: 0.9rem;
            }
            .price {
                font-size: 0.95rem;
            }
            .modal-title {
                font-size: 1.1rem;
            }
            .modal-price-large {
                font-size: 1.2rem;
            }
        }

        @media (pointer: coarse) {
            .car-card {
                cursor: default;
            }
            .btn-primary-dark,
            .btn-ghost-dark,
            .modal-close {
                min-height: 44px;
                min-width: 44px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }
            .nav-links a {
                padding: 6px 0;
                display: inline-block;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .car-card {
                animation: none;
            }
            * {
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">QMSL</a>
            <ul class="nav-links">
                <li><a href="index.php">HOME</a></li>
                <li><a href="search.php">SEARCH</a></li>
                <li><a href="add-car.html">ADD CAR</a></li>
                <li><a href="inventory.php">INVENTORY</a></li>
                <li><a href="login.php">LOGIN</a></li>
            </ul>
        </div>
    </nav>

    <main class="main-content">
        <div class="login-card">
            <?php if ($error): ?>
                <div class="success-msg show" style="background: rgba(255,0,0,0.1); border-color: red;"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <div class="card-header">
                <h1>SELLER LOGIN</h1>
                <p class="subtitle">Sign in to manage your vehicles</p>
            </div>
            <form method="POST" action="login.php" novalidate>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter password" required>
                </div>
                <button type="submit" class="btn-submit">LOG IN</button>
            </form>
            <div class="card-footer">
                Don't have an account? <a href="register.php">Register here</a>
            </div>
        </div>
    </main>
</body>
</html>
    