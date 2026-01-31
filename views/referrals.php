<?php
/**
 * Page Parrainage - TrustPick V2
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/url.php';
require_once __DIR__ . '/../includes/helpers.php';

if (session_status() === PHP_SESSION_NONE)
    session_start();

if (empty($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Connectez-vous pour accéder au parrainage.';
    header('Location: ' . url('index.php?page=login'));
    exit;
}

$uid = intval($_SESSION['user_id']);

// Récupérer le code de parrainage de l'utilisateur
$userStmt = $pdo->prepare('SELECT referral_code, COALESCE(balance, 0) as balance FROM users WHERE id = ?');
$userStmt->execute([$uid]);
$user = $userStmt->fetch();
$referralCode = $user['referral_code'] ?? '';
$balance = floatval($user['balance']);

// Générer le lien de parrainage
$referralLink = url('index.php?page=register&ref=' . urlencode($referralCode));

// Statistiques de parrainage
$referralsStmt = $pdo->prepare('
    SELECT r.*, u.name, u.created_at as user_created, u.is_active
    FROM referrals r
    JOIN users u ON r.referred_id = u.id
    WHERE r.referrer_id = ?
    ORDER BY r.created_at DESC
');
$referralsStmt->execute([$uid]);
$referrals = $referralsStmt->fetchAll();

$totalReferrals = count($referrals);
$activeReferrals = 0;
$totalEarned = 0;

foreach ($referrals as $ref) {
    if (!empty($ref['is_active']))
        $activeReferrals++;
    $totalEarned += floatval($ref['bonus_amount'] ?? 0);
}

// Bonus configuré
$bonusAmount = 1000; // FCFA par parrainage réussi
?>

<main class="container py-4">
    <!-- En-tête -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 class="h2 mb-1"><i class="bi bi-people me-2"></i>Programme de Parrainage</h1>
                    <p class="text-muted mb-0">Invitez vos amis et gagnez des récompenses</p>
                </div>
                <span class="badge bg-success fs-6 px-3 py-2">
                    <i class="bi bi-wallet2 me-1"></i>Solde: <?= formatFCFA($balance) ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Lien de parrainage -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #0066cc 0%, #1ab991 100%);">
                <div class="card-body text-white p-4">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <h4 class="text-white mb-2"><i class="bi bi-gift me-2"></i>Votre lien de parrainage unique
                            </h4>
                            <p class="mb-3 opacity-75">Partagez ce lien et gagnez
                                <strong><?= formatFCFA($bonusAmount) ?></strong> par filleul inscrit !
                            </p>

                            <div class="input-group mb-3">
                                <input type="text" class="form-control bg-white" id="referralLink"
                                    value="<?= htmlspecialchars($referralLink) ?>" readonly>
                                <button class="btn btn-light" type="button" onclick="copyLink()">
                                    <i class="bi bi-clipboard me-1"></i>Copier
                                </button>
                            </div>

                            <div class="d-flex gap-2 flex-wrap">
                                <a href="https://wa.me/?text=<?= urlencode('Rejoins TrustPick et gagne de l\'argent ! ' . $referralLink) ?>"
                                    target="_blank" class="btn btn-light btn-sm">
                                    <i class="bi bi-whatsapp me-1"></i>WhatsApp
                                </a>
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($referralLink) ?>"
                                    target="_blank" class="btn btn-light btn-sm">
                                    <i class="bi bi-facebook me-1"></i>Facebook
                                </a>
                                <a href="https://twitter.com/intent/tweet?text=<?= urlencode('Rejoins TrustPick ! ' . $referralLink) ?>"
                                    target="_blank" class="btn btn-light btn-sm">
                                    <i class="bi bi-twitter me-1"></i>Twitter
                                </a>
                                <a href="https://t.me/share/url?url=<?= urlencode($referralLink) ?>&text=<?= urlencode('Rejoins TrustPick !') ?>"
                                    target="_blank" class="btn btn-light btn-sm">
                                    <i class="bi bi-telegram me-1"></i>Telegram
                                </a>
                            </div>
                        </div>
                        <div class="col-lg-4 text-center d-none d-lg-block">
                            <div style="font-size: 80px;"><i class="bi bi-people-fill text-white"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-4 mb-2 text-primary"><?= $totalReferrals ?></div>
                    <p class="text-muted mb-0">Filleuls invités</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-4 mb-2 text-success"><?= $activeReferrals ?></div>
                    <p class="text-muted mb-0">Filleuls actifs</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-4 mb-2 text-warning"><?= formatFCFA($totalEarned) ?></div>
                    <p class="text-muted mb-0">Total gagné</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Comment ça marche -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">📖 Comment ça marche ?</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="step-icon mx-auto mb-3"
                                style="width:64px;height:64px;background:linear-gradient(135deg,#e6f0ff,#fff);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:28px;border:2px solid #0066cc;">
                                1️⃣
                            </div>
                            <h6><span class="badge bg-primary rounded-circle">1</span>tagez votre lien</h6>
                            <p class="text-muted small">Envoyez votre lien unique à vos amis par WhatsApp, SMS ou
                                réseaux sociaux</p>
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="step-icon mx-auto mb-3"
                                style="width:64px;height:64px;background:linear-gradient(135deg,#e6f9f2,#fff);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:28px;border:2px solid #10b981;">
                                2️⃣
                            </div>
                            <h6><span class="badge bg-primary rounded-circle">2</span> amis s'inscrivent</h6>
                            <p class="text-muted small">Ils créent leur compte TrustPick via votre lien de parrainage
                            </p>
                        </div>
                        <div class="col-md-4">
                            <div class="step-icon mx-auto mb-3"
                                style="width:64px;height:64px;background:linear-gradient(135deg,#fef3c7,#fff);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:28px;border:2px solid #f59e0b;">
                                3️⃣
                            </div>
                            <h6><span class="badge bg-primary rounded-circle">3</span>nez des FCFA</h6>
                            <p class="text-muted small">Recevez <?= formatFCFA($bonusAmount) ?> pour chaque filleul qui
                                s'inscrit !</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des filleuls -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-people me-2"></i>Mes Filleuls</h5>
                    <span class="badge bg-primary"><?= $totalReferrals ?>
                        filleul<?= $totalReferrals > 1 ? 's' : '' ?></span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($referrals)): ?>
                        <div class="text-center py-5">
                            <div class="display-1 mb-3" style="opacity:0.3"><i class="bi bi-people"></i></div>
                            <h5 class="text-muted">Aucun filleul pour le moment</h5>
                            <p class="text-muted">Partagez votre lien pour inviter vos premiers filleuls !</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Filleul</th>
                                        <th>Date d'inscription</th>
                                        <th>Statut</th>
                                        <th>Bonus reçu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($referrals as $ref): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div
                                                        style="width:36px;height:36px;background:linear-gradient(135deg,#e6f0ff,#fff);border-radius:50%;display:flex;align-items:center;justify-content:center;">
                                                        <i class="bi bi-person-fill text-primary"></i>
                                                    </div>
                                                    <span><?= htmlspecialchars($ref['name']) ?></span>
                                                </div>
                                            </td>
                                            <td class="text-muted"><?= date('d/m/Y', strtotime($ref['user_created'])) ?></td>
                                            <td>
                                                <?php if (!empty($ref['is_active'])): ?>
                                                    <span class="badge bg-success">Actif</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-success fw-bold">
                                                +<?= formatFCFA($ref['bonus_amount'] ?? $bonusAmount) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    function copyLink() {
        const input = document.getElementById('referralLink');
        input.select();
        input.setSelectionRange(0, 99999);

        navigator.clipboard.writeText(input.value).then(() => {
            showToast('success', 'Lien copié dans le presse-papiers !');
        }).catch(() => {
            document.execCommand('copy');
            showToast('success', 'Lien copié !');
        });
    }
</script>