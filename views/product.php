<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/image_helper.php';
require_once __DIR__ . '/../includes/url.php';
require_once __DIR__ . '/../includes/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$id = intval($_GET['id'] ?? 0);
if (!$id) {
  echo "<main class=\"container\"><div class='fade-up' style='padding:24px;background:white;border-radius:12px'>Produit non trouvé.</div></main>";
  return;
}

$stmt = $pdo->prepare('SELECT p.*, c.name AS company_name FROM products p LEFT JOIN companies c ON p.company_id = c.id WHERE p.id = ?');
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) {
  echo "<main class=\"container\"><div class='fade-up' style='padding:24px;background:white;border-radius:12px'>Produit introuvable.</div></main>";
  return;
}

$avg = $pdo->prepare('SELECT AVG(rating) as avg_rating, COUNT(*) as cnt FROM reviews WHERE product_id = ?');
$avg->execute([$id]);
$stats = $avg->fetch();
$avg_rating = $stats['avg_rating'] ? round($stats['avg_rating'], 1) : 'N/A';
$review_count = $stats['cnt'] ?? 0;

// Récupérer les avis avec le nombre de likes
$reviews = $pdo->prepare('
  SELECT r.*, u.name AS user_name, COALESCE(r.likes_count, 0) as likes_count
  FROM reviews r 
  LEFT JOIN users u ON r.user_id = u.id 
  WHERE r.product_id = ? 
  ORDER BY r.created_at DESC 
  LIMIT 10
');
$reviews->execute([$id]);
$reviews = $reviews->fetchAll();

// Récupérer les likes de l'utilisateur connecté pour ces avis
$userLikes = [];
if (!empty($_SESSION['user_id'])) {
  $reviewIds = array_column($reviews, 'id');
  if (!empty($reviewIds)) {
    $placeholders = implode(',', array_fill(0, count($reviewIds), '?'));

    // Essayer d'abord review_likes, sinon review_reactions
    try {
      $likesStmt = $pdo->prepare("SELECT review_id FROM review_likes WHERE user_id = ? AND review_id IN ($placeholders)");
      $likesStmt->execute(array_merge([$_SESSION['user_id']], $reviewIds));
      $userLikes = $likesStmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
      // Fallback sur review_reactions
      try {
        $likesStmt = $pdo->prepare("SELECT review_id FROM review_reactions WHERE user_id = ? AND review_id IN ($placeholders) AND reaction_type = 'like'");
        $likesStmt->execute(array_merge([$_SESSION['user_id']], $reviewIds));
        $userLikes = $likesStmt->fetchAll(PDO::FETCH_COLUMN);
      } catch (Exception $e2) {
        $userLikes = [];
      }
    }
  }
}
?>

<main class="container product-page">
    <div class="product-header fade-up"
        style="background:linear-gradient(135deg,#e6f0ff,#fff);padding:32px;border-radius:16px;border:1px solid #c7e9ff">
        <img src="<?= htmlspecialchars(getProductImage($product, 500, 500)) ?>"
            alt="<?= htmlspecialchars($product['title'] ?? '') ?>"
            onerror="this.src='<?= htmlspecialchars(getFallbackImage(500, 500)) ?>'"
            style="border-radius:12px;box-shadow:0 8px 24px rgba(0,102,204,0.15)">
        <div class="product-info">
            <h1><?= htmlspecialchars($product['title']) ?></h1>
            <p class="rating">★ <?= htmlspecialchars($avg_rating) ?> /5 (<?= intval($review_count) ?> avis)</p>
            <p class="price">Prix indicatif: <?= formatFCFA($product['price'] ?? 0) ?></p>
            <p style="color:#6c757d;margin:12px 0 20px">Marque:
                <strong><?= htmlspecialchars($product['brand'] ?? $product['company_name'] ?? '—') ?></strong> ·
                Catégorie:
                <?= htmlspecialchars($product['category'] ?? '—') ?>
            </p>
            <?php if (empty($_SESSION['user_id'])): ?>
            <p><a class="btn btn-animated ripple" href="<?= url('index.php?page=login') ?>">Se connecter pour noter</a>
            </p>
            <?php else: ?>
            <p><a class="btn btn-animated ripple" href="#leave-review">Laisser un avis</a></p>
            <p style="margin-top:12px">
                <button class="btn btn-outline"
                    onclick="document.getElementById('recommend-modal').style.display='block'"
                    style="background:white;border:2px solid #0066cc;color:#0066cc;padding:10px 20px;border-radius:8px;cursor:pointer">
                    <i class="bi bi-megaphone me-1"></i>Recommander ce produit (+<?= formatFCFA(200) ?>)
                </button>
            </p>
            <?php endif; ?>
            <div class="glow"
                style="background:linear-gradient(135deg,#e6f9f2,#fff);padding:12px;border-radius:8px;margin-top:16px;font-size:13px;border-left:4px solid #1ab991">
                <p style="margin:0">▶ <?= intval($product['recommendations'] ?? 0) ?> recommandations</p>
                <p style="margin:4px 0 0">▶ <?= intval($product['views'] ?? 0) ?> visites</p>
            </div>
        </div>
    </div>

    <section class="description fade-up"
        style="background:white;padding:24px;border-radius:12px;border:1px solid #e0e4e8;margin-bottom:32px;box-shadow:0 4px 12px rgba(0,0,0,0.08)">
        <h2>Description</h2>
        <p><?= nl2br(htmlspecialchars($product['description'] ?? 'Aucune description fournie.')) ?></p>
        <?php if (!empty($product['features'] ?? '')): ?>
        <h3 style="margin-top:20px;margin-bottom:12px">Caractéristiques principales</h3>
        <ul style="color:#1a1f36;padding-left:20px;margin:0">
            <?php foreach (explode("\n", ($product['features'] ?? '')) as $f):
          if (trim($f)): ?>
            <li><?= htmlspecialchars($f) ?></li>
            <?php endif; endforeach; ?>
        </ul>
        <?php endif; ?>
    </section>

    <section class="reviews">
        <h2>Avis récents (<?= intval($review_count) ?> avis)</h2>

        <?php if (empty($reviews)): ?>
        <div
            style="background:white;padding:16px;border-radius:12px;border:1px solid #e0e4e8;margin-bottom:12px;box-shadow:0 4px 12px rgba(0,0,0,0.04)">
            Aucun avis pour le moment. Soyez le premier !</div>
        <?php else: ?>
        <?php foreach ($reviews as $r):
        $isLiked = in_array($r['id'], $userLikes);
        $likesCount = intval($r['likes_count']);
        ?>
        <div class="review-card"
            style="background:white;padding:16px;border-radius:12px;border:1px solid #e0e4e8;margin-bottom:12px;box-shadow:0 4px 12px rgba(0,0,0,0.08)">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px">
                <div>
                    <strong style="font-size:15px"><?= htmlspecialchars($r['user_name'] ?: 'Utilisateur') ?></strong>
                    <p style="color:#6c757d;font-size:12px;margin:0">Acheteur vérifié ·
                        <?= date('j M Y', strtotime($r['created_at'])) ?>
                    </p>
                </div>
                <span style="color:#f59e0b;font-weight:600"><?= str_repeat('★', intval($r['rating'])) ?></span>
            </div>
            <p style="margin:0 0 12px"><?= nl2br(htmlspecialchars($r['body'])) ?></p>

            <!-- Bouton Like -->
            <div class="review-actions"
                style="display:flex;gap:12px;align-items:center;padding-top:12px;border-top:1px solid #f0f0f0">
                <button class="like-btn <?= $isLiked ? 'liked' : '' ?>" data-review-id="<?= intval($r['id']) ?>"
                    aria-pressed="<?= $isLiked ? 'true' : 'false' ?>"
                    title="<?= $isLiked ? 'Retirer le like' : 'Aimer cet avis' ?>">
                    <i class="bi bi-hand-thumbs-up<?= $isLiked ? '-fill' : '' ?>"></i>
                    <span class="like-count"><?= $likesCount ?></span>
                    <span class="like-text"><?= $likesCount > 1 ? 'likes' : 'like' ?></span>
                </button>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <div style="text-align:center;margin-top:20px">
            <a class="btn btn-secondary btn-animated ripple"
                href="<?= url('index.php?page=product&id=' . $product['id']) ?>">Voir tous
                les avis</a>
        </div>
    </section>

    <section style="margin-top:48px">
        <h2><span style="font-size:24px">◈</span> Produits similaires</h2>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px;margin-top:24px">
            <?php
      // Récupérer d'autres produits de la même entreprise
      $sim = $pdo->prepare('SELECT * FROM products WHERE company_id = ? AND id != ? LIMIT 3');
      $sim->execute([$product['company_id'], $product['id']]);
      foreach ($sim->fetchAll() as $s): ?>
            <article class="card card-dynamic">
                <img class="card-img" src="<?= htmlspecialchars(getProductImage($s)) ?>"
                    alt="<?= htmlspecialchars($s['title']) ?>"
                    onerror="this.src='<?= htmlspecialchars(getFallbackImage()) ?>'">
                <div class="card-body">
                    <span class="badge">Alternatif</span>
                    <h3><?= htmlspecialchars($s['title']) ?></h3>
                    <p style="color:#6c757d;font-size:13px">Prix: <?= formatFCFA($s['price']) ?></p>
                    <p class="rating">★ <?= htmlspecialchars($s['rating'] ?? '—') ?></p>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section
        style="margin-top:48px;background:linear-gradient(135deg,#f5f3ff,#fff);padding:24px;border-radius:12px;border:1px solid #e0e4e8;box-shadow:0 4px 12px rgba(0,0,0,0.08)">
        <h2><span style="font-size:24px">◉</span> Informations vendeur</h2>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
            <div style="padding-right:24px;border-right:1px solid #e0e4e8">
                <p style="margin:0 0 8px"><strong
                        style="color:#0066cc"><?= htmlspecialchars($product['company_name'] ?: 'Vendeur') ?></strong>
                </p>
                <p style="color:#6c757d;font-size:13px;margin:0 0 12px">Profil du vendeur et informations de contact.
                </p>
                <p style="font-size:13px;margin:0 0 12px"><strong>Cote globale:</strong> <span
                        style="color:#0066cc;font-weight:700"><?= htmlspecialchars($product['company_rating'] ?? '—') ?></span>
                </p>
                <p style="font-size:13px;margin:0 0 12px"><strong>Produits:</strong>
                    <?= intval($product['company_products'] ?? 0) ?> • <strong>Avis:</strong>
                    <?= intval($product['company_reviews'] ?? 0) ?></p>
                <p style="font-size:13px;margin:0"><a style="color:#0066cc;text-decoration:none;font-weight:500"
                        href="<?= url('index.php?page=company&id=' . intval($product['company_id'])) ?>">Voir le profil
                        vendeur
                        →</a></p>
            </div>
            <div>
                <p style="font-size:13px;margin:0 0 12px"><strong>▶ Livraison gratuite</strong> dès 25 000 FCFA</p>
                <p style="font-size:13px;margin:0 0 12px"><strong>▶ Retours gratuits</strong> 30 jours</p>
                <p style="font-size:13px;margin:0 0 12px"><strong>▶ Garantie</strong> 2 ans constructeur</p>
                <p style="font-size:13px;margin:0"><strong>▶ Support</strong> Chat/Email 24h/24</p>
            </div>
        </div>
    </section>

    <section class="float"
        style="background:linear-gradient(135deg,#0066cc 0%,#1ab991 100%);color:white;padding:40px;border-radius:12px;margin-top:48px;text-align:center;box-shadow:0 12px 32px rgba(0,102,204,0.25)">
        <h2 style="color:white;margin-bottom:12px">Intéressé par ce produit ?</h2>
        <p style="color:rgba(255,255,255,0.95);margin:0 0 24px">Connectez-vous et partagez votre avis avec la
            communauté.
            Gagnez des points et de l'argent réel !</p>
        <?php if (empty($_SESSION['user_id'])): ?>
        <a class="btn" style="background:white;color:#0066cc;padding:12px 28px;font-size:16px;font-weight:700"
            href="<?= url('index.php?page=login') ?>">Se connecter et noter →</a>
        <?php else: ?>
        <a class="btn" style="background:white;color:#0066cc;padding:12px 28px;font-size:16px;font-weight:700"
            href="#leave-review">Laisser un avis →</a>
        <?php endif; ?>
    </section>

    <?php if (!empty($_SESSION['user_id'])): ?>
    <section id="leave-review" style="margin-top:32px">
        <h2>Laisser un avis</h2>
        <form action="<?= url('actions/review.php') ?>" method="post"
            style="background:white;padding:20px;border-radius:12px;border:1px solid #e0e4e8">
            <input type="hidden" name="product_id" value="<?= intval($product['id']) ?>">
            <div style="display:flex;gap:12px;align-items:center;margin-bottom:12px">
                <label style="font-weight:600">Note:</label>
                <select name="rating" style="padding:8px;border-radius:8px">
                    <option value="5">5 — Excellent</option>
                    <option value="4">4 — Très bien</option>
                    <option value="3">3 — Moyen</option>
                    <option value="2">2 — Médiocre</option>
                    <option value="1">1 — Mauvais</option>
                </select>
            </div>
            <div style="margin-bottom:12px">
                <input class="input-enhanced" type="text" name="title" placeholder="Titre (facultatif)"
                    style="width:100%;padding:10px;border-radius:8px;border:1px solid #e6eef8">
            </div>
            <div style="margin-bottom:12px">
                <textarea name="body" rows="4" placeholder="Racontez votre expérience..."
                    style="width:100%;padding:10px;border-radius:8px;border:1px solid #e6eef8"></textarea>
            </div>
            <div style="text-align:right">
                <button class="btn btn-animated" type="submit">Publier l'avis (+<?= formatFCFA(500) ?>)</button>
            </div>
        </form>
    </section>
    <?php endif; ?>

    <!-- Modal Recommandation -->
    <div id="recommend-modal"
        style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:10000;align-items:center;justify-content:center"
        onclick="if(event.target.id==='recommend-modal') this.style.display='none'">
        <div style="background:white;padding:32px;border-radius:12px;max-width:500px;width:90%;box-shadow:0 8px 32px rgba(0,0,0,0.2)"
            onclick="event.stopPropagation()">
            <h2 style="margin-top:0"><i class="bi bi-megaphone me-2"></i>Recommander ce produit</h2>
            <p style="color:#6c757d;margin-bottom:20px">Partagez ce produit avec un ami et gagnez
                <strong><?= formatFCFA(200) ?></strong> immédiatement !
            </p>
            <form action="<?= url('actions/recommend.php') ?>" method="POST">
                <input type="hidden" name="product_id" value="<?= intval($product['id']) ?>">
                <div style="margin-bottom:16px">
                    <label style="display:block;margin-bottom:8px;font-weight:600">À qui recommandez-vous ce produit
                        ?</label>
                    <input type="text" name="contact_info" placeholder="Nom, email ou téléphone"
                        style="width:100%;padding:12px;border:1px solid #e6eef8;border-radius:8px" required>
                    <small style="color:#6c757d;display:block;margin-top:4px">Ex: Jean Dupont, jean@email.com, +237 690
                        123
                        456</small>
                </div>
                <div style="display:flex;gap:12px;justify-content:flex-end">
                    <button type="button" onclick="document.getElementById('recommend-modal').style.display='none'"
                        style="padding:10px 20px;border:1px solid #e0e4e8;background:white;border-radius:8px;cursor:pointer">
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-animated ripple">
                        Envoyer (+<?= formatFCFA(200) ?>)
                    </button>
                </div>
            </form>
        </div>
    </div>

</main>

<!-- Styles pour les boutons Like -->
<style>
.like-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    background: #f8f9fa;
    border: 1px solid #e0e4e8;
    border-radius: 20px;
    color: #6c757d;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.like-btn:hover {
    background: #e6f0ff;
    border-color: #0066cc;
    color: #0066cc;
}

.like-btn.liked {
    background: #0066cc;
    border-color: #0066cc;
    color: white;
}

.like-btn.liked:hover {
    background: #0052a3;
}

.like-btn.loading {
    opacity: 0.7;
    pointer-events: none;
}

.like-btn i {
    font-size: 16px;
}

.like-count {
    font-weight: 600;
}

.like-text {
    font-weight: 400;
}

/* Toast pour les likes */
.like-toast {
    position: fixed;
    bottom: 20px;
    right: 20px;
    padding: 12px 20px;
    background: #333;
    color: white;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    z-index: 10001;
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.like-toast.show {
    opacity: 1;
    transform: translateY(0);
}

.like-toast-success {
    background: #10b981;
}

.like-toast-error {
    background: #ef4444;
}

.like-toast-info {
    background: #0066cc;
}

.like-toast i {
    font-size: 18px;
}
</style>

<!-- Script JS pour les likes -->
<script src="<?= url('public/assets/js/likes.js') ?>"></script>