<?php
session_start();
require __DIR__ . '/../includes/url.php';
require __DIR__ . '/../includes/db.php';
if (empty($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Connectez-vous pour poster un avis.';
    header('Location: ../public/index.php?page=login');
}

$product_id = intval($_POST['product_id'] ?? 0);
$rating = intval($_POST['rating'] ?? 5);
$title = trim($_POST['title'] ?? '');
$body = trim($_POST['body'] ?? '');

if (!$product_id || $rating < 1 || $rating > 5) {
    $_SESSION['error'] = 'Données invalides.';
    header('Location: ../public/index.php?page=product&id=' . $product_id);
}

$stmt = $pdo->prepare('INSERT INTO reviews (product_id,user_id,rating,title,body) VALUES (?,?,?,?,?)');
$stmt->execute([$product_id, $_SESSION['user_id'], $rating, $title, $body]);

// small wallet reward for posting
$pdo->prepare('UPDATE wallets SET balance = balance + 1 WHERE user_id = ?')->execute([$_SESSION['user_id']]);

$_SESSION['success'] = 'Merci pour votre avis ! +1€ crédité.';
header('Location: ../public/index.php?page=product&id=' . $product_id);
