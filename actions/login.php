<?php
session_start();
require __DIR__ . '/../includes/url.php';
require __DIR__ . '/../includes/db.php';

$email = trim($_POST['email'] ?? '');
$pass = $_POST['password'] ?? '';

if (!$email || !$pass) {
    $_SESSION['error'] = 'Veuillez remplir tous les champs.';
    header('Location: ../public/index.php?page=login');
}

$stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();
if (!$user) {
    $_SESSION['error'] = 'Utilisateur non trouvé.';
    header('Location: ../public/index.php?page=login');
}

// support legacy plaintext or hashed passwords
$stored = $user['password'];
$ok = false;
if (strpos($stored, '$2y$') === 0 || strpos($stored, '$2a$') === 0) {
    $ok = password_verify($pass, $stored);
} else {
    $ok = ($pass === $stored);
}

if (!$ok) {
    $_SESSION['error'] = 'Mot de passe incorrect.';
    header('Location: ../public/index.php?page=login');
}

// success
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['name'];
header('Location: ../public/index.php?page=home');
