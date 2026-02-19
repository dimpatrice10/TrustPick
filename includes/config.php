<?php
// Support DATABASE_URL de Render (format: postgresql://user:pass@host:port/dbname)
$databaseUrl = getenv('DATABASE_URL');
if ($databaseUrl) {
    $dbParts = parse_url($databaseUrl);
    $dbHost = $dbParts['host'] ?? '127.0.0.1';
    $dbPort = $dbParts['port'] ?? 5432;
    $dbName = ltrim($dbParts['path'] ?? '/trustpick_v2', '/');
    $dbUser = $dbParts['user'] ?? 'postgres';
    $dbPass = $dbParts['pass'] ?? '';
} else {
    $dbHost = getenv('PGHOST') ?: '127.0.0.1';
    $dbPort = getenv('PGPORT') ?: 5432;
    $dbName = getenv('PGDATABASE') ?: 'trustpick_v2';
    $dbUser = getenv('PGUSER') ?: 'root';
    $dbPass = getenv('PGPASSWORD') ?: '';
}

return [
    'db_host' => $dbHost,
    'db_name' => $dbName,
    'db_user' => $dbUser,
    'db_pass' => $dbPass,
    'db_port' => $dbPort,

    'payment' => [
        'mesomb' => [
            'application_key' => getenv('MESOMB_APP_KEY') ?: '18bfc8002ab9555601c82fcb07e2817e221dad36',
            'access_key' => getenv('MESOMB_ACCESS_KEY') ?: '5c63e664-2993-4f11-9cea-54347347a307',
            'secret_key' => getenv('MESOMB_SECRET_KEY') ?: 'd68f6eb3-9a8b-4315-8228-587d6f25c2a4',
            'api_url' => getenv('MESOMB_API_URL') ?: 'https://mesomb.hachther.com/api/v1.1',
            'enabled' => getenv('MESOMB_ENABLED') !== 'false'
        ],

        'receiving_accounts' => [
            'orange_money' => getenv('ORANGE_ACCOUNT') ?: '657317490',
            'mtn_money' => getenv('MTN_ACCOUNT') ?: '683833646'
        ],

        'min_deposit' => 5000,
        'currency' => 'XAF'
    ]
];