<?php
// Insert a test user into the database (idempotent)
require __DIR__ . '/includes/db.php';

$email = 'test+bot@example.com';
$name = 'Test Bot';
$plain = 'TestPass123!';

try {
    $chk = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $chk->execute([$email]);
    $exist = $chk->fetchColumn();
    if ($exist) {
        echo "User already exists with id={$exist}\n";
        exit(0);
    }

    $hash = password_hash($plain, PASSWORD_DEFAULT);
    $ins = $pdo->prepare('INSERT INTO users (name,email,password,role,created_at) VALUES (?,?,?,?,NOW())');
    $ins->execute([$name, $email, $hash, 'user']);
    $id = $pdo->lastInsertId();
    echo "Created test user id={$id} email={$email} password={$plain}\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
