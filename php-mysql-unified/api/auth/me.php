<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/response.php';

require_method('GET');

$userId = (int)($_SESSION['user_id'] ?? 0);
if ($userId <= 0) {
    send_json([
        'ok' => false,
        'message' => 'Not authenticated',
    ], 401);
}

$pdo = get_db();
$stmt = $pdo->prepare(
    'SELECT id, name, address, phone, email, username, created_at
     FROM users
     WHERE id = :id
     LIMIT 1'
);
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch();

if (!$user) {
    send_json([
        'ok' => false,
        'message' => 'User not found',
    ], 404);
}

send_json([
    'ok' => true,
    'message' => 'Authenticated',
    'data' => ['user' => public_user($user)],
]);
