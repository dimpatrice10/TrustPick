<?php
require __DIR__ . '/../includes/db.php';
if (session_status() === PHP_SESSION_NONE)
  session_start();
if (empty($_SESSION['user_id'])) {
  $_SESSION['error'] = 'Connectez-vous pour accéder au porte-monnaie.';
  header('Location: ' . url('index.php?page=login'));
  exit;
}

$uid = intval($_SESSION['user_id']);
// current balance
$balStmt = $pdo->prepare('SELECT COALESCE(balance,0) FROM wallets WHERE user_id = ?');
$balStmt->execute([$uid]);
$balance = floatval($balStmt->fetchColumn() ?? 0);
// total earned (sum of credits in wallets history not present); approximate by summing withdrawals + current
$wdStmt = $pdo->prepare('SELECT IFNULL(SUM(amount),0) FROM withdrawals WHERE user_id = ?');
$wdStmt->execute([$uid]);
$withdrawn = floatval($wdStmt->fetchColumn());
// count withdrawals
$wdCountStmt = $pdo->prepare('SELECT COUNT(*) FROM withdrawals WHERE user_id = ?');
$wdCountStmt->execute([$uid]);
$wdCount = intval($wdCountStmt->fetchColumn());
// reviews count / points
$pointsStmt = $pdo->prepare('SELECT COUNT(*) FROM reviews WHERE user_id = ?');
$pointsStmt->execute([$uid]);
$pointsCount = intval($pointsStmt->fetchColumn());
// recent earnings: use reviews as proxy
$earningsStmt = $pdo->prepare('SELECT r.created_at, r.rating, p.title FROM reviews r LEFT JOIN products p ON p.id = r.product_id WHERE r.user_id = ? ORDER BY r.created_at DESC LIMIT 10');
$earningsStmt->execute([$uid]);
$earnings = $earningsStmt->fetchAll();
// withdrawals list
$withListStmt = $pdo->prepare('SELECT * FROM withdrawals WHERE user_id = ? ORDER BY created_at DESC LIMIT 10');
$withListStmt->execute([$uid]);
$withList = $withListStmt->fetchAll();
?>
<main class="container">
  <h1 style="margin-bottom:24px">Porte-monnaie</h1>

  <div class="dashboard-grid fade-up" style="margin-bottom:32px">
    <div class="dashboard-card glow">
      <h3><span style="font-size:20px">◆</span> Solde disponible</h3>
      <strong style="color:#10b981"><?= number_format($balance, 2, ',', ' ') ?> €</strong>
      <p>Prêt à retirer</p>
    </div>

    <div class="dashboard-card glow">
      <h3><span style="font-size:20px">▲</span> Gains cumulés</h3>
      <strong><?= number_format($balance + $withdrawn, 2, ',', ' ') ?> €</strong>
      <p>Depuis votre inscription (approx.)</p>
    </div>

    <div class="dashboard-card glow">
      <h3><span style="font-size:20px">★</span> Points</h3>
      <strong><?= number_format($pointsCount * 5) ?> pts</strong>
      <p>À convertir en argent</p>
    </div>

    <div class="dashboard-card glow">
      <h3><span style="font-size:20px">◇</span> Retraits</h3>
      <strong><?= $wdCount ?></strong>
      <p>Total: <?= number_format($withdrawn, 2, ',', ' ') ?> €</p>
    </div>
  </div>

  <div class="wallet-history-grid" style="display:grid;gap:24px;margin-bottom:32px">
    <section
      style="background:white;padding:24px;border-radius:12px;border:1px solid #e0e4e8;box-shadow:0 4px 12px rgba(0,0,0,0.08)">
      <h2 style="margin-top:0">Historique des gains (par avis)</h2>
      <div style="font-size:13px">
        <?php if (empty($earnings)): ?>
          <div style="padding:12px">Aucun gain enregistré.</div>
        <?php else: ?>
          <?php foreach ($earnings as $e): ?>
            <div style="padding:12px;border-bottom:1px solid #e0e4e8;display:flex;justify-content:space-between">
              <span><?= htmlspecialchars($e['title'] ?? 'Avis') ?> —
                <?= date('j M Y', strtotime($e['created_at'])) ?></span>
              <strong style="color:#10b981">+<?= 1 ?> €</strong>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>

    <section
      style="background:white;padding:24px;border-radius:12px;border:1px solid #e0e4e8;box-shadow:0 4px 12px rgba(0,0,0,0.08)">
      <h2 style="margin-top:0">Historique des retraits</h2>
      <div style="font-size:13px">
        <?php if (empty($withList)): ?>
          <div style="padding:12px">Aucun retrait enregistré.</div>
        <?php else: ?>
          <?php foreach ($withList as $w): ?>
            <div style="padding:12px;border-bottom:1px solid #e0e4e8;display:flex;justify-content:space-between">
              <div>
                <span style="display:block">Retrait du <?= date('j M Y', strtotime($w['created_at'])) ?></span>
                <span style="color:#6c757d;font-size:12px">Status: <?= htmlspecialchars($w['status']) ?></span>
              </div>
              <strong style="color:#ef4444">-<?= number_format($w['amount'], 2, ',', ' ') ?> €</strong>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>
  </div>

  <section
    style="background:linear-gradient(135deg, #0066cc 0%, #1ab991 100%);color:white;padding:32px;border-radius:12px;margin-bottom:32px;box-shadow:0 12px 32px rgba(0,102,204,0.25)">
    <h2 style="color:white">Demander un retrait</h2>
    <p>Montant minimum: <strong>10,00 €</strong> · Votre solde: <strong><?= number_format($balance, 2, ',', ' ') ?>
        €</strong></p>
    <form method="post" action="<?= url('actions/withdraw.php') ?>" style="display:flex;gap:12px;margin-top:16px">
      <input name="amount" class="input-enhanced" type="number" placeholder="Montant à retirer" min="10"
        max="<?= htmlspecialchars($balance) ?>" step="0.01"
        style="flex:1;padding:10px;border:none;border-radius:8px;font-size:14px">
      <button class="btn btn-animated ripple" type="submit"
        style="background:white;color:#0b5ed7;padding:10px 16px">Retirer</button>
    </form>
  </section>

  <section
    style="background:white;padding:24px;border-radius:12px;border:1px solid #e0e4e8;box-shadow:0 4px 12px rgba(0,0,0,0.08)">
    <h3>Moyens de paiement</h3>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-top:16px">
      <div
        style="padding:16px;border:2px solid #0066cc;border-radius:10px;text-align:center;cursor:pointer;background:linear-gradient(135deg,#e6f0ff,#fff)">
        <div style="font-size:28px;margin-bottom:8px">◆</div>
        <strong style="display:block">Mobile Money</strong>
        <p style="font-size:12px;color:#6c757d;margin:4px 0 0">Connecté</p>
      </div>
      <div style="padding:16px;border:2px solid #e0e4e8;border-radius:10px;text-align:center;cursor:pointer;opacity:.7">
        <div style="font-size:28px;margin-bottom:8px">◉</div>
        <strong style="display:block">PayPal</strong>
        <p style="font-size:12px;color:#6c757d;margin:4px 0 0">Ajouter</p>
      </div>
      <div style="padding:16px;border:2px solid #e0e4e8;border-radius:10px;text-align:center;cursor:pointer;opacity:.7">
        <div style="font-size:28px;margin-bottom:8px">◆</div>
        <strong style="display:block">Virement bancaire</strong>
        <p style="font-size:12px;color:#6c757d;margin:4px 0 0">Ajouter</p>
      </div>
    </div>
  </section>
</main>