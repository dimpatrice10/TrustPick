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
$wdStmt = $pdo->prepare('SELECT IFNULL(SUM(amount), 0) FROM withdrawals WHERE user_id = ?');
$wdStmt->execute([$uid]);
$totalWithdrawn = floatval($wdStmt->fetchColumn());

// Nombre de retraits
$wdCountStmt = $pdo->prepare('SELECT COUNT(*) FROM withdrawals WHERE user_id = ?');
$wdCountStmt->execute([$uid]);
$withdrawalCount = intval($wdCountStmt->fetchColumn());

// Retraits en attente
$pendingStmt = $pdo->prepare('SELECT COUNT(*) FROM withdrawals WHERE user_id = ? AND status = "pending"');
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
                  <i class="bi bi-exclamation-triangle-fill me-1"></i>Min. <?= formatFCFA($minWithdrawal) ?> pour retrait
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
            <input type="number" name="amount" class="form-control form-control-lg" min="<?= $minWithdrawal ?>"
              max="<?= $balance ?>" step="100" placeholder="Ex: 50000" required>
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