<main class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6 text-center">
      <div class="display-1 text-warning mb-4">🔍</div>
      <h1 class="h2 mb-3">Page introuvable</h1>
      <p class="text-muted mb-4">
        Désolé, la page que vous recherchez n'existe pas ou a été déplacée.
      </p>
      <div class="d-flex gap-2 justify-content-center flex-wrap">
        <a href="<?= url('index.php?page=home') ?>" class="btn btn-primary">
          🏠 Retour à l'accueil
        </a>
        <a href="<?= url('index.php?page=catalog') ?>" class="btn btn-outline-primary">
          <i class="bi bi-box-seam me-1"></i>Voir le catalogue
        </a>
      </div>
    </div>
  </div>
</main>