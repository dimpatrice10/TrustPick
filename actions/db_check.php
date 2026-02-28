<?php
/**
 * TrustPick V2 - Diagnostic de connexion DB (Super Admin uniquement)
 * URL: actions/db_check.php
 * 
 * Affiche la source de config utilisée, le host résolu, et teste la connexion.
 * Ne révèle JAMAIS le mot de passe.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

// Sécurité : Super Admin uniquement (ou pas de session = debug temporaire avec token)
$debugToken = $_GET['token'] ?? '';
$isSuperAdmin = ($_SESSION['user_role'] ?? '') === 'super_admin';
$isValidToken = $debugToken === 'tp_diag_2026';

if (!$isSuperAdmin && !$isValidToken) {
    http_response_code(403);
    echo json_encode(['error' => 'Accès refusé.']);
    exit;
}

$result = [
    'timestamp' => date('Y-m-d H:i:s'),
    'server_name' => $_SERVER['SERVER_NAME'] ?? '(unknown)',
    'http_host' => $_SERVER['HTTP_HOST'] ?? '(unknown)',
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? '(unknown)',
];

// Charger la config
try {
    require_once __DIR__ . '/../includes/env.php';
    $config = require __DIR__ . '/../includes/config.php';

    $result['config'] = [
        'source' => $config['_config_source'] ?? 'unknown',
        'env_file_exists' => $config['_env_file_exists'] ?? false,
        'is_infinityfree' => $config['_is_infinityfree'] ?? false,
        'db_driver' => $config['db_driver'],
        'db_host' => $config['db_host'],
        'db_port' => $config['db_port'],
        'db_name' => $config['db_name'],
        'db_user' => $config['db_user'],
        'db_pass' => str_repeat('*', strlen($config['db_pass'] ?? '')),  // Masqué
    ];

    // Tester la connexion
    $dsn = "mysql:host={$config['db_host']};port={$config['db_port']};dbname={$config['db_name']};charset=utf8mb4";
    $startTime = microtime(true);
    $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5,
    ]);
    $connectTime = round((microtime(true) - $startTime) * 1000, 1);

    $result['connection'] = [
        'status' => 'OK',
        'connect_time_ms' => $connectTime,
        'server_version' => $pdo->getAttribute(PDO::ATTR_SERVER_VERSION),
    ];

    // Vérifier quelques tables clés
    $tables = ['users', 'reviews', 'review_reactions', 'review_likes', 'tasks_definitions', 'notifications', 'transactions'];
    $tableStatus = [];
    foreach ($tables as $table) {
        try {
            $count = $pdo->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
            $tableStatus[$table] = ['exists' => true, 'rows' => intval($count)];
        } catch (Exception $e) {
            $tableStatus[$table] = ['exists' => false, 'error' => $e->getMessage()];
        }
    }
    $result['tables'] = $tableStatus;

} catch (PDOException $e) {
    $result['connection'] = [
        'status' => 'FAILED',
        'error' => $e->getMessage(),
        'code' => $e->getCode(),
    ];
} catch (Exception $e) {
    $result['error'] = $e->getMessage();
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);