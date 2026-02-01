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
      <!-- Bouton d'installation PWA -->
      <button id="pwa-install-btn" class="pwa-install-btn footer-install-btn" style="display:none">
        <i class="bi bi-download"></i>
        <span>Installer l'app</span>
      </button>
    </nav>
  </div>
</footer>

<!-- Bouton d'installation flottant PWA -->
<div id="pwa-install-floating" class="pwa-install-floating" style="display:none">
  <button class="pwa-install-btn">
    <i class="bi bi-download"></i>
    <span>Installer TrustPick</span>
  </button>
</div>

<!-- Bootstrap 5 JS Bundle (inclut Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= url('assets/js/app.js') ?>"></script>
<script src="<?= url('assets/js/ui-enhancements.js') ?>"></script>
<script src="<?= url('assets/js/likes.js') ?>"></script>
<script src="<?= url('assets/js/pwa-install.js') ?>"></script>

<!-- PWA Service Worker Registration (statique) -->
<script>
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      navigator.serviceWorker.register('<?= url('service-worker.js') ?>')
        .then(reg => {
          console.log('Service Worker enregistré:', reg.scope);
          // Vérifier les mises à jour
          reg.addEventListener('updatefound', () => {
            const newWorker = reg.installing;
            newWorker.addEventListener('statechange', () => {
              if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                // Nouvelle version disponible
                if (confirm('Une nouvelle version de TrustPick est disponible. Mettre à jour ?')) {
                  window.location.reload();
                }
              }
            });
          });
        })
        .catch(err => console.log('Service Worker erreur:', err));
    });
  }
</script>
</body>

</html>