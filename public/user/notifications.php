<?php
$page_title = 'Notifications';
$current_page = 'notifications';

include '../layouts/header.php';
include '../layouts/sidebar-user.php';
?>

<!-- Contenu principal -->
<div class="col-md-9 col-lg-10" style="padding: 2rem;">
    <h2 style="margin-bottom: 2rem;">🔔 Centre de Notifications</h2>

    <!-- Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="tp-card">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>📬 <span id="unread-count">0</span> notification(s) non lue(s)</strong>
                    </div>
                    <button class="btn-tp-primary" onclick="markAllAsRead()">
                        ✓ Tout marquer comme lu
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="tabs-container mb-4">
        <div class="tabs-header">
            <button class="tab-button active" data-filter="all">Toutes</button>
            <button class="tab-button" data-filter="unread">Non lues</button>
            <button class="tab-button" data-filter="task_reminder">Tâches</button>
            <button class="tab-button" data-filter="reward">Récompenses</button>
            <button class="tab-button" data-filter="referral">Parrainages</button>
        </div>
    </div>

    <!-- Liste des notifications avec pagination -->
    <div id="notifications-container">
        <div class="pagination-items"></div>
        <div class="pagination-controls"></div>
    </div>
</div>

<script>
    let notificationsPagination;
    let currentFilter = 'all';

    // Initialiser la pagination des notifications
    function initNotificationsPagination(filter = 'all') {
        const params = filter !== 'all' ? `?filter=${filter}` : '';
        const endpoint = TrustPick.API_BASE + '/notifications-list.php' + params;

        notificationsPagination = new TrustPickPagination({
            endpoint: endpoint,
            containerId: 'notifications-container',
            itemsPerPage: 5,
            renderItem: renderNotification,
            emptyMessage: 'Aucune notification'
        });

        window['pagination_notifications-container'] = notificationsPagination;
    }

    // Renderer personnalisé pour notifications
    function renderNotification(notif) {
        const icons = {
            task_reminder: '✅',
            new_product: '🛍️',
            new_review: '⭐',
            reward: '💰',
            referral: '👥',
            withdrawal: '💵',
            system: 'ℹ️'
        };

        return `
        <div class="notification-card ${!notif.is_read ? 'unread' : ''}" 
             onclick="markNotificationAsRead(${notif.id}, '${notif.link || ''}')">
            <div class="notif-icon">${icons[notif.type] || 'ℹ️'}</div>
            <div class="notif-content">
                <h6>${notif.title}</h6>
                <p>${notif.message}</p>
                <small>${TrustPick.formatRelativeTime(notif.created_at)}</small>
            </div>
            ${!notif.is_read ? '<span class="badge-tp badge-active" style="margin-left: auto;">Nouveau</span>' : ''}
        </div>
    `;
    }

    // Charger le compteur de non lues
    async function loadUnreadCount() {
        try {
            const response = await fetch(TrustPick.API_BASE + '/notifications-unread-count.php');
            const data = await response.json();

            if (data.success) {
                document.getElementById('unread-count').textContent = data.count || 0;
            }
        } catch (error) {
            console.error('Erreur chargement compteur:', error);
        }
    }

    // Marquer une notification comme lue
    async function markNotificationAsRead(notifId, link) {
        try {
            await TrustPick.api('notifications-mark-read.php', {
                method: 'POST',
                body: { notification_id: notifId }
            });

            // Recharger le compteur
            loadUnreadCount();

            // Rediriger si lien présent
            if (link) {
                window.location.href = link;
            } else {
                // Recharger les notifications
                notificationsPagination.reset();
            }
        } catch (error) {
            console.error('Erreur marquage notification:', error);
        }
    }

    // Marquer toutes comme lues
    async function markAllAsRead() {
        const confirmed = await TrustPick.confirm(
            'Voulez-vous marquer toutes les notifications comme lues ?',
            'Confirmer'
        );

        if (!confirmed) return;

        try {
            const response = await TrustPick.api('notifications-mark-all-read.php', {
                method: 'POST'
            });

            if (response && response.success) {
                TrustPick.showToast('Toutes les notifications ont été marquées comme lues', 'success');
                loadUnreadCount();
                notificationsPagination.reset();
            }
        } catch (error) {
            console.error('Erreur marquage toutes notifications:', error);
        }
    }

    // Gestion des filtres
    document.querySelectorAll('.tab-button').forEach(button => {
        button.addEventListener('click', () => {
            // Désactiver tous les boutons
            document.querySelectorAll('.tab-button').forEach(b => b.classList.remove('active'));

            // Activer le bouton cliqué
            button.classList.add('active');

            // Appliquer le filtre
            const filter = button.getAttribute('data-filter');
            currentFilter = filter;
            initNotificationsPagination(filter);
        });
    });

    // Initialiser
    window.addEventListener('load', () => {
        loadUnreadCount();
        initNotificationsPagination();

        // Recharger toutes les 30 secondes
        setInterval(() => {
            loadUnreadCount();
            if (currentFilter === 'all' || currentFilter === 'unread') {
                notificationsPagination.reset();
            }
        }, 30000);
    });
</script>

<?php include '../layouts/footer.php'; ?>