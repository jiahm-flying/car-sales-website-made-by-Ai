<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/response.php';

require_method('POST');

$data = read_json_body();
$username = trim((string)($data['username'] ?? ''));
$password = trim((string)($data['password'] ?? ''));

if ($username === '' || $password === '') {
    send_json([
        'ok' => false,
        'message' => 'Username and password are required',
    ], 422);
}

$pdo = get_db();
$stmt = $pdo->prepare(
    'SELECT id, name, address, phone, email, username, password_hash, created_at
     FROM users
     WHERE username = :username
     LIMIT 1'
);
$stmt->execute([':username' => $username]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, (string)$user['password_hash'])) {
    send_json([
        'ok' => false,
        'message' => 'Invalid username or password',
    ], 401);
}

$_SESSION['user_id'] = (int)$user['id'];
session_regenerate_id(true);

send_json([
    'ok' => true,
    'message' => 'Login successful',
    'data' => ['user' => public_user($user)],
]);
