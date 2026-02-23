<?php
/**
 * Page Profil Utilisateur - TrustPick V2
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/url.php';
require_once __DIR__ . '/../includes/helpers.php';

if (session_status() === PHP_SESSION_NONE)
    session_start();

if (empty($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Veuillez vous connecter pour accéder à votre profil.';
    header('Location: ' . url('index.php?page=login'));
    exit;
}

$uid = intval($_SESSION['user_id']);
$successMsg = '';
$errorMsg = '';

// Récupérer les infos utilisateur
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$uid]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['error'] = 'Utilisateur non trouvé.';
    header('Location: ' . url('index.php?page=login'));
    exit;
}

// Traitement du formulaire de mise à jour
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if (empty($name)) {
            $errorMsg = 'Le nom est obligatoire.';
        } else {
            $updateStmt = $pdo->prepare('UPDATE users SET name = ?, phone = ? WHERE id = ?');
            $updateStmt->execute([$name, $phone, $uid]);
            $_SESSION['user_name'] = $name;
            $successMsg = 'Profil mis à jour avec succès !';

            // Recharger les données
            $stmt->execute([$uid]);
            $user = $stmt->fetch();
        }
    }
}

// Statistiques utilisateur
$reviewCount = 0;
try {
    $revStmt = $pdo->prepare('SELECT COUNT(*) FROM reviews WHERE user_id = ?');
    $revStmt->execute([$uid]);
    $reviewCount = intval($revStmt->fetchColumn());
} catch (Exception $e) {
}

$referralCount = 0;
try {
    $refStmt = $pdo->prepare('SELECT COUNT(*) FROM referrals WHERE referrer_id = ?');
    $refStmt->execute([$uid]);
    $referralCount = intval($refStmt->fetchColumn());
} catch (Exception $e) {
}

$taskCount = 0;
try {
    $taskStmt = $pdo->prepare('SELECT COUNT(*) FROM user_tasks WHERE user_id = ?');
    $taskStmt->execute([$uid]);
    $taskCount = intval($taskStmt->fetchColumn());
} catch (Exception $e) {
}

$memberSince = date('d/m/Y', strtotime($user['created_at'] ?? 'now'));

// Rôle lisible
$roleLabels = [
    'user' => 'Utilisateur',
    'admin_entreprise' => 'Admin Entreprise',
    'super_admin' => 'Super Admin'
];
$userRole = $user['role'] ?? 'user';
$roleLabel = $roleLabels[$userRole] ?? 'Utilisateur';
?>

<main class="container py-4">
    <!-- Messages -->
    <?php if ($successMsg): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($successMsg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($errorMsg): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($errorMsg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- En-tête profil -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #0066cc 0%, #1ab991 100%);">
                <div class="card-body text-white py-4">
                    <div class="d-flex align-items-center flex-wrap gap-3">
                        <div class="rounded-circle bg-white d-flex align-items-center justify-content-center"
                            style="width:80px;height:80px">
                            <i class="bi bi-person-fill text-primary" style="font-size:2.5rem"></i>
                        </div>
                        <div>
                            <h1 class="h3 mb-1"><?= htmlspecialchars($user['name'] ?? '') ?></h1>
                            <p class="mb-0 opacity-75">CAU: <?= htmlspecialchars($user['cau'] ?? '') ?></p>
                            <span class="badge bg-white text-primary mt-2"><?= $roleLabel ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-3 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100 text-center py-3">
                <div class="card-body">
                    <div class="display-6 text-primary mb-2"><i class="bi bi-wallet2"></i></div>
                    <h3 class="mb-0"><?= formatFCFA($user['balance'] ?? 0) ?></h3>
                    <small class="text-muted">Solde</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100 text-center py-3">
                <div class="card-body">
                    <div class="display-6 text-warning mb-2"><i class="bi bi-star-fill"></i></div>
                    <h3 class="mb-0"><?= number_format($reviewCount) ?></h3>
                    <small class="text-muted">Avis donnés</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100 text-center py-3">
                <div class="card-body">
                    <div class="display-6 text-success mb-2"><i class="bi bi-people-fill"></i></div>
                    <h3 class="mb-0"><?= number_format($referralCount) ?></h3>
                    <small class="text-muted">Filleuls</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100 text-center py-3">
                <div class="card-body">
                    <div class="display-6 text-info mb-2"><i class="bi bi-check-circle-fill"></i></div>
                    <h3 class="mb-0"><?= number_format($taskCount) ?></h3>
                    <small class="text-muted">Tâches</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Formulaire profil -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Informations personnelles</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="update_profile">

                        <div class="mb-3">
                            <label class="form-label">Code d'Accès Utilisateur (CAU)</label>
                            <input type="text" class="form-control bg-light"
                                value="<?= htmlspecialchars($user['cau'] ?? '') ?>" readonly>
                            <small class="text-muted">Votre identifiant unique - non modifiable</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nom complet <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control"
                                value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Téléphone</label>
                            <input type="tel" name="phone" class="form-control"
                                value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+237 6XX XXX XXX">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Code de parrainage</label>
                            <div class="input-group">
                                <input type="text" class="form-control bg-light"
                                    value="<?= htmlspecialchars($user['referral_code'] ?? 'N/A') ?>" readonly>
                                <button type="button" class="btn btn-outline-primary" onclick="copyReferralCode()">
                                    <i class="bi bi-clipboard"></i> Copier
                                </button>
                            </div>
                            <small class="text-muted">Partagez ce code pour parrainer des amis</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Membre depuis</label>
                            <input type="text" class="form-control bg-light" value="<?= $memberSince ?>" readonly>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-save me-2"></i>Enregistrer les modifications
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Liens rapides (pas de mot de passe dans CAU) -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informations du compte</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted">Rôle</label>
                        <p class="mb-0 fw-bold"><?= $roleLabel ?></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Statut</label>
                        <p class="mb-0">
                            <?php if ($user['is_active'] ?? true): ?>
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Actif</span>
                            <?php else: ?>
                                <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Inactif</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Dernière connexion</label>
                        <p class="mb-0">
                            <?= $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'N/A' ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Liens rapides -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0"><i class="bi bi-lightning-fill me-2"></i>Accès rapides</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="<?= url('index.php?page=user_dashboard') ?>" class="btn btn-outline-primary">
                            <i class="bi bi-speedometer2 me-2"></i>Mon Dashboard
                        </a>
                        <a href="<?= url('index.php?page=wallet') ?>" class="btn btn-outline-success">
                            <i class="bi bi-wallet2 me-2"></i>Mon Portefeuille
                        </a>
                        <a href="<?= url('index.php?page=referrals') ?>" class="btn btn-outline-info">
                            <i class="bi bi-people-fill me-2"></i>Mes Parrainages
                        </a>
                        <a href="<?= url('index.php?page=tasks') ?>" class="btn btn-outline-warning">
                            <i class="bi bi-check-square me-2"></i>Mes Tâches
                        </a>
                        <a href="<?= url('index.php?page=documentation') ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-book me-2"></i>Documentation
                        </a>
                        <?php if (($user['role'] ?? '') === 'admin_entreprise'): ?>
                            <hr class="my-2">
                            <a href="<?= url('index.php?page=admin_dashboard') ?>" class="btn btn-primary">
                                <i class="bi bi-building me-2"></i>Dashboard Entreprise
                            </a>
                        <?php endif; ?>
                        <?php if (($user['role'] ?? '') === 'super_admin'): ?>
                            <hr class="my-2">
                            <a href="<?= url('index.php?page=admin_dashboard') ?>" class="btn btn-primary">
                                <i class="bi bi-building me-2"></i>Dashboard Entreprise
                            </a>
                            <a href="<?= url('index.php?page=superadmin_dashboard') ?>" class="btn btn-danger">
                                <i class="bi bi-shield-lock-fill me-2"></i>Panel SuperAdmin
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    function copyReferralCode() {
        const code = '<?= htmlspecialchars($user['referral_code'] ?? '') ?>';
        navigator.clipboard.writeText(code).then(() => {
            alert('Code de parrainage copié : ' + code);
        });
    }
</script>