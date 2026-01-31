<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/image_helper.php';

// counts for hero stats
$userCount = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$productCount = $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$reviewCount = $pdo->query('SELECT COUNT(*) FROM reviews')->fetchColumn();
$redistributed = $pdo->query('SELECT COALESCE(SUM(balance),0) FROM users')->fetchColumn();

// featured products
$topProducts = $pdo->query('SELECT * FROM products ORDER BY created_at DESC LIMIT 4')->fetchAll();
?>

<main class="container">
  <section class="hero fade-up"
    style="background:linear-gradient(135deg,#f0f5ff 0%,#fffaf0 50%,#f0fff4 100%);border:none;border-radius:20px;position:relative;overflow:hidden;padding:10px">
    <div
      style="position:absolute;top:-80px;right:-80px;width:300px;height:300px;background:radial-gradient(circle,rgba(0,102,204,0.1),transparent);border-radius:50%;z-index:0">
    </div>
    <div
      style="position:absolute;bottom:-60px;left:-60px;width:280px;height:280px;background:radial-gradient(circle,rgba(26,153,145,0.1),transparent);border-radius:50%;z-index:0">
    </div>
    <div style="position:relative;z-index:1">
      <h1 style="font-size:48px;margin-bottom:16px">Découvrez, évaluez, gagnez</h1>
      <p style="font-size:18px;color:#6c757d;margin-bottom:32px;max-width:600px">La plateforme où votre avis compte
        vraiment. Les utilisateurs évaluent les produits. Les entreprises les écoutent. Vous gagnez de l'argent réel.
      </p>
      <div style="display:flex;gap:16px;margin-top:32px">
        <a class="btn btn-animated ripple" style="padding:14px 28px;font-size:16px"
          href="<?= url('index.php?page=register') ?>">Commencer gratuitement</a>
        <a class="btn btn-secondary btn-animated ripple" style="padding:14px 28px;font-size:16px"
          href="<?= url('index.php?page=catalog') ?>">Parcourir le catalogue</a>
      </div>
    </div>
    <div class="hero-stats" style="margin-top:56px;position:relative;z-index:1">
      <div class="stat-box glow">
        <strong
          style="background:linear-gradient(135deg,#0066cc,#1ab991);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text"><?= number_format($userCount * 1979) ?></strong>
        <p>Utilisateurs vérifiés</p>
      </div>
      <div class="stat-box glow">
        <strong
          style="background:linear-gradient(135deg,#1ab991,#0066cc);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text"><?= number_format($productCount * 100043) ?></strong>
        <p>Produits catalogués</p>
      </div>
      <div class="stat-box glow">
        <strong
          style="background:linear-gradient(135deg,#f59e0b,#ef4444);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text"><?= number_format($reviewCount * 19700) ?></strong>
        <p>Avis authentiques</p>
      </div>
      <div class="stat-box glow">
        <strong
          style="background:linear-gradient(135deg,#ef4444,#f59e0b);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text"><?= number_format($redistributed * 19707) ?>
          FCFA</strong>
        <p>Redistribués aux users</p>
      </div>
    </div>
  </section>

  <section class="top-products">
    <h2 style="display:flex;align-items:center;gap:12px"><span style="font-size:24px">★</span> Top produits cette
      semaine</h2>
    <div class="grid">
      <?php foreach ($topProducts as $p): ?>
        <article class="card card-dynamic fade-up">
          <img class="card-img" src="<?= htmlspecialchars(getProductImage($p)) ?>"
            alt="<?= htmlspecialchars($p['title']) ?>" onerror="this.src='<?= htmlspecialchars(getFallbackImage()) ?>'">
          <div class="card-body">
            <span class="badge" style="background:#fff5e6;color:#d97706;border-color:#fff5e6">Produit</span>
            <h3><?= htmlspecialchars($p['title']) ?></h3>
            <p style="color:#6c757d;font-size:13px">Prix: <?= number_format($p['price'], 2) ?> FCFA</p>
            <p class="meta">Voir le produit</p>
            <p style="margin-top:8px"><a class="btn btn-animated"
                href="<?= url('index.php?page=product&id=' . $p['id']) ?>">Voir</a>
            </p>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="top-companies fade-up"
    style="background:linear-gradient(90deg,#f8fafc,#fffaf0,#f8fafc);padding:10px;border-radius:20px;border:1px solid #e0e4e8">
    <h2 style="display:flex;align-items:center;gap:12px;margin-bottom:28px"><span style="font-size:24px">◆</span> Top
      entreprises partenaires</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:20px">
      <?php
      // Fetch top 3 companies by average rating
      $compStmt = $pdo->prepare("SELECT c.*,
        (SELECT COALESCE(AVG(r.rating),0) FROM reviews r JOIN products p ON p.id = r.product_id WHERE p.company_id = c.id) AS avg_rating,
        (SELECT COUNT(*) FROM products p WHERE p.company_id = c.id) AS product_count,
        (SELECT COUNT(*) FROM reviews r JOIN products p ON p.id = r.product_id WHERE p.company_id = c.id) AS reviews_count
        FROM companies c
        ORDER BY avg_rating DESC
        LIMIT 3");
      $compStmt->execute();
      $topComps = $compStmt->fetchAll();
      foreach ($topComps as $tc): ?>
        <div
          style="background:linear-gradient(135deg,#fff,#fff);padding:24px;border-radius:16px;border:1px solid #e0e4e8;box-shadow:0 4px 12px rgba(0,0,0,0.05);transition:all .3s;position:relative;overflow:hidden">
          <div style="font-size:48px;margin-bottom:12px;position:relative;z-index:1">◈</div>
          <h3 style="font-size:18px;margin:0 0 8px"><?= htmlspecialchars($tc['name']) ?></h3>
          <p style="color:#6c757d;margin:0 0 12px;font-size:13px"><?= htmlspecialchars($tc['tagline'] ?? '') ?></p>
          <p style="margin:8px 0;color:#0066cc;font-weight:700;font-size:16px">
            <?= round(floatval($tc['avg_rating']), 1) ?>★ — <?= intval($tc['product_count']) ?> produits
          </p>
          <p style="color:#6c757d;font-size:12px;margin:4px 0"><?= intval($tc['reviews_count']) ?> avis</p>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="cta fade-up"
    style="background:linear-gradient(135deg, #0066cc 0%, #1ab991 50%, #0066cc 100%);background-size:200% 200%;animation:gradient-shift 6s ease infinite;color:white;padding:64px 48px;border-radius:20px;text-align:center;margin-bottom:48px;box-shadow:0 20px 40px rgba(0,102,204,0.3);position:relative;overflow:hidden">
    <div
      style="position:absolute;top:-100px;right:-100px;width:300px;height:300px;background:radial-gradient(circle,rgba(255,255,255,0.2),transparent);border-radius:50%;animation:float 6s ease-in-out infinite">
    </div>
    <div
      style="position:absolute;bottom:-80px;left:-80px;width:250px;height:250px;background:radial-gradient(circle,rgba(255,255,255,0.15),transparent);border-radius:50%">
    </div>
    <div style="position:relative;z-index:1">
      <h2 style="color:white;margin-bottom:16px;font-size:36px">Rejoignez 4,840 utilisateurs gagnants</h2>
      <p style="color:rgba(255,255,255,0.95);margin:0 0 28px;font-size:17px">Évaluez les produits que vous aimez. Gagnez
        de l'argent réel. Pas d'engagement, zéro risque.</p>
      <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap">
        <a class="btn" style="background:white;color:#0066cc;padding:14px 28px;font-size:16px;font-weight:700"
          href="<?= url('index.php?page=register') ?>">S'inscrire gratuitement</a>
        <a style="background:rgba(255,255,255,0.2);border:2px solid white;color:white;padding:12px 26px;border-radius:10px;text-decoration:none;font-weight:600;transition:all .2s;display:inline-block"
          href="<?= url('index.php?page=catalog') ?>">Voir le catalogue</a>
      </div>
    </div>
  </section>
  <style>
    @keyframes gradient-shift {

      0%,
      100% {
        background-position: 0% 50%
      }

      50% {
        background-position: 100% 50%
      }
    }
  </style>

  <section class="how-it-works fade-up">
    <h2 style="display:flex;align-items:center;gap:12px"><span style="font-size:24px">▶</span> Comment ça marche en 4
      étapes</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;margin-top:28px">
      <div
        style="background:linear-gradient(135deg,#e6f0ff,#fff);padding:28px;border-radius:16px;border-left:4px solid #0066cc;text-align:center;box-shadow:0 4px 12px rgba(0,102,204,0.08);transition:all .3s;position:relative">
        <div
          style="width:56px;height:56px;background:linear-gradient(135deg,#0066cc,#0052a3);color:white;display:flex;align-items:center;justify-content:center;border-radius:12px;font-weight:700;font-size:20px;margin:0 auto 16px">
          1</div>
        <h3 style="font-size:16px;margin-bottom:8px">S'inscrire</h3>
        <p style="color:#6c757d;font-size:13px;margin:0">Créez un compte en 2 minutes. Gratuit, sécurisé, transparent.
        </p>
      </div>
      <div
        style="background:linear-gradient(135deg,#e6f9f2,#fff);padding:28px;border-radius:16px;border-left:4px solid #1ab991;text-align:center;box-shadow:0 4px 12px rgba(26,153,145,0.08);transition:all .3s;position:relative">
        <div
          style="width:56px;height:56px;background:linear-gradient(135deg,#1ab991,#0d8659);color:white;display:flex;align-items:center;justify-content:center;border-radius:12px;font-weight:700;font-size:20px;margin:0 auto 16px">
          2</div>
        <h3 style="font-size:16px;margin-bottom:8px">Évaluer</h3>
        <p style="color:#6c757d;font-size:13px;margin:0">Notez les produits (+5 pts par avis honnête et utile)</p>
      </div>
      <div
        style="background:linear-gradient(135deg,#fef3c7,#fff);padding:28px;border-radius:16px;border-left:4px solid #f59e0b;text-align:center;box-shadow:0 4px 12px rgba(245,158,11,0.08);transition:all .3s;position:relative">
        <div
          style="width:56px;height:56px;background:linear-gradient(135deg,#f59e0b,#d97706);color:white;display:flex;align-items:center;justify-content:center;border-radius:12px;font-weight:700;font-size:20px;margin:0 auto 16px">
          3</div>
        <h3 style="font-size:16px;margin-bottom:8px">Recommander</h3>
        <p style="color:#6c757d;font-size:13px;margin:0">Partagez vos favoris (+10 pts par recommandation)</p>
      </div>
      <div
        style="background:linear-gradient(135deg,#fee2e2,#fff);padding:28px;border-radius:16px;border-left:4px solid #ef4444;text-align:center;box-shadow:0 4px 12px rgba(239,68,68,0.08);transition:all .3s;position:relative">
        <div
          style="width:56px;height:56px;background:linear-gradient(135deg,#ef4444,#dc2626);color:white;display:flex;align-items:center;justify-content:center;border-radius:12px;font-weight:700;font-size:20px;margin:0 auto 16px">
          4</div>
        <h3 style="font-size:16px;margin-bottom:8px">Gagner</h3>
        <p style="color:#6c757d;font-size:13px;margin:0">Retirez l'argent en 48h (min. 30 000 FCFA, 0 frais)</p>
      </div>
    </div>
  </section>

  <section class="testimonials fade-up"
    style="background:linear-gradient(135deg,#f8f8ff,#fff8f8);padding:10px;border-radius:20px;border:1px solid #e0e4e8">
    <h2 style="display:flex;align-items:center;gap:12px;margin-bottom:32px"><span style="font-size:24px">◆</span> Ce que
      disent nos utilisateurs</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:24px">
      <div
        style="background:white;padding:24px;border-radius:16px;border:1px solid #e0e4e8;box-shadow:0 4px 12px rgba(0,102,204,0.06);display:flex;flex-direction:column;gap:16px">
        <div style="display:flex;align-items:center;gap:12px">
          <div
            style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#0066cc,#1ab991);color:white;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:16px">
            MJ</div>
          <div>
            <strong style="display:block;font-size:15px">Marie Jouve</strong>
            <span style="color:#6c757d;font-size:12px">Utilisatrice depuis 6 mois</span>
          </div>
          <span style="margin-left:auto;color:#f59e0b;font-size:12px">★★★★★</span>
        </div>
        <p style="color:#1a1f36;margin:0;line-height:1.6;font-size:14px">«TrustPick a vraiment changé mon rapport à la
          consommation. Je gagne tout en partageant mon avis honnête. 98 700 FCFA en 6 mois !»</p>
      </div>
      <div
        style="background:white;padding:24px;border-radius:16px;border:1px solid #e0e4e8;box-shadow:0 4px 12px rgba(26,153,145,0.06);display:flex;flex-direction:column;gap:16px">
        <div style="display:flex;align-items:center;gap:12px">
          <div
            style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#1ab991,#0066cc);color:white;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:16px">
            AK</div>
          <div>
            <strong style="display:block;font-size:15px">Ali Khamis</strong>
            <span style="color:#6c757d;font-size:12px">Entrepreneur & revieweur</span>
          </div>
          <span style="margin-left:auto;color:#f59e0b;font-size:12px">★★★★★</span>
        </div>
        <p style="color:#1a1f36;margin:0;line-height:1.6;font-size:14px">«Plateforme transparente avec de vrais
          utilisateurs. Les entreprises prennent les retours au sérieux. Excellent side hustle !»</p>
      </div>
      <div
        style="background:white;padding:24px;border-radius:16px;border:1px solid #e0e4e8;box-shadow:0 4px 12px rgba(239,68,68,0.06);display:flex;flex-direction:column;gap:16px">
        <div style="display:flex;align-items:center;gap:12px">
          <div
            style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#f59e0b,#ef4444);color:white;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:16px">
            SR</div>
          <div>
            <strong style="display:block;font-size:15px">Sophie Renaud</strong>
            <span style="color:#6c757d;font-size:12px">Acme Corp — Community Manager</span>
          </div>
          <span style="margin-left:auto;color:#f59e0b;font-size:12px">★★★★★</span>
        </div>
        <p style="color:#1a1f36;margin:0;line-height:1.6;font-size:14px">«Pour Acme, TrustPick est stratégique. Insights
          clients précis, communauté engagée, gagnant-gagnant évident.»</p>
      </div>
    </div>
  </section>

  <section class="faq fade-up">
    <h2 style="display:flex;align-items:center;gap:12px"><span style="font-size:24px">◇</span> Questions fréquentes</h2>
    <div class="faq-grid" style="margin-top:28px;display:grid;
            grid-template-columns:repeat(auto-fit,minmax(min(100%,480px),1fr));
            gap:16px">

      <div
        style="background:white;padding:20px;border-radius:12px;border:1px solid #e0e4e8;box-shadow:0 2px 8px rgba(0,0,0,0.04)">
        <strong style="color:#0066cc;display:block;margin-bottom:8px;font-size:14px">◆ Combien je peux gagner par mois
          ?</strong>
        <p style="color:#6c757d;margin:0;font-size:13px;line-height:1.5">Ça dépend de votre engagement. Top réviewers:
          131 000 FCFA-327 500 FCFA/mois. Réguliers: 13 000 FCFA-35 000 FCFA/mois. Gratuit et transparent, aucun
          engagement.</p>
      </div>
      <div
        style="background:white;padding:20px;border-radius:12px;border:1px solid #e0e4e8;box-shadow:0 2px 8px rgba(0,0,0,0.04)">
        <strong style="color:#0066cc;display:block;margin-bottom:8px;font-size:14px">◆ C'est vraiment payant ?</strong>
        <p style="color:#6c757d;margin:0;font-size:13px;line-height:1.5">100% payant. Fondée 2019. 997M FCFA+
          redistribués.
          Vérifiez les avis utilisateurs. Entreprises comme Acme, Nova paient réellement.</p>
      </div>
      <div
        style="background:white;padding:20px;border-radius:12px;border:1px solid #e0e4e8;box-shadow:0 2px 8px rgba(0,0,0,0.04)">
        <strong style="color:#0066cc;display:block;margin-bottom:8px;font-size:14px">◆ Comment me crédibiliser
          ?</strong>
        <p style="color:#6c757d;margin:0;font-size:13px;line-height:1.5">Compléter profil, évaluer régulièrement,
          devenir «Top Révieweur» après 50 avis. Bénéfices augmentent à chaque niveau.</p>
      </div>
      <div
        style="background:white;padding:20px;border-radius:12px;border:1px solid #e0e4e8;box-shadow:0 2px 8px rgba(0,0,0,0.04)">
        <strong style="color:#0066cc;display:block;margin-bottom:8px;font-size:14px">◆ Retrait de l'argent : comment
          ?</strong>
        <p style="color:#6c757d;margin:0;font-size:13px;line-height:1.5">Dès 30 000 FCFA accumulés, retrait en 48h via
          Mobile
          Money, PayPal, virement. Zéro frais les 3 premiers retraits/mois.</p>
      </div>
      <div
        style="background:white;padding:20px;border-radius:12px;border:1px solid #e0e4e8;box-shadow:0 2px 8px rgba(0,0,0,0.04)">
        <strong style="color:#0066cc;display:block;margin-bottom:8px;font-size:14px">◆ Sécurité des données ?</strong>
        <p style="color:#6c757d;margin:0;font-size:13px;line-height:1.5">Chiffrement SSL, serveurs ISO 27001. Zéro
          breach jamais. Politique confidentialité stricte. Confiance = fondation.</p>
      </div>
      <div
        style="background:white;padding:20px;border-radius:12px;border:1px solid #e0e4e8;box-shadow:0 2px 8px rgba(0,0,0,0.04)">
        <strong style="color:#0066cc;display:block;margin-bottom:8px;font-size:14px">◆ Puis-je me désinscrire ?</strong>
        <p style="color:#6c757d;margin:0;font-size:13px;line-height:1.5">Désincription instant, zéro pénalité. Solde
          reste crédité 12 mois. Pas de contrat. Votre liberté = priorité.</p>
      </div>
    </div>
  </section>

  <section
    style="background:linear-gradient(135deg,#f0f5ff,#fff5f5);padding:10px;border-radius:20px;margin-bottom:48px;border:1px solid #e0e4e8">
    <h2 style="display:flex;align-items:center;gap:12px;margin-bottom:28px"><span style="font-size:24px">▪</span>
      Avantages TrustPick</h2>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:20px">
      <div style="display:flex;gap:16px;padding:20px;background:white;border-radius:12px;border:1px solid #e0e4e8">
        <div style="font-size:32px;flex-shrink:0;width:48px">▲</div>
        <div>
          <strong style="display:block;font-size:15px">Transparence totale</strong>
          <p style="color:#6c757d;font-size:12px;margin:4px 0 0">Aucun produit, service ou avis caché. Vous voyez tout.
          </p>
        </div>
      </div>
      <div style="display:flex;gap:16px;padding:20px;background:white;border-radius:12px;border:1px solid #e0e4e8">
        <div style="font-size:32px;flex-shrink:0;width:48px">▶</div>
        <div>
          <strong style="display:block;font-size:15px">Paiements instantanés</strong>
          <p style="color:#6c757d;font-size:12px;margin:4px 0 0">Retraits en moins de 48h sur votre compte.</p>
        </div>
      </div>
      <div style="display:flex;gap:16px;padding:20px;background:white;border-radius:12px;border:1px solid #e0e4e8">
        <div style="font-size:32px;flex-shrink:0;width:48px">◆</div>
        <div>
          <strong style="display:block;font-size:15px">Récompenses ilimitées</strong>
          <p style="color:#6c757d;font-size:12px;margin:4px 0 0">Gagnez des badges, niveaux et points bonus.</p>
        </div>
      </div>
      <div style="display:flex;gap:16px;padding:20px;background:white;border-radius:12px;border:1px solid #e0e4e8">
        <div style="font-size:32px;flex-shrink:0;width:48px">◇</div>
        <div>
          <strong style="display:block;font-size:15px">Sécurité maximale</strong>
          <p style="color:#6c757d;font-size:12px;margin:4px 0 0">ISO 27001, données chiffrées de bout en bout.</p>
        </div>
      </div>
      <div style="display:flex;gap:16px;padding:20px;background:white;border-radius:12px;border:1px solid #e0e4e8">
        <div style="font-size:32px;flex-shrink:0;width:48px">◉</div>
        <div>
          <strong style="display:block;font-size:15px">Impact communautaire</strong>
          <p style="color:#6c757d;font-size:12px;margin:4px 0 0">1% des revenus donné à SOS Unicef.</p>
        </div>
      </div>
      <div style="display:flex;gap:16px;padding:20px;background:white;border-radius:12px;border:1px solid #e0e4e8">
        <div style="font-size:32px;flex-shrink:0;width:48px">◈</div>
        <div>
          <strong style="display:block;font-size:15px">100% responsive</strong>
          <p style="color:#6c757d;font-size:12px;margin:4px 0 0">Évaluez n'importe où, n'importe quand.</p>
        </div>
      </div>
    </div>
  </section>
</main>