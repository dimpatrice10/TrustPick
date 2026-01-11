<?php
require __DIR__ . '/includes/db.php';
$rows = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = DATABASE() ORDER BY table_name")->fetchAll(PDO::FETCH_COLUMN);
foreach ($rows as $t)
    echo $t . "\n";
