<?php
require __DIR__ . '/includes/db.php';
$uid = 6; // id created earlier
$balance = 20.00;
try {
    $chk = $pdo->prepare('SELECT user_id FROM wallets WHERE user_id = ?');
    $chk->execute([$uid]);
    if ($chk->fetch()) {
        echo "Wallet already exists for user {$uid}\n";
        exit(0);
    }
    $ins = $pdo->prepare('INSERT INTO wallets (user_id,balance) VALUES (?,?)');
    $ins->execute([$uid, $balance]);
    echo "Created wallet for user {$uid} with balance={$balance}\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
