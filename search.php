<?php
$keyword   = isset($_GET['searchKeyword'])  ? trim($_GET['searchKeyword']) : '';
$minYear   = isset($_GET['searchMinYear']) && $_GET['searchMinYear'] !== '' ? (int)$_GET['searchMinYear'] : null;
$maxYear   = isset($_GET['searchMaxYear']) && $_GET['searchMaxYear'] !== '' ? (int)$_GET['searchMaxYear'] : null;
$minPrice  = isset($_GET['searchMinPrice']) && $_GET['searchMinPrice'] !== '' ? (int)$_GET['searchMinPrice'] : null;
$maxPrice  = isset($_GET['searchMaxPrice']) && $_GET['searchMaxPrice'] !== '' ? (int)$_GET['searchMaxPrice'] : null;

$connection = mysqli_connect("localhost","root","","car_sales");
$connection->set_charset("utf8mb4");

$where  = [];
$params = [];
$types  = '';

if ($keyword !== '') {
    $where[] = '(model LIKE ? OR brand LIKE ?)';
    $params[] = '%' . $keyword . '%';
    $params[] = '%' . $keyword . '%';
    $types .= 'ss';
}

if ($minYear !== null) {
    $where[]  = 'year >= ?';
    $params[] = $minYear;
    $types   .= 'i';
}

if ($maxYear !== null) {
    $where[]  = 'year <= ?';
    $params[] = $maxYear;
    $types   .= 'i';
}

if ($minPrice !== null) {
    $where[]  = 'price >= ?';
    $params[] = $minPrice;  
    $types   .= 'i';       
}

if ($maxPrice !== null) {
    $where[]  = 'price <= ?';
    $params[] = $maxPrice;
    $types   .= 'i';
}

$sql = "SELECT * FROM cars";
if (count($where) > 0) {
    $sql .= " WHERE " . implode(' AND ', $where);
}

$sql .= " ORDER BY year ASC, price ASC";   

$stmt = $connection->prepare($sql);
if (!$stmt) {
    die("SQL fall: " . $connection->error);
}

if (!empty($params)) {
    $refs = [];
    foreach ($params as $key => $value) {
        $refs[$key] = &$params[$key];
    }
    $stmt->bind_param($types, ...$refs);
}

$stmt->execute();
$result = $stmt->get_result();  

$cars = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cars[] = $row;
    }
}

