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

    // Créer l'avis (toujours autorisé, avec ou sans récompense)
    $stmt = $pdo->prepare('INSERT INTO reviews (product_id,user_id,rating,title,body,created_at) VALUES (?,?,?,?,?,NOW())');
    $stmt->execute([$product_id, $user_id, $rating, $title, $body]);

    // Vérifier si l'utilisateur peut exécuter cette tâche pour la récompense
    $canExecute = TaskManager::canExecuteTask($user_id, 'leave_review', $pdo);

    if ($canExecute['can_execute']) {
        // Compléter la tâche via TaskManager (qui gère sa propre transaction et récompense)
        $result = TaskManager::completeTask($user_id, 'leave_review', $pdo);
        
        if ($result['success']) {
            addToast('success', 'Avis publié avec succès ! +' . formatFCFA(200) . ' ajoutés à votre solde.');
        } else {
            addToast('success', 'Avis publié avec succès !');
        }
    } else {
        // Avis publié mais sans récompense de tâche
        addToast('success', 'Avis publié avec succès ! ' . $canExecute['message']);
    }

    redirect(url('index.php?page=product&id=' . $product_id));

} catch (Exception $e) {
    addToast('error', 'Erreur lors de la publication: ' . $e->getMessage());
    redirect(url('index.php?page=product&id=' . $product_id));
}