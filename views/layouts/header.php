<?php
// Démarrage sécurisé de la session
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/../../includes/url.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/helpers.php';

// Récupérer les toasts pour affichage
$toasts = getToasts();
?>
<!doctype html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
  <meta name="theme-color" content="#0066cc">
  <meta name="description" content="TrustPick - Gagnez de l'argent en laissant des avis sur vos produits préférés">

  <!-- PWA iOS Support -->
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="TrustPick">
  <link rel="apple-touch-icon" href="<?= url('assets/img/icon-192.png') ?>">
  <link rel="apple-touch-icon" sizes="152x152" href="<?= url('assets/img/icon-192.png') ?>">
  <link rel="apple-touch-icon" sizes="180x180" href="<?= url('assets/img/icon-192.png') ?>">
  <link rel="apple-touch-icon" sizes="167x167" href="<?= url('assets/img/icon-192.png') ?>">

  <!-- PWA Windows/Edge Support -->
  <meta name="msapplication-TileColor" content="#0066cc">
  <meta name="msapplication-TileImage" content="<?= url('assets/img/icon-192.png') ?>">
  <meta name="msapplication-config" content="none">

  <!-- PWA Android/Chrome Support -->
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="application-name" content="TrustPick">

  <!-- Manifest & Icons -->
  <link rel="manifest" href="<?= url('manifest.json') ?>">
  <link rel="icon" type="image/png" sizes="192x192" href="<?= url('assets/img/icon-192.png') ?>">
  <link rel="icon" type="image/png" sizes="512x512" href="<?= url('assets/img/icon-512.png') ?>">

  <title>TrustPick — Plateforme de recommandation des produits</title>

  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap"
    rel="stylesheet"> <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= url('assets/css/demo.css') ?>">
  <link rel="stylesheet" href="<?= url('assets/css/app.css') ?>">
  <link rel="stylesheet" href="<?= url('assets/css/ui-enhancements.css') ?>">

  <!-- api-client.js removed: not present in assets; avoid 404 that returns HTML -->


  <style>
    /* Desktop par défaut : 2 colonnes */
    .responsive-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 24px;
      margin: 32px 0;
    }

    /* Mobile : une colonne, sections empilées */
    @media (max-width: 768px) {
      .responsive-grid {
        grid-template-columns: 1fr !important;
        /* !important si style inline */
      }
    }
  </style>
</head>

<body>
  <?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show m-0 rounded-0" role="alert">
      <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($_SESSION['error']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>
  <?php if (!empty($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show m-0 rounded-0" role="alert">
      <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($_SESSION['success']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
  <?php endif; ?>
  <header class="tp-header" role="banner">
    <div class="container header-inner">
      <a class="logo" href="<?= url('index.php?page=home') ?>" aria-label="TrustPick accueil"
        style="display:flex;align-items:center;gap:8px">
        <img src="<?= url('assets/img/logo.png') ?>" alt="TrustPick Logo" style="height:40px;width:auto">
        <!-- <span style="font-weight:800;font-family:Poppins;font-size:18px;background:linear-gradient(135deg,#0066cc,#1ab991);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">TrustPick</span> -->
        <?php if (!empty($_SESSION['user_id'])):
          try {
            $uSt = $pdo->prepare('SELECT u.id,u.name, COALESCE(u.balance,0) AS balance FROM users u WHERE u.id = ? LIMIT 1');
            $uSt->execute([$_SESSION['user_id']]);
            $me = $uSt->fetch();
          } catch (Exception $e) {
            $me = null;
          }
          ?>
          <div style="font-size:13px;color:#6c757d">Solde: <strong
              style="color:#0066cc"><?= isset($me['balance']) ? number_format($me['balance'], 0, ',', ' ') . ' FCFA' : '—' ?></strong>
          </div>
        <?php endif; ?>
      </a>
      <!-- <div class="search-wrap">
        <input class="search" type="search" placeholder="Rechercher un produit, marque, entreprise..."
          aria-label="Rechercher">
        <button class="search-btn" aria-label="Lancer la recherche">🔎</button>
      </div> -->
      <nav class="tp-nav" role="navigation" aria-label="Navigation principale">
        <a class="nav-link" href="<?= url('index.php?page=catalog') ?>">Catalogue</a>
        <a class="nav-link" href="<?= url('index.php?page=company') ?>">Entreprises</a>
        <?php if (!empty($_SESSION['user_id'])):
          try {
            $uSt = $pdo->prepare('SELECT u.id,u.name, COALESCE(u.balance,0) AS balance FROM users u WHERE u.id = ? LIMIT 1');
            $uSt->execute([$_SESSION['user_id']]);
            $me = $uSt->fetch();
          } catch (Exception $e) {
            $me = null;
          }
          ?>
          <div style="display:flex;align-items:center;gap:12px">
            <!-- <div style="font-size:14px;color:#1a1f36">Bonjour,
              <strong><?= htmlspecialchars($me['name'] ?? 'Utilisateur') ?></strong>
            </div> -->
            <!-- Mon compte -->
            <a class="nav-link nav-icon" style="margin: 0px 10px;" href="<?= url('index.php?page=user_dashboard') ?>"
              title="Mon compte" aria-label="Mon compte">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                <circle cx="12" cy="7" r="4" />
              </svg>
            </a>

            <!-- Portefeuille -->
            <a class="nav-link nav-icon" style="margin: 0px 10px;" href="<?= url('index.php?page=wallet') ?>"
              title="Portefeuille" aria-label="Portefeuille">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="6" width="20" height="12" rx="2" ry="2" />
                <path d="M16 12h.01" />
              </svg>
            </a>

            <!-- Déconnexion -->
            <a class="nav-link nav-icon" style="margin: 0px 10px;" href="<?= url('actions/logout.php') ?>"
              title="Déconnexion" aria-label="Déconnexion">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                <polyline points="16 17 21 12 16 7" />
                <line x1="21" y1="12" x2="9" y2="12" />
              </svg>
            </a>

          </div>
        <?php else: ?>
          <a class="nav-link" href="<?= url('index.php?page=login') ?>">Se connecter</a>
          <a class="nav-cta" href="<?= url('index.php?page=register') ?>">S'inscrire</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>

  <!-- Injecter les toasts PHP pour JavaScript -->
  <script>
    document.body.setAttribute('data-toasts', '<?= addslashes(json_encode($toasts)) ?>');
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="<?= url('assets/js/notifications.js') ?>"></script>