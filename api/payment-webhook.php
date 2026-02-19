<?php
/**
 * TrustPick V2 - Webhook MeSomb
 * Reçoit les notifications de paiement de MeSomb
 * 
 * URL du webhook à configurer dans MeSomb:
 * https://votre-domaine.com/api/payment-webhook.php
 */

// Désactiver l'affichage des erreurs (production)
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Log des erreurs dans un fichier
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/webhook_errors.log');

// Headers pour JSON
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/payment.php';

/**
 * Logger pour webhook
 */
function logWebhook($message, $data = null)
{
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }

    $logFile = $logDir . '/webhook_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message";

    if ($data !== null) {
        $logMessage .= "\n" . json_encode($data, JSON_PRETTY_PRINT);
    }

    $logMessage .= "\n" . str_repeat('-', 80) . "\n";

    @file_put_contents($logFile, $logMessage, FILE_APPEND);
}

try {
    // Récupérer les données brutes du webhook
    $rawInput = file_get_contents('php://input');
    logWebhook('Webhook reçu - Données brutes:', $rawInput);

    // Méthode HTTP attendue: POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        logWebhook('Erreur: Méthode non autorisée - ' . $_SERVER['REQUEST_METHOD']);
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        exit;
    }

    // Décoder le JSON
    $webhookData = json_decode($rawInput, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        logWebhook('Erreur: JSON invalide - ' . json_last_error_msg());
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON']);
        exit;
    }

    logWebhook('Webhook décodé:', $webhookData);

    // Validation du webhook MeSomb
    // MeSomb ne fournit pas de webhook_secret séparé.
    // La validation se fait en vérifiant la transaction via l'API MeSomb.
    $config = require __DIR__ . '/../includes/config.php';

    // Vérification basique: le webhook doit contenir une référence connue
    // La confirmation définitive se fait via l'API MeSomb (dans processWebhook)

    // Extraire les informations essentielles
    $reference = $webhookData['reference'] ?? null;
    $status = $webhookData['status'] ?? null;
    $transactionId = $webhookData['transaction']['pk'] ?? null;

    if (!$reference) {
        logWebhook('Erreur: Référence manquante dans le webhook');
        http_response_code(400);
        echo json_encode(['error' => 'Missing reference']);
        exit;
    }

    logWebhook("Traitement pour référence: $reference, statut: $status, transaction: $transactionId");

    // Traiter via PaymentManager
    $paymentManager = new PaymentManager();
    $result = $paymentManager->processWebhook($webhookData);

    if ($result) {
        logWebhook("Webhook traité avec succès pour $reference");

        // Réponse de succès à MeSomb
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Webhook processed successfully',
            'reference' => $reference
        ]);
    } else {
        logWebhook("Échec du traitement du webhook pour $reference");

        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to process webhook',
            'reference' => $reference
        ]);
    }

} catch (Exception $e) {
    logWebhook('Exception: ' . $e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ]);
}