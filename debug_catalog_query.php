<?php
require __DIR__ . '/includes/db.php';
header('Content-Type: text/plain; charset=utf-8');

try {
    echo "=== PRODUCTS table schema ===\n";
    $stmt = $pdo->query("DESCRIBE products");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) {
        echo $c['Field'] . "\t" . $c['Type'] . "\n";
    }
} catch (Exception $e) {
    echo "DESCRIBE products failed: " . $e->getMessage() . "\n";
}

try {
    $c = $pdo->query('SELECT COUNT(*) as cnt FROM products')->fetchColumn();
    echo "\nTOTAL PRODUCTS: " . intval($c) . "\n";
} catch (Exception $e) {
    echo "COUNT failed: " . $e->getMessage() . "\n";
}

try {
    echo "\n=== SAMPLE 5 products (cols) ===\n";
    $s = $pdo->query('SELECT * FROM products LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($s as $row) {
        foreach ($row as $k => $v) {
            $val = is_null($v) ? 'NULL' : (is_string($v) ? substr($v, 0, 80) : $v);
            echo "$k: $val\n";
        }
        echo "---\n";
    }
} catch (Exception $e) {
    echo "SELECT sample failed: " . $e->getMessage() . "\n";
}
?>