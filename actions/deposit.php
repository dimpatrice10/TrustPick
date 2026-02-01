<?php
/**
 * TrustPick V2 - Action: Dépôt d'argent
 * Tâche obligatoire: dépôt minimum 5000 FCFA
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/url.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/task_manager.php';

// Vérifier connexion
if (empty($_SESSION['user_id'])) {
    addToast('error', 'Connectez-vous pour effectuer un dépôt.');
    redirect(url('index.php?page=login'));
}

$user_id = intval($_SESSION['user_id']);
$amount = intval($_POST['amount'] ?? 0);
$payment_method = trim($_POST['method'] ?? $_POST['payment_method'] ?? 'mobile_money');
$phone_number = trim($_POST['phone'] ?? $_POST['phone_number'] ?? '');

$minDeposit = 5000; // Minimum 5000 FCFA

if ($amount < $minDeposit) {
    addToast('error', 'Le montant minimum de dépôt est de ' . formatFCFA($minDeposit) . '.');
    redirect(url('index.php?page=wallet'));
}

try {
    $pdo = Database::getInstance()->getConnection();
    $pdo->beginTransaction();

    // Créditer le compte utilisateur
    $pdo->prepare('UPDATE users SET balance = balance + ? WHERE id = ?')
        ->execute([$amount, $user_id]);

    // Récupérer le nouveau solde
    $balanceStmt = $pdo->prepare('SELECT balance FROM users WHERE id = ?');
    $balanceStmt->execute([$user_id]);
    $newBalance = $balanceStmt->fetchColumn();

    // Enregistrer la transaction
    $pdo->prepare('
        INSERT INTO transactions (user_id, type, amount, description, reference_type, balance_after, created_at)
        VALUES (?, "bonus", ?, ?, "deposit", ?, NOW())
    ')->execute([$user_id, $amount, 'Dépôt via ' . $payment_method, $newBalance]);

    // Vérifier si c'est le premier dépôt >= 5000 aujourd'hui (pour la tâche)
    $checkTask = TaskManager::isTaskCompletedToday($user_id, 'deposit_5000', $pdo);

    if (!$checkTask && $amount >= $minDeposit) {
        // Compléter la tâche dépôt
        TaskManager::completeTask($user_id, 'deposit_5000', $pdo);

        // Notification
        $pdo->prepare('
            INSERT INTO notifications (user_id, type, title, message, created_at)
            VALUES (?, "reward", "Tâche complétée", ?, NOW())
        ')->execute([$user_id, 'Vous avez effectué un dépôt de ' . formatFCFA($amount) . '. Tâche quotidienne validée !']);
    }

    // Mettre à jour la session
    $_SESSION['balance'] = $newBalance;

    $pdo->commit();

    addToast('success', 'Dépôt de ' . formatFCFA($amount) . ' effectué avec succès !');

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    addToast('error', 'Erreur lors du dépôt: ' . $e->getMessage());
}

redirect(url('index.php?page=wallet'));
