<?php
/**
 * Dashboard Admin Entreprise - TrustPick V2
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/url.php';
require_once __DIR__ . '/../includes/helpers.php';

if (session_status() === PHP_SESSION_NONE)
  session_start();

// Vérifier authentification admin entreprise
$role = $_SESSION['role'] ?? $_SESSION['user_role'] ?? '';
if (empty($_SESSION['user_id']) || !in_array($role, ['admin_entreprise', 'super_admin'])) {
  $_SESSION['error'] = 'Accès réservé aux administrateurs d\'entreprise.';
  header('Location: ' . url('index.php?page=login'));
  exit;
}

$uid = intval($_SESSION['user_id']);

// Récupérer l'entreprise de l'admin
$companyStmt = $pdo->prepare('SELECT c.* FROM companies c JOIN users u ON u.company_id = c.id WHERE u.id = ?');
$companyStmt->execute([$uid]);
$company = $companyStmt->fetch();

$companyId = $company['id'] ?? 0;
$companyName = $company['name'] ?? 'Mon Entreprise';

// Statistiques de l'entreprise
if ($companyId > 0) {
  $stmt = $pdo->prepare('SELECT COUNT(*) FROM products WHERE company_id = ?');
  $stmt->execute([$companyId]);
  $totalProducts = intval($stmt->fetchColumn());

  $stmt = $pdo->prepare('SELECT COUNT(*) FROM reviews r JOIN products p ON r.product_id = p.id WHERE p.company_id = ?');
  $stmt->execute([$companyId]);
  $totalReviews = intval($stmt->fetchColumn());

  $stmt = $pdo->prepare('SELECT AVG(r.rating) FROM reviews r JOIN products p ON r.product_id = p.id WHERE p.company_id = ?');
  $stmt->execute([$companyId]);
  $avgRating = round(floatval($stmt->fetchColumn()), 1);

  // Produits récents
  $stmt = $pdo->prepare('
        SELECT p.*, 
               (SELECT COUNT(*) FROM reviews WHERE product_id = p.id) as review_count,
               (SELECT AVG(rating) FROM reviews WHERE product_id = p.id) as avg_rating
        FROM products p 
        WHERE p.company_id = ? 
        ORDER BY p.created_at DESC 
        LIMIT 5
    ');
  $stmt->execute([$companyId]);
  $recentProducts = $stmt->fetchAll();

  // Avis récents
  $stmt = $pdo->prepare('
        SELECT r.*, p.title as product_title, u.name as user_name
        FROM reviews r
        JOIN products p ON r.product_id = p.id
        LEFT JOIN users u ON r.user_id = u.id
        WHERE p.company_id = ?
        ORDER BY r.created_at DESC
        LIMIT 5
    ');
  $stmt->execute([$companyId]);
  $recentReviews = $stmt->fetchAll();
} else {
  // Stats globales si pas d'entreprise liée
  $totalProducts = intval($pdo->query('SELECT COUNT(*) FROM products')->fetchColumn());
  $totalReviews = intval($pdo->query('SELECT COUNT(*) FROM reviews')->fetchColumn());
  $avgRating = round(floatval($pdo->query('SELECT AVG(rating) FROM reviews')->fetchColumn()), 1);
  $recentProducts = [];
  $recentReviews = [];
}

// Distribution des notes
$ratingDistribution = [];
for ($i = 1; $i <= 5; $i++) {
  if ($companyId > 0) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM reviews r JOIN products p ON r.product_id = p.id WHERE p.company_id = ? AND r.rating = ?');
    $stmt->execute([$companyId, $i]);
  } else {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM reviews WHERE rating = ?');
    $stmt->execute([$i]);
  }
  $ratingDistribution[$i] = intval($stmt->fetchColumn());
}
$maxRatingCount = max($ratingDistribution) ?: 1;
?>

<main class="container py-4">
  <!-- En-tête -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
          <h1 class="h2 mb-1"><i class="bi bi-building me-2"></i>Dashboard Entreprise</h1>
          <p class="text-muted mb-0"><?= htmlspecialchars($companyName) ?></p>
        </div>
        <div class="btn-group">
          <a href="<?= url('index.php?page=catalog') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-box-seam me-1"></i>Voir Catalogue
          </a>
          <a href="<?= url('index.php?page=home') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-house me-1"></i>Accueil
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- KPIs -->
  <div class="row mb-4">
    <div class="col-md-4 col-sm-6 mb-3">
      <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #0066cc, #0052a3);">
        <div class="card-body text-white">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <p class="mb-1 opacity-75 small">Produits</p>
              <h3 class="mb-0"><?= number_format($totalProducts) ?></h3>
            </div>
            <div class="display-4 opacity-50"><i class="bi bi-box-seam"></i></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4 col-sm-6 mb-3">
      <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
        <div class="card-body text-white">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <p class="mb-1 opacity-75 small">Avis reçus</p>
              <h3 class="mb-0"><?= number_format($totalReviews) ?></h3>
            </div>
            <div class="display-4 opacity-50"><i class="bi bi-star-fill"></i></div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4 col-sm-6 mb-3">
      <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #10b981, #059669);">
        <div class="card-body text-white">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <p class="mb-1 opacity-75 small">Note moyenne</p>
              <h3 class="mb-0"><?= $avgRating ?: 'N/A' ?> /5</h3>
            </div>
            <div class="display-4 opacity-50"><i class="bi bi-bar-chart-line"></i></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Génération en masse de produits -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
              <h5 class="mb-1"><i class="bi bi-magic me-2"></i>Génération Automatique de Produits</h5>
              <p class="text-muted mb-0 small">Créez plusieurs produits en un clic pour peupler votre catalogue</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateProductsModal">
              <i class="bi bi-plus-circle me-1"></i>Générer des Produits
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="row">
    <!-- Produits récents -->
    <div class="col-lg-8 mb-4">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
          <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Produits Récents</h5>
          <a href="<?= url('index.php?page=catalog') ?>" class="btn btn-sm btn-outline-primary">Voir tout</a>
        </div>
        <div class="card-body p-0">
          <?php if (empty($recentProducts)): ?>
            <div class="text-center py-4">
              <p class="text-muted mb-0">Aucun produit enregistré</p>
            </div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Produit</th>
                    <th>Prix</th>
                    <th>Avis</th>
                    <th>Note</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($recentProducts as $product): ?>
                    <tr>
                      <td>
                        <a href="<?= url('index.php?page=product&id=' . $product['id']) ?>" class="text-decoration-none">
                          <?= htmlspecialchars($product['title']) ?>
                        </a>
                      </td>
                      <td><?= formatFCFA($product['price']) ?></td>
                      <td><?= $product['review_count'] ?></td>
                      <td>
                        <?php if ($product['avg_rating']): ?>
                          <span class="text-warning">★</span> <?= round($product['avg_rating'], 1) ?>
                        <?php else: ?>
                          <span class="text-muted">—</span>
                        <?php endif; ?>
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

    <!-- Distribution des notes -->
    <div class="col-lg-4 mb-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header bg-white border-bottom py-3">
          <h5 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Distribution des Notes</h5>
        </div>
        <div class="card-body">
          <?php for ($i = 5; $i >= 1; $i--):
            $count = $ratingDistribution[$i];
            $percent = $maxRatingCount > 0 ? ($count / $maxRatingCount) * 100 : 0;
            ?>
            <div class="d-flex align-items-center mb-2">
              <span class="me-2" style="width:30px"><?= $i ?>★</span>
              <div class="flex-grow-1 bg-light rounded" style="height:20px">
                <div class="bg-warning rounded h-100" style="width:<?= $percent ?>%"></div>
              </div>
              <span class="ms-2 text-muted small" style="width:40px"><?= $count ?></span>
            </div>
          <?php endfor; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Avis récents -->
  <div class="row">
    <div class="col-12">
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
          <h5 class="mb-0"><i class="bi bi-star me-2"></i>Avis Récents</h5>
        </div>
        <div class="card-body p-0">
          <?php if (empty($recentReviews)): ?>
            <div class="text-center py-4">
              <p class="text-muted mb-0">Aucun avis reçu</p>
            </div>
          <?php else: ?>
            <div class="list-group list-group-flush">
              <?php foreach ($recentReviews as $review): ?>
                <div class="list-group-item py-3">
                  <div class="d-flex justify-content-between align-items-start">
                    <div>
                      <div class="mb-1">
                        <span class="text-warning"><?= str_repeat('★', $review['rating']) ?></span>
                        <span class="text-muted"><?= str_repeat('☆', 5 - $review['rating']) ?></span>
                        <span class="ms-2 fw-bold"><?= htmlspecialchars($review['product_title']) ?></span>
                      </div>
                      <p class="mb-1 text-muted small">
                        par <?= htmlspecialchars($review['user_name'] ?? 'Anonyme') ?>
                        · <?= date('d/m/Y', strtotime($review['created_at'])) ?>
                      </p>
                      <p class="mb-0">
                        <?= htmlspecialchars(substr($review['body'] ?? '', 0, 150)) ?>
                        <?= strlen($review['body'] ?? '') > 150 ? '...' : '' ?>
                      </p>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</main>

<!-- Modal Génération de Produits -->
<div class="modal fade" id="generateProductsModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-magic me-2"></i>Générer des Produits</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="generateProductsForm" action="<?= url('actions/company_generate_products.php') ?>" method="POST">
        <div class="modal-body">
          <div class="alert alert-info small">
            <i class="bi bi-info-circle me-1"></i>
            Générez automatiquement des produits avec des noms réalistes pour votre catalogue.
          </div>

          <div class="mb-3">
            <label class="form-label">Nombre de produits à générer</label>
            <select name="count" class="form-select" required>
              <option value="5">5 produits</option>
              <option value="10" selected>10 produits</option>
              <option value="20">20 produits</option>
              <option value="30">30 produits</option>
              <option value="50">50 produits (max)</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Catégorie (optionnel)</label>
            <select name="category_id" class="form-select">
              <option value="">Toutes les catégories (aléatoire)</option>
              <?php
              $categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
              foreach ($categories as $cat):
                ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Fourchette de prix</label>
            <div class="row g-2">
              <div class="col-6">
                <div class="input-group">
                  <span class="input-group-text">Min</span>
                  <input type="number" name="price_min" class="form-control" value="1000" min="100">
                  <span class="input-group-text">FCFA</span>
                </div>
              </div>
              <div class="col-6">
                <div class="input-group">
                  <span class="input-group-text">Max</span>
                  <input type="number" name="price_max" class="form-control" value="50000" min="100">
                  <span class="input-group-text">FCFA</span>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary" id="btnGenerateProducts">
            <i class="bi bi-magic me-1"></i>Générer
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  document.getElementById('generateProductsForm')?.addEventListener('submit', function (e) {
    const btn = document.getElementById('btnGenerateProducts');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Génération...';
  });
</script>