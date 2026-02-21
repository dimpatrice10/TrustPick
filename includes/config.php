<?php
/**
 * TrustPick V2 - Configuration
 * Lit les variables d'environnement (compatible Docker/Apache/Render)
 */

// Helper: lire une variable d'environnement depuis toutes les sources possibles
if (!function_exists('tp_env')) {
    function tp_env($name, $default = null)
    {
        // 1. getenv() - fonctionne en CLI et parfois en Apache
        $val = getenv($name);
        if ($val !== false)
            return $val;
        // 2. $_ENV - fonctionne quand variables_order contient 'E'
        if (isset($_ENV[$name]))
            return $_ENV[$name];
        // 3. $_SERVER - Apache passe souvent les env vars ici via PassEnv
        if (isset($_SERVER[$name]))
            return $_SERVER[$name];
        // 4. Fallback
        return $default;
    }
}

// Support DATABASE_URL de Render (format: postgresql://user:pass@host:port/dbname)
$databaseUrl = tp_env('DATABASE_URL');
if ($databaseUrl) {
    $dbParts = parse_url($databaseUrl);
    $dbHost = $dbParts['host'] ?? '127.0.0.1';
    $dbPort = $dbParts['port'] ?? 5432;
    $dbName = ltrim($dbParts['path'] ?? '/trustpick_v2', '/');
    $dbUser = $dbParts['user'] ?? 'postgres';
    $dbPass = $dbParts['pass'] ?? '';
} else {
    $dbHost = tp_env('PGHOST', '127.0.0.1');
    $dbPort = tp_env('PGPORT', 5432);
    $dbName = tp_env('PGDATABASE', 'trustpick_v2');
    $dbUser = tp_env('PGUSER', 'root');
    $dbPass = tp_env('PGPASSWORD', '');
}

return [
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
            'api_url' => tp_env('MESOMB_API_URL', 'https://mesomb.hachther.com/api/v1.1'),
            'enabled' => tp_env('MESOMB_ENABLED') !== 'false'
        ],

        'receiving_accounts' => [
            'orange_money' => tp_env('ORANGE_ACCOUNT', '657317490'),
            'mtn_money' => tp_env('MTN_ACCOUNT', '683833646')
        ],

        'min_deposit' => 5000,
        'currency' => 'XAF'
    ]
];