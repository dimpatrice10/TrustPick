<?php
/**
 * API TrustPick V2 - Compléter une Tâche
 * POST /api/v2/tasks/complete.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['task_code'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Le code de tâche est requis'
    ]);
    exit;
}

$taskSystem = new TaskSystem($pdo);
$result = $taskSystem->completeTask(
    $_SESSION['user_id'],
    $data['task_code'],
    $data['reference_id'] ?? null,
    $data['reference_type'] ?? null
);

if ($result['success']) {
    http_response_code(200);
} else {
    http_response_code(400);
}

echo json_encode($result);
