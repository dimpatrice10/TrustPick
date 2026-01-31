<?php
/**
 * API TrustPick V2 - Tâches Disponibles
 * GET /api/v2/tasks/available.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../../includes/db.php';
require_once '../../includes/tasks.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Authentification requise'
    ]);
    exit;
}

$taskSystem = new TaskSystem($pdo);
$result = $taskSystem->getAvailableTasks($_SESSION['user_id']);

http_response_code(200);
echo json_encode($result);
