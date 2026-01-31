<?php
/**
 * API TrustPick V2 - Authentification
 * POST /api/v2/auth/login.php
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
require_once '../../includes/auth.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['cau']) || empty($data['cau'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Le Code d\'Accès Utilisateur (CAU) est requis'
    ]);
    exit;
}

$auth = new AuthCAU($pdo);
$result = $auth->loginWithCAU($data['cau']);

if ($result['success']) {
    http_response_code(200);
    echo json_encode($result);
} else {
    http_response_code(401);
    echo json_encode($result);
}
