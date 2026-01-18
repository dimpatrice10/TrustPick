<?php
session_start();
require __DIR__ . '/../includes/url.php';
require __DIR__ . '/../includes/db.php';
if (empty($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Connectez-vous.';
    header('../public/index.php?page=login');
}

$amount = floatval($_POST['amount'] ?? 0);
if ($amount <= 0) {
    $_SESSION['error'] = 'Montant invalide.';
    header('../public/index.php?page=wallet');
}

// check balance
$stmt = $pdo->prepare('SELECT balance FROM wallets WHERE user_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$w = $stmt->fetch();
if (!$w || $w['balance'] < $amount) {
    $_SESSION['error'] = 'Solde insuffisant.';
    header('../public/index.php?page=wallet');
}

// create withdrawal (pending) and debit wallet
$pdo->beginTransaction();
$pdo->prepare('INSERT INTO withdrawals (user_id,amount,status) VALUES (?,?,"pending")')->execute([$_SESSION['user_id'], $amount]);
$pdo->prepare('UPDATE wallets SET balance = balance - ? WHERE user_id = ?')->execute([$amount, $_SESSION['user_id']]);
$pdo->commit();

$_SESSION['success'] = 'Demande de retrait créée. Traitement en cours.';
header('../public/index.php?page=wallet');
