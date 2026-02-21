<?php
/**
 * TrustPick V2 - Action: Recommander un produit
 * Récompense: 200 FCFA par recommandation
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/url.php';
require_once __DIR__ . '/../includes/task_manager.php';

// Vérifier connexion
if (empty($_SESSION['user_id'])) {
    addToast('error', 'Vous devez être connecté pour recommander un produit.');
    redirect(url('index.php?page=login'));
}

$user_id = intval($_SESSION['user_id']);
$product_id = intval($_POST['product_id'] ?? 0);
$contact_info = trim($_POST['contact_info'] ?? '');

// Validation
if ($product_id <= 0) {
    addToast('error', 'Produit invalide.');
    redirect(url('index.php?page=catalog'));
}

if (empty($contact_info)) {
    addToast('error', 'Veuillez entrer un nom, email ou numéro de la personne à qui vous recommandez ce produit.');
    redirect(url('index.php?page=product&id=' . $product_id));
}

try {
    $pdo = Database::getInstance()->getConnection();

    // Vérifier si le produit existe
    $stmt = $pdo->prepare('SELECT id, title FROM products WHERE id = ? AND is_active = TRUE');
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        addToast('error', 'Produit introuvable.');
        redirect(url('index.php?page=catalog'));
    }

    // Vérifier si l'utilisateur a déjà recommandé ce produit aujourd'hui
    $checkStmt = $pdo->prepare('
        SELECT id FROM recommendations 
        WHERE recommender_id = ? AND product_id = ? AND DATE(created_at) = CURRENT_DATE
    ');
    $checkStmt->execute([$user_id, $product_id]);

    if ($checkStmt->fetch()) {
        addToast('error', 'Vous avez déjà recommandé ce produit aujourd\'hui.');
        redirect(url('index.php?page=product&id=' . $product_id));
    }

    // Vérifier si l'utilisateur peut exécuter cette tâche (ordre respecté)
    $canExecute = TaskManager::canExecuteTask($user_id, 'recommend_product', $pdo);

    $pdo->beginTransaction();

    // Enregistrer la recommandation
    $stmt = $pdo->prepare('
        INSERT INTO recommendations (product_id, recommender_id, recommended_to_id, message, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ');
    $stmt->execute([$product_id, $user_id, $user_id, $contact_info]);

    $reward = 200;

    if ($canExecute['can_execute']) {
        // Créditer la récompense
        $pdo->prepare('UPDATE users SET balance = balance + ? WHERE id = ?')
            ->execute([$reward, $user_id]);

        // Récupérer nouveau solde
        $balanceStmt = $pdo->prepare('SELECT balance FROM users WHERE id = ?');
        $balanceStmt->execute([$user_id]);
        $newBalance = $balanceStmt->fetchColumn();

        // Enregistrer la transaction
        $pdo->prepare("
            INSERT INTO transactions (user_id, type, amount, description, reference_type, balance_after, created_at)
            VALUES (?, 'reward', ?, ?, 'recommendation', ?, NOW())
        ")->execute([$user_id, $reward, 'Recommandation produit: ' . $product['title'], $newBalance]);

        // Mettre à jour la session
        $_SESSION['balance'] = $newBalance;

        // Compléter la tâche
        TaskManager::completeTask($user_id, 'recommend_product', $pdo);

        // Créer notification
        $pdo->prepare("
            INSERT INTO notifications (user_id, type, title, message, created_at)
            VALUES (?, 'reward', 'Recommandation envoyée', ?, NOW())
        ")->execute([$user_id, 'Vous avez recommandé "' . $product['title'] . '" et gagné ' . formatFCFA($reward) . ' !']);

        addToast('success', 'Produit recommandé avec succès ! +' . formatFCFA($reward) . ' crédités.');
    } else {
        // Recommandation enregistrée mais pas de récompense
        addToast('warning', $canExecute['message']);
    }

    $pdo->commit();

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    addToast('error', 'Erreur: ' . $e->getMessage());
}

redirect(url('index.php?page=product&id=' . $product_id));
