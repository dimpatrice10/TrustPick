<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/url.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/settings.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier authentification super admin
SessionManager::requireRole('super_admin', 'index.php?page=login');

// Statistiques globales
$totalUsers = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$totalCompanies = $pdo->query('SELECT COUNT(*) FROM companies')->fetchColumn();
$totalProducts = $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$totalTransactions = $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE type IN ('reward', 'referral')")->fetchColumn();
$totalReviews = $pdo->query('SELECT COUNT(*) FROM reviews')->fetchColumn();
$pendingWithdrawals = $pdo->query("SELECT COUNT(*) FROM withdrawals WHERE status = 'pending'")->fetchColumn();
$totalReferrals = $pdo->query('SELECT COUNT(*) FROM referrals')->fetchColumn();

// Utilisateurs récents
$recentUsers = $pdo->query('SELECT id, cau, name, role, created_at FROM users ORDER BY created_at DESC LIMIT 5')->fetchAll();

// Entreprises actives
$companies = $pdo->query('SELECT id, name, slug, is_active, created_at FROM companies ORDER BY created_at DESC LIMIT 10')->fetchAll();

// Paramètres système
$settingsMinDeposit = Settings::getInt('min_deposit', 10);
$settingsMinWithdrawal = Settings::getInt('min_withdrawal', 5000);
$settingsReviewReward = Settings::getInt('review_reward', 500);
$settingsReferralReward = Settings::getInt('referral_reward', 5000);
$settingsDailyNotif = Settings::getInt('daily_notifications_count', 2);
$settingsProdGenFreq = Settings::getInt('products_generation_frequency', 3);

