<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/url.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$referral_code = strtoupper(trim($_POST['referral_code'] ?? ''));

if (!$name || !$phone) {
    addToast('error', 'Veuillez remplir tous les champs obligatoires.');
    redirect(url('index.php?page=register'));
}

try {
    $pdo = Database::getInstance()->getConnection();

    // Vérifier si le téléphone existe déjà
    $stmt = $pdo->prepare('SELECT id FROM users WHERE phone = ?');
    $stmt->execute([$phone]);
    if ($stmt->fetch()) {
        addToast('error', 'Ce numéro de téléphone est déjà utilisé.');
        redirect(url('index.php?page=register'));
    }

    // Vérifier le code de parrainage si fourni
    $referrer_id = null;
    if (!empty($referral_code)) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE referral_code = ?');
        $stmt->execute([$referral_code]);
        $referrer = $stmt->fetch();
        if ($referrer) {
            $referrer_id = $referrer['id'];
        } else {
            addToast('error', 'Code de parrainage invalide.');
            redirect(url('index.php?page=register'));
        }
    }

    $pdo->beginTransaction();

    // Générer CAU et code de parrainage uniques
    $auth = new AuthCAU($pdo);
    $cau = $auth->generateCAU('user');
    $my_referral_code = $auth->generateReferralCode();

    // Créer l'utilisateur
    $stmt = $pdo->prepare('
        INSERT INTO users (cau, name, phone, role, balance, referral_code, referred_by, is_active, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, TRUE, NOW())
    ');
    $initialBalance = $referrer_id ? 5000 : 5000; // Bonus de démarrage
    $stmt->execute([$cau, $name, $phone, 'user', $initialBalance, $my_referral_code, $referrer_id]);
    $uid = $pdo->lastInsertId();

    // Transaction pour le nouvel utilisateur
    $pdo->prepare('
        INSERT INTO transactions (user_id, type, amount, description, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ')->execute([$uid, 'reward', $initialBalance, 'Crédit de bienvenue']);

    // Si parrainage, récompenser aussi le parrain
    if ($referrer_id) {
        // Enregistrer le parrainage
        $pdo->prepare('
            INSERT INTO referrals (referrer_id, referred_id, reward_amount, is_rewarded, created_at, rewarded_at)
            VALUES (?, ?, 5000, TRUE, NOW(), NOW())
        ')->execute([$referrer_id, $uid]);

        // Récompenser le parrain: +5 000 FCFA
        $pdo->prepare('UPDATE users SET balance = balance + 5000 WHERE id = ?')->execute([$referrer_id]);
        $pdo->prepare('
            INSERT INTO transactions (user_id, type, amount, description, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ')->execute([$referrer_id, 'reward', 5000, 'Bonus parrainage - Nouvel utilisateur inscrit']);

        // Notification parrain
        $pdo->prepare('
            INSERT INTO notifications (user_id, type, title, message, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ')->execute([
                    $referrer_id,
                    'success',
                    '🎁 Nouveau parrainage !',
                    "Vous avez gagné " . formatFCFA(5000) . " grâce au parrainage de $name !"
                ]);
    }

    // Notification de bienvenue
    $pdo->prepare('
        INSERT INTO notifications (user_id, type, title, message, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ')->execute([
                $uid,
                'success',
                '🎉 Bienvenue sur TrustPick !',
                "Votre code d'accès (CAU) est $cau. Vous commencez avec " . formatFCFA($initialBalance) . " !"
            ]);

    $pdo->commit();

    // Créer la session
    $_SESSION['user_id'] = $uid;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_role'] = 'user';
    $_SESSION['user_cau'] = $cau;

    addToast('success', "Bienvenue $name ! Votre CAU est : <strong>$cau</strong>. Conservez-le précieusement.");
    redirect(url('index.php?page=user_dashboard'));

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    addToast('error', 'Erreur lors de l\'inscription: ' . $e->getMessage());
    redirect(url('index.php?page=register'));
}