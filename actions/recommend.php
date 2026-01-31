<?php
/**
 * Action: Recommander un produit
 * Récompense: 200 FCFA par recommandation (task_id 4)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/url.php';

// Vérifier connexion
if (!isLoggedIn()) {
    addToast('error', 'Vous devez être connecté pour recommander un produit.');
    redirect(url('index.php?page=login'));
}

$user_id = $_SESSION['user_id'];
$product_id = (int) ($_POST['product_id'] ?? 0);
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
    $pdo->beginTransaction();

    // Vérifier si le produit existe
    $stmt = $pdo->prepare('SELECT name FROM products WHERE id = ?');
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        throw new Exception('Produit introuvable.');
    }

    // Créer la recommandation
    $stmt = $pdo->prepare('
        INSERT INTO referrals (referrer_id, referred_email, status, created_at)
        VALUES (?, ?, "pending", NOW())
    ');
    $stmt->execute([$user_id, $contact_info]);

    // Vérifier si tâche recommandation existe (task_id 4)
    $stmt = $pdo->prepare('SELECT id, reward_fcfa FROM tasks_definitions WHERE id = 4');
    $stmt->execute();
    $task = $stmt->fetch();

    $reward = $task ? $task['reward_fcfa'] : 200; // Default 200 FCFA

    // Créditer immédiatement l'utilisateur
    $stmt = $pdo->prepare('UPDATE users SET balance = balance + ? WHERE id = ?');
    $stmt->execute([$reward, $user_id]);

    // Enregistrer la transaction
    $stmt = $pdo->prepare('
        INSERT INTO transactions (user_id, type, amount, description, created_at)
        VALUES (?, "reward", ?, ?, NOW())
    ');
    $description = 'Recommandation produit: ' . $product['name'];
    $stmt->execute([$user_id, $reward, $description]);

    // Enregistrer la validation de tâche
    $stmt = $pdo->prepare('
        INSERT INTO user_tasks (user_id, task_id, status, validated_at, reward_fcfa, created_at)
        VALUES (?, 4, "approved", NOW(), ?, NOW())
    ');
    $stmt->execute([$user_id, $reward]);

    // Créer une notification
    $stmt = $pdo->prepare('
        INSERT INTO notifications (user_id, title, message, type, created_at)
        VALUES (?, "Recommandation envoyée", ?, "success", NOW())
    ');
    $notif_message = 'Vous avez recommandé "' . $product['name'] . '" à ' . $contact_info . '. +' . formatFCFA($reward) . ' crédités !';
    $stmt->execute([$user_id, $notif_message]);

    $pdo->commit();

    addToast('success', 'Recommandation envoyée ! +' . formatFCFA($reward) . ' ajoutés à votre solde.');
    redirect(url('index.php?page=product&id=' . $product_id));

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    addToast('error', 'Erreur: ' . $e->getMessage());
    redirect(url('index.php?page=product&id=' . $product_id));
}
