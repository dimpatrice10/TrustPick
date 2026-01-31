<?php
if (!isset($pdo)) {
  require_once __DIR__ . '/../includes/db.php';
}
require_once __DIR__ . '/../includes/image_helper.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) {
  http_response_code(404);
  echo "<main class=\"container\"><h1>Entreprise introuvable</h1><p>Identifiant invalide.</p></main>";
  return;
}

// Récupérer l'entreprise
$cstmt = $pdo->prepare('SELECT * FROM companies WHERE id = ? LIMIT 1');
$cstmt->execute([$id]);
$company = $cstmt->fetch();
if (!$company) {
  http_response_code(404);
  echo "<main class=\"container\"><h1>Entreprise introuvable</h1><p>Aucune entreprise correspondante.</p></main>";
  return;
}

// Statistiques
$prodCountStmt = $pdo->prepare('SELECT COUNT(*) FROM products WHERE company_id = ?');
$prodCountStmt->execute([$id]);
$prodCount = intval($prodCountStmt->fetchColumn());

$reviewsCountStmt = $pdo->prepare('SELECT COUNT(*) FROM reviews r JOIN products p ON p.id = r.product_id WHERE p.company_id = ?');
$reviewsCountStmt->execute([$id]);
$reviewsCount = intval($reviewsCountStmt->fetchColumn());

$avgStmt = $pdo->prepare('SELECT COALESCE(AVG(r.rating),0) FROM reviews r JOIN products p ON p.id = r.product_id WHERE p.company_id = ?');
$avgStmt->execute([$id]);
$avgRating = round(floatval($avgStmt->fetchColumn()), 2);

// Produits publiés
$pstmt = $pdo->prepare('SELECT p.*, (SELECT COALESCE(AVG(r.rating),0) FROM reviews r WHERE r.product_id = p.id) AS avg_rating, (SELECT COUNT(*) FROM reviews r WHERE r.product_id = p.id) AS review_count FROM products p WHERE p.company_id = ? ORDER BY p.created_at DESC LIMIT 12');
$pstmt->execute([$id]);
$products = $pstmt->fetchAll();
?>
<main class="container">
  <div
    style="background:linear-gradient(135deg,#f0f5ff,#fff);padding:24px;border-radius:12px;border:1px solid #c7e9ff;margin-bottom:24px;display:flex;gap:20px;align-items:flex-start">
    <div style="width:120px;height:120px;flex-shrink:0">
      <?php if (!empty($company['logo'])): ?>
        <img src="<?= htmlspecialchars($company['logo']) ?>" alt="<?= htmlspecialchars($company['name']) ?>"
          style="width:120px;height:120px;border-radius:12px;object-fit:cover">
      <?php else: ?>
        <div
          style="width:120px;height:120px;background:linear-gradient(135deg,#0066cc,#0052a3);border-radius:12px;display:flex;align-items:center;justify-content:center;color:white;font-size:36px">
          ◉</div>
      <?php endif; ?>
    </div>
    <div style="flex:1">
      <h1 style="margin-top:0"><?= htmlspecialchars($company['name']) ?></h1>
      <p style="color:#6c757d;margin:0 0 8px">
        <?= htmlspecialchars($company['tagline'] ?? $company['description'] ?? '') ?>
      </p>
      <div style="display:flex;gap:16px;margin:12px 0">
        <div>
          <p style="color:#6c757d;font-size:13px;margin:0">Cote globale</p>
          <strong style="color:#f59e0b;font-size:18px">★ <?= $avgRating ?> / 5</strong>
        </div>
        <div>
          <p style="color:#6c757d;font-size:13px;margin:0">Produits publiés</p>
          <strong style="color:#0066cc;font-size:18px"><?= $prodCount ?></strong>
        </div>
        <div>
          <p style="color:#6c757d;font-size:13px;margin:0">Avis reçus</p>
          <strong style="color:#0066cc;font-size:18px"><?= $reviewsCount ?></strong>
        </div>
      </div>
    </div>
  </div>

  <?php if (!empty($company['description'])): ?>
    <section style="margin-bottom:24px">
      <h2>À propos</h2>
      <p style="color:#1a1f36"><?= nl2br(htmlspecialchars($company['description'])) ?></p>
    </section>
  <?php endif; ?>

  <section style="margin-bottom:32px">
    <h2>Produits publiés</h2>
    <div class="grid">
      <?php if (empty($products)): ?>
        <div style="color:#6c757d">Aucun produit trouvé pour cette entreprise.</div>
      <?php endif; ?>
      <?php foreach ($products as $p): ?>
        <article class="card card-dynamic fade-up">
          <img class="card-img" src="<?= htmlspecialchars(getProductImage($p)) ?>"
            alt="<?= htmlspecialchars($p['title']) ?>" onerror="this.src='<?= htmlspecialchars(getFallbackImage()) ?>'">
          <div class="card-body">
            <?php if (!empty($p['avg_rating']) && $p['avg_rating'] >= 4.5): ?>
              <span class="badge" style="background:#fff5e6;color:#d97706;border-color:#fff5e6">⬆ Top</span>
            <?php endif; ?>
            <h3><a href="<?= url('index.php?page=product&id=' . $p['id']) ?>"><?= htmlspecialchars($p['title']) ?></a>
            </h3>
            <p class="rating">★ <?= round(floatval($p['avg_rating']), 1) ?> (<?= intval($p['review_count']) ?> avis)</p>
            <p class="meta"><?= number_format($p['price'], 2, ',', ' ') ?> €</p>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <section
    style="background:linear-gradient(135deg,#0066cc,#1ab991);color:white;padding:20px;border-radius:12px;text-align:center">
    <h2 style="margin:0 0 8px">Vous êtes client de <?= htmlspecialchars($company['name']) ?> ?</h2>
    <p>Connectez-vous et partagez votre avis pour aider d'autres utilisateurs et gagner des récompenses.</p>
    <a class="btn btn-animated ripple" style="background:white;color:#0b5ed7;padding:10px 16px"
      href="<?= url('index.php?page=login') ?>">Se connecter</a>
  </section>
</main>