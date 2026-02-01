<footer class="tp-footer" role="contentinfo">
  <div class="container footer-inner">
    <div class="footer-left">
      <p class="muted">© TrustPick — Plateforme de recommandation</p>
      <small class="muted">Conçu pour la confiance et la transparence.</small>
    </div>
    <nav class="footer-nav">
      <a href="<?= url('index.php?page=home') ?>">Accueil</a>
      <a href="<?= url('index.php?page=catalog') ?>">Catalogue</a>
      <a href="<?= url('index.php?page=login') ?>">Aide</a>
    </nav>
  </div>
</footer>

<!-- Bannière d'installation iOS -->
<div id="ios-install-banner" class="ios-install-banner d-none">
  <div class="ios-install-content">
    <img src="/TrustPick/public/assets/img/icon-192.png" alt="TrustPick" class="ios-install-icon">
    <div class="ios-install-text">
      <strong>Installer TrustPick</strong>
      <p>Appuyez sur <i class="bi bi-box-arrow-up"></i> puis "Sur l'écran d'accueil"</p>
    </div>
    <button id="ios-banner-close" class="ios-banner-close" aria-label="Fermer">
      <i class="bi bi-x-lg"></i>
    </button>
  </div>
</div>

<!-- Bootstrap 5 JS Bundle (inclut Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= url('assets/js/app.js') ?>"></script>
<script src="<?= url('assets/js/ui-enhancements.js') ?>"></script>
<script src="<?= url('assets/js/likes.js') ?>"></script>
<script src="<?= url('assets/js/pwa-install.js') ?>"></script>

<!-- PWA Service Worker Registration -->
<script>
  // Enregistrement du Service Worker
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      navigator.serviceWorker.register('/TrustPick/public/service-worker.js', {
        scope: '/TrustPick/public/'
      })
        .then(reg => {
          console.log('[PWA] Service Worker enregistré avec succès:', reg.scope);

          // Vérifier les mises à jour
          reg.addEventListener('updatefound', () => {
            const newWorker = reg.installing;
            console.log('[PWA] Nouveau Service Worker trouvé');

            newWorker.addEventListener('statechange', () => {
              if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                console.log('[PWA] Nouvelle version disponible');
              }
            });
          });
        })
        .catch(err => console.error('[PWA] Erreur Service Worker:', err));
    });
  }
</script>
</body>

</html>