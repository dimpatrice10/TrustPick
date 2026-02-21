<?php
// Catalogue dynamique: filtres via GET, tri, pagination
if (!isset($pdo)) {
  require_once __DIR__ . '/../includes/db.php';
}

// Charger la fonction helper pour les images
require_once __DIR__ . '/../includes/image_helper.php';
require_once __DIR__ . '/../includes/helpers.php';

$perPage = 12;
$pageNum = max(1, intval($_GET['p'] ?? 1));
$offset = ($pageNum - 1) * $perPage;

// Charger entreprises (la base fournie n'a pas forcément categories/brands)
try {
  $companies = $pdo->query('SELECT id, name FROM companies ORDER BY name')->fetchAll();
} catch (Exception $e) {
  $companies = [];
}

// détecter colonnes optionnelles dans products (stock, is_eco)
$hasStock = false;
$hasEco = false;
try {
  $colStmt = $pdo->prepare("SELECT column_name FROM information_schema.columns WHERE table_schema = 'public' AND table_name = 'products' AND column_name IN ('stock','is_eco')");
  $colStmt->execute();
  $cols = $colStmt->fetchAll(PDO::FETCH_COLUMN);
  $hasStock = in_array('stock', $cols, true);
  $hasEco = in_array('is_eco', $cols, true);
} catch (Exception $e) {
  $hasStock = false;
  $hasEco = false;
}

// Construire la clause WHERE dynamiquement
$where = [];
$params = [];

// search
if (!empty($_GET['q'])) {
  $where[] = '(p.title LIKE ? OR p.description LIKE ?)';
  $q = '%' . str_replace('%', '\\%', (string) $_GET['q']) . '%';
  $params[] = $q;
  $params[] = $q;
}

// company filter (exists in this schema)
if (!empty($_GET['company']) && $_GET['company'] !== 'all') {
  $where[] = 'p.company_id = ?';
  $params[] = intval($_GET['company']);
}

// price range
if (!empty($_GET['price'])) {
  switch ($_GET['price']) {
    case 'lt50':
      $where[] = 'p.price < ?';
      $params[] = 50;
      break;
    case '50-100':
      $where[] = 'p.price BETWEEN ? AND ?';
      $params[] = 50;
      $params[] = 100;
      break;
    case '100-250':
      $where[] = 'p.price BETWEEN ? AND ?';
      $params[] = 100;
      $params[] = 250;
      break;
    case 'gt250':
      $where[] = 'p.price > ?';
      $params[] = 250;
      break;
  }
}

// in stock (only if column exists)
if ($hasStock && !empty($_GET['in_stock'])) {
  $where[] = 'p.stock > 0';
}

// eco (only if column exists)
if ($hasEco && !empty($_GET['eco'])) {
  $where[] = 'p.is_eco = 1';
}

// min rating
if (!empty($_GET['min_rating'])) {
  $where[] = '(SELECT COALESCE(AVG(r.rating),0) FROM reviews r WHERE r.product_id = p.id) >= ?';
  $params[] = floatval($_GET['min_rating']);
}

// build order by
$sort = $_GET['sort'] ?? 'best_rating';
switch ($sort) {
  case 'recommended':
    $orderBy = 'reco_count DESC';
    break;
  case 'recent':
    $orderBy = 'p.created_at DESC';
    break;
  case 'most_commented':
    $orderBy = 'review_count DESC';
    break;
  case 'tendance':
    $orderBy = 'reco_count DESC';
    break;
  case 'best_rating':
  default:
    $orderBy = 'avg_rating DESC';
}

// count total
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$countSql = "SELECT COUNT(*) as cnt FROM products p $whereSql";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$total = intval($countStmt->fetchColumn());

$limit = intval($perPage);
$off = intval($offset);
// main products query with subqueries for metrics
$hasReco = false;
try {
  $chk = $pdo->prepare("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'public' AND table_name = ?");
  $chk->execute(['recommendations']);
  $hasReco = intval($chk->fetchColumn()) > 0;
} catch (Exception $e) {
  $hasReco = false;
}

