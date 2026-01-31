</div><!-- Fin row -->
</div><!-- Fin container-fluid -->

<!-- Footer -->
<footer style="background: #1e293b; color: white; padding: 2rem 0; margin-top: 3rem;">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5>🛍️ TrustPick</h5>
                <p>Plateforme d'avis et de recommandations adaptée au marché africain.</p>
            </div>
            <div class="col-md-4">
                <h6>Liens utiles</h6>
                <ul style="list-style: none; padding: 0;">
                    <li><a href="#" style="color: #94a3b8;">À propos</a></li>
                    <li><a href="#" style="color: #94a3b8;">Conditions d'utilisation</a></li>
                    <li><a href="#" style="color: #94a3b8;">Politique de confidentialité</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6>Contact</h6>
                <p style="color: #94a3b8;">
                    Email: contact@trustpick.com<br>
                    Tél: +225 XX XX XX XX XX
                </p>
            </div>
        </div>
        <hr style="border-color: #334155; margin: 1.5rem 0;">
        <p class="text-center" style="color: #94a3b8; margin: 0;">
            © 2026 TrustPick - Tous droits réservés
        </p>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Scripts personnalisés -->
<script src="assets/js/trustpick-core.js"></script>
<script src="assets/js/pagination.js"></script>

<?php if (isset($extra_js)): ?>
    <?php foreach ($extra_js as $js): ?>
        <script src="<?php echo $js; ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Script de chargement des notifications non lues -->
<script>
    async function loadUnreadNotifications() {
        try {
            const response = await fetch(TrustPick.API_BASE + '/notifications-unread-count.php');
            const data = await response.json();

            if (data.success) {
                const badge = document.getElementById('notif-count');
                if (badge && data.count > 0) {
                    badge.textContent = data.count;
                    badge.style.display = 'flex';
                } else if (badge) {
                    badge.style.display = 'none';
                }
            }
        } catch (error) {
            console.error('Erreur chargement notifications:', error);
        }
    }

    // Charger au démarrage
    loadUnreadNotifications();

    // Recharger toutes les 30 secondes
    setInterval(loadUnreadNotifications, 30000);
</script>
</body>

</html>