<?php
/**
 * Action: Créer un utilisateur (Admin uniquement)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/url.php';

// Vérifier que l'utilisateur est super admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'super_admin') {
    addToast('error', 'Accès interdit. Vous devez être super admin.');
    redirect(url('index.php?page=home'));
}

$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$role = trim($_POST['role'] ?? 'user');
$balance = (float) ($_POST['balance'] ?? 5000);

// Validation
if (empty($name) || empty($phone)) {
    addToast('error', 'Le nom et le téléphone sont obligatoires.');
    redirect(url('index.php?page=manage_users'));
}

if (!in_array($role, ['user', 'admin_entreprise', 'super_admin'])) {
    addToast('error', 'Rôle invalide.');
    redirect(url('index.php?page=manage_users'));
}

try {
    $pdo = Database::getInstance()->getConnection();
    $pdo->beginTransaction();

    // Générer CAU
    $auth = new AuthCAU($pdo);
    $cau = $auth->generateCAU($role);

    // Créer l'utilisateur
    $stmt = $pdo->prepare('
        INSERT INTO users (name, phone, cau, role, balance, referral_code, is_active, created_at)
        VALUES (?, ?, ?, ?, ?, ?, TRUE, NOW())
    ');

    $referral_code = $auth->generateReferralCode();
    $stmt->execute([$name, $phone, $cau, $role, $balance, $referral_code]);

    $user_id = $pdo->lastInsertId();

    // Si solde initial > 0, créer transaction
    if ($balance > 0) {
        $stmt = $pdo->prepare('
            INSERT INTO transactions (user_id, type, amount, description, created_at)
            VALUES (?, "reward", ?, "Crédit initial de bienvenue", NOW())
        ');
        $stmt->execute([$user_id, $balance]);
    }

    $pdo->commit();

    // Afficher le CAU dans un toast spécial
    addToast('success', "Utilisateur créé ! CAU: <strong>$cau</strong> - Communiquez ce code à l'utilisateur pour qu'il se connecte.");

    redirect(url('index.php?page=manage_users'));

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    addToast('error', 'Erreur lors de la création: ' . $e->getMessage());
    redirect(url('index.php?page=manage_users'));
}