$stmt->close();
$connection->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Search | QMSL</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        /* ==================== RESET & BASE ==================== */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            -webkit-text-size-adjust: 100%;
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

        /* ==================== CONTAINER ==================== */
        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 32px;
        }

        /* ==================== NAVBAR (FIXED) ==================== */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: rgba(10, 10, 10, 0.75);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
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
        .logo:hover {
            opacity: 0.75;
        }
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
        .nav-links a:hover {
            color: #ffffff;
        }

        /* ==================== SEARCH HERO — BLACK ZONE ==================== */
        .search-hero {
            background-color: #000000;
            padding: 60px 0 70px;
            min-height: 340px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        .search-hero .container {
            width: 100%;
        }

        .search-hero-title {
            font-size: 2.6rem;
            font-weight: 300;
            letter-spacing: 0.08em;
            color: #ffffff;
            margin-bottom: 8px;
            line-height: 1.15;
        }

        .search-hero-subtitle {
            font-size: 1rem;
            font-weight: 400;
            color: rgba(255, 255, 255, 0.50);
            letter-spacing: 0.06em;
            margin-bottom: 36px;
        }

        /* Glass search panel */
        .search-panel {
            background: rgba(10, 10, 10, 0.70);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.10);
            border-radius: 2px;
            padding: 32px 36px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .search-row {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .search-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
            flex: 1;
            min-width: 160px;
        }

        .search-field label {
            font-size: 0.75rem;
            font-weight: 500;
            letter-spacing: 0.08em;
            color: rgba(255, 255, 255, 0.50);
            text-transform: uppercase;
        }

        .search-field input {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #ffffff;
            padding: 13px 16px;
            font-size: 0.95rem;
            font-family: 'Inter', 'Helvetica Neue', 'Segoe UI', sans-serif;
            font-weight: 400;
            letter-spacing: 0.03em;
            border-radius: 2px;
            outline: none;
            transition: border-color 0.2s, background 0.2s;
            width: 100%;
        }

        .search-field input::placeholder {
            color: rgba(255, 255, 255, 0.30);
        }

        .search-field input:focus {
            border-color: rgba(255, 255, 255, 0.35);
            background: rgba(255, 255, 255, 0.09);
            outline: none;
        }

        /* Buttons in dark zone */
        .btn-primary-dark {
            background: #ffffff;
            color: #000000;
            border: none;
            padding: 14px 32px;
            font-size: 0.9rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            cursor: pointer;
            border-radius: 2px;
            font-family: 'Inter', 'Helvetica Neue', 'Segoe UI', sans-serif;
            transition: opacity 0.2s, transform 0.15s;
            white-space: nowrap;
            text-transform: uppercase;
        }

        .btn-primary-dark:hover {
            opacity: 0.85;
            transform: translateY(-1px);
        }
        .btn-primary-dark:active {
            transform: translateY(0);
            opacity: 0.70;
        }

        .btn-ghost-dark {
            background: transparent;
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.40);
            padding: 13px 28px;
            font-size: 0.85rem;
            font-weight: 500;
            letter-spacing: 0.07em;
            cursor: pointer;
            border-radius: 2px;
            font-family: 'Inter', 'Helvetica Neue', 'Segoe UI', sans-serif;
            transition: border-color 0.2s, color 0.2s, background 0.2s;
            white-space: nowrap;
            text-transform: uppercase;
        }

        .btn-ghost-dark:hover {
            border-color: rgba(255, 255, 255, 0.75);
            background: rgba(255, 255, 255, 0.04);
            color: #ffffff;
        }

        .search-actions {
            display: flex;
            gap: 12px;
            align-items: flex-end;
            flex-shrink: 0;
        }

        /* Form validation error styles */
        .highlight-error {
            border-color: #ff4d4d !important;
            background: rgba(255, 77, 77, 0.08) !important;
        }
        .form-error-box {
            background: rgba(255, 77, 77, 0.12);
            border: 1px solid rgba(255, 77, 77, 0.4);
            color: #ff9999;
            padding: 12px 16px;
            border-radius: 2px;
            font-size: 0.85rem;
            letter-spacing: 0.03em;
            display: none;
            margin-bottom: 8px;
        }

        /* ==================== RESULTS SECTION — WHITE ZONE ==================== */
        .results-section {
            background-color: #fafafa;
            padding: 50px 0 70px;
            min-height: 400px;
            border-top: 1px solid rgba(0, 0, 0, 0.04);
        }

        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
            margin-bottom: 36px;
        }

        .results-count {
            font-size: 1.1rem;
            font-weight: 500;
            letter-spacing: 0.05em;
            color: #111111;
        }

        .results-count span {
            font-weight: 300;
            color: rgba(0, 0, 0, 0.40);
        }

        /* Car card grid */
        .car-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 28px;
        }

        /* Car card — light zone glass */
        .car-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 2px;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.25s, border-color 0.2s;
            display: flex;
            flex-direction: column;
        }

        .car-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.10);
            border-color: rgba(0, 0, 0, 0.18);
        }

        .car-card:active {
            transform: translateY(-1px);
        }

        .card-img {
            width: 100%;
            aspect-ratio: 3 / 2;
            overflow: hidden;
            background: #e8e8e8;
            position: relative;
        }

        .card-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.4s ease;
        }

        .car-card:hover .card-img img {
            transform: scale(1.03);
        }

        .card-body {
            padding: 20px 22px 22px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex: 1;
        }

        .car-title {
            font-size: 1.15rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            color: #111111;
            line-height: 1.2;
        }

        .car-meta {
            display: flex;
            gap: 14px;
            font-size: 0.82rem;
            font-weight: 400;
            color: rgba(0, 0, 0, 0.50);
            letter-spacing: 0.04em;
        }

        .car-meta span {
            position: relative;
        }

        .car-meta span+span::before {
            content: '·';
            position: absolute;
            left: -9px;
            color: rgba(0, 0, 0, 0.25);
        }

        .car-details {
            font-size: 0.8rem;
            color: rgba(0, 0, 0, 0.45);
            letter-spacing: 0.03em;
        }

        .price {
            font-size: 1.25rem;
            font-weight: 600;
            letter-spacing: 0.03em;
            color: #000000;
            margin-top: 4px;
        }

        .location {
            font-size: 0.78rem;
            color: rgba(0, 0, 0, 0.40);
            letter-spacing: 0.04em;
        }

        /* Empty state — white zone */
        .empty-state {
            text-align: center;
            padding: 80px 40px;
            color: rgba(0, 0, 0, 0.30);
            font-size: 1rem;
            font-weight: 400;
            letter-spacing: 0.06em;
            grid-column: 1 / -1;
        }

        /* ==================== DETAIL MODAL ==================== */
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
            background: rgba(20, 20, 20, 0.88);
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
            border: 1px solid rgba(255, 255, 255, 0.10);
            border-radius: 2px;
            max-width: 720px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            box-shadow: 0 30px 70px rgba(0, 0, 0, 0.60);
        }

        .modal-close {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #ffffff;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.2rem;
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
            padding: 32px 36px 36px;
            color: #ffffff;
        }

        .modal-title {
            font-size: 1.6rem;
            font-weight: 500;
            letter-spacing: 0.06em;
            color: #ffffff;
            margin-bottom: 6px;
            line-height: 1.2;
        }

        .modal-subtitle {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.50);
            letter-spacing: 0.05em;
            margin-bottom: 20px;
        }

        .modal-specs {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 14px 28px;
            margin-bottom: 24px;
            padding: 20px 0;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .modal-spec-item {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .modal-spec-label {
            font-size: 0.7rem;
            font-weight: 500;
            letter-spacing: 0.08em;
            color: rgba(255, 255, 255, 0.40);
            text-transform: uppercase;
        }

        .modal-spec-value {
            font-size: 0.95rem;
            font-weight: 500;
            letter-spacing: 0.03em;
            color: #ffffff;
        }

        .modal-price-large {
            font-size: 2rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            color: #ffffff;
            margin-bottom: 20px;
        }

        .modal-description {
            font-size: 0.9rem;
            line-height: 1.7;
            color: rgba(255, 255, 255, 0.60);
            letter-spacing: 0.03em;
            margin-bottom: 20px;
        }

        .modal-contact {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.45);
            letter-spacing: 0.04em;
        }

        .modal-contact strong {
            color: rgba(255, 255, 255, 0.75);
            font-weight: 500;
        }

        /* ==================== FOOTER ==================== */
        .footer {
            background-color: #0a0a0a;
            padding: 48px 0;
            text-align: center;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
        }
        .footer p {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.8rem;
            letter-spacing: 0.1em;
        }

        /* ==================== SCROLLBAR ==================== */
        .modal-panel::-webkit-scrollbar { width: 6px; }
        .modal-panel::-webkit-scrollbar-track { background: transparent; }
        .modal-panel::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 3px;
        }
        .modal-panel::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.25);
        }

        /* ==================== ANIMATIONS ==================== */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .car-card { animation: fadeInUp 0.5s ease forwards; }
        .car-card:nth-child(1) { animation-delay: 0.05s; }
        .car-card:nth-child(2) { animation-delay: 0.10s; }
        .car-card:nth-child(3) { animation-delay: 0.15s; }
        .car-card:nth-child(4) { animation-delay: 0.20s; }
        .car-card:nth-child(5) { animation-delay: 0.25s; }
        .car-card:nth-child(6) { animation-delay: 0.30s; }
        .car-card:nth-child(7) { animation-delay: 0.35s; }
        .car-card:nth-child(8) { animation-delay: 0.40s; }
        .car-card:nth-child(9) { animation-delay: 0.45s; }

        /* ==================== RESPONSIVE ==================== */
        @media (max-width: 1024px) {
            .car-grid { grid-template-columns: repeat(2, 1fr); gap: 22px; }
            .search-hero { padding: 48px 0 56px; min-height: auto; }
            .search-hero-title { font-size: 2.2rem; }
            .search-panel { padding: 24px 24px; gap: 16px; }
        }
        @media (max-width: 768px) {
            body { padding-top: 70px; }
            .navbar { padding: 14px 0; }
            .nav-container { padding: 0 20px; flex-direction: column; align-items: flex-start; gap: 12px; }
            .logo { font-size: 1.5rem; }
            .nav-links { gap: 1.5rem; }
            .nav-links a { font-size: 0.8rem; }
            .search-hero { padding: 40px 0 50px; min-height: auto; }
            .search-hero-title { font-size: 1.8rem; }
            .search-hero-subtitle { font-size: 0.9rem; margin-bottom: 24px; }
            .search-panel { padding: 20px 18px; gap: 14px; }
            .search-row { flex-direction: column; gap: 12px; }
            .search-field { min-width: 100%; }
            .search-actions { flex-direction: row; width: 100%; flex-wrap: wrap; }
            .search-actions button { flex: 1; white-space: normal; text-align: center; }
            .car-grid { grid-template-columns: repeat(2, 1fr); gap: 18px; }
            .container { padding: 0 20px; }
            .results-section { padding: 40px 0 50px; }
            .results-header { margin-bottom: 24px; }
            .empty-state { padding: 60px 20px; font-size: 0.9rem; }
        }
        @media (max-width: 600px) {
            .car-grid { grid-template-columns: 1fr; gap: 16px; }
            .search-hero-title { font-size: 1.6rem; }
            .search-panel { padding: 16px 14px; gap: 12px; }
            .search-field input { padding: 12px 14px; font-size: 0.9rem; }
            .btn-primary-dark, .btn-ghost-dark { padding: 12px 16px; font-size: 0.8rem; letter-spacing: 0.06em; }
            .card-body { padding: 16px 18px 18px; gap: 6px; }
            .car-title { font-size: 1rem; }
            .price { font-size: 1.1rem; }
            .modal-panel { max-width: 100%; margin: 0 8px; }
            .modal-body { padding: 20px 18px 24px; }
            .modal-title { font-size: 1.3rem; }
            .modal-specs { grid-template-columns: 1fr; gap: 10px; }
            .modal-price-large { font-size: 1.5rem; }
            .modal-close { top: 12px; right: 12px; width: 34px; height: 34px; font-size: 1rem; }
        }
        @media (max-width: 480px) {
            body { padding-top: 64px; }
            .container { padding: 0 14px; }
            .navbar { padding: 10px 0; }
            .nav-container { padding: 0 14px; gap: 8px; }
            .logo { font-size: 1.3rem; letter-spacing: 0.08em; }
            .nav-links { gap: 1rem; flex-wrap: wrap; }
            .nav-links a { font-size: 0.7rem; letter-spacing: 0.05em; }
            .search-hero { padding: 32px 0 40px; }
            .search-hero-title { font-size: 1.5rem; letter-spacing: 0.05em; }
            .search-hero-subtitle { font-size: 0.8rem; margin-bottom: 20px; }
            .search-panel { padding: 14px 12px; gap: 10px; }
            .search-field label { font-size: 0.65rem; }
            .search-field input { padding: 10px 12px; font-size: 0.8rem; }
            .search-actions { gap: 8px; }
            .btn-primary-dark, .btn-ghost-dark { padding: 10px 14px; font-size: 0.75rem; }
            .results-section { padding: 30px 0 40px; }
            .results-count { font-size: 0.95rem; }
            .car-grid { grid-template-columns: 1fr; gap: 14px; }
            .card-body { padding: 14px 14px 16px; gap: 4px; }
            .car-title { font-size: 0.95rem; }
            .car-meta { font-size: 0.7rem; gap: 10px; }
            .car-meta span+span::before { left: -7px; }
            .car-details { font-size: 0.7rem; }
            .price { font-size: 1rem; }
            .location { font-size: 0.7rem; }
            .modal-overlay { padding: 12px; }
            .modal-panel { max-height: 85vh; }
            .modal-img { aspect-ratio: 4/3; }
            .modal-body { padding: 16px 14px 20px; }
            .modal-title { font-size: 1.2rem; }
            .modal-subtitle { font-size: 0.75rem; margin-bottom: 14px; }
            .modal-specs { grid-template-columns: 1fr; gap: 8px; padding: 14px 0; margin-bottom: 18px; }
            .modal-spec-label { font-size: 0.6rem; }
            .modal-spec-value { font-size: 0.85rem; }
            .modal-price-large { font-size: 1.3rem; margin-bottom: 14px; }
            .modal-description { font-size: 0.8rem; line-height: 1.6; }
            .modal-contact { font-size: 0.75rem; }
            .modal-close { top: 10px; right: 10px; width: 30px; height: 30px; font-size: 0.9rem; }
            .empty-state { padding: 40px 16px; font-size: 0.8rem; }
        }
        @media (max-width: 360px) {
            .search-hero-title { font-size: 1.3rem; }
            .search-field input { padding: 8px 10px; font-size: 0.75rem; }
            .btn-primary-dark, .btn-ghost-dark { padding: 8px 10px; font-size: 0.7rem; letter-spacing: 0.04em; }
            .car-title { font-size: 0.9rem; }
            .price { font-size: 0.95rem; }
            .modal-title { font-size: 1.1rem; }
            .modal-price-large { font-size: 1.2rem; }
        }
        @media (pointer: coarse) {
            .car-card { cursor: default; }
            .btn-primary-dark, .btn-ghost-dark, .modal-close { min-height: 44px; min-width: 44px; display: inline-flex; align-items: center; justify-content: center; }
            .nav-links a { padding: 6px 0; display: inline-block; }
        }
        @media (prefers-reduced-motion: reduce) {
            .car-card { animation: none; }
            * { transition-duration: 0.01ms !important; }
        }
    </style>
