<?php
require __DIR__ . '/../includes/db.php';
if (session_status() === PHP_SESSION_NONE)
  session_start();
if (empty($_SESSION['user_id'])) {
  $_SESSION['error'] = 'Connectez-vous pour accéder au tableau de bord.';
  header('Location: ' . url('index.php?page=login'));
  exit;
}

$uid = intval($_SESSION['user_id']);
// wallet balance
$balStmt = $pdo->prepare('SELECT COALESCE(balance,0) FROM wallets WHERE user_id = ?');
$balStmt->execute([$uid]);
$balance = number_format(floatval($balStmt->fetchColumn() ?? 0), 2, ',', ' ');
// reviews count
$revStmt = $pdo->prepare('SELECT COUNT(*) FROM reviews WHERE user_id = ?');
$revStmt->execute([$uid]);
$reviewsCount = intval($revStmt->fetchColumn());
// points (simple heuristic: 5 points per review)
$points = $reviewsCount * 5;
// recent activity (latest 6 reviews)
$actStmt = $pdo->prepare('SELECT r.*, p.title FROM reviews r LEFT JOIN products p ON p.id = r.product_id WHERE r.user_id = ? ORDER BY r.created_at DESC LIMIT 6');
$actStmt->execute([$uid]);
$activities = $actStmt->fetchAll();
?>
<main class="container dashboard">
  <section style="margin-bottom:32px">
    <h1>Tableau de bord • Bienvenue <?= htmlspecialchars($_SESSION['user_name'] ?? 'Utilisateur') ?></h1>
    <p style="color:#6c757d">Gérez vos évaluations, récompenses et votre porte-monnaie</p>
  </section>

  <div class="dashboard-grid fade-up">
    <div class="dashboard-card glow">
      <h3><span style="font-size:20px">◆</span> Porte-monnaie</h3>
      <strong style="color:#10b981"><?= $balance ?> €</strong>
      <p>Solde disponible</p>
      <a class="btn-animated" href="<?= url('index.php?page=wallet') ?>"
        style="color:#0066cc;font-size:13px;text-decoration:none;padding:6px 8px;border-radius:6px">Voir détails →</a>
    </div>

    <div class="dashboard-card glow">
      <h3><span style="font-size:20px">★</span> Points cumulés</h3>
      <strong><?= number_format($points) ?> pts</strong>
      <p>À utiliser ou convertir</p>
      <p style="font-size:12px;color:#6c757d;margin-top:8px">1 pt = 0,01€</p>
    </div>

    <div class="dashboard-card glow">
      <h3><span style="font-size:20px">◇</span> Niveau utilisateur</h3>
      <strong>Niveau <?= min(10, floor($reviewsCount / 10) + 1) ?> • <?= ($reviewsCount > 50) ? 'Or' : 'Argent' ?></strong>
      <p>Expert recommandateur</p>
      <div
        style="background:linear-gradient(90deg,#f59e0b,#f97316);height:6px;border-radius:3px;margin-top:8px;width:60%">
      </div>
    </div>

    <div class="dashboard-card glow">
      <h3><span style="font-size:20px">▶</span> Avis donnés</h3>
      <strong><?= $reviewsCount ?></strong>
      <p>Dont <?= intval($reviewsCount) ?> ce mois-ci</p>
    </div>
  </div>

  <section style="margin-top:40px">
    <h2>Activité récente</h2>
    <div
      style="background:white;border-radius:12px;border:1px solid #e0e4e8;overflow:hidden;box-shadow:0 4px 12px rgba(0,0,0,0.08)">
      <?php if (empty($activities)): ?>
        <div style="padding:16px">Aucune activité récente.</div>
      <?php else: ?>
        <?php foreach ($activities as $a): ?>
          <div style="padding:16px;border-bottom:1px solid #e0e4e8;display:flex;justify-content:space-between">
            <div>
              <strong style="display:block;margin-bottom:4px">Vous avez noté
                "<?= htmlspecialchars($a['title'] ?? '') ?>"</strong>
              <p style="color:#6c757d;font-size:13px;margin:0"><?= intval($a['rating']) ?> étoiles ·
                <?= nl2br(htmlspecialchars($a['title'] ?? '')) ?></p>
            </div>
            <p style="color:#10b981;font-weight:600;margin:0">+<?= 5 ?> pts</p>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>

  <section style="margin-top:40px">
    <h2>Mes badges & récompenses</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(100px,1fr));gap:16px">
      <div
        style="background:linear-gradient(135deg,#fef3c7,#fff);padding:16px;border-radius:12px;border:2px solid #f59e0b;text-align:center">
        <div style="font-size:36px;margin-bottom:8px">★</div>
        <p style="font-size:12px;font-weight:600;margin:0">Expert</p>
      </div>
      <div
        style="background:linear-gradient(135deg,#e6f0ff,#fff);padding:16px;border-radius:12px;border:2px solid #0066cc;text-align:center">
        <div style="font-size:36px;margin-bottom:8px">◆</div>
        <p style="font-size:12px;font-weight:600;margin:0">Recommandateur</p>
      </div>
      <div
        style="background:linear-gradient(135deg,#e6f9f2,#fff);padding:16px;border-radius:12px;border:2px solid #10b981;text-align:center">
        <div style="font-size:36px;margin-bottom:8px">◉</div>
        <p style="font-size:12px;font-weight:600;margin:0">Fidèle 30j</p>
      </div>
    </div>
  </section>
</main>