// Dernières transactions importantes
$topTransactions = $pdo->query('
    SELECT t.id, t.amount, t.type, t.description, u.name, t.created_at 
    FROM transactions t
    JOIN users u ON t.user_id = u.id
    WHERE t.amount >= 500
    ORDER BY t.created_at DESC 
    LIMIT 8
')->fetchAll();
?>

<main class="container py-5">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h2"><i class="bi bi-speedometer2 me-2"></i>Tableau de Bord Super Admin</h1>
                <div class="btn-group" role="group">
                    <a href="<?= url('index.php?page=manage_users') ?>" class="btn btn-sm btn-primary"><i
                            class="bi bi-people me-1"></i>Gérer
                        Utilisateurs</a>
                    <a href="<?= url('index.php?page=manage_tasks') ?>" class="btn btn-sm btn-warning"><i
                            class="bi bi-list-check me-1"></i>Gérer Tâches</a>
                    <a href="<?= url('index.php?page=home') ?>" class="btn btn-sm btn-outline-secondary">Accueil</a>
                </div>
            </div>
            <p class="text-muted">Gestion globale de TrustPick V2</p>
        </div>
    </div>

    <!-- KPIs Principaux -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1">Utilisateurs</p>
                            <h3 class="mb-0"><?php echo number_format($totalUsers); ?></h3>
                        </div>
                        <div class="display-4 text-primary opacity-25"><i class="bi bi-people-fill"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1">Entreprises</p>
                            <h3 class="mb-0"><?php echo number_format($totalCompanies); ?></h3>
                        </div>
                        <div class="display-4 text-success opacity-25"><i class="bi bi-building"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1">Produits</p>
                            <h3 class="mb-0"><?php echo number_format($totalProducts); ?></h3>
                        </div>
                        <div class="display-4 text-info opacity-25"><i class="bi bi-box-seam"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1">Avis</p>
                            <h3 class="mb-0"><?php echo number_format($totalReviews); ?></h3>
                        </div>
                        <div class="display-4 text-warning opacity-25"><i class="bi bi-star-fill"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1">Parrainages</p>
                            <h3 class="mb-0"><?php echo number_format($totalReferrals); ?></h3>
                        </div>
                        <div class="display-4 text-danger opacity-25"><i class="bi bi-link-45deg"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1">Retraits En Attente</p>
                            <h3 class="mb-0 text-warning"><?php echo number_format($pendingWithdrawals); ?></h3>
                        </div>
                        <div class="display-4 opacity-25"><i class="bi bi-hourglass-split"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-sm-12 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1">Récompenses Distribuées</p>
                            <h3 class="mb-0"><?php echo number_format($totalTransactions, 0); ?> FCFA</h3>
                        </div>
                        <div class="display-4 text-success opacity-25"><i class="bi bi-wallet2"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Génération en masse d'entreprises -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h5 class="mb-1"><i class="bi bi-building-add me-2"></i>Génération Automatique d'Entreprises
                            </h5>
                            <p class="text-muted mb-0 small">Créez plusieurs entreprises avec leurs admins en un clic
                            </p>
                        </div>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#generateCompaniesModal">
                            <i class="bi bi-plus-circle me-1"></i>Générer des Entreprises
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Paramètres Système -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-gear me-2"></i>Paramètres Système</h5>
                    <span class="badge bg-secondary">Configuration</span>
                </div>
                <div class="card-body">
                    <form action="<?= url('actions/save_settings.php') ?>" method="POST" id="settingsForm">
                        <div class="row g-3">
                            <!-- Paiements -->
                            <div class="col-12">
                                <h6 class="text-primary mb-3"><i class="bi bi-wallet2 me-1"></i>Paiements & Portefeuille
                                </h6>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Dépôt minimum (FCFA)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-arrow-down-circle"></i></span>
                                    <input type="number" name="min_deposit" class="form-control"
                                        value="<?= $settingsMinDeposit ?>" min="1" required>
                                    <span class="input-group-text">FCFA</span>
                                </div>
                                <div class="form-text">Montant minimum pour un dépôt Mobile Money</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Retrait minimum (FCFA)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-arrow-up-circle"></i></span>
                                    <input type="number" name="min_withdrawal" class="form-control"
                                        value="<?= $settingsMinWithdrawal ?>" min="1" required>
                                    <span class="input-group-text">FCFA</span>
                                </div>
                                <div class="form-text">Montant minimum pour une demande de retrait</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Devise</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-currency-exchange"></i></span>
                                    <input type="text" class="form-control" value="XAF (FCFA)" disabled>
                                </div>
                                <div class="form-text">Devise fixe pour le Cameroun</div>
                            </div>

                            <!-- Récompenses -->
                            <div class="col-12 mt-4">
                                <h6 class="text-success mb-3"><i class="bi bi-gift me-1"></i>Récompenses</h6>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Récompense par avis (FCFA)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-star"></i></span>
                                    <input type="number" name="review_reward" class="form-control"
                                        value="<?= $settingsReviewReward ?>" min="0" required>
                                    <span class="input-group-text">FCFA</span>
                                </div>
                                <div class="form-text">Montant gagné par avis validé</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Récompense parrainage (FCFA)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-people"></i></span>
                                    <input type="number" name="referral_reward" class="form-control"
                                        value="<?= $settingsReferralReward ?>" min="0" required>
                                    <span class="input-group-text">FCFA</span>
                                </div>
                                <div class="form-text">Montant gagné par parrainage réussi</div>
                            </div>

                            <!-- Système -->
                            <div class="col-12 mt-4">
                                <h6 class="text-info mb-3"><i class="bi bi-sliders me-1"></i>Système</h6>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Notifications / jour</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-bell"></i></span>
                                    <input type="number" name="daily_notifications_count" class="form-control"
                                        value="<?= $settingsDailyNotif ?>" min="0" required>
                                </div>
                                <div class="form-text">Nombre de notifications quotidiennes</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Fréq. génération produits</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-box-seam"></i></span>
                                    <input type="number" name="products_generation_frequency" class="form-control"
                                        value="<?= $settingsProdGenFreq ?>" min="1" required>
                                </div>
                                <div class="form-text">Nombre de générations / jour</div>
                            </div>
                        </div>

                        <hr class="my-4">
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary" id="btnSaveSettings">
                                <i class="bi bi-check-circle me-1"></i>Enregistrer les paramètres
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Utilisateurs Récents -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0"><i class="bi bi-person me-2"></i>Derniers Utilisateurs</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (count($recentUsers) > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentUsers as $user): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center py-3 px-4">
                                    <div>
                                        <p class="mb-1 fw-bold"><?php echo htmlspecialchars($user['name']); ?></p>
                                        <p class="mb-0 small text-muted"><?php echo htmlspecialchars($user['cau']); ?></p>
                                    </div>
                                    <span
                                        class="badge bg-primary"><?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center py-4">Aucun utilisateur récent</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Entreprises -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0"><i class="bi bi-building me-2"></i>Entreprises</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (count($companies) > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($companies as $company): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center py-3 px-4">
                                    <div>
                                        <p class="mb-1 fw-bold"><?php echo htmlspecialchars($company['name']); ?></p>
                                        <p class="mb-0 small text-muted"><?php echo htmlspecialchars($company['slug']); ?></p>
                                    </div>
                                    <span class="badge <?php echo $company['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo $company['is_active'] ? '<i class="bi bi-check-circle"></i> Actif' : '<i class="bi bi-x-circle"></i> Inactif'; ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center py-4">Aucune entreprise</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions Importantes -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0"><i class="bi bi-credit-card me-2"></i>Transactions Importantes (≥ 500 FCFA)</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (count($topTransactions) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Utilisateur</th>
                                        <th>Type</th>
                                        <th>Montant</th>
                                        <th>Description</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topTransactions as $tx): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($tx['name']); ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?php echo ucfirst($tx['type']); ?></span>
                                            </td>
                                            <td>
                                                <span class="text-success fw-bold">
                                                    +<?php echo number_format($tx['amount'], 0); ?> FCFA
                                                </span>
                                            </td>
                                            <td class="text-muted small"><?php echo htmlspecialchars($tx['description']); ?>
                                            </td>
                                            <td class="text-muted small">
                                                <?php echo date('d/m/Y H:i', strtotime($tx['created_at'])); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center py-4">Aucune transaction importante</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Modal Génération d'Entreprises -->
<div class="modal fade" id="generateCompaniesModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-building-add me-2"></i>Générer des Entreprises</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="generateCompaniesForm" action="<?= url('actions/superadmin_generate_companies.php') ?>"
                method="POST">
                <div class="modal-body">
                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle me-1"></i>
                        Chaque entreprise sera créée avec un administrateur dédié (CAU auto-généré).
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nombre d'entreprises à générer</label>
                        <select name="count" class="form-select" required>
                            <option value="3">3 entreprises</option>
                            <option value="5" selected>5 entreprises</option>
                            <option value="10">10 entreprises</option>
                            <option value="15">15 entreprises</option>
                            <option value="20">20 entreprises (max)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Secteur d'activité (optionnel)</label>
                        <select name="sector" class="form-select">
                            <option value="">Aléatoire (tous secteurs)</option>
                            <option value="tech">Technologies & Informatique</option>
                            <option value="commerce">Commerce & Distribution</option>
                            <option value="services">Services</option>
                            <option value="industrie">Industrie & Production</option>
                            <option value="alimentation">Alimentation & Restauration</option>
                            <option value="mode">Mode & Beauté</option>
                            <option value="sante">Santé & Bien-être</option>
                        </select>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" name="generate_products" value="1" class="form-check-input"
                            id="genProductsCheck" checked>
                        <label class="form-check-label" for="genProductsCheck">
                            Générer aussi 5-10 produits par entreprise
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success" id="btnGenerateCompanies">
                        <i class="bi bi-building-add me-1"></i>Générer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('generateCompaniesForm')?.addEventListener('submit', function (e) {
        const btn = document.getElementById('btnGenerateCompanies');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Génération...';
    });
</script>