</head>
<body>

    <!-- ==================== NAVIGATION BAR ==================== -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.html" class="logo">QMSL</a>
            <ul class="nav-links">
                <li><a href="index.html">HOME</a></li>
                <li><a href="search.html">SEARCH</a></li>
                <li><a href="add-car.html">ADD CAR</a></li>
                <li><a href="inventory.html">INVENTORY</a></li>
                <li><a href="login.html">LOGIN</a></li>
            </ul>
        </div>
    </nav>

    <!-- ==================== SEARCH HERO ==================== -->
    <section class="search-hero">
        <div class="container">
            <h1 class="search-hero-title">Find Your Vehicle</h1>
            <p class="search-hero-subtitle">Search by year, model, or refine with advanced filters.</p>
            
            <form id="vehicleSearchForm" action="" method="get" novalidate>
                <!-- Form error message container -->
                <div id="formErrorBox" class="form-error-box"></div>

                <div class="search-panel">
                    <div class="search-row">
                        <div class="search-field" style="flex: 1.8;">
                            <label for="searchKeyword">Brand / Model</label>
                            <input type="text" id="searchKeyword" name="searchKeyword" placeholder="e.g. BMW, Mercedes, Porsche…" autocomplete="off">
                        </div>
                        <div class="search-field" style="flex: 0.8;">
                            <label for="searchMinYear">Min Year</label>
                            <input type="number" id="searchMinYear" name="searchMinYear" placeholder="2018" min="1990" max="2026">
                        </div>
                        <div class="search-field" style="flex: 0.8;">
                            <label for="searchMaxYear">Max Year</label>
                            <input type="number" id="searchMaxYear" name="searchMaxYear" placeholder="2026" min="1990" max="2026">
                        </div>
                        <div class="search-actions">
                            <button class="btn-primary-dark" id="btnSearch" type="submit">SEARCH</button>
                            <button class="btn-ghost-dark" id="btnReset" type="reset">RESET</button>
                        </div>
                    </div>
                    <div class="search-row">
                        <div class="search-field" style="flex: 0.8;">
                            <label for="searchMinPrice">Min Price (USD)</label>
                            <input type="number" id="searchMinPrice" name="searchMinPrice" placeholder="0" min="0" step="1000">
                        </div>
                        <div class="search-field" style="flex: 0.8;">
                            <label for="searchMaxPrice">Max Price (USD)</label>
                            <input type="number" id="searchMaxPrice" name="searchMaxPrice" placeholder="200000" min="0" step="1000">
                        </div>
                        <div style="flex: 1.8; min-width: 160px;"></div>
                        <div class="search-actions" style="visibility: hidden; pointer-events: none;">
                            <button class="btn-primary-dark" type="button" tabindex="-1">&nbsp;</button>
                            <button class="btn-ghost-dark" type="button" tabindex="-1">&nbsp;</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!-- ==================== RESULTS SECTION ==================== -->
    <section class="results-section">
        <div class="container">
            <div class="results-header">
                <p class="results-count" id="resultsCount">Showing <span>0 cars</span></p>
            </div>
            <div class="car-grid" id="carGrid"></div>
            <div class="empty-state" id="emptyState" style="display:none;">No cars found. Try adjusting your search filters.</div>
        </div>
    </section>

    <!-- ==================== DETAIL MODAL ==================== -->
    <div class="modal-overlay" id="modalOverlay">
        <div class="modal-panel" id="modalPanel">
            <button class="modal-close" id="modalClose" aria-label="Close detail view">&times;</button>
            <div class="modal-img" id="modalImg">
                <img src="" alt="" id="modalImageEl">
            </div>
            <div class="modal-body" id="modalBody"></div>
        </div>
    </div>

    <!-- ==================== FOOTER ==================== -->
    <footer class="footer">
        <div class="container">
            <p>© 2025 QMSL. All rights reserved.</p>
        </div>
    </footer>


    <!-- ==================== SEARCH PAGE LOGIC (UPDATED) ==================== -->
    <script>
        window.__INITIAL_CARS__ = <?php echo json_encode($cars, JSON_UNESCAPED_UNICODE); ?>;
        window.QMSL = window.QMSL || {};
        window.QMSL.cars = window.__INITIAL_CARS__ || [];

        // ---------- 真正的客户端过滤 ----------
        window.QMSL.searchCars = function(filters) {
            return window.QMSL.cars.filter(function(car) {
                // Keyword match (brand or model)
                if (filters.keyword) {
                    const kw = filters.keyword.toLowerCase();
                    if (!car.brand.toLowerCase().includes(kw) && !car.model.toLowerCase().includes(kw)) {
                        return false;
                    }
                }
                // Year range
                const minY = parseInt(filters.minYear, 10);
                const maxY = parseInt(filters.maxYear, 10);
                if (!isNaN(minY) && car.year < minY) return false;
                if (!isNaN(maxY) && car.year > maxY) return false;

                // Price range
                const minP = parseFloat(filters.minPrice);
                const maxP = parseFloat(filters.maxPrice);
                if (!isNaN(minP) && car.price < minP) return false;
                if (!isNaN(maxP) && car.price > maxP) return false;

                return true;
            });
        };

        document.addEventListener('DOMContentLoaded', function() {
            // Form and inputs
            const form = document.getElementById('vehicleSearchForm');
            const errorBox = document.getElementById('formErrorBox');
            const searchKeyword = document.getElementById('searchKeyword');
            const searchMinYear = document.getElementById('searchMinYear');
            const searchMaxYear = document.getElementById('searchMaxYear');
            const searchMinPrice = document.getElementById('searchMinPrice');
            const searchMaxPrice = document.getElementById('searchMaxPrice');

            // Results
            const carGrid = document.getElementById('carGrid');
            const resultsCount = document.getElementById('resultsCount');
            const emptyState = document.getElementById('emptyState');

            // Modal
            const modalOverlay = document.getElementById('modalOverlay');
            const modalPanel = document.getElementById('modalPanel');
            const modalClose = document.getElementById('modalClose');
            const modalImageEl = document.getElementById('modalImageEl');
            const modalBody = document.getElementById('modalBody');

            function formatPrice(price) { return '$' + price.toLocaleString('en-US'); }
            function formatMileage(mileage) { return mileage.toLocaleString('en-US') + ' mi'; }

            // ---------- 获取当前过滤条件 ----------
            function getFilters() {
                return {
                    keyword: searchKeyword.value.trim(),
                    minYear: searchMinYear.value,
                    maxYear: searchMaxYear.value,
                    minPrice: searchMinPrice.value,
                    maxPrice: searchMaxPrice.value
                };
            }

            // ---------- 渲染车辆卡片 ----------
            function renderCars(cars) {
                carGrid.innerHTML = '';
                if (cars.length === 0) {
                    emptyState.style.display = 'block';
                    resultsCount.innerHTML = 'Showing <span>0 cars</span>';
                    return;
                }
                emptyState.style.display = 'none';
                resultsCount.innerHTML = 'Showing <span>' + cars.length + ' car' + (cars.length !== 1 ? 's' : '') + '</span>';

                cars.forEach(function(car) {
                    const card = document.createElement('div');
                    card.className = 'car-card';
                    card.setAttribute('data-car-id', car.id);
                    card.addEventListener('click', function() { openDetailModal(car); });
                    const imageUrl = car.image || 'https://placehold.co/600x400/eeeeee/000000?text=' + encodeURIComponent(car.brand + '+' + car.model);
                    card.innerHTML =
                        '<div class="card-img"><img src="' + imageUrl + '" alt="' + car.brand + ' ' + car.model + '" loading="lazy"></div>' +
                        '<div class="card-body">' +
                        '<div class="car-title">' + car.brand + ' ' + car.model + '</div>' +
                        '<div class="car-meta"><span>' + car.year + '</span><span>' + car.color + '</span></div>' +
                        '<div class="car-details"><span>&#128196; ' + formatMileage(car.mileage) + '</span></div>' +
                        '<div class="price">' + formatPrice(car.price) + '</div>' +
                        '<div class="location">&#128205; ' + car.location + '</div>' +
                        '</div>';
                    carGrid.appendChild(card);
                });
            }

            // ---------- 执行搜索（先验证） ----------
            function performSearch() {
                const filters = getFilters();
                const filtered = window.QMSL.searchCars(filters);
                renderCars(filtered);
            }

            // ---------- 表单验证 ----------
            function validateForm(filters) {
                let errors = [];
                // 清除上一次高亮
                clearErrors();

                const minYear = parseInt(filters.minYear, 10);
                const maxYear = parseInt(filters.maxYear, 10);
                const minPrice = parseFloat(filters.minPrice);
                const maxPrice = parseFloat(filters.maxPrice);

                // 年份交叉验证
                if (!isNaN(minYear) && !isNaN(maxYear) && minYear > maxYear) {
                    errors.push('Min Year cannot be greater than Max Year.');
                    highlightField(searchMinYear);
                    highlightField(searchMaxYear);
                } else {
                    if (!isNaN(minYear) && (minYear < 1990 || minYear > 2026)) {
                        errors.push('Min Year must be between 1990 and 2026.');
                        highlightField(searchMinYear);
                    }
                    if (!isNaN(maxYear) && (maxYear < 1990 || maxYear > 2026)) {
                        errors.push('Max Year must be between 1990 and 2026.');
                        highlightField(searchMaxYear);
                    }
                }

                // 价格交叉验证
                if (!isNaN(minPrice) && !isNaN(maxPrice) && minPrice > maxPrice) {
                    errors.push('Min Price cannot be greater than Max Price.');
                    highlightField(searchMinPrice);
                    highlightField(searchMaxPrice);
                } else {
                    if (!isNaN(minPrice) && minPrice < 0) {
                        errors.push('Min Price cannot be negative.');
                        highlightField(searchMinPrice);
                    }
                    if (!isNaN(maxPrice) && maxPrice < 0) {
                        errors.push('Max Price cannot be negative.');
                        highlightField(searchMaxPrice);
                    }
                }

                return errors;
            }

            function showErrors(errorsArr) {
                errorBox.style.display = 'block';
                errorBox.innerHTML = errorsArr.join('<br>');
            }

            function clearErrors() {
                errorBox.style.display = 'none';
                errorBox.innerHTML = '';
                document.querySelectorAll('.highlight-error').forEach(el => el.classList.remove('highlight-error'));
            }

            function highlightField(inputEl) {
                inputEl.classList.add('highlight-error');
            }

            // ---------- 表单提交事件：阻止默认提交，验证后在前端搜索 ----------
            form.addEventListener('submit', function(e) {
                e.preventDefault();                // 永远不刷新页面
                const filters = getFilters();
                const errors = validateForm(filters);
                if (errors.length > 0) {
                    showErrors(errors);
                    return;
                }
                // 验证通过，执行前端搜索
                clearErrors();
                performSearch();
            });

            // ---------- 重置按钮：清空字段，显示全部车辆 ----------
            form.addEventListener('reset', function() {
                // 给浏览器一点时间重置字段值
                setTimeout(function() {
                    clearErrors();
                    performSearch();   // 此时字段已空，显示所有车
                }, 0);
            });

            // ---------- Enter 键触发搜索 ----------
            const allInputs = [searchKeyword, searchMinYear, searchMaxYear, searchMinPrice, searchMaxPrice];
            allInputs.forEach(input => {
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const filters = getFilters();
                        const errors = validateForm(filters);
                        if (errors.length > 0) {
                            showErrors(errors);
                        } else {
                            clearErrors();
                            performSearch();
                        }
                    }
                });
            });

            // ---------- Modal 逻辑 ----------
            function openDetailModal(car) {
                const largeImageUrl = car.imageLarge || car.image || 'https://placehold.co/800x500/1a1a1a/ffffff?text=' + encodeURIComponent(car.brand + '+' + car.model);
                modalImageEl.src = largeImageUrl;
                modalImageEl.alt = car.brand + ' ' + car.model;
                modalBody.innerHTML =
                    '<h2 class="modal-title">' + car.brand + ' ' + car.model + '</h2>' +
                    '<p class="modal-subtitle">' + car.year + ' · ' + car.color + ' · ' + car.location + '</p>' +
                    '<div class="modal-price-large">' + formatPrice(car.price) + '</div>' +
                    '<div class="modal-specs">' +
                    '<div class="modal-spec-item"><span class="modal-spec-label">Year</span><span class="modal-spec-value">' + car.year + '</span></div>' +
                    '<div class="modal-spec-item"><span class="modal-spec-label">Mileage</span><span class="modal-spec-value">' + formatMileage(car.mileage) + '</span></div>' +
                    '<div class="modal-spec-item"><span class="modal-spec-label">Color</span><span class="modal-spec-value">' + car.color + '</span></div>' +
                    '<div class="modal-spec-item"><span class="modal-spec-label">Location</span><span class="modal-spec-value">' + car.location + '</span></div>' +
                    '<div class="modal-spec-item"><span class="modal-spec-label">Transmission</span><span class="modal-spec-value">' + (car.transmission || 'N/A') + '</span></div>' +
                    '<div class="modal-spec-item"><span class="modal-spec-label">Fuel Type</span><span class="modal-spec-value">' + (car.fuelType || 'N/A') + '</span></div>' +
                    '<div class="modal-spec-item"><span class="modal-spec-label">Engine</span><span class="modal-spec-value">' + (car.engine || 'N/A') + '</span></div>' +
                    '<div class="modal-spec-item"><span class="modal-spec-label">Drivetrain</span><span class="modal-spec-value">' + (car.drivetrain || 'N/A') + '</span></div>' +
                    '<div class="modal-spec-item"><span class="modal-spec-label">VIN</span><span class="modal-spec-value" style="font-size:0.78rem;letter-spacing:0.06em;">' + (car.vin || 'N/A') + '</span></div>' +
                    '</div>' +
                    '<p class="modal-description">' + (car.description || 'No description available.') + '</p>' +
                    '<p class="modal-contact">Seller contact: <strong>' + (car.phone || 'N/A') + '</strong></p>';
                modalOverlay.classList.add('active');
                document.body.style.overflow = 'hidden';
                modalPanel.scrollTop = 0;
            }

            function closeDetailModal() {
                modalOverlay.classList.remove('active');
                document.body.style.overflow = '';
            }

            modalClose.addEventListener('click', function(e) { e.stopPropagation(); closeDetailModal(); });
            modalOverlay.addEventListener('click', function(e) { if (e.target === modalOverlay) closeDetailModal(); });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && modalOverlay.classList.contains('active')) closeDetailModal();
            });

            // 初始加载：显示所有车辆
            performSearch();
        });
    </script>
</body>
</html>