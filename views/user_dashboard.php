<?php
/**
 * Dashboard Utilisateur - TrustPick V2
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/url.php';
require_once __DIR__ . '/../includes/helpers.php';

if (session_status() === PHP_SESSION_NONE)
  session_start();

if (empty($_SESSION['user_id'])) {
  $_SESSION['error'] = 'Connectez-vous pour accéder au tableau de bord.';
  header('Location: ' . url('index.php?page=login'));
  exit;
}

$uid = intval($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? 'Utilisateur';

// Solde portefeuille
$balStmt = $pdo->prepare('SELECT COALESCE(balance, 0) FROM users WHERE id = ?');
$balStmt->execute([$uid]);
$balance = floatval($balStmt->fetchColumn() ?? 0);

// Nombre d'avis
$revStmt = $pdo->prepare('SELECT COUNT(*) FROM reviews WHERE user_id = ?');
$revStmt->execute([$uid]);
$reviewsCount = intval($revStmt->fetchColumn());

// Points (5 points par avis)
$points = $reviewsCount * 5;

// Niveau utilisateur
$userLevel = min(10, floor($reviewsCount / 10) + 1);
$levelProgress = ($reviewsCount % 10) * 10;
$levelNames = ['Bronze', 'Argent', 'Or', 'Platine', 'Diamant'];
$levelName = $levelNames[min(4, floor($userLevel / 3))];

// Parrainages
$referralCount = 0;
try {
  $refStmt = $pdo->prepare('SELECT COUNT(*) FROM referrals WHERE referrer_id = ?');
  $refStmt->execute([$uid]);
  $referralCount = intval($refStmt->fetchColumn());
} catch (Exception $e) {
}

// Tâches complétées
$taskCount = 0;
try {
  $taskStmt = $pdo->prepare('SELECT COUNT(*) FROM user_tasks WHERE user_id = ?');
  $taskStmt->execute([$uid]);
  $taskCount = intval($taskStmt->fetchColumn());
} catch (Exception $e) {
}

// Notifications non lues
$unreadNotifications = 0;
try {
  $notifStmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
  $notifStmt->execute([$uid]);
  $unreadNotifications = intval($notifStmt->fetchColumn());
} catch (Exception $e) {
}

// Activités récentes (6 derniers avis)
$actStmt = $pdo->prepare('
    SELECT r.*, p.title, p.id as product_id
    FROM reviews r 
    LEFT JOIN products p ON p.id = r.product_id 
    WHERE r.user_id = ? 
    ORDER BY r.created_at DESC 
    LIMIT 6
');
$actStmt->execute([$uid]);
$activities = $actStmt->fetchAll();

// Gains du mois
$monthGains = 0;
try {
  $monthStmt = $pdo->prepare('
        SELECT COALESCE(SUM(amount), 0) 
        FROM transactions 
        WHERE user_id = ? AND type IN ("reward", "referral") 
        AND created_at >= DATE_FORMAT(NOW(), "%Y-%m-01")
    ');
  $monthStmt->execute([$uid]);
  $monthGains = floatval($monthStmt->fetchColumn());
} catch (Exception $e) {
}
?>

<main class="container py-4">
  <!-- En-tête -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #0066cc 0%, #1ab991 100%);">
        <div class="card-body text-white py-4">
          <div class="row align-items-center">
            <div class="col-md-8">
              <h1 class="h3 mb-2"><i class="bi bi-hand-wave me-2"></i>Bienvenue, <?= htmlspecialchars($userName) ?> !
              </h1>
              <p class="mb-0 opacity-75">Gérez vos évaluations, récompenses et votre portefeuille</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
              <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                <span class="badge bg-white text-primary">
                  Niveau <?= $userLevel ?> • <?= $levelName ?>
                </span>
                <?php if ($unreadNotifications > 0): ?>
                  <a href="<?= url('index.php?page=notifications') ?>" class="badge bg-danger text-decoration-none">
                    <i class="bi bi-bell-fill me-1"></i><?= $unreadNotifications ?> notifications
                  </a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- KPIs principaux -->
  <div class="row mb-4">
    <div class="col-md-3 col-6 mb-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <p class="text-muted small mb-1"><i class="bi bi-wallet2 me-1"></i>Portefeuille</p>
              <h3 class="mb-0 text-success"><?= formatFCFA($balance) ?></h3>
            </div>
          </div>
          <a href="<?= url('index.php?page=wallet') ?>" class="btn btn-sm btn-outline-success mt-2 w-100">
            Voir détails
          </a>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <p class="text-muted small mb-1"><i class="bi bi-star-fill text-warning me-1"></i>Points</p>
              <h3 class="mb-0"><?= number_format($points) ?> pts</h3>
            </div>
          </div>
          <div class="progress mt-2" style="height:6px">
            <div class="progress-bar bg-warning" style="width:<?= $levelProgress ?>%"></div>
          </div>
          <small class="text-muted"><?= 10 - ($reviewsCount % 10) ?> avis pour niveau suivant</small>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <p class="text-muted small mb-1"><i class="bi bi-chat-quote me-1"></i>Avis donnés</p>
              <h3 class="mb-0"><?= $reviewsCount ?></h3>
            </div>
          </div>
          <a href="<?= url('index.php?page=catalog') ?>" class="btn btn-sm btn-outline-primary mt-2 w-100">
            Donner un avis
          </a>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <p class="text-muted small mb-1"><i class="bi bi-people me-1"></i>Filleuls</p>
              <h3 class="mb-0"><?= $referralCount ?></h3>
            </div>
          </div>
          <a href="<?= url('index.php?page=referrals') ?>" class="btn btn-sm btn-outline-info mt-2 w-100">
            Parrainer
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Actions rapides -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <h5 class="mb-3"><i class="bi bi-lightning-charge me-2"></i>Actions rapides</h5>
          <div class="d-flex flex-wrap gap-2">
            <a href="<?= url('index.php?page=tasks') ?>" class="btn btn-primary">
              <i class="bi bi-check-square me-1"></i>Mes tâches <?php if ($taskCount > 0): ?><span
                  class="badge bg-white text-primary ms-1"><?= $taskCount ?></span><?php endif; ?>
            </a>
            <a href="<?= url('index.php?page=catalog') ?>" class="btn btn-success">
              <i class="bi bi-box-seam me-1"></i>Voir produits
            </a>
            <a href="<?= url('index.php?page=wallet') ?>" class="btn btn-warning">
              <i class="bi bi-cash-stack me-1"></i>Retirer des fonds
            </a>
            <a href="<?= url('index.php?page=profile') ?>" class="btn btn-secondary">
              <i class="bi bi-person me-1"></i>Mon profil
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Activité récente -->
    <div class="col-lg-8 mb-4">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
          <h5 class="mb-0"><i class="bi bi-activity me-2"></i>Activité récente</h5>
          <a href="<?= url('index.php?page=catalog') ?>" class="btn btn-sm btn-outline-primary">Voir plus</a>
        </div>
        <div class="card-body p-0">
          <?php if (empty($activities)): ?>
            <div class="text-center py-4">
              <p class="text-muted mb-2">Aucune activité récente</p>
              <a href="<?= url('index.php?page=catalog') ?>" class="btn btn-primary btn-sm">
                Donner votre premier avis
              </a>
            </div>
          <?php else: ?>
            <div class="list-group list-group-flush">
              <?php foreach ($activities as $a): ?>
                <div class="list-group-item py-3">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <strong>
                        <a href="<?= url('index.php?page=product&id=' . ($a['product_id'] ?? '')) ?>"
                          class="text-decoration-none">
                          <?= htmlspecialchars($a['title'] ?? 'Produit') ?>
                        </a>
                      </strong>
                      <br>
                      <span class="text-warning">
                        <?= str_repeat('★', intval($a['rating'])) ?>
                        <?= str_repeat('☆', 5 - intval($a['rating'])) ?>
                      </span>
                      <small class="text-muted ms-2">
                        <?= date('d/m/Y', strtotime($a['created_at'])) ?>
                      </small>
                    </div>
                    <span class="badge bg-success">+5 pts</span>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Badges & Stats -->
    <div class="col-lg-4 mb-4">
      <!-- Badges -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
          <h5 class="mb-0"><i class="bi bi-trophy me-2"></i>Mes badges</h5>
        </div>
        <div class="card-body">
          <div class="row g-2">
            <?php if ($reviewsCount >= 1): ?>
              <div class="col-4 text-center">
                <div class="p-2 rounded" style="background:linear-gradient(135deg,#fef3c7,#fff);border:2px solid #f59e0b">
                  <div style="font-size:24px"><i class="bi bi-star-fill text-warning"></i></div>
                  <small class="d-block text-truncate">1er Avis</small>
                </div>
              </div>
            <?php endif; ?>

            <?php if ($reviewsCount >= 10): ?>
              <div class="col-4 text-center">
                <div class="p-2 rounded" style="background:linear-gradient(135deg,#e6f0ff,#fff);border:2px solid #0066cc">
                  <div style="font-size:24px"><i class="bi bi-stars text-primary"></i></div>
                  <small class="d-block text-truncate">Expert</small>
                </div>
              </div>
            <?php endif; ?>

            <?php if ($referralCount >= 1): ?>
              <div class="col-4 text-center">
                <div class="p-2 rounded" style="background:linear-gradient(135deg,#e6f9f2,#fff);border:2px solid #10b981">
                  <div style="font-size:24px"><i class="bi bi-people-fill text-success"></i></div>
                  <small class="d-block text-truncate">Parrain</small>
                </div>
              </div>
            <?php endif; ?>

            <?php if ($reviewsCount < 1 && $referralCount < 1): ?>
              <div class="col-12 text-center py-3">
                <p class="text-muted mb-0">Gagnez vos premiers badges en donnant des avis !</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Stats rapides -->
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
          <h5 class="mb-0"><i class="bi bi-graph-up-arrow me-2"></i>Ce mois-ci</h5>
        </div>
        <div class="card-body">
          <div class="d-flex justify-content-between mb-2">
            <span>Gains</span>
            <strong class="text-success"><?= formatFCFA($monthGains) ?></strong>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span>Tâches</span>
            <strong><?= $taskCount ?> complétées</strong>
          </div>
          <div class="d-flex justify-content-between">
            <span>Niveau</span>
            <strong><?= $levelName ?></strong>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>