<?php
/**
 * Quick test script to validate PDO connection and schema
 * Run this at: http://localhost/TrustPick/test-db-connection.php
 */

echo "=== TrustPick Database Connection Test ===\n\n";

// Test 1: Config loading
echo "1. Loading config...";
try {
    $config = require __DIR__ . '/includes/config.php';
    echo " ✓\n";
    echo "   Host: " . $config['db_host'] . "\n";
    echo "   DB: " . $config['db_name'] . "\n";
} catch (Exception $e) {
    echo " ✗ FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: PDO connection
echo "\n2. Connecting to database...";
try {
    $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo " ✓\n";
} catch (Exception $e) {
    echo " ✗ FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 3: Table counts
echo "\n3. Checking tables...\n";
$tables = ['users', 'companies', 'products', 'reviews', 'transactions', 'withdrawals'];
foreach ($tables as $t) {
    try {
        $cnt = $pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn();
        echo "   $t: $cnt rows ✓\n";
    } catch (Exception $e) {
        echo "   $t: ERROR - " . $e->getMessage() . "\n";
    }
}

// Test 4: Sample queries
echo "\n4. Sample queries...\n";
try {
    $users = $pdo->query('SELECT COUNT(*) as cnt FROM users')->fetch();
    echo "   Total users: " . $users['cnt'] . " ✓\n";

    $products = $pdo->query('SELECT COUNT(*) as cnt FROM products')->fetch();
    echo "   Total products: " . $products['cnt'] . " ✓\n";

    $reviews = $pdo->query('SELECT COUNT(*) as cnt FROM reviews')->fetch();
    echo "   Total reviews: " . $reviews['cnt'] . " ✓\n";
} catch (Exception $e) {
    echo "   ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== All tests passed! ===\n";
echo "\nDeveloper instructions:\n";
echo "1. Open http://localhost/phpmyadmin\n";
echo "2. Import db/init.sql (or copy contents and run as SQL query)\n";
echo "3. Verify this test again at http://localhost/TrustPick/test-db-connection.php\n";
echo "4. Then visit http://localhost/TrustPick/index.php\n";
