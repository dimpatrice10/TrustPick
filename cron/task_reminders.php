<?php
/**
 * CRON - Rappels de Tâches
 * À exécuter 2 fois par jour (10h, 16h)
 * 
 * CRON: 0 10,16 * * * php /path/to/TrustPick/cron/task_reminders.php
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/tasks.php';
require_once __DIR__ . '/../includes/notifications.php';

if (php_sapi_name() !== 'cli') {
    die("Ce script doit être exécuté en ligne de commande\n");
}

$startTime = microtime(true);
$logFile = __DIR__ . '/logs/task_reminders.log';

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

writeLog("=== Début des rappels de tâches ===");

try {
    $notifSystem = new NotificationSystem($pdo);

    // Récupérer tous les utilisateurs actifs
    $stmt = $pdo->query("
        SELECT id, name 
        FROM users 
        WHERE is_active = TRUE AND role = 'user'
    ");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    writeLog("Envoi de rappels à " . count($users) . " utilisateurs");

    $sentCount = 0;
    foreach ($users as $user) {
        $result = $notifSystem->sendTaskReminder($user['id']);
        if ($result['success'] && $result['tasks_pending'] > 0) {
            $sentCount++;
            writeLog("  → {$user['name']}: {$result['tasks_pending']} tâche(s) en attente");
        }
    }

    writeLog("✓ Rappels envoyés: {$sentCount}");

} catch (Exception $e) {
    writeLog("✗ Exception: " . $e->getMessage());
}

$executionTime = round(microtime(true) - $startTime, 2);
writeLog("=== Fin des rappels (Durée: {$executionTime}s) ===\n");
