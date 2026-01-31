<?php
// Démarrage sécurisé de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../includes/url.php';
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/helpers.php';

$cau = strtoupper(trim($_POST['cau'] ?? ''));

if (empty($cau)) {
    addToast('error', 'Veuillez entrer votre Code d\'Accès Utilisateur (CAU).');
    redirect(url('index.php?page=login'));
}

// Vérifier le CAU dans la base de données
$stmt = $pdo->prepare('SELECT * FROM users WHERE cau = ? AND is_active = TRUE');
$stmt->execute([$cau]);
$user = $stmt->fetch();

if (!$user) {
    addToast('error', 'CAU invalide ou compte inactif. Vérifiez votre code.');
    redirect(url('index.php?page=login'));
}

// Connexion réussie - créer la session
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['user_role'] = $user['role'];
$_SESSION['cau'] = $user['cau'];
$_SESSION['company_id'] = $user['company_id'] ?? null;
$_SESSION['balance'] = $user['balance'] ?? 0;
$_SESSION['referral_code'] = $user['referral_code'] ?? null;
$_SESSION['login_time'] = time();
$_SESSION['last_activity'] = time();

// Mettre à jour last_login
$pdo->prepare('UPDATE users SET last_login = NOW() WHERE id = ?')->execute([$user['id']]);

// Enregistrer dans login_history
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
$pdo->prepare('INSERT INTO login_history (user_id, ip_address, user_agent) VALUES (?, ?, ?)')
    ->execute([$user['id'], $ip, $user_agent]);

// Toast de bienvenue
addToast('success', 'Bienvenue ' . $user['name'] . ' ! Connexion réussie.');

// Redirection selon le rôle
switch ($user['role']) {
    case 'super_admin':
        redirect(path: url('index.php?page=superadmin_dashboard'));
        break;
    case 'admin_entreprise':
        redirect(url('index.php?page=admin_dashboard'));
        break;
    default:
        redirect(url('index.php?page=home'));
}

