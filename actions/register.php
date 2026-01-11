<?php
session_start();
require __DIR__ . '/../includes/url.php';
require __DIR__ . '/../includes/db.php';

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$pass = $_POST['password'] ?? '';

if (!$name || !$email || !$pass) {
    $_SESSION['error'] = 'Veuillez remplir tous les champs.';
    redirect('index.php?page=register');
}

// basic check
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    $_SESSION['error'] = 'Email déjà utilisé.';
    redirect('index.php?page=register');
}

// NOTE: passwords stored in plaintext in seed for local dev; but here we hash

$hash = password_hash($pass, PASSWORD_DEFAULT);
$stmt = $pdo->prepare('INSERT INTO users (name,email,password,role,created_at) VALUES (?,?,?,?,NOW())');
$stmt->execute([$name, $email, $hash, 'user']);
$uid = $pdo->lastInsertId();
// create wallet with welcome credit
$pdo->prepare('INSERT INTO wallets (user_id,balance) VALUES (?,?)')->execute([$uid, 10.00]);

// login
$_SESSION['user_id'] = $uid;
$_SESSION['user_name'] = $name;
redirect('index.php?page=home');
