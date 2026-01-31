<?php
/**
 * API TrustPick V2 - Notifications
 * GET /api/v2/notifications/list.php?page=1&unread_only=1
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../../includes/db.php';
require_once '../../includes/pagination.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Authentification requise'
    ]);
    exit;
}

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$unreadOnly = isset($_GET['unread_only']) && $_GET['unread_only'] == '1';

$pagination = new SmartPagination($pdo, 5);
$result = $pagination->paginateNotifications($_SESSION['user_id'], $page, $unreadOnly);

http_response_code(200);
echo json_encode($result);
