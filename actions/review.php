<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../includes/url.php';
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/task_manager.php';

if (empty($_SESSION['user_id'])) {
    addToast('error', 'Connectez-vous pour poster un avis.');
    redirect(url('index.php?page=login'));
}

$user_id = intval($_SESSION['user_id']);
$product_id = intval($_POST['product_id'] ?? 0);
$rating = intval($_POST['rating'] ?? 5);
$title = trim($_POST['title'] ?? '');
$body = trim($_POST['body'] ?? '');

if (!$product_id || $rating < 1 || $rating > 5) {
    addToast('error', 'Données invalides. Vérifiez la note (1-5) et le produit.');
    redirect(url('index.php?page=product&id=' . $product_id));
}

try {
    $pdo = Database::getInstance()->getConnection();

    // Vérifier si l'utilisateur a déjà posté un avis sur ce produit
    $checkStmt = $pdo->prepare('SELECT id FROM reviews WHERE user_id = ? AND product_id = ?');
    $checkStmt->execute([$user_id, $product_id]);
    if ($checkStmt->fetch()) {
        addToast('error', 'Vous avez déjà publié un avis sur ce produit.');
        redirect(url('index.php?page=product&id=' . $product_id));
    }

    // Vérifier si l'utilisateur peut exécuter cette tâche (ordre respecté)
    $canExecute = TaskManager::canExecuteTask($user_id, 'leave_review', $pdo);

    $pdo->beginTransaction();

    // Créer l'avis
    $stmt = $pdo->prepare('INSERT INTO reviews (product_id,user_id,rating,title,body,created_at) VALUES (?,?,?,?,?,NOW())');
    $stmt->execute([$product_id, $user_id, $rating, $title, $body]);

    $reward = 500;
    $message = 'Avis publié avec succès !';

    if ($canExecute['can_execute']) {
        // Récompense pour avis: 500 FCFA
        $pdo->prepare('UPDATE users SET balance = balance + ? WHERE id = ?')->execute([$reward, $user_id]);

        // Récupérer nouveau solde
        $balanceStmt = $pdo->prepare('SELECT balance FROM users WHERE id = ?');
        $balanceStmt->execute([$user_id]);
        $newBalance = $balanceStmt->fetchColumn();

        // Enregistrer transaction avec balance_after
        $stmt = $pdo->prepare("
            INSERT INTO transactions (user_id, type, amount, description, reference_type, balance_after, created_at)
            VALUES (?, 'reward', ?, 'Avis posté sur produit', 'review', ?, NOW())
        ");
        $stmt->execute([$user_id, $reward, $newBalance]);

        // Compléter la tâche
        TaskManager::completeTask($user_id, 'leave_review', $pdo);

        // Mettre à jour la session
        $_SESSION['balance'] = $newBalance;

        // Créer notification
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, title, message, type, created_at)
            VALUES (?, 'Avis publié', ?, 'reward', NOW())
        ");
        $stmt->execute([$user_id, 'Merci pour votre avis ! +' . formatFCFA($reward) . ' crédités.']);

        $message .= ' +' . formatFCFA($reward) . ' ajoutés à votre solde.';
        addToast('success', $message);
    } else {
        // Avis publié mais pas de récompense (tâches précédentes non complétées)
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, title, message, type, created_at)
            VALUES (?, 'Avis publié', ?, 'system', NOW())
        ");
        $stmt->execute([$user_id, 'Votre avis a été publié. ' . $canExecute['message']]);

        addToast('warning', $message . ' ' . $canExecute['message']);
    }

    $pdo->commit();

    redirect(url('index.php?page=product&id=' . $product_id));

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    addToast('error', 'Erreur lors de la publication: ' . $e->getMessage());
    redirect(url('index.php?page=product&id=' . $product_id));
}