<?php
/**
 * CRON - Notifications Quotidiennes
 * À exécuter 2 fois par jour (9h, 18h)
 * 
 * CRON: 0 9,18 * * * php /path/to/TrustPick/cron/daily_notifications.php
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/notifications.php';

if (php_sapi_name() !== 'cli') {
    die("Ce script doit être exécuté en ligne de commande\n");
}

$startTime = microtime(true);
$logFile = __DIR__ . '/logs/notifications.log';

if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

function writeLog($message)
{
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
    echo "[$timestamp] $message\n";
}

writeLog("=== Début de la génération de notifications ===");

try {
    $notifSystem = new NotificationSystem($pdo);

    $result = $notifSystem->generateDailyNotifications();

    if ($result['success']) {
        writeLog("✓ Succès: {$result['notifications_generated']} notifications envoyées");
    } else {
        writeLog("✗ Erreur: " . $result['message']);
    }

} catch (Exception $e) {
    writeLog("✗ Exception: " . $e->getMessage());
}

$executionTime = round(microtime(true) - $startTime, 2);
writeLog("=== Fin de la génération (Durée: {$executionTime}s) ===\n");