$recoSelect = $hasReco
  ? "(SELECT COUNT(*) FROM recommendations rec WHERE rec.product_id = p.id) AS reco_count"
  : "0 AS reco_count";

$sql = "SELECT p.*, c.name AS company_name,
  (SELECT COALESCE(AVG(r.rating),0) FROM reviews r WHERE r.product_id = p.id) AS avg_rating,
  (SELECT COUNT(*) FROM reviews r WHERE r.product_id = p.id) AS review_count,
  {$recoSelect}
  FROM products p
  LEFT JOIN companies c ON c.id = p.company_id
  $whereSql
  ORDER BY {$orderBy}
  LIMIT {$limit} OFFSET {$off}";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

?>
<main class="container">
  <section class="fade-up" style="margin-bottom:24px">
    <h1>Catalogue</h1>
    <p style="color:#6c757d;margin:0">Parcourez les produits évalués par notre communauté.</p>

    <form method="get" action="<?= url('index.php') ?>" style="margin-top:16px;max-width:900px">
      <input type="hidden" name="page" value="catalog">
      <div style="display:flex;gap:12px">
        <input name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" class="input-enhanced" type="search"
          placeholder="▶ Cherchez un produit..."
          style="flex:1;padding:12px 16px;border:1px solid #e0e4e8;border-radius:10px;font-size:14px">
        <button class="btn btn-animated ripple" type="submit">Chercher</button>
      </div>
    </form>
  </section>

  <div class="layout-row">
    <aside class="filters">
      <h3 style="margin-bottom:16px">Filtrer</h3>
      <form method="get" action="<?= url('index.php') ?>">
        <input type="hidden" name="page" value="catalog">
        <label
          style="display:block;font-weight:600;margin-bottom:8px;font-size:13px;text-transform:uppercase;color:#6c757d">Entreprise</label>
        <select name="company" class="input-enhanced"
          style="width:100%;padding:8px;border:1px solid #e0e4e8;border-radius:8px;color:#1a1f36;margin-bottom:12px">
          <option value="all">Toutes les entreprises</option>
          <?php foreach ($companies as $comp): ?>
            <option value="<?= $comp['id'] ?>" <?= (isset($_GET['company']) && $_GET['company'] == $comp['id']) ? 'selected' : '' ?>><?= htmlspecialchars($comp['name']) ?></option>
          <?php endforeach; ?>
        </select>

        <label
          style="display:block;font-weight:600;margin-bottom:8px;font-size:13px;text-transform:uppercase;color:#6c757d">Prix</label>
        <select name="price"
          style="width:100%;padding:8px;border:1px solid #e0e4e8;border-radius:8px;color:#1a1f36;margin-bottom:12px">
          <option value="">Tous les prix</option>
          <option value="lt50" <?= (($_GET['price'] ?? '') == 'lt50') ? 'selected' : '' ?>>Moins de 25 000 FCFA</option>
          <option value="50-100" <?= (($_GET['price'] ?? '') == '50-100') ? 'selected' : '' ?>>25 000 - 50 000 FCFA
          </option>
          <option value="100-250" <?= (($_GET['price'] ?? '') == '100-250') ? 'selected' : '' ?>>50 000 - 125 000 FCFA
          </option>
          <option value="gt250" <?= (($_GET['price'] ?? '') == 'gt250') ? 'selected' : '' ?>>Plus de 125 000 FCFA</option>
        </select>

        <label
          style="display:block;font-weight:600;margin-bottom:8px;font-size:13px;text-transform:uppercase;color:#6c757d">Note
          minimale</label>
        <select name="min_rating"
          style="width:100%;padding:8px;border:1px solid #e0e4e8;border-radius:8px;color:#1a1f36;margin-bottom:12px">
          <option value="">Toutes</option>
          <option value="4.5" <?= (($_GET['min_rating'] ?? '') == '4.5') ? 'selected' : '' ?>>4.5+ ★★★★★</option>
          <option value="4.0" <?= (($_GET['min_rating'] ?? '') == '4.0') ? 'selected' : '' ?>>4.0+ ★★★★</option>
          <option value="3.0" <?= (($_GET['min_rating'] ?? '') == '3.0') ? 'selected' : '' ?>>3.0+ ★★★</option>
        </select>

        <?php if ($hasStock): ?>
          <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;margin-bottom:12px">
            <input type="checkbox" name="in_stock" value="1" <?= !empty($_GET['in_stock']) ? 'checked' : '' ?>
              style="cursor:pointer"> Produits en stock
          </label>
        <?php endif; ?>
        <?php if ($hasEco): ?>
          <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;margin-bottom:12px">
            <input type="checkbox" name="eco" value="1" <?= !empty($_GET['eco']) ? 'checked' : '' ?>
              style="cursor:pointer"> Produits éco-responsables
          </label>
        <?php endif; ?>

        <div style="display:flex;gap:8px;margin-top:8px">
          <button class="btn btn-animated ripple" type="submit">Appliquer</button>
          <a class="btn btn-secondary btn-animated" href="<?= url('index.php?page=catalog') ?>">Réinitialiser</a>
        </div>
      </form>
    </aside>

    <section class="products">
      <div style="margin-bottom:16px;display:flex;justify-content:space-between;align-items:center">
        <p style="color:#6c757d;margin:0;font-size:14px"><strong><?= $total ?></strong> produits</p>
        <form method="get" action="<?= url('index.php') ?>">
          <input type="hidden" name="page" value="catalog">
          <label style="display:flex;align-items:center;gap:8px">
            <select name="sort"
              style="padding:8px 12px;border:1px solid #e0e4e8;border-radius:8px;font-size:13px;color:#1a1f36">
              <option value="best_rating" <?= ($sort == 'best_rating') ? 'selected' : '' ?>>Mieux noté</option>
              <option value="recommended" <?= ($sort == 'recommended') ? 'selected' : '' ?>>Plus recommandé</option>
              <option value="recent" <?= ($sort == 'recent') ? 'selected' : '' ?>>Plus récent</option>
              <option value="most_commented" <?= ($sort == 'most_commented') ? 'selected' : '' ?>>Plus commenté</option>
              <option value="tendance" <?= ($sort == 'tendance') ? 'selected' : '' ?>>Tendance</option>
            </select>
            <button class="btn btn-animated" type="submit">Appliquer</button>
          </label>
        </form>
      </div>

      <div class="grid">
        <?php if (empty($products)): ?>
          <div style="color:#6c757d">Aucun produit trouvé pour ces filtres.</div>
        <?php endif; ?>
        <?php foreach ($products as $p): ?>
          <article class="card card-dynamic fade-up">
            <img class="card-img" src="<?= htmlspecialchars(getProductImage($p)) ?>"
              alt="<?= htmlspecialchars($p['title']) ?>" onerror="this.src='<?= htmlspecialchars(getFallbackImage()) ?>'">
            <div class="card-body">
              <?php if (!empty($p['reco_count']) && $p['reco_count'] > 50): ?>
                <span class="badge" style="background:#fff5e6;color:#d97706;border-color:#fff5e6">⬆ Top</span>
              <?php endif; ?>
              <h3><a href="<?= url('index.php?page=product&id=' . $p['id']) ?>"><?= htmlspecialchars($p['title']) ?></a>
              </h3>
              <p style="color:#6c757d;font-size:13px;margin:0"><?= htmlspecialchars($p['company_name'] ?? '') ?></p>
              <p class="rating" style="margin-top:8px">★ <?= round(floatval($p['avg_rating']), 1) ?>/5
                (<?= intval($p['review_count']) ?>)</p>
              <p class="meta"><?= intval($p['reco_count']) ?> recommandations •
                <?= formatFCFA($p['price']) ?>
              </p>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <div style="text-align:center;margin-top:24px">
        <?php if ($total > ($pageNum * $perPage)): ?>
          <?php
          $qp = $_GET;
          $qp['p'] = $pageNum + 1;
          $nextUrl = url('index.php') . '?' . http_build_query(array_merge(['page' => 'catalog'], $qp));
          ?>
          <a class="btn btn-secondary" href="<?= $nextUrl ?>">Charger plus de produits ▼</a>
        <?php endif; ?>
      </div>
    </section>
  </div>
</main>