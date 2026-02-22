<?php
/**
 * Fichier de test de connexion à la base de données
 * Utilise la même logique que db.php
 */
$config = require __DIR__ . '/config.php';

try {
    $driver = $config['db_driver'] ?? 'mysql';
    $port = $config['db_port'] ?? ($driver === 'pgsql' ? 5432 : 3306);

    if ($driver === 'pgsql') {
        $dsn = "pgsql:host={$config['db_host']};port={$port};dbname={$config['db_name']}";
    } else {
        $dsn = "mysql:host={$config['db_host']};port={$port};dbname={$config['db_name']};charset=utf8mb4";
    }

    $pdo = new PDO($dsn, $config['db_user'], $config['db_pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connexion {$driver} réussie !";

} catch (PDOException $e) {
    die("Erreur connexion : " . $e->getMessage());
}