<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/url.php';
require_once __DIR__ . '/../includes/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier authentification super admin
SessionManager::requireRole('super_admin', 'index.php?page=login');

// Statistiques globales
$totalUsers = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$totalCompanies = $pdo->query('SELECT COUNT(*) FROM companies')->fetchColumn();
$totalProducts = $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$totalTransactions = $pdo->query('SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE type IN ("reward", "referral")')->fetchColumn();
$totalReviews = $pdo->query('SELECT COUNT(*) FROM reviews')->fetchColumn();
$pendingWithdrawals = $pdo->query('SELECT COUNT(*) FROM withdrawals WHERE status = "pending"')->fetchColumn();
$totalReferrals = $pdo->query('SELECT COUNT(*) FROM referrals')->fetchColumn();

// Utilisateurs récents
$recentUsers = $pdo->query('SELECT id, cau, name, role, created_at FROM users ORDER BY created_at DESC LIMIT 5')->fetchAll();

// Entreprises actives
$companies = $pdo->query('SELECT id, name, slug, is_active, created_at FROM companies ORDER BY created_at DESC LIMIT 10')->fetchAll();

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
                                        <?php echo $company['is_active'] ? '✓ Actif' : '✗ Inactif'; ?>
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