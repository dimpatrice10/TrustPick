<?php
/**
 * Page Portefeuille - TrustPick V2
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/url.php';
require_once __DIR__ . '/../includes/helpers.php';

if (session_status() === PHP_SESSION_NONE)
    session_start();

if (empty($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Connectez-vous pour accéder au portefeuille.';
    header('Location: ' . url('index.php?page=login'));
    exit;
}

$uid = intval($_SESSION['user_id']);

// Solde actuel
$balStmt = $pdo->prepare('SELECT COALESCE(balance, 0) FROM users WHERE id = ?');
$balStmt->execute([$uid]);
$balance = floatval($balStmt->fetchColumn() ?? 0);

// Total retiré
$wdStmt = $pdo->prepare('SELECT COALESCE(SUM(amount), 0) FROM withdrawals WHERE user_id = ?');
$wdStmt->execute([$uid]);
$totalWithdrawn = floatval($wdStmt->fetchColumn());

// Nombre de retraits
$wdCountStmt = $pdo->prepare('SELECT COUNT(*) FROM withdrawals WHERE user_id = ?');
$wdCountStmt->execute([$uid]);
$withdrawalCount = intval($wdCountStmt->fetchColumn());

// Retraits en attente
$pendingStmt = $pdo->prepare("SELECT COUNT(*) FROM withdrawals WHERE user_id = ? AND status = 'pending'");
$pendingStmt->execute([$uid]);
$pendingCount = intval($pendingStmt->fetchColumn());

// Nombre d'avis (points)
$reviewStmt = $pdo->prepare('SELECT COUNT(*) FROM reviews WHERE user_id = ?');
$reviewStmt->execute([$uid]);
$reviewCount = intval($reviewStmt->fetchColumn());

// Transactions récentes (si table existe)
$transactions = [];
try {
    $txStmt = $pdo->prepare('
        SELECT * FROM transactions 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 10
    ');
    $txStmt->execute([$uid]);
    $transactions = $txStmt->fetchAll();
} catch (Exception $e) {
    // Table n'existe peut-être pas, utiliser les avis comme proxy
    $txProxy = $pdo->prepare('
        SELECT r.created_at, r.rating, p.title 
        FROM reviews r 
        LEFT JOIN products p ON p.id = r.product_id 
        WHERE r.user_id = ? 
        ORDER BY r.created_at DESC 
        LIMIT 10
    ');
    $txProxy->execute([$uid]);
    $transactions = $txProxy->fetchAll();
}

// Liste des retraits
$withdrawals = $pdo->prepare('SELECT * FROM withdrawals WHERE user_id = ? ORDER BY created_at DESC LIMIT 10');
$withdrawals->execute([$uid]);
$withdrawals = $withdrawals->fetchAll();

// Montant minimum de retrait
$minWithdrawal = 30000;
$canWithdraw = $balance >= $minWithdrawal;
?>

<main class="container py-4">
    <!-- En-tête -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1 class="h2 mb-1"><i class="bi bi-wallet2 me-2"></i>Mon Portefeuille</h1>
                    <p class="text-muted mb-0">Gérez vos gains et retraits</p>
                </div>
                <a href="<?= url('index.php?page=user_dashboard') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Retour au Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Solde principal -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                <div class="card-body text-white py-4">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <p class="mb-1 opacity-75">Solde disponible</p>
                            <h1 class="display-4 mb-0 fw-bold"><?= formatFCFA($balance) ?></h1>
                            <p class="mb-0 mt-2 opacity-75">
                                <?php if ($canWithdraw): ?>
                                    <i class="bi bi-check-circle-fill me-1"></i>Éligible au retrait
                                <?php else: ?>
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>Min.
                                    <?= formatFCFA($minWithdrawal) ?> pour retrait
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-6 text-md-end mt-3 mt-md-0">
                            <button type="button" class="btn btn-light btn-lg" <?= !$canWithdraw ? 'disabled' : '' ?>
                                data-bs-toggle="modal" data-bs-target="#withdrawModal">
                                <i class="bi bi-cash-stack me-1"></i>Demander un retrait
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section Dépôt - Tâche obligatoire -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm border-primary">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="mb-2"><i class="bi bi-plus-circle-fill text-primary me-2"></i>Effectuer un Dépôt
                            </h5>
                            <p class="text-muted mb-2">
                                Le dépôt minimum de <strong>5000 FCFA</strong> est une tâche quotidienne obligatoire
                                pour débloquer
                                toutes vos récompenses.
                            </p>
                            <small class="text-info">
                                <i class="bi bi-info-circle me-1"></i>
                                Vos dépôts sont sécurisés et ajoutés à votre solde immédiatement.
                            </small>
                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal"
                                data-bs-target="#depositModal">
                                <i class="bi bi-wallet-fill me-1"></i>Déposer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPIs -->
    <div class="row mb-4">
        <div class="col-md-3 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-primary mb-2"><i class="bi bi-graph-up-arrow"></i></div>
                    <h4 class="mb-0"><?= formatFCFA($balance + $totalWithdrawn) ?></h4>
                    <small class="text-muted">Gains totaux</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-success mb-2"><i class="bi bi-check-circle-fill"></i></div>
                    <h4 class="mb-0"><?= formatFCFA($totalWithdrawn) ?></h4>
                    <small class="text-muted">Déjà retiré</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-warning mb-2"><i class="bi bi-star-fill"></i></div>
                    <h4 class="mb-0"><?= number_format($reviewCount * 5) ?> pts</h4>
                    <small class="text-muted">Points gagnés</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-info mb-2"><i class="bi bi-arrow-repeat"></i></div>
                    <h4 class="mb-0"><?= $withdrawalCount ?></h4>
                    <small class="text-muted">Retraits effectués</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Historique des transactions -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0"><i class="bi bi-bar-chart-line me-2"></i>Historique des gains</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($transactions)): ?>
                        <div class="text-center py-4">
                            <p class="text-muted mb-0">Aucune transaction enregistrée</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($transactions as $tx): ?>
                                <div class="list-group-item py-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <?php if (isset($tx['type'])): ?>
                                                <span
                                                    class="badge bg-<?= $tx['type'] === 'reward' ? 'success' : ($tx['type'] === 'referral' ? 'info' : 'secondary') ?> me-2">
                                                    <?= ucfirst($tx['type']) ?>
                                                </span>
                                                <?= htmlspecialchars($tx['description'] ?? 'Transaction') ?>
                                            <?php else: ?>
                                                <span class="badge bg-success me-2">Avis</span>
                                                <?= htmlspecialchars($tx['title'] ?? 'Avis produit') ?>
                                            <?php endif; ?>
                                            <br>
                                            <small class="text-muted">
                                                <?= date('d/m/Y H:i', strtotime($tx['created_at'])) ?>
                                            </small>
                                        </div>
                                        <span class="text-success fw-bold">
                                            +<?= formatFCFA($tx['amount'] ?? 500) ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Historique des retraits -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-cash-coin me-2"></i>Historique des retraits</h5>
                    <?php if ($pendingCount > 0): ?>
                        <span class="badge bg-warning"><?= $pendingCount ?> en attente</span>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($withdrawals)): ?>
                        <div class="text-center py-4">
                            <p class="text-muted mb-0">Aucun retrait effectué</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($withdrawals as $w):
                                $statusColors = [
                                    'pending' => 'warning',
                                    'approved' => 'success',
                                    'completed' => 'success',
                                    'rejected' => 'danger'
                                ];
                                $statusLabels = [
                                    'pending' => 'En attente',
                                    'approved' => 'Approuvé',
                                    'completed' => 'Complété',
                                    'rejected' => 'Rejeté'
                                ];
                                $color = $statusColors[$w['status']] ?? 'secondary';
                                $label = $statusLabels[$w['status']] ?? $w['status'];
                                ?>
                                <div class="list-group-item py-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge bg-<?= $color ?>"><?= $label ?></span>
                                            <br>
                                            <small class="text-muted">
                                                <?= date('d/m/Y H:i', strtotime($w['created_at'])) ?>
                                            </small>
                                        </div>
                                        <span class="text-danger fw-bold">
                                            -<?= formatFCFA($w['amount']) ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Moyens de paiement -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0"><i class="bi bi-credit-card me-2"></i>Moyens de paiement</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="card h-100 border-primary" style="cursor:pointer">
                                <div class="card-body text-center">
                                    <div class="display-4 mb-2"><i class="bi bi-phone text-primary"></i></div>
                                    <h6 class="mb-1">Mobile Money</h6>
                                    <span class="badge bg-success">Connecté</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100 border-light opacity-75" style="cursor:pointer">
                                <div class="card-body text-center">
                                    <div class="display-4 mb-2"><i class="bi bi-bank text-secondary"></i></div>
                                    <h6 class="mb-1">Virement bancaire</h6>
                                    <span class="badge bg-secondary">Ajouter</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100 border-light opacity-75" style="cursor:pointer">
                                <div class="card-body text-center">
                                    <div class="display-4 mb-2"><i class="bi bi-cash text-warning"></i></div>
                                    <h6 class="mb-1">Orange Money</h6>
                                    <span class="badge bg-secondary">Ajouter</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Modal de retrait -->
<div class="modal fade" id="withdrawModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title"><i class="bi bi-cash-stack me-2"></i>Demander un retrait</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= url('actions/withdraw.php') ?>">
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <strong>Solde disponible :</strong> <?= formatFCFA($balance) ?><br>
                        <strong>Minimum de retrait :</strong> <?= formatFCFA($minWithdrawal) ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Montant à retirer (FCFA) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control form-control-lg"
                            min="<?= $minWithdrawal ?>" max="<?= $balance ?>" step="100" placeholder="Ex: 50000"
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Moyen de paiement</label>
                        <select name="method" class="form-select">
                            <option value="mobile_money">Mobile Money</option>
                            <option value="bank_transfer">Virement bancaire</option>
                            <option value="orange_money">Orange Money</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Numéro de téléphone / Compte</label>
                        <input type="text" name="account" class="form-control" placeholder="Ex: +237 6XX XXX XXX">
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success" <?= !$canWithdraw ? 'disabled' : '' ?>>
                        Confirmer le retrait
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de dépôt -->
<div class="modal fade" id="depositModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-wallet-fill me-2"></i>Dépôt Mobile Money</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= url('actions/deposit.php') ?>" id="depositForm">
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        <strong>Minimum requis :</strong> 5000 FCFA<br>
                        <small>Ce dépôt valide votre tâche quotidienne.</small>
                    </div>

                    <!-- Sélection de l'opérateur -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Opérateur Mobile Money <span
                                class="text-danger">*</span></label>
                        <div class="row g-3">
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="channel" id="orange" value="ORANGE" checked>
                                <label class="btn btn-outline-warning w-100 p-3" for="orange"
                                    style="border: 2px solid #ff6b00;">
                                    <div class="fw-bold" style="color: #ff6b00;">Orange Money</div>
                                    <small class="text-muted">#150#</small>
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="channel" id="mtn" value="MTN">
                                <label class="btn btn-outline-warning w-100 p-3" for="mtn"
                                    style="border: 2px solid #ffcc00;">
                                    <div class="fw-bold" style="color: #000;">MTN Mobile Money</div>
                                    <small class="text-muted">#126#</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Numéro de téléphone -->
                    <div class="mb-3">
                        <label class="form-label">Votre numéro Mobile Money <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">+237</span>
                            <input type="tel" name="phone" class="form-control" placeholder="6XX XXX XXX"
                                pattern="[6][0-9]{8}" maxlength="9" required id="phoneInput">
                        </div>
                        <small class="text-muted">Format: 9 chiffres (ex: 657317490)</small>
                    </div>

                    <!-- Montant -->
                    <div class="mb-3">
                        <label class="form-label">Montant à déposer (FCFA) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control form-control-lg" min="5000" step="1000"
                            placeholder="Ex: 5000" value="5000" required id="amountInput">
                    </div>

                    <!-- Montants rapides -->
                    <div class="mb-3">
                        <label class="form-label">Montants rapides</label>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-outline-primary btn-quick-amount" data-amount="5000">5
                                000</button>
                            <button type="button" class="btn btn-outline-primary btn-quick-amount"
                                data-amount="10000">10 000</button>
                            <button type="button" class="btn btn-outline-primary btn-quick-amount"
                                data-amount="20000">20 000</button>
                            <button type="button" class="btn btn-outline-primary btn-quick-amount"
                                data-amount="50000">50 000</button>
                        </div>
                    </div>

                    <!-- Info importante -->
                    <div class="alert alert-warning mb-0">
                        <small>
                            <strong>⚠️ Important:</strong><br>
                            • Assurez-vous d'avoir du solde sur votre compte Mobile Money<br>
                            • Vous recevrez des instructions de paiement après validation
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="btnDeposit">
                        <i class="bi bi-check-circle me-1"></i>Confirmer le dépôt
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Styles pour les radio buttons operators */
    .btn-check:checked+label {
        background-color: #fff3cd !important;
        border-width: 3px !important;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
    }

    .btn-quick-amount.active {
        background-color: #0d6efd;
        color: white;
        border-color: #0d6efd;
    }
