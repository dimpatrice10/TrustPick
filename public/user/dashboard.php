<?php
$page_title = 'Dashboard';
$current_page = 'dashboard';

include '../layouts/header.php';
include '../layouts/sidebar-user.php';
?>

<!-- Contenu principal -->
<div class="col-md-9 col-lg-10" style="padding: 2rem;">
    <h2 style="margin-bottom: 2rem;">👋 Bienvenue, <?php echo htmlspecialchars($currentUser['name']); ?> !</h2>

    <!-- Stats rapides -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-value" id="balance"><?php echo number_format($currentUser['balance'], 0, ',', ' '); ?>
                </div>
                <div class="stat-label">Solde FCFA</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-value" id="tasks-completed">0</div>
                <div class="stat-label">Tâches complétées</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon">⭐</div>
                <div class="stat-value" id="reviews-count">0</div>
                <div class="stat-label">Avis donnés</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-value" id="referrals-count">0</div>
                <div class="stat-label">Filleuls</div>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="tp-card">
                <h4 class="tp-card-header">🚀 Actions rapides</h4>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="index.php?page=tasks" class="btn-tp-primary">✅ Voir mes tâches</a>
                    <a href="index.php?page=catalog" class="btn-tp-primary">🛍️ Découvrir les produits</a>
                    <a href="index.php?page=referrals" class="btn-tp-primary">👥 Parrainer un ami</a>
                    <a href="index.php?page=wallet" class="btn-tp-primary">💰 Mon portefeuille</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Deux colonnes -->
    <div class="row">
        <!-- Tâches disponibles -->
        <div class="col-md-6">
            <div class="tp-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="tp-card-header mb-0">✅ Tâches du jour</h4>
                    <a href="index.php?page=tasks" class="btn btn-sm btn-tp-primary">Voir tout</a>
                </div>
                <div id="dashboard-tasks"></div>
            </div>
        </div>

        <!-- Notifications récentes -->
        <div class="col-md-6">
            <div class="tp-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="tp-card-header mb-0">🔔 Notifications</h4>
                    <a href="index.php?page=user_notifications" class="btn btn-sm btn-tp-primary">Voir tout</a>
                </div>
                <div id="dashboard-notifications"></div>
            </div>
        </div>
    </div>

    <!-- Nouveaux produits -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="tp-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="tp-card-header mb-0">🆕 Nouveaux produits</h4>
                    <a href="index.php?page=catalog" class="btn btn-sm btn-tp-primary">Voir tout</a>
                </div>
                <div class="row" id="dashboard-products"></div>
            </div>
        </div>
    </div>
</div>

<script>
    // Charger les stats
    async function loadDashboardStats() {
        try {
            const response = await fetch(TrustPick.API_BASE + '/user-stats.php');
            const data = await response.json();

            if (data.success) {
                document.getElementById('tasks-completed').textContent = data.tasks_completed || 0;
                document.getElementById('reviews-count').textContent = data.reviews_count || 0;
                document.getElementById('referrals-count').textContent = data.referrals_count || 0;
            }
        } catch (error) {
            console.error('Erreur chargement stats:', error);
        }
    }

    // Charger les tâches disponibles
    async function loadDashboardTasks() {
        try {
            const response = await fetch(TrustPick.API_BASE + '/tasks-available.php');
            const data = await response.json();

            const container = document.getElementById('dashboard-tasks');

            if (data.success && data.tasks && data.tasks.length > 0) {
                const tasksHTML = data.tasks.slice(0, 3).map(task => `
                <div class="task-card mb-3 ${task.is_completed ? 'completed' : ''}" style="padding: 1rem;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${task.task_name}</strong><br>
                            <small class="text-fcfa">${TrustPick.formatFCFA(task.reward_amount)}</small>
                        </div>
                        ${!task.is_completed ? '<button class="btn btn-sm btn-tp-success" onclick="location.href=\'index.php?page=tasks\'">Faire</button>' : '<span class="badge-tp badge-active">✓ Fait</span>'}
                    </div>
                </div>
            `).join('');
                container.innerHTML = tasksHTML;
            } else {
                container.innerHTML = '<p class="text-muted text-center">Aucune tâche disponible pour le moment.</p>';
            }
        } catch (error) {
            console.error('Erreur chargement tâches:', error);
        }
    }

    // Charger les notifications récentes
    async function loadDashboardNotifications() {
        try {
            const response = await fetch(TrustPick.API_BASE + '/notifications-list.php?limit=3');
            const data = await response.json();

            const container = document.getElementById('dashboard-notifications');

            if (data.success && data.notifications && data.notifications.length > 0) {
                const notifsHTML = data.notifications.map(notif => `
                <div class="notification-card mb-2 ${!notif.is_read ? 'unread' : ''}" style="padding: 0.8rem;">
                    <div class="notif-content">
                        <strong>${notif.title}</strong><br>
                        <small>${notif.message}</small><br>
                        <small class="text-muted">${TrustPick.formatRelativeTime(notif.created_at)}</small>
                    </div>
                </div>
            `).join('');
                container.innerHTML = notifsHTML;
            } else {
                container.innerHTML = '<p class="text-muted text-center">Aucune notification.</p>';
            }
        } catch (error) {
            console.error('Erreur chargement notifications:', error);
        }
    }

    // Charger les nouveaux produits
    async function loadDashboardProducts() {
        try {
            const response = await fetch(TrustPick.API_BASE + '/products-list.php?limit=3&sort=newest');
            const data = await response.json();

            const container = document.getElementById('dashboard-products');

            if (data.success && data.products && data.products.length > 0) {
                const productsHTML = data.products.map(product => `
                <div class="col-md-4 mb-3">
                    <div class="product-card">
                        <img src="${product.image || '/TrustPick/public/assets/img/placeholder.jpg'}" alt="${product.title}" class="product-image">
                        <div class="product-info">
                            <h5 class="product-title">${product.title}</h5>
                            <p class="product-price">${TrustPick.formatFCFA(product.price)}</p>
                            <a href="index.php?page=product&id=${product.id}" class="btn btn-sm btn-tp-primary w-100">Voir</a>
                        </div>
                    </div>
                </div>
            `).join('');
                container.innerHTML = productsHTML;
            } else {
                container.innerHTML = '<p class="text-muted text-center col-12">Aucun produit disponible.</p>';
            }
        } catch (error) {
            console.error('Erreur chargement produits:', error);
        }
    }

    // Charger tout au démarrage
    window.addEventListener('load', () => {
        loadDashboardStats();
        loadDashboardTasks();
        loadDashboardNotifications();
        loadDashboardProducts();
    });
</script>

<?php include '../layouts/footer.php'; ?>