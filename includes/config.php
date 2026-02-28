<?php
/**
 * TrustPick V2 - Configuration
 * Compatible MySQL (InfinityFree, XAMPP) et PostgreSQL (Render)
 * 
 * Ordre de résolution des credentials :
 * 1. Fichier .env (si présent)
 * 2. Détection automatique InfinityFree (hostname contient .infinityfree. ou .ct.ws)
 * 3. Variables d'environnement système
 * 4. Valeurs par défaut développement (XAMPP local)
 */

// Charger la fonction tp_env() depuis un fichier séparé (évite les redéclarations)
require_once __DIR__ . '/env.php';

// ── Détection automatique de l'environnement InfinityFree ──
// Si pas de .env ET qu'on est sur un serveur InfinityFree, utiliser les credentials hardcodés
$isInfinityFree = false;
$serverName = $_SERVER['SERVER_NAME'] ?? $_SERVER['HTTP_HOST'] ?? '';
if (
    strpos($serverName, '.infinityfree.') !== false ||
    strpos($serverName, '.ct.ws') !== false ||
    strpos($serverName, '.epizy.com') !== false ||
    strpos($serverName, '.rf.gd') !== false
) {
    $isInfinityFree = true;
}

// Vérifier si le .env existe réellement
$envFileExists = file_exists(__DIR__ . '/../.env');

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
} elseif ($isInfinityFree && !$envFileExists) {
    // ── FALLBACK InfinityFree : credentials en dur si .env absent ──
    // Ces valeurs proviennent du panel InfinityFree — À REMPLACER par les vraies
    $dbHost = 'sql106.infinityfree.com';
    $dbPort = 3306;
    $dbName = 'if0_41221351_trustpick';
    $dbUser = 'if0_41221351';
    $dbPass = 'IUPuYZ9piT1KGMd';
    $dbDriver = 'mysql';
} else {
    // MySQL standard (.env lu OU local XAMPP)
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

    // Métadonnées utiles pour le debug
    '_env_file_exists' => $envFileExists,
    '_is_infinityfree' => $isInfinityFree,
    '_config_source' => $envFileExists ? '.env' : ($isInfinityFree ? 'infinityfree_fallback' : 'defaults_local'),

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