</style>

<script>
    // Montants rapides pour le dépôt
    document.querySelectorAll('.btn-quick-amount').forEach(btn => {
        btn.addEventListener('click', function () {
            document.getElementById('amountInput').value = this.dataset.amount;
            // Mettre en surbrillance le bouton sélectionné
            document.querySelectorAll('.btn-quick-amount').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Validation du numéro de téléphone selon l'opérateur sélectionné
    const channelRadios = document.querySelectorAll('input[name="channel"]');
    const phoneInput = document.getElementById('phoneInput');

    channelRadios.forEach(radio => {
        radio.addEventListener('change', function () {
            // Orange: 65, 69
            // MTN: 67, 68, 65, 69
            if (this.value === 'ORANGE') {
                phoneInput.placeholder = '6XX XXX XXX (Orange)';
            } else {
                phoneInput.placeholder = '6XX XXX XXX (MTN)';
            }
        });
    });

    // Feedback lors de la soumission du formulaire
    document.getElementById('depositForm')?.addEventListener('submit', function (e) {
        const phone = document.getElementById('phoneInput').value;
        const channel = document.querySelector('input[name="channel"]:checked').value;

        // Validation basique
        if (phone.length !== 9 || !phone.startsWith('6')) {
            e.preventDefault();
            alert('Veuillez entrer un numéro de téléphone valide (9 chiffres commençant par 6)');
            return false;
        }

        const btn = document.getElementById('btnDeposit');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Initialisation...';
    });

    // Réinitialiser le formulaire à la fermeture du modal
    document.getElementById('depositModal')?.addEventListener('hidden.bs.modal', function () {
        const form = document.getElementById('depositForm');
        if (form) {
            form.reset();
            document.querySelectorAll('.btn-quick-amount').forEach(b => b.classList.remove('active'));
            const btn = document.getElementById('btnDeposit');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Confirmer le dépôt';
        }
    });
</script>