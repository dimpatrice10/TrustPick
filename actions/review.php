<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../includes/url.php';
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/helpers.php';

if (empty($_SESSION['user_id'])) {
    addToast('error', 'Connectez-vous pour poster un avis.');
    redirect(url('index.php?page=login'));
}

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
    $checkStmt->execute([$_SESSION['user_id'], $product_id]);
    if ($checkStmt->fetch()) {
        addToast('error', 'Vous avez déjà publié un avis sur ce produit.');
        redirect(url('index.php?page=product&id=' . $product_id));
    }

    $pdo->beginTransaction();

    // Créer l'avis
    $stmt = $pdo->prepare('INSERT INTO reviews (product_id,user_id,rating,title,body,created_at) VALUES (?,?,?,?,?,NOW())');
    $stmt->execute([$product_id, $_SESSION['user_id'], $rating, $title, $body]);

    // Récompense pour avis: 500 FCFA
    $pdo->prepare('UPDATE users SET balance = balance + 500 WHERE id = ?')->execute([$_SESSION['user_id']]);

    // Enregistrer transaction
    $stmt = $pdo->prepare('
        INSERT INTO transactions (user_id, type, amount, description, created_at)
        VALUES (?, "reward", 500, "Avis posté sur produit", NOW())
    ');
    $stmt->execute([$_SESSION['user_id']]);

    // Créer notification
    $stmt = $pdo->prepare('
        INSERT INTO notifications (user_id, title, message, type, created_at)
        VALUES (?, "Avis publié", ?, "success", NOW())
    ');
    $stmt->execute([$_SESSION['user_id'], 'Merci pour votre avis ! +' . formatFCFA(500) . ' crédités.']);

    $pdo->commit();

    addToast('success', 'Avis publié avec succès ! +' . formatFCFA(500) . ' ajoutés à votre solde.');
    redirect(url('index.php?page=product&id=' . $product_id));

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    addToast('error', 'Erreur lors de la publication: ' . $e->getMessage());
    redirect(url('index.php?page=product&id=' . $product_id));
}

