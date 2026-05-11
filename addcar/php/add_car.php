<?php

header('Content-Type: application/json; charset=utf-8');

/* =========================
   1. 只允许 POST 请求
   ========================= */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Only POST request is allowed.'
    ]);
    exit;
}

/* =========================
   2. MySQL 数据库配置
   ========================= */

$DB_HOST = '127.0.0.1';
$DB_PORT = '3306';
$DB_NAME = 'car_sales';
$DB_USER = 'root';
$DB_PASS = '';
$DB_CHARSET = 'utf8mb4';

$dsn = "mysql:host={$DB_HOST};port={$DB_PORT};dbname={$DB_NAME};charset={$DB_CHARSET}";

/* =========================
   3. 连接数据库
   ========================= */

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed.',
        'error' => $e->getMessage()
    ]);
    exit;
}

/* =========================
   4. 读取前端发送的数据
   ========================= */

$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

if (!is_array($data)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON data.'
    ]);
    exit;
}

/* =========================
   5. 获取字段
   ========================= */

$brand = isset($data['brand']) ? trim($data['brand']) : '';
$model = isset($data['model']) ? trim($data['model']) : '';
$year = isset($data['year']) ? intval($data['year']) : 0;
$price = isset($data['price']) ? floatval($data['price']) : 0;
$mileage = isset($data['mileage']) ? intval($data['mileage']) : 0;
$color = isset($data['color']) ? trim($data['color']) : '';
$location = isset($data['location']) ? trim($data['location']) : '';
$image = isset($data['image']) ? trim($data['image']) : '';

/* =========================
   6. 后端数据验证
   ========================= */

if ($brand === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Brand is required.'
    ]);
    exit;
}

if ($model === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Model is required.'
    ]);
    exit;
}

if ($year < 1900 || $year > 2026) {
    echo json_encode([
        'success' => false,
        'message' => 'Year must be between 1900 and 2026.'
    ]);
    exit;
}

if ($price <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Price must be greater than 0.'
    ]);
    exit;
}

if ($mileage < 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Mileage cannot be negative.'
    ]);
    exit;
}

if ($color === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Color is required.'
    ]);
    exit;
}

if ($location === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Location is required.'
    ]);
    exit;
}

if ($image === '') {
    $imageText = urlencode($brand . ' ' . $model);
    $image = "https://placehold.co/600x400/1a1a1a/ffffff?text={$imageText}";
}

/* =========================
   7. 插入数据库
   ========================= */

try {
    $sql = "
        INSERT INTO cars 
        (brand, model, year, price, mileage, color, location, image, created_at)
        VALUES
        (:brand, :model, :year, :price, :mileage, :color, :location, :image, NOW())
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':brand' => $brand,
        ':model' => $model,
        ':year' => $year,
        ':price' => $price,
        ':mileage' => $mileage,
        ':color' => $color,
        ':location' => $location,
        ':image' => $image
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Vehicle integrated into inventory.',
        'car_id' => $pdo->lastInsertId()
    ]);
    exit;

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to insert car data.',
        'error' => $e->getMessage()
    ]);
    exit;
}
?>