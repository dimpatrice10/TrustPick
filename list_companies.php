<?php
require __DIR__ . '/includes/db.php';
$rows = $pdo->query('SELECT id,name FROM companies ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r)
    echo $r['id'] . '\t' . $r['name'] . "\n";
