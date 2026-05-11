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
    $name = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || $address === '' || $phone === '' || $email === '' || $username === '' || $password === '') {
        $error = 'Please complete all fields.';
    } elseif (!preg_match('/^[A-Za-z ]+$/', $name)) {
        $error = 'Name allows letters and spaces only.';
    } elseif (!preg_match('/^[A-Za-z0-9 ]+$/', $address)) {
        $error = 'Address allows letters, numbers, and spaces only.';
    } elseif (!preg_match('/^1[3-9]\d{9}$/', $phone)) {
        $error = 'Invalid phone number format.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (!preg_match('/^[A-Za-z0-9]{6,}$/', $username)) {
        $error = 'Username must be at least 6 alphanumeric characters.';
    } elseif (!preg_match('/^[A-Za-z0-9]{6,}$/', $password)) {
        $error = 'Password must be at least 6 alphanumeric characters.';
    } else {
        $check = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
        $check->execute([$username, $email]);
        $exists = $check->fetch();

        if ($exists) {
            $error = 'Username or email already exists.';
        } else {
            $inserted = false;
            try {
                $stmt = $pdo->prepare(
                    'INSERT INTO users (name, address, phone, email, username, password)
                     VALUES (?, ?, ?, ?, ?, ?)'
                );
                $stmt->execute([$name, $address, $phone, $email, $username, $password]);
                $inserted = true;
            } catch (PDOException $e) {
                $msg = $e->getMessage();
                if (stripos($msg, 'password') !== false && stripos($msg, 'Unknown column') !== false) {
                    try {
                        $hash = password_hash($password, PASSWORD_DEFAULT);
                        $stmt2 = $pdo->prepare(
                            'INSERT INTO users (name, address, phone, email, username, password_hash)
                             VALUES (?, ?, ?, ?, ?, ?)'
                        );
                        $stmt2->execute([$name, $address, $phone, $email, $username, $hash]);
                        $inserted = true;
                    } catch (PDOException $e2) {
                        $error = 'Database error: ' . $e2->getMessage();
                    }
                } else {
                    $error = 'Database error: ' . $msg;
                }
            }

            if ($inserted) {
                header('Location: login.php?registered=1');
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | QMSL</title>
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

        .register-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border-black);
            border-radius: 16px;
            padding: 48px;
            max-width: 560px;
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

        .feedback-msg {
            border: 1px solid var(--border-black);
            border-radius: 8px;
            padding: 14px 18px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 0.85rem;
            font-weight: 500;
            letter-spacing: 0.04em;
        }
        .feedback-msg.error {
            background: rgba(255, 0, 0, 0.08);
            color: #8b0000;
            border-color: rgba(255, 0, 0, 0.25);
        }
        .feedback-msg.success {
            background: rgba(0, 0, 0, 0.03);
            color: var(--black-text);
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
        <div class="register-card">
            <?php if ($error !== ''): ?>
                <div class="feedback-msg error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <div class="card-header">
                <h1>SELLER REGISTRATION</h1>
                <p class="subtitle">Create your account to list vehicles</p>
            </div>

            <form method="POST" action="register.php" novalidate>
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" placeholder="e.g. Zhang Wei" maxlength="80" required>
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" placeholder="e.g. 123 Chaoyang Road Apt 4B" maxlength="150" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" placeholder="e.g. 13812345678" maxlength="11" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="e.g. seller@example.com" maxlength="120" required>
                </div>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="At least 6 alphanumeric characters" maxlength="40" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="At least 6 alphanumeric characters" maxlength="60" required>
                </div>
                <button type="submit" class="btn-submit">CREATE ACCOUNT</button>
            </form>

            <div class="card-footer">
                Already have an account? <a href="login.php">Sign in</a>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>© 2025 QMSL. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
