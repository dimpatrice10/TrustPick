<?php
/**
 * TrustPick V2 - Action: Dépôt d'argent
 * Paiement Mobile Money via MeSomb (Orange Money & MTN Mobile Money)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/url.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/payment.php';

// Vérifier connexion
if (empty($_SESSION['user_id'])) {
    addToast('error', 'Connectez-vous pour effectuer un dépôt.');
    redirect(url('index.php?page=login'));
}

$user_id = intval($_SESSION['user_id']);
$amount = intval($_POST['amount'] ?? 0);
$channel = strtoupper(trim($_POST['channel'] ?? 'ORANGE')); // ORANGE ou MTN
$phone = trim($_POST['phone'] ?? '');

// Validation
if (empty($phone)) {
    addToast('error', 'Veuillez fournir votre numéro de téléphone.');
    redirect(url('index.php?page=wallet'));
}

if (!in_array($channel, ['ORANGE', 'MTN'])) {
    addToast('error', 'Opérateur de paiement invalide.');
    redirect(url('index.php?page=wallet'));
}

try {
    $paymentManager = new PaymentManager();

    // Initier le paiement via MeSomb
    $result = $paymentManager->initiatePayment($user_id, $amount, $phone, $channel);

    if ($result['success']) {
        // Stocker les infos en session pour la page d'instructions
        $_SESSION['payment_reference'] = $result['reference'];
        $_SESSION['payment_amount'] = $amount;
        $_SESSION['payment_channel'] = strtolower($channel);
        $_SESSION['payment_phone'] = $phone;
        $_SESSION['payment_status'] = $result['status'] ?? 'pending';

        // Si le paiement nécessite USSD, rediriger vers les instructions
        if (isset($result['requires_ussd']) && $result['requires_ussd'] === true) {
            addToast('info', 'Veuillez suivre les instructions pour confirmer votre paiement.');
            redirect(url('index.php?page=payment_instructions'));
        } else {
            // Paiement immédiat (rare avec MeSomb, mais possible)
            addToast('success', 'Paiement de ' . formatFCFA($amount) . ' effectué avec succès !');
            redirect(url('index.php?page=wallet'));
        }
    } else {
        // Échec de l'initialisation
        addToast('error', $result['message'] ?? 'Échec de l\'initialisation du paiement.');
        redirect(url('index.php?page=wallet'));
    }

} catch (Exception $e) {
    error_log('Deposit Error: ' . $e->getMessage());
    addToast('error', 'Erreur lors du dépôt: ' . $e->getMessage());
    redirect(url('index.php?page=wallet'));
}
