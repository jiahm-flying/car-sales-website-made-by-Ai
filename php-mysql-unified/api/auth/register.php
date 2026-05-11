<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/response.php';

require_method('POST');

$data = read_json_body();

$name = trim((string)($data['name'] ?? ''));
$address = trim((string)($data['address'] ?? ''));
$phone = trim((string)($data['phone'] ?? ''));
$email = trim((string)($data['email'] ?? ''));
$username = trim((string)($data['username'] ?? ''));
$password = trim((string)($data['password'] ?? ''));

$errors = [];

if (!preg_match('/^[A-Za-z ]{2,80}$/', $name)) {
    $errors['name'] = 'Name must be 2-80 letters/spaces.';
}
if (mb_strlen($address) < 3 || mb_strlen($address) > 150) {
    $errors['address'] = 'Address must be 3-150 characters.';
}
if (!preg_match('/^1[3-9]\d{9}$/', $phone)) {
    $errors['phone'] = 'Invalid China mobile number.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 120) {
    $errors['email'] = 'Invalid email address.';
}
if (!preg_match('/^[A-Za-z0-9]{6,40}$/', $username)) {
    $errors['username'] = 'Username must be 6-40 letters/digits.';
}
if (!preg_match('/^[A-Za-z0-9]{6,60}$/', $password)) {
    $errors['password'] = 'Password must be 6-60 letters/digits.';
}

if ($errors) {
    send_json([
        'ok' => false,
        'message' => 'Validation failed',
        'errors' => $errors,
    ], 422);
}

$pdo = get_db();

$check = $pdo->prepare('SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1');
$check->execute([
    ':username' => $username,
    ':email' => $email,
]);
$exists = $check->fetch();

if ($exists) {
    send_json([
        'ok' => false,
        'message' => 'Username or email already exists',
    ], 409);
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$insert = $pdo->prepare(
    'INSERT INTO users (name, address, phone, email, username, password_hash)
     VALUES (:name, :address, :phone, :email, :username, :password_hash)'
);
$insert->execute([
    ':name' => $name,
    ':address' => $address,
    ':phone' => $phone,
    ':email' => $email,
    ':username' => $username,
    ':password_hash' => $hash,
]);

$userId = (int)$pdo->lastInsertId();
$_SESSION['user_id'] = $userId;
session_regenerate_id(true);

$query = $pdo->prepare(
    'SELECT id, name, address, phone, email, username, created_at
     FROM users WHERE id = :id LIMIT 1'
);
$query->execute([':id' => $userId]);
$user = $query->fetch();

send_json([
    'ok' => true,
    'message' => 'Registration successful',
    'data' => ['user' => public_user($user ?: [])],
], 201);
