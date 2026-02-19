<?php
/**
 * TrustPick V2 - Vérification du statut de paiement
 * Utilisé par la page d'instructions pour vérifier si le paiement est complété
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/payment.php';

// Vérifier connexion
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
}

$reference = $_GET['reference'] ?? '';

if (empty($reference)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Référence manquante']);
    exit;
}

try {
    $paymentManager = new PaymentManager();
    $result = $paymentManager->checkPaymentStatus($reference);

    // Si le paiement est réussi, mettre à jour le solde en session
    if (isset($result['status']) && $result['status'] === 'success') {
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare('SELECT balance FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $newBalance = $stmt->fetchColumn();
        $_SESSION['balance'] = $newBalance;
    }

    echo json_encode($result);

} catch (Exception $e) {
    error_log('Check Payment Status Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur lors de la vérification: ' . $e->getMessage()
    ]);
}
