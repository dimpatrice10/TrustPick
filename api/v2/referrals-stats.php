<?php
/**
 * API TrustPick V2 - Statistiques de Parrainage
 * GET /api/v2/referrals/stats.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../../includes/db.php';
require_once '../../includes/referrals.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Authentification requise'
    ]);
    exit;
}

$referralSystem = new ReferralSystem($pdo);
$result = $referralSystem->getReferralStats($_SESSION['user_id']);

http_response_code(200);
echo json_encode($result);
