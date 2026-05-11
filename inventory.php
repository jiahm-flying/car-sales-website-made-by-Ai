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

if (!isset($_SESSION['user'])) {
    echo "<script>
        window.location.href = 'login.php';
    </script>";
    exit;
}

$current_user = $_SESSION['user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    header('Content-Type: application/json');
    
    if (!isset($_POST['car_id'])) {
        echo json_encode(['success' => false, 'message' => 'Missing car ID.']);
        exit;
    }
    
    $car_id = $_POST['car_id'];
    $seller_id = $current_user['id'];
    
    $stmt = $pdo->prepare("DELETE FROM cars WHERE id = ? AND sellerId = ?");
    $stmt->execute([$car_id, $seller_id]);
    $deleted = $stmt->rowCount() > 0;
    
    echo json_encode(['success' => true, 'deleted' => $deleted]);
    exit;
}

$seller_id = $current_user['id'];
$stmt = $pdo->prepare("SELECT * FROM cars WHERE sellerId = ? ORDER BY id DESC");
$stmt->execute([$seller_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cars = [];
foreach ($rows as $row) {
    $cars[] = [
        'id'           => $row['id'],
        'brand'        => $row['brand'] ?? '',
        'model'        => $row['model'] ?? '',
        'year'         => (int)($row['year'] ?? 0),
        'color'        => $row['color'] ?? '',
        'mileage'      => (int)($row['mileage'] ?? 0),
        'price'        => (float)($row['price'] ?? 0),
        'location'     => $row['location'] ?? '',
        'transmission' => $row['transmission'] ?? '',
        'fuelType'     => $row['fuelType'] ?? '',
        'drivetrain'   => $row['drivetrain'] ?? '',
        'description'  => $row['description'] ?? '',
        'phone'        => $row['phone'] ?? $current_user['phone'] ?? '',
        'image'        => $row['image'] ?? '',
        'imageLarge'   => $row['imageLarge'] ?? ''
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Inventory | QMSL</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        /* ==================== RESET & BASE ==================== */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        html {
            scroll-behavior: smooth;
            -webkit-font-smoothing: antialiased;
        }
        body {
            font-family: 'Inter', 'Helvetica Neue', 'Segoe UI', sans-serif;
            font-weight: 400;
            line-height: 1.6;
            background-color: #0a0a0a;
            color: #ffffff;
            min-height: 100vh;
            padding-top: 80px;
            letter-spacing: 0.02em;
            overflow-x: hidden;
        }
        .container { max-width: 1280px; margin: 0 auto; padding: 0 32px; }
        
        /* ── Navbar ── */
        .navbar {
            position: fixed; top: 0; left: 0; width: 100%;
            background: rgba(10,10,10,0.75); backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255,255,255,0.08); z-index: 1000;
            padding: 18px 0; transition: all 0.2s ease;
        }
        .nav-container {
            max-width: 1280px; margin: 0 auto; padding: 0 32px;
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;
        }
        .logo { font-size: 1.6rem; font-weight: 600; letter-spacing: 0.12em; color: #fff; text-decoration: none; transition: opacity 0.2s; }
        .logo:hover { opacity: 0.75; }
        .nav-links { display: flex; gap: 2.2rem; list-style: none; flex-wrap: wrap; align-items: center; }
        .nav-links a {
            color: rgba(255,255,255,0.75); text-decoration: none; font-size: 0.9rem; font-weight: 500;
            letter-spacing: 0.08em; transition: color 0.2s;
        }
        .nav-links a:hover { color: #fff; }
        .user-badge {
            background: rgba(255,255,255,0.12); padding: 6px 18px; border-radius: 20px;
            color: #fff !important; font-weight: 500;
        }

        /* ── Hero Section ── */
        .hero-section {
            background-color: #000; padding: 60px 0 70px;
            border-bottom: 1px solid rgba(255,255,255,0.06); min-height: 280px;
        }
        .hero-top-row {
            display: flex; justify-content: space-between; align-items: flex-start;
            flex-wrap: wrap; gap: 24px; margin-bottom: 12px;
        }
        .hero-title {
            font-size: 2.4rem; font-weight: 300; letter-spacing: 0.10em; color: #fff;
            line-height: 1.15; text-transform: uppercase;
        }
        .hero-subtitle {
            font-size: 0.95rem; font-weight: 400; color: rgba(255,255,255,0.50);
            letter-spacing: 0.06em; margin-top: 6px;
        }
        .hero-actions { display: flex; gap: 12px; flex-wrap: wrap; align-items: center; }
        .btn-primary-dark {
            background: #fff; color: #000; border: none; padding: 14px 28px;
            font-size: 0.85rem; font-weight: 600; letter-spacing: 0.08em; cursor: pointer;
            border-radius: 2px; text-transform: uppercase; transition: opacity 0.2s, transform 0.15s;
            text-decoration: none; display: inline-flex; align-items: center; justify-content: center;
        }
        .btn-primary-dark:hover { opacity: 0.85; transform: translateY(-1px); }
        .btn-ghost-dark {
            background: transparent; color: #fff; border: 1px solid rgba(255,255,255,0.40);
            padding: 13px 24px; font-size: 0.8rem; font-weight: 500; letter-spacing: 0.07em;
            cursor: pointer; border-radius: 2px; text-transform: uppercase;
            transition: border-color 0.2s, color 0.2s, background 0.2s;
        }
        .btn-ghost-dark:hover { border-color: rgba(255,255,255,0.75); background: rgba(255,255,255,0.04); }
        .btn-ghost-dark:disabled { opacity: 0.30; cursor: not-allowed; }
        
        .selection-bar {
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.12);
            border-radius: 2px; padding: 16px 20px; margin-top: 20px;
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;
            gap: 14px; opacity: 0; transform: translateY(-8px); pointer-events: none;
            transition: opacity 0.3s ease, transform 0.3s ease;
        }
        .selection-bar.active { opacity: 1; transform: translateY(0); pointer-events: auto; }
        .selection-info { font-size: 0.85rem; font-weight: 500; letter-spacing: 0.05em; color: rgba(255,255,255,0.75); }
        .selection-info span { font-weight: 600; color: #fff; }
        .selection-actions { display: flex; gap: 10px; flex-wrap: wrap; }

        .toast {
            position: fixed; bottom: 32px; left: 50%; transform: translateX(-50%) translateY(120px);
            background: rgba(20,20,20,0.92); backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,0.15); color: #fff; padding: 16px 28px;
            border-radius: 2px; font-size: 0.85rem; font-weight: 500; letter-spacing: 0.05em;
            z-index: 3000; transition: transform 0.35s cubic-bezier(0.22, 0.61, 0.36, 1);
        }
        .toast.show { transform: translateX(-50%) translateY(0); }

        /* ── Content Section ── */
        .content-section { background-color: #fafafa; padding: 50px 0 70px; }
        .content-header {
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;
            gap: 16px; margin-bottom: 36px;
        }
        .inventory-count { font-size: 1.05rem; font-weight: 500; letter-spacing: 0.05em; color: #111; }
        .inventory-count span { font-weight: 300; color: rgba(0,0,0,0.40); }
        .select-all-link {
            font-size: 0.8rem; font-weight: 500; letter-spacing: 0.07em; color: rgba(0,0,0,0.50);
            cursor: pointer; background: none; border: none; text-transform: uppercase; transition: color 0.2s;
        }
        .select-all-link:hover { color: #000; }

        .car-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 28px; }
        .car-card {
            background: rgba(255,255,255,0.85); backdrop-filter: blur(16px);
            border: 1px solid rgba(0,0,0,0.08); border-radius: 2px; overflow: hidden;
            cursor: pointer; transition: transform 0.2s, box-shadow 0.25s, border-color 0.25s;
            display: flex; flex-direction: column; position: relative;
            animation: fadeInUp 0.5s ease forwards; opacity: 0;
        }
        .car-card:nth-child(1) { animation-delay: 0.05s; }
        .car-card:nth-child(2) { animation-delay: 0.10s; }
        .car-card:nth-child(3) { animation-delay: 0.15s; }
        .car-card:nth-child(4) { animation-delay: 0.20s; }
        .car-card:nth-child(5) { animation-delay: 0.25s; }
        .car-card:nth-child(6) { animation-delay: 0.30s; }
        .car-card:nth-child(7) { animation-delay: 0.35s; }
        .car-card:nth-child(8) { animation-delay: 0.40s; }
        .car-card:nth-child(9) { animation-delay: 0.45s; }
        .car-card:hover { transform: translateY(-4px); box-shadow: 0 16px 40px rgba(0,0,0,0.10); border-color: rgba(0,0,0,0.18); }
        .car-card.selected { border-color: rgba(0,0,0,0.55); background: rgba(255,255,255,0.95); box-shadow: 0 8px 28px rgba(0,0,0,0.12); }
        .select-indicator {
            position: absolute; top: 14px; left: 14px; z-index: 10; width: 28px; height: 28px;
            border-radius: 50%; background: rgba(255,255,255,0.80); backdrop-filter: blur(6px);
            border: 2px solid rgba(0,0,0,0.30); display: flex; align-items: center; justify-content: center;
            transition: all 0.2s ease; cursor: pointer; pointer-events: auto;
        }
        .select-indicator:hover { border-color: rgba(0,0,0,0.60); background: rgba(255,255,255,0.95); transform: scale(1.08); }
        .car-card.selected .select-indicator { background: #000; border-color: #000; }
        .select-indicator::after {
            content: ''; display: block; width: 10px; height: 6px;
            border-left: 2px solid #fff; border-bottom: 2px solid #fff;
            transform: rotate(-45deg) translateY(-1px); opacity: 0; transition: opacity 0.15s;
        }
        .car-card.selected .select-indicator::after { opacity: 1; }
        .card-img { width: 100%; aspect-ratio: 3/2; overflow: hidden; background: #e8e8e8; position: relative; }
        .card-img img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 0.4s ease; }
        .car-card:hover .card-img img { transform: scale(1.03); }
        .card-body { padding: 20px 22px 22px; display: flex; flex-direction: column; gap: 8px; flex: 1; pointer-events: none; color: #111; }
        .car-title { font-size: 1.1rem; font-weight: 600; letter-spacing: 0.04em; color: #111; line-height: 1.2; }
        .car-meta { display: flex; gap: 14px; font-size: 0.8rem; font-weight: 400; color: rgba(0,0,0,0.50); letter-spacing: 0.04em; }
        .car-meta span { position: relative; }
        .car-meta span+span::before { content: '·'; position: absolute; left: -9px; color: rgba(0,0,0,0.25); }
        .car-details { font-size: 0.78rem; color: rgba(0,0,0,0.45); letter-spacing: 0.03em; }
        .price { font-size: 1.2rem; font-weight: 600; letter-spacing: 0.03em; color: #000; margin-top: 2px; }
        .location { font-size: 0.76rem; color: rgba(0,0,0,0.40); letter-spacing: 0.04em; }
        .empty-state {
            text-align: center; padding: 80px 40px; color: rgba(0,0,0,0.30); font-size: 1rem;
            font-weight: 400; letter-spacing: 0.06em;
        }
        .empty-icon { font-size: 3rem; margin-bottom: 16px; opacity: 0.35; }
        .empty-link {
            display: inline-block; margin-top: 18px; color: #000; text-decoration: none;
            font-weight: 600; font-size: 0.85rem; letter-spacing: 0.07em;
            border-bottom: 1px solid rgba(0,0,0,0.25); transition: opacity 0.2s; text-transform: uppercase;
        }
        .empty-link:hover { opacity: 0.6; }

        /* Modal styles (unchanged) */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.82);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            z-index: 2000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }
        .modal-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }

        .modal-panel {
            background: rgba(20, 20, 20, 0.90);
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
            border: 1px solid rgba(255, 255, 255, 0.10);
            border-radius: 2px;
            max-width: 680px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            box-shadow: 0 30px 70px rgba(0, 0, 0, 0.60);
        }
        .modal-panel::-webkit-scrollbar {
            width: 6px;
        }
        .modal-panel::-webkit-scrollbar-track {
            background: transparent;
        }
        .modal-panel::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 3px;
        }
        .modal-panel::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.25);
        }

        .modal-close {
            position: absolute;
            top: 18px;
            right: 18px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #ffffff;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s, border-color 0.2s;
            z-index: 10;
            line-height: 1;
        }
        .modal-close:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.30);
        }

        .modal-img {
            width: 100%;
            aspect-ratio: 16 / 10;
            overflow: hidden;
            background: #1a1a1a;
            position: relative;
        }
        .modal-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .modal-body {
            padding: 30px 34px 34px;
            color: #ffffff;
        }
        .modal-title {
            font-size: 1.5rem;
            font-weight: 500;
            letter-spacing: 0.06em;
            color: #ffffff;
            margin-bottom: 4px;
            line-height: 1.2;
        }
        .modal-subtitle {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.50);
            letter-spacing: 0.05em;
            margin-bottom: 18px;
        }
        .modal-price-large {
            font-size: 1.8rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            color: #ffffff;
            margin-bottom: 18px;
        }
        .modal-specs {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px 24px;
            margin-bottom: 22px;
            padding: 18px 0;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .modal-spec-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .modal-spec-label {
            font-size: 0.68rem;
            font-weight: 500;
            letter-spacing: 0.08em;
            color: rgba(255, 255, 255, 0.40);
            text-transform: uppercase;
        }
        .modal-spec-value {
            font-size: 0.9rem;
            font-weight: 500;
            letter-spacing: 0.03em;
            color: #ffffff;
        }
        .modal-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 6px;
        }
        .btn-modal-primary {
            background: #ffffff;
            color: #000000;
            border: none;
            padding: 12px 22px;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.07em;
            cursor: pointer;
            border-radius: 2px;
            font-family: inherit;
            text-transform: uppercase;
            transition: opacity 0.2s;
        }
        .btn-modal-primary:hover {
            opacity: 0.85;
        }
        .btn-modal-ghost {
            background: transparent;
            color: rgba(255, 255, 255, 0.70);
            border: 1px solid rgba(255, 255, 255, 0.25);
            padding: 11px 20px;
            font-size: 0.78rem;
            font-weight: 500;
            letter-spacing: 0.06em;
            cursor: pointer;
            border-radius: 2px;
            font-family: inherit;
            text-transform: uppercase;
            transition: border-color 0.2s, color 0.2s, background 0.2s;
        }
        .btn-modal-ghost:hover {
            border-color: rgba(255, 255, 255, 0.55);
            color: #ffffff;
            background: rgba(255, 255, 255, 0.04);
        }

        /* Footer, animations, responsive (keep all) */
        .footer {
            background-color: #0a0a0a; padding: 48px 0; text-align: center;
            border-top: 1px solid rgba(255,255,255,0.06);
        }
        .footer p { color: rgba(255,255,255,0.5); font-size: 0.8rem; letter-spacing: 0.1em; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @media (max-width: 1024px) {
            .car-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 22px;
            }
            .hero-section {
                padding: 48px 0 56px;
                min-height: auto;
            }
            .hero-title {
                font-size: 2rem;
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
            .hero-section {
                padding: 40px 0 48px;
            }
            .hero-title {
                font-size: 1.7rem;
            }
            .hero-top-row {
                flex-direction: column;
                gap: 16px;
            }
            .hero-actions {
                width: 100%;
            }
            .hero-actions .btn-primary-dark,
            .hero-actions .btn-ghost-dark {
                flex: 1;
                text-align: center;
            }
            .selection-bar {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            .selection-actions {
                width: 100%;
            }
            .selection-actions button {
                flex: 1;
                text-align: center;
            }
            .car-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 18px;
            }
            .container {
                padding: 0 20px;
            }
            .content-section {
                padding: 40px 0 50px;
            }
            .content-header {
                margin-bottom: 24px;
            }
            .empty-state {
                padding: 60px 20px;
                font-size: 0.9rem;
            }
            .modal-panel {
                max-width: 100%;
                margin: 0 4px;
            }
            .modal-body {
                padding: 20px 18px 24px;
            }
            .modal-title {
                font-size: 1.3rem;
            }
            .modal-specs {
                grid-template-columns: 1fr;
                gap: 8px;
            }
            .modal-price-large {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 600px) {
            .car-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }
            .hero-title {
                font-size: 1.5rem;
                letter-spacing: 0.07em;
            }
            .hero-subtitle {
                font-size: 0.8rem;
            }
            .select-indicator {
                width: 32px;
                height: 32px;
                top: 12px;
                left: 12px;
            }
            .select-indicator::after {
                width: 11px;
                height: 7px;
            }
            .card-body {
                padding: 14px 16px 16px;
                gap: 5px;
            }
            .car-title {
                font-size: 1rem;
            }
            .price {
                font-size: 1.1rem;
            }
            .modal-close {
                top: 10px;
                right: 10px;
                width: 32px;
                height: 32px;
                font-size: 0.95rem;
            }
            .modal-body {
                padding: 16px 14px 20px;
            }
            .modal-title {
                font-size: 1.2rem;
            }
            .modal-price-large {
                font-size: 1.3rem;
            }
            .modal-actions {
                flex-direction: column;
            }
            .modal-actions button {
                width: 100%;
                text-align: center;
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
            }
            .nav-links a {
                font-size: 0.7rem;
                letter-spacing: 0.05em;
            }
            .hero-section {
                padding: 32px 0 38px;
            }
            .hero-title {
                font-size: 1.4rem;
            }
            .hero-subtitle {
                font-size: 0.75rem;
            }
            .btn-primary-dark,
            .btn-ghost-dark {
                padding: 11px 16px;
                font-size: 0.72rem;
                letter-spacing: 0.05em;
            }
            .selection-bar {
                padding: 12px 14px;
                gap: 10px;
            }
            .selection-info {
                font-size: 0.75rem;
            }
            .content-section {
                padding: 30px 0 40px;
            }
            .inventory-count {
                font-size: 0.9rem;
            }
            .car-grid {
                grid-template-columns: 1fr;
                gap: 14px;
            }
            .card-body {
                padding: 12px 14px 14px;
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
                padding: 8px;
            }
            .modal-panel {
                max-height: 85vh;
            }
            .modal-img {
                aspect-ratio: 4/3;
            }
            .modal-specs {
                grid-template-columns: 1fr;
                gap: 6px;
                padding: 12px 0;
                margin-bottom: 16px;
            }
            .modal-spec-label {
                font-size: 0.6rem;
            }
            .modal-spec-value {
                font-size: 0.8rem;
            }
            .empty-state {
                padding: 40px 16px;
                font-size: 0.8rem;
            }
            .empty-state .empty-icon {
                font-size: 2.4rem;
                margin-bottom: 12px;
            }
        }

        @media (max-width: 360px) {
            .hero-title {
                font-size: 1.2rem;
            }
            .btn-primary-dark,
            .btn-ghost-dark {
                padding: 9px 12px;
                font-size: 0.68rem;
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
            .select-indicator {
                min-width: 32px;
                min-height: 32px;
            }
            .btn-primary-dark,
            .btn-ghost-dark,
            .btn-modal-primary,
            .btn-modal-ghost,
            .modal-close {
                min-height: 44px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }
            .nav-links a {
                padding: 6px 0;
                display: inline-block;
            }
            .select-all-link {
                padding: 8px 0;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .car-card {
                animation: none;
                opacity: 1;
            }
            * {
                transition-duration: 0.01ms !important;
            }
        }
    </style>
</head>
<body>

    <!-- ==================== NAVIGATION BAR ==================== -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">QMSL</a>
            <ul class="nav-links">
                <li><a href="index.php">HOME</a></li>
                <li><a href="search.php">SEARCH</a></li>
                <li><a href="add-car.php">ADD CAR</a></li>
                <li><a href="inventory.php">INVENTORY</a></li>
                <?php if (isset($_SESSION['user'])): ?>
                    <li><a href="logout.php" class="user-badge" title="Click to logout"><?= htmlspecialchars($_SESSION['user']['name']) ?></a></li>
                <?php else: ?>
                    <li><a href="login.php">LOGIN</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- ==================== HERO SECTION ==================== -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-top-row">
                <div>
                    <h1 class="hero-title">My Inventory</h1>
                    <p class="hero-subtitle">Manage your listed vehicles with precision</p>
                </div>
                <div class="hero-actions">
                    <a href="add-car.php" class="btn-primary-dark">+ Add Vehicle</a>
                    <button class="btn-ghost-dark" id="btnRefresh" type="button">Refresh</button>
                </div>
            </div>
            <div class="selection-bar" id="selectionBar">
                <div class="selection-info">
                    <span id="selectedCount">0</span> vehicle<span id="selectedPlural">s</span> selected
                </div>
                <div class="selection-actions">
                    <button class="btn-primary-dark" id="btnMarkSold" type="button">Mark as Sold</button>
                    <button class="btn-ghost-dark" id="btnDeselectAll" type="button">Deselect All</button>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== CONTENT SECTION ==================== -->
    <section class="content-section">
        <div class="container">
            <div class="content-header">
                <p class="inventory-count" id="inventoryCount">You have <span>0 cars</span> listed</p>
                <button class="select-all-link" id="btnSelectAll" type="button">Select All</button>
            </div>
            <div class="car-grid" id="carGrid"></div>
            <div class="empty-state" id="emptyState" style="display:none;">
                <div class="empty-icon">🏗️</div>
                <p>No cars listed yet.</p>
                <p style="font-size:0.8rem;margin-top:4px;">Start building your inventory with a new listing.</p>
                <a href="add-car.php" class="empty-link">Add Your First Vehicle</a>
            </div>
        </div>
    </section>

    <!-- Modal, Toast, Footer identical to original (keep as is) -->
    <div class="modal-overlay" id="modalOverlay">
        <div class="modal-panel" id="modalPanel">
            <button class="modal-close" id="modalClose" aria-label="Close detail view">&times;</button>
            <div class="modal-img"><img src="" alt="" id="modalImageEl"></div>
            <div class="modal-body" id="modalBody"></div>
        </div>
    </div>
    <div class="toast" id="toast"></div>
    <footer class="footer">
        <div class="container">
            <p>© 2025 QMSL. All rights reserved.</p>
        </div>
    </footer>

    <!-- ==================== SCRIPTS ==================== -->
    <script>
        var currentUser = <?= json_encode($current_user, JSON_HEX_TAG | JSON_HEX_AMP) ?>;
        var inventoryData = <?= json_encode($cars, JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    </script>

    <script>
        (function() {
            'use strict';

            const carGrid = document.getElementById('carGrid');
            const inventoryCount = document.getElementById('inventoryCount');
            const emptyState = document.getElementById('emptyState');
            const selectionBar = document.getElementById('selectionBar');
            const selectedCountEl = document.getElementById('selectedCount');
            const selectedPluralEl = document.getElementById('selectedPlural');
            const btnSelectAll = document.getElementById('btnSelectAll');
            const btnDeselectAll = document.getElementById('btnDeselectAll');
            const btnMarkSold = document.getElementById('btnMarkSold');
            const btnRefresh = document.getElementById('btnRefresh');
            const modalOverlay = document.getElementById('modalOverlay');
            const modalPanel = document.getElementById('modalPanel');
            const modalClose = document.getElementById('modalClose');
            const modalImageEl = document.getElementById('modalImageEl');
            const modalBody = document.getElementById('modalBody');
            const toast = document.getElementById('toast');

            let allCars = [];
            let selectedCarIds = new Set();
            let toastTimer = null;

            function formatPrice(price) {
                return '$' + Number(price).toLocaleString('en-US');
            }
            function formatMileage(mileage) {
                return Number(mileage).toLocaleString('en-US') + ' mi';
            }

            function showToast(message) {
                if (toastTimer) clearTimeout(toastTimer);
                toast.textContent = message;
                toast.classList.add('show');
                toastTimer = setTimeout(() => {
                    toast.classList.remove('show');
                    toastTimer = null;
                }, 2800);
            }

            function loadInventory() {
                allCars = inventoryData.slice(); 
                selectedCarIds.clear();
                updateSelectionUI();
                renderCars(allCars);
                updateInventoryCount();
            }

            function updateInventoryCount() {
                const count = allCars.length;
                inventoryCount.innerHTML = `You have <span>${count} car${count !== 1 ? 's' : ''}</span> listed`;
                btnSelectAll.style.display = count === 0 ? 'none' : 'inline-block';
            }

            function updateSelectionUI() {
                const count = selectedCarIds.size;
                selectedCountEl.textContent = count;
                selectedPluralEl.textContent = count !== 1 ? 's' : '';
                if (count > 0) {
                    selectionBar.classList.add('active');
                    btnSelectAll.textContent = 'Deselect All';
                } else {
                    selectionBar.classList.remove('active');
                    btnSelectAll.textContent = 'Select All';
                }
                document.querySelectorAll('.car-card').forEach(card => {
                    const carId = card.getAttribute('data-car-id');
                    card.classList.toggle('selected', selectedCarIds.has(carId));
                });
            }

            function renderCars(cars) {
                carGrid.innerHTML = '';
                if (cars.length === 0) {
                    emptyState.style.display = 'block';
                    carGrid.style.display = 'none';
                    return;
                }
                emptyState.style.display = 'none';
                carGrid.style.display = 'grid';

                cars.forEach(car => {
                    const card = document.createElement('div');
                    card.className = 'car-card';
                    card.setAttribute('data-car-id', car.id);
                    if (selectedCarIds.has(car.id)) card.classList.add('selected');

                    const imageUrl = car.image || `https://placehold.co/600x400/eeeeee/000000?text=${encodeURIComponent(car.brand + '+' + car.model)}`;

                    card.innerHTML = `
                        <div class="select-indicator" data-action="select" data-car-id="${car.id}" title="Select this vehicle"></div>
                        <div class="card-img"><img src="${imageUrl}" alt="${car.brand} ${car.model}" loading="lazy"></div>
                        <div class="card-body">
                            <div class="car-title">${car.brand} ${car.model}</div>
                            <div class="car-meta"><span>${car.year}</span><span>${car.color}</span></div>
                            <div class="car-details"><span>📄 ${formatMileage(car.mileage)}</span></div>
                            <div class="price">${formatPrice(car.price)}</div>
                            <div class="location">📍 ${car.location}</div>
                        </div>
                    `;

                    const indicator = card.querySelector('.select-indicator');
                    indicator.addEventListener('click', (e) => {
                        e.stopPropagation();
                        toggleSelect(car.id);
                    });

                    card.addEventListener('click', (e) => {
                        if (e.target.closest('[data-action="select"]')) return;
                        openDetailModal(car);
                    });

                    carGrid.appendChild(card);
                });
                updateSelectionUI();
            }

            function toggleSelect(carId) {
                selectedCarIds.has(carId) ? selectedCarIds.delete(carId) : selectedCarIds.add(carId);
                updateSelectionUI();
            }

            function toggleSelectAll() {
                if (selectedCarIds.size === allCars.length && allCars.length > 0) {
                    selectedCarIds.clear();
                } else {
                    allCars.forEach(car => selectedCarIds.add(car.id));
                }
                updateSelectionUI();
            }

            async function deleteCarById(carId) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('car_id', carId);
                try {
                    const response = await fetch(window.location.href, { method: 'POST', body: formData });
                    const data = await response.json();
                    return data.deleted === true;
                } catch (err) {
                    console.error('Delete failed:', err);
                    return false;
                }
            }

            async function markSelectedAsSold() {
                if (selectedCarIds.size === 0) return;
                const count = selectedCarIds.size;
                if (!confirm(`Mark ${count} vehicle${count!==1?'s':''} as sold? This will remove them from your inventory.`)) return;

                let deleted = 0;
                for (let carId of selectedCarIds) {
                    if (await deleteCarById(carId)) deleted++;
                }
                selectedCarIds.clear();
                location.reload();  // 刷新以从数据库重新加载
            }

            function openDetailModal(car) {
                const largeImage = car.imageLarge || car.image || `https://placehold.co/800x500/1a1a1a/ffffff?text=${encodeURIComponent(car.brand + '+' + car.model)}`;
                modalImageEl.src = largeImage;
                modalImageEl.alt = `${car.brand} ${car.model}`;

                modalBody.innerHTML = `
                    <h2 class="modal-title">${car.brand} ${car.model}</h2>
                    <p class="modal-subtitle">${car.year} · ${car.color} · ${car.location}</p>
                    <div class="modal-price-large">${formatPrice(car.price)}</div>
                    <div class="modal-specs">
                        <div class="modal-spec-item"><span class="modal-spec-label">Year</span><span class="modal-spec-value">${car.year}</span></div>
                        <div class="modal-spec-item"><span class="modal-spec-label">Mileage</span><span class="modal-spec-value">${formatMileage(car.mileage)}</span></div>
                        <div class="modal-spec-item"><span class="modal-spec-label">Color</span><span class="modal-spec-value">${car.color}</span></div>
                        <div class="modal-spec-item"><span class="modal-spec-label">Location</span><span class="modal-spec-value">${car.location}</span></div>
                        <div class="modal-spec-item"><span class="modal-spec-label">Transmission</span><span class="modal-spec-value">${car.transmission || 'N/A'}</span></div>
                        <div class="modal-spec-item"><span class="modal-spec-label">Fuel Type</span><span class="modal-spec-value">${car.fuelType || 'N/A'}</span></div>
                        <div class="modal-spec-item"><span class="modal-spec-label">Drivetrain</span><span class="modal-spec-value">${car.drivetrain || 'N/A'}</span></div>
                    </div>
                    ${car.description ? `<p style="font-size:0.88rem;line-height:1.7;color:rgba(255,255,255,0.55);margin-bottom:18px;">${car.description}</p>` : ''}
                    <p style="font-size:0.8rem;color:rgba(255,255,255,0.40);margin-bottom:16px;">Seller contact: <strong style="color:rgba(255,255,255,0.70);">${car.phone || currentUser.phone || 'N/A'}</strong></p>
                    <div class="modal-actions">
                        <button class="btn-modal-primary" id="btnModalMarkSold">Mark as Sold</button>
                        <button class="btn-modal-ghost" id="btnModalDelete">Delete Listing</button>
                    </div>
                `;

                document.getElementById('btnModalMarkSold').addEventListener('click', async () => {
                    if (confirm('Mark this vehicle as sold? It will be removed from your inventory.')) {
                        await deleteCarById(car.id);
                        selectedCarIds.delete(car.id);
                        closeDetailModal();
                        loadInventory();
                        showToast('Vehicle marked as sold.');
                    }
                });

                document.getElementById('btnModalDelete').addEventListener('click', async () => {
                    if (confirm('Delete this listing? This action cannot be undone.')) {
                        await deleteCarById(car.id);
                        selectedCarIds.delete(car.id);
                        closeDetailModal();
                        loadInventory();
                        showToast('Listing deleted.');
                    }
                });

                modalOverlay.classList.add('active');
                document.body.style.overflow = 'hidden';
                modalPanel.scrollTop = 0;
            }

            function closeDetailModal() {
                modalOverlay.classList.remove('active');
                document.body.style.overflow = '';
            }

            // 事件监听
            btnSelectAll.addEventListener('click', toggleSelectAll);
            btnDeselectAll.addEventListener('click', () => {
                selectedCarIds.clear();
                updateSelectionUI();
            });
            btnMarkSold.addEventListener('click', markSelectedAsSold);
            btnRefresh.addEventListener('click', () => {
                location.reload(); // 刷新页面，重新从数据库加载数据
            });

            modalClose.addEventListener('click', closeDetailModal);
            modalOverlay.addEventListener('click', (e) => {
                if (e.target === modalOverlay) closeDetailModal();
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && modalOverlay.classList.contains('active')) closeDetailModal();
            });

            // 启动
            loadInventory();
            console.log(`Inventory loaded — ${allCars.length} vehicles for ${currentUser.name}`);
        })();
    </script>
</body>
</html>