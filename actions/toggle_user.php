<?php
/**
 * Action: Activer/Désactiver un utilisateur
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/url.php';

// Vérifier que l'utilisateur est super admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'super_admin') {
    addToast('error', 'Accès interdit.');
    redirect(url('index.php?page=home'));
}

$user_id = (int) ($_POST['user_id'] ?? 0);

if ($user_id <= 0) {
    addToast('error', 'Utilisateur invalide.');
    redirect(url('index.php?page=manage_users'));
}

try {
    $pdo = Database::getInstance()->getConnection();

    // Récupérer l'état actuel
    $stmt = $pdo->prepare('SELECT is_active, name FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        throw new Exception('Utilisateur introuvable.');
    }

    // Inverser l'état
    $new_status = !$user['is_active'];
    $stmt = $pdo->prepare('UPDATE users SET is_active = ? WHERE id = ?');
    $stmt->execute([$new_status, $user_id]);

    $status_text = $new_status ? 'activé' : 'désactivé';
    addToast('success', "L'utilisateur {$user['name']} a été $status_text.");

    redirect(url('index.php?page=manage_users'));

} catch (Exception $e) {
    addToast('error', 'Erreur: ' . $e->getMessage());
    redirect(url('index.php?page=manage_users'));
}
