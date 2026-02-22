<?php
/**
 * TrustPick V2 - Configuration
 * Compatible MySQL (InfinityFree, XAMPP) et PostgreSQL (Render)
 */

// Charger la fonction tp_env() depuis un fichier séparé (évite les redéclarations)
require_once __DIR__ . '/env.php';

// Détection du driver de base de données
// DATABASE_URL ou PGHOST = PostgreSQL (Render), sinon MySQL (InfinityFree/XAMPP)
$databaseUrl = tp_env('DATABASE_URL');
if ($databaseUrl) {
    // PostgreSQL via DATABASE_URL (Render.com)
    $dbParts = parse_url($databaseUrl);
    $dbHost = $dbParts['host'] ?? '127.0.0.1';
    $dbPort = $dbParts['port'] ?? 5432;
    $dbName = ltrim($dbParts['path'] ?? '/trustpick_v2', '/');
    $dbUser = $dbParts['user'] ?? 'postgres';
    $dbPass = $dbParts['pass'] ?? '';
    $dbDriver = 'pgsql';
} elseif (tp_env('PGHOST')) {
    // PostgreSQL explicite
    $dbHost = tp_env('PGHOST', '127.0.0.1');
    $dbPort = tp_env('PGPORT', 5432);
    $dbName = tp_env('PGDATABASE', 'trustpick_v2');
    $dbUser = tp_env('PGUSER', 'postgres');
    $dbPass = tp_env('PGPASSWORD', '');
    $dbDriver = 'pgsql';
} else {
    // MySQL (InfinityFree / XAMPP / hébergements classiques)
    $dbHost = tp_env('DB_HOST', tp_env('MYSQL_HOST', '127.0.0.1'));
    $dbPort = tp_env('DB_PORT', tp_env('MYSQL_PORT', 3306));
    $dbName = tp_env('DB_NAME', tp_env('MYSQL_DATABASE', 'trustpick_v2'));
    $dbUser = tp_env('DB_USER', tp_env('MYSQL_USER', 'root'));
    $dbPass = tp_env('DB_PASS', tp_env('MYSQL_PASSWORD', ''));
    $dbDriver = 'mysql';
}

return [
    'db_driver' => $dbDriver,
    'db_host' => $dbHost,
    'db_name' => $dbName,
    'db_user' => $dbUser,
    'db_pass' => $dbPass,
    'db_port' => $dbPort,

    'payment' => [
        'mesomb' => [
            'application_key' => tp_env('MESOMB_APP_KEY', '18bfc8002ab9555601c82fcb07e2817e221dad36'),
            'access_key' => tp_env('MESOMB_ACCESS_KEY', '5c63e664-2993-4f11-9cea-54347347a307'),
            'secret_key' => tp_env('MESOMB_SECRET_KEY', 'd68f6eb3-9a8b-4315-8228-587d6f25c2a4'),
            'enabled' => tp_env('MESOMB_ENABLED') !== 'false'
        ],

        'receiving_accounts' => [
            'orange_money' => tp_env('ORANGE_ACCOUNT', '657317490'),
            'mtn_money' => tp_env('MTN_ACCOUNT', '683833646')
        ],

        'min_deposit' => 1000,
        'currency' => 'XAF'
    ]
];