<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../includes/url.php';
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/settings.php';

if (empty($_SESSION['user_id'])) {
    addToast('error', 'Connectez-vous pour faire un retrait.');
    redirect(url('index.php?page=login'));
}

$amount = floatval($_POST['amount'] ?? 0);

// Vérifier montant minimum depuis system_settings
$minWithdrawal = Settings::getInt('min_withdrawal', 5000);
if ($amount < $minWithdrawal) {
    addToast('error', 'Montant minimum de retrait: ' . formatFCFA($minWithdrawal));
    redirect(url('index.php?page=wallet'));
}

if ($amount <= 0) {
    addToast('error', 'Montant invalide.');
    redirect(url('index.php?page=wallet'));
}

try {
    $pdo = Database::getInstance()->getConnection();

    // Vérifier solde
    $stmt = $pdo->prepare('SELECT balance, name FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user || $user['balance'] < $amount) {
        addToast('error', 'Solde insuffisant. Solde actuel: ' . formatFCFA($user['balance'] ?? 0));
        redirect(url('index.php?page=wallet'));
    }

    $pdo->beginTransaction();

    // Créer demande de retrait
    $stmt = $pdo->prepare("INSERT INTO withdrawals (user_id,amount,status,created_at) VALUES (?,?,'pending',NOW())");
    $stmt->execute([$_SESSION['user_id'], $amount]);

    // Débiter le solde
    $pdo->prepare('UPDATE users SET balance = balance - ? WHERE id = ?')->execute([$amount, $_SESSION['user_id']]);

    // Enregistrer transaction
    $stmt = $pdo->prepare("
        INSERT INTO transactions (user_id, type, amount, description, created_at)
        VALUES (?, 'withdrawal', ?, 'Demande de retrait', NOW())
    ");
    $stmt->execute([$_SESSION['user_id'], -$amount]);

    // Créer notification
    $stmt = $pdo->prepare("
        INSERT INTO notifications (user_id, title, message, type, created_at)
        VALUES (?, 'Retrait demandé', ?, 'info', NOW())
    ");
    $msg = 'Votre demande de retrait de ' . formatFCFA($amount) . ' est en cours de traitement.';
    $stmt->execute([$_SESSION['user_id'], $msg]);

    $pdo->commit();

    addToast('success', 'Demande de retrait créée ! ' . formatFCFA($amount) . ' sera traité sous 48h.');
    redirect(url('index.php?page=wallet'));

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    addToast('error', 'Erreur: ' . $e->getMessage());
    redirect(url('index.php?page=wallet'));
}

