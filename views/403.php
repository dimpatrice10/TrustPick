<?php
/**
 * Page 403 - Accès Refusé - TrustPick V2
 */
?>
<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <div class="display-1 text-danger mb-4"><i class="bi bi-shield-exclamation"></i></div>
            <h1 class="h2 mb-3">Accès Refusé</h1>
            <p class="text-muted mb-4">
                Vous n'avez pas les permissions nécessaires pour accéder à cette page.
            </p>
            <div class="d-flex gap-2 justify-content-center flex-wrap">
                <a href="<?= url('index.php?page=home') ?>" class="btn btn-primary">
                    🏠 Retour à l'accueil
                </a>
                <?php if (empty($_SESSION['user_id'])): ?>
                    <a href="<?= url('index.php?page=login') ?>" class="btn btn-outline-primary">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Se connecter
                    </a>
                <?php else: ?>
                    <a href="<?= url('index.php?page=user_dashboard') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-speedometer2 me-1"></i>Mon Dashboard
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>