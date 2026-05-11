<?php
session_start();

// 未登录则跳转到登录页
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// 数据库配置
$DB_HOST = '127.0.0.1';
$DB_PORT = '3306';
$DB_NAME = 'car_sales';
$DB_USER = 'root';
$DB_PASS = '';
$DB_CHARSET = 'utf8mb4';

$error = '';
$success = false;
$oldData = [];

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 获取表单字段
    $brand         = trim($_POST['brand'] ?? '');
    $model         = trim($_POST['model'] ?? '');
    $year          = intval($_POST['year'] ?? 0);
    $price         = floatval($_POST['price'] ?? 0);
    $mileage       = intval($_POST['mileage'] ?? 0);
    $color         = trim($_POST['color'] ?? '');
    $location      = trim($_POST['location'] ?? '');
    $transmission  = trim($_POST['transmission'] ?? '');
    $fuelType      = trim($_POST['fuelType'] ?? '');
    $drivetrain    = trim($_POST['drivetrain'] ?? '');
    $description   = trim($_POST['description'] ?? '');
    $imageUrl      = trim($_POST['imageUrl'] ?? '');

    // 保存旧数据用于回填
    $oldData = [
        'brand'        => $brand,
        'model'        => $model,
        'year'         => $year,
        'price'        => $price,
        'mileage'      => $mileage,
        'color'        => $color,
        'location'     => $location,
        'transmission' => $transmission,
        'fuelType'     => $fuelType,
        'drivetrain'   => $drivetrain,
        'description'  => $description,
        'imageUrl'     => $imageUrl,
    ];

    // 1. 基础验证
    $errors = [];
    if ($brand === '' || $model === '' || $color === '' || $location === '' ||
        $transmission === '' || $fuelType === '' || $drivetrain === '' || $description === '') {
        $errors[] = 'All text fields are required.';
    }
    if ($year < 1900 || $year > 2026) {
        $errors[] = 'Year must be between 1900 and 2026.';
    }
    if ($price <= 0) {
        $errors[] = 'Price must be greater than 0.';
    }
    if ($mileage < 0) {
        $errors[] = 'Mileage cannot be negative.';
    }

    // 2. 连接数据库
    if (empty($errors)) {
        try {
            $pdo = new PDO("mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_NAME;charset=$DB_CHARSET", $DB_USER, $DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (PDOException $e) {
            $errors[] = 'Database connection failed.';
        }
    }

    // 3. 获取当前用户信息
    if (empty($errors)) {
        if (is_array($_SESSION['user'])) {
            $username = $_SESSION['user']['username'] ?? '';
        } else {
            $username = $_SESSION['user'];
        }
        
        if (empty($username)) {
            $errors[] = 'Session invalid. Please login again.';
        } else {
            $stmt = $pdo->prepare("SELECT id, phone FROM users WHERE username = :uname LIMIT 1");
            $stmt->execute([':uname' => $username]);
            $user = $stmt->fetch();
            if (!$user) {
                $errors[] = 'User account not found.';
            } else {
                $sellerId = $user['id'];
                $phone    = $user['phone'] ?? '';
            }
        }
    }

    // 4. 图片处理
    $imagePath = '';
    $imageLargePath = '';
    if (empty($errors)) {
        $imageFile = $_FILES['imageFile'] ?? null;

        if ($imageFile && $imageFile['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            if (!in_array($imageFile['type'], $allowedTypes)) {
                $errors[] = 'Only JPG, PNG, WebP images allowed.';
            } elseif ($imageFile['size'] > 10 * 1024 * 1024) {
                $errors[] = 'Image size must be < 10MB.';
            } else {
                $uploadDir = __DIR__ . '/uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $ext = pathinfo($imageFile['name'], PATHINFO_EXTENSION);
                $baseName = uniqid('car_', true) . '.' . $ext;
                if (move_uploaded_file($imageFile['tmp_name'], $uploadDir . $baseName)) {
                    $imagePath = 'uploads/' . $baseName;
                    $imageLargePath = $imagePath;
                } else {
                    $errors[] = 'Failed to save image.';
                }
            }
        } elseif ($imageUrl !== '') {
            if (filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                $imagePath = $imageUrl;
                $imageLargePath = $imageUrl;
            } else {
                $errors[] = 'Invalid image URL.';
            }
        } else {
            $imageText = urlencode($brand . ' ' . $model);
            $imagePath = "https://placehold.co/600x400/1a1a1a/ffffff?text={$imageText}";
            $imageLargePath = $imagePath;
        }
    }

    // 5. 插入数据库
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->query("SELECT MAX(CAST(SUBSTRING(id, 2) AS UNSIGNED)) as max_num FROM cars FOR UPDATE");
            $row = $stmt->fetch();
            $maxNum = $row['max_num'] ?? 0;       
            $newId = 'c' . ($maxNum + 1);         
            
            $sql = "INSERT INTO cars 
                (id, sellerId, brand, model, year, price, mileage, color, location, 
                transmission, fuelType, drivetrain, description, phone, image, imageLarge)
                VALUES 
                (:id, :sellerId, :brand, :model, :year, :price, :mileage, :color, :location,
                :transmission, :fuelType, :drivetrain, :description, :phone, :image, :imageLarge)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id'           => $newId,
                ':sellerId'     => $sellerId,
                ':brand'        => $brand,
                ':model'        => $model,
                ':year'         => $year,
                ':price'        => $price,
                ':mileage'      => $mileage,
                ':color'        => $color,
                ':location'     => $location,
                ':transmission' => $transmission,
                ':fuelType'     => $fuelType,
                ':drivetrain'   => $drivetrain,
                ':description'  => $description,
                ':phone'        => $phone,
                ':image'        => $imagePath,
                ':imageLarge'   => $imageLargePath
            ]);

            $pdo->commit();
            header('Location: inventory.php?added=1');
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            // 生产环境应记录日志，避免暴露 SQL 细节
            $errors[] = 'Database insert failed: ' . $e->getMessage();
        }
    }

    // ★ 修复关键：将验证错误合并到 $error 变量中，以便模板显示
    if (!empty($errors)) {
        $error = implode('<br>', $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Car | QMSL</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Inter',sans-serif; background:#000; color:#fff; }
        .container { max-width:1280px; margin:0 auto; padding:0 32px; }
        .navbar { position:fixed; top:0; width:100%; background:rgba(10,10,10,0.75); backdrop-filter:blur(20px); border-bottom:1px solid rgba(255,255,255,0.08); z-index:1000; padding:18px 0; }
        .nav-container { max-width:1280px; margin:0 auto; padding:0 32px; display:flex; justify-content:space-between; align-items:center; }
        .logo { font-size:1.6rem; font-weight:600; color:#fff; text-decoration:none; }
        .nav-links { display:flex; gap:2.2rem; list-style:none; }
        .nav-links a { color:rgba(255,255,255,0.75); text-decoration:none; font-size:0.9rem; font-weight:500; }
        .nav-links a:hover { color:#fff; }
        .hero-section { padding-top:180px; padding-bottom:100px; text-align:center; }
        .hero-section h1 { font-size:3rem; font-weight:300; letter-spacing:0.15em; margin-bottom:20px; text-transform:uppercase; }
        .form-section { background:#fff; padding:100px 0; color:#000; }
        .form-card { max-width:800px; margin:0 auto; background:rgba(255,255,255,0.85); border:1px solid rgba(0,0,0,0.1); padding:60px; box-shadow:0 20px 40px rgba(0,0,0,0.03); }
        .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:30px; }
        .form-group { margin-bottom:25px; }
        .form-group.full-width { grid-column:span 2; }
        label { display:block; font-size:0.75rem; font-weight:600; letter-spacing:0.1em; margin-bottom:10px; text-transform:uppercase; color:rgba(0,0,0,0.5); }
        input, select, textarea { width:100%; padding:15px; background:rgba(0,0,0,0.03); border:1px solid rgba(0,0,0,0.1); color:#000; font-family:inherit; font-size:1rem; }
        textarea { min-height:120px; resize:vertical; }
        .image-upload-container { border:2px dashed rgba(0,0,0,0.2); padding:30px; text-align:center; cursor:pointer; position:relative; }
        .image-upload-container input[type="file"] { position:absolute; top:0; left:0; width:100%; height:100%; opacity:0; cursor:pointer; }
        .preview-image { max-width:100%; max-height:250px; margin-top:15px; display:none; object-fit:contain; }
        .url-input-group { margin-top:15px; display:flex; gap:10px; }
        .separator { margin:25px 0 15px; border-top:1px solid rgba(0,0,0,0.1); display:flex; align-items:center; justify-content:center; gap:10px; font-size:0.7rem; color:rgba(0,0,0,0.4); }
        .btn-submit { width:100%; padding:20px; background:#000; color:#fff; border:none; font-size:0.9rem; font-weight:600; letter-spacing:0.2em; text-transform:uppercase; cursor:pointer; margin-top:20px; }
        .footer { background:#0a0a0a; padding:48px 0; text-align:center; border-top:1px solid rgba(255,255,255,0.06); }
        .footer p { color:rgba(255,255,255,0.5); font-size:0.8rem; }
        .error-message { background:#f8d7da; color:#721c24; padding:12px 20px; border-radius:4px; margin-bottom:25px; font-size:0.9rem; border-left:4px solid #f5c6cb; }
        .success-message { background:#d4edda; color:#155724; padding:12px 20px; border-radius:4px; margin-bottom:25px; font-size:0.9rem; border-left:4px solid #c3e6cb; }
        @media (max-width:768px) { .form-card { padding:30px; } .form-grid { grid-template-columns:1fr; } .form-group.full-width { grid-column:span 1; } .hero-section h1 { font-size:2rem; } }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="nav-container">
        <a href="index.html" class="logo">QMSL</a>
        <ul class="nav-links">
            <li><a href="index.php">HOME</a></li>
            <li><a href="search.php">SEARCH</a></li>
            <li><a href="add-car.php">ADD CAR</a></li>
            <li><a href="inventory.php">INVENTORY</a></li>
            <li><a href="login.php">LOGIN</a></li>
        </ul>
    </div>
</nav>

<section class="hero-section">
    <div class="container">
        <h1>List Your Vehicle</h1>
        <p>CONVERT ASSETS TO CAPITAL WITH INDUSTRIAL PRECISION</p>
    </div>
</section>

<section class="form-section">
    <div class="container">
        <div class="form-card">
            <!-- 显示错误信息（已修复） -->
            <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- 从 inventory.php 重定向回来的成功提示 -->
            <?php if (isset($_GET['added']) && $_GET['added'] == 1): ?>
                <div class="success-message">Vehicle added successfully! Redirecting...</div>
                <script>setTimeout(function(){ window.location.href='inventory.php'; }, 1500);</script>
            <?php endif; ?>
            
            <form method="post" action="" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group"><label>Brand</label><input type="text" name="brand" id="brand" value="<?php echo htmlspecialchars($oldData['brand'] ?? ''); ?>" required></div>
                    <div class="form-group"><label>Model</label><input type="text" name="model" id="model" value="<?php echo htmlspecialchars($oldData['model'] ?? ''); ?>" required></div>
                    <div class="form-group"><label>Year</label><input type="number" name="year" id="year" min="1900" max="2026" value="<?php echo htmlspecialchars($oldData['year'] ?? ''); ?>" required></div>
                    <div class="form-group"><label>Price ($)</label><input type="number" step="0.01" name="price" id="price" value="<?php echo htmlspecialchars($oldData['price'] ?? ''); ?>" required></div>
                    <div class="form-group"><label>Mileage (mi)</label><input type="number" name="mileage" id="mileage" value="<?php echo htmlspecialchars($oldData['mileage'] ?? ''); ?>" required></div>
                    <div class="form-group"><label>Color</label><input type="text" name="color" id="color" value="<?php echo htmlspecialchars($oldData['color'] ?? ''); ?>" required></div>
                    <div class="form-group">
                        <label>Transmission</label>
                        <select name="transmission" id="transmission" required>
                            <option value="">Select</option>
                            <option value="Automatic" <?php echo (($oldData['transmission'] ?? '') == 'Automatic') ? 'selected' : ''; ?>>Automatic</option>
                            <option value="Manual" <?php echo (($oldData['transmission'] ?? '') == 'Manual') ? 'selected' : ''; ?>>Manual</option>
                            <option value="Semi-Automatic" <?php echo (($oldData['transmission'] ?? '') == 'Semi-Automatic') ? 'selected' : ''; ?>>Semi-Automatic</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Fuel Type</label>
                        <select name="fuelType" id="fuelType" required>
                            <option value="">Select</option>
                            <option value="Gasoline" <?php echo (($oldData['fuelType'] ?? '') == 'Gasoline') ? 'selected' : ''; ?>>Gasoline</option>
                            <option value="Diesel" <?php echo (($oldData['fuelType'] ?? '') == 'Diesel') ? 'selected' : ''; ?>>Diesel</option>
                            <option value="Electric" <?php echo (($oldData['fuelType'] ?? '') == 'Electric') ? 'selected' : ''; ?>>Electric</option>
                            <option value="Hybrid" <?php echo (($oldData['fuelType'] ?? '') == 'Hybrid') ? 'selected' : ''; ?>>Hybrid</option>
                        </select>
                    </div>
                    <div class="form-group full-width">
                        <label>Drivetrain</label>
                        <select name="drivetrain" id="drivetrain" required>
                            <option value="">Select</option>
                            <option value="FWD" <?php echo (($oldData['drivetrain'] ?? '') == 'FWD') ? 'selected' : ''; ?>>FWD</option>
                            <option value="RWD" <?php echo (($oldData['drivetrain'] ?? '') == 'RWD') ? 'selected' : ''; ?>>RWD</option>
                            <option value="AWD" <?php echo (($oldData['drivetrain'] ?? '') == 'AWD') ? 'selected' : ''; ?>>AWD</option>
                            <option value="4WD" <?php echo (($oldData['drivetrain'] ?? '') == '4WD') ? 'selected' : ''; ?>>4WD</option>
                        </select>
                    </div>
                    <div class="form-group full-width"><label>Description</label><textarea name="description" id="description" required><?php echo htmlspecialchars($oldData['description'] ?? ''); ?></textarea></div>
                    <div class="form-group full-width"><label>Location</label><input type="text" name="location" id="location" value="<?php echo htmlspecialchars($oldData['location'] ?? ''); ?>" required></div>
                    <div class="form-group full-width">
                        <label>Vehicle Image (Optional)</label>
                        <div class="image-upload-container" id="dropZone">
                            <div class="upload-icon">📷</div>
                            <div class="upload-text">Drop image or click to browse</div>
                            <input type="file" name="imageFile" id="imageFile" accept="image/*">
                        </div>
                        <img id="imagePreview" class="preview-image">
                        <div class="separator">OR</div>
                        <div class="url-input-group">
                            <input type="url" name="imageUrl" id="imageUrl" placeholder="https://example.com/car.jpg" value="<?php echo htmlspecialchars($oldData['imageUrl'] ?? ''); ?>">
                            <span style="font-size:0.7rem;">or paste URL</span>
                        </div>
                        <div style="font-size:0.7rem; margin-top:5px;">Leave blank → auto placeholder</div>
                    </div>
                </div>
                <button type="submit" class="btn-submit">Initialize Listing</button>
            </form>
        </div>
    </div>
</section>

<footer class="footer"><div class="container"><p>© 2025 QMSL. All rights reserved.</p></div></footer>

<script>
    const fileInput = document.getElementById('imageFile');
    const dropZone = document.getElementById('dropZone');
    const previewImg = document.getElementById('imagePreview');
    const imageUrlInput = document.getElementById('imageUrl');

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(ev => dropZone.addEventListener(ev, e => e.preventDefault()));
    dropZone.addEventListener('drop', e => {
        let file = e.dataTransfer.files[0];
        if (file && file.type.match('image.*')) {
            fileInput.files = e.dataTransfer.files;
            previewFile(file);
        }
    });
    fileInput.addEventListener('change', () => {
        if (fileInput.files[0]) previewFile(fileInput.files[0]);
    });
    imageUrlInput.addEventListener('input', () => {
        if (imageUrlInput.value.trim()) {
            fileInput.value = '';
            clearPreview();
        }
    });

    function previewFile(file) {
        if (!file.type.match('image.*')) { alert('Invalid image'); fileInput.value = ''; return; }
        if (file.size > 10 * 1024 * 1024) { alert('Max 10MB'); fileInput.value = ''; return; }
        const reader = new FileReader();
        reader.onload = e => { previewImg.src = e.target.result; previewImg.style.display = 'block'; };
        reader.readAsDataURL(file);
    }
    function clearPreview() { previewImg.src = ''; previewImg.style.display = 'none'; }
</script>
</body>
</html>