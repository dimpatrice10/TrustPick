<?php
$page_title = 'Mes Tâches';
$current_page = 'tasks';

include '../layouts/header.php';
include '../layouts/sidebar-user.php';
?>

<!-- Contenu principal -->
<div class="col-md-9 col-lg-10" style="padding: 2rem;">
    <h2 style="margin-bottom: 2rem;">✅ Mes Tâches Quotidiennes</h2>

    <!-- Stats tâches -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-value" id="tasks-completed-today">0</div>
                <div class="stat-label">Tâches complétées aujourd'hui</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-value" id="earnings-today">0 FCFA</div>
                <div class="stat-label">Gains du jour</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon">🏆</div>
                <div class="stat-value" id="total-tasks">0</div>
                <div class="stat-label">Total des tâches complétées</div>
            </div>
        </div>
    </div>

    <!-- Alerte info -->
    <div class="alert alert-info mb-4">
        <span class="alert-icon">ℹ️</span>
        <div>
            <strong>Comment ça marche ?</strong><br>
            Complétez des tâches quotidiennes pour gagner des FCFA. Certaines tâches sont répétables chaque jour,
            d'autres une seule fois. Une fois une tâche complétée, vous ne pouvez pas la refaire (sauf si elle est
            quotidienne).
        </div>
    </div>

    <!-- Liste des tâches -->
    <div class="tp-card">
        <h4 class="tp-card-header">📋 Tâches disponibles</h4>
        <div id="tasks-list"></div>
    </div>

    <!-- Historique -->
    <div class="tp-card mt-4">
        <h4 class="tp-card-header">📜 Historique des tâches complétées</h4>
        <div id="tasks-history"></div>
    </div>
</div>

<script>
    // Charger les stats des tâches
    async function loadTasksStats() {
        try {
            const response = await fetch(TrustPick.API_BASE + '/tasks-stats.php');
            const data = await response.json();

            if (data.success) {
                document.getElementById('tasks-completed-today').textContent = data.completed_today || 0;
                document.getElementById('earnings-today').textContent = TrustPick.formatFCFA(data.earnings_today || 0);
                document.getElementById('total-tasks').textContent = data.total_completed || 0;
            }
        } catch (error) {
            console.error('Erreur chargement stats tâches:', error);
        }
    }

    // Charger les tâches disponibles
    async function loadAvailableTasks() {
        try {
            const response = await fetch(TrustPick.API_BASE + '/tasks-available.php');
            const data = await response.json();

            const container = document.getElementById('tasks-list');

            if (data.success && data.tasks && data.tasks.length > 0) {
                const tasksHTML = data.tasks.map(task => {
                    const isCompleted = task.is_completed || false;
                    const canComplete = !isCompleted || (task.is_daily && !task.completed_today);

                    return `
                    <div class="task-card ${isCompleted && !canComplete ? 'completed' : ''}">
                        <div class="task-icon">${getTaskIcon(task.task_code)}</div>
                        <div class="task-info">
                            <h5>${task.task_name}</h5>
                            <p>${task.description}</p>
                            <span class="task-reward">${TrustPick.formatFCFA(task.reward_amount)}</span>
                            ${task.is_daily ? '<span class="badge-tp badge-active ms-2">Quotidien</span>' : ''}
                        </div>
                        <button 
                            class="task-complete-btn btn btn-tp-success" 
                            onclick="completeTask(${task.id}, '${task.task_code}')"
                            ${!canComplete ? 'disabled' : ''}
                        >
                            ${!canComplete ? '✓ Complété' : 'Compléter'}
                        </button>
                    </div>
                `;
                }).join('');

                container.innerHTML = tasksHTML;
            } else {
                container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">✅</div>
                    <h3 class="empty-state-title">Aucune tâche disponible</h3>
                    <p class="empty-state-description">Toutes les tâches ont été complétées !</p>
                </div>
            `;
            }
        } catch (error) {
            console.error('Erreur chargement tâches:', error);
            TrustPick.showToast('Erreur de chargement des tâches', 'error');
        }
    }

    // Icônes des tâches
    function getTaskIcon(taskCode) {
        const icons = {
            'leave_review': '✍️',
            'recommend_product': '👍',
            'like_review': '❤️',
            'invite_user': '👥',
            'daily_login': '🎯'
        };
        return icons[taskCode] || '✅';
    }

    // Compléter une tâche
    async function completeTask(taskId, taskCode) {
        // Confirmation
        const confirmed = await TrustPick.confirm(
            'Êtes-vous sûr d\'avoir accompli cette tâche ? La récompense sera créditée immédiatement.',
            'Confirmer la tâche'
        );

        if (!confirmed) return;

        try {
            const response = await TrustPick.api('tasks-complete.php', {
                method: 'POST',
                body: {
                    task_id: taskId
                }
            });

            if (response && response.success) {
                TrustPick.showToast(
                    `Bravo ! Vous avez gagné ${TrustPick.formatFCFA(response.reward_amount)}`,
                    'success',
                    'Tâche complétée'
                );

                // Mettre à jour le solde dans la session
                if (response.new_balance) {
                    SessionManager.updateBalance(response.new_balance);
                    document.getElementById('balance').textContent = TrustPick.formatFCFA(response.new_balance);
                }

                // Recharger les tâches et stats
                loadAvailableTasks();
                loadTasksStats();
                loadTasksHistory();
            } else {
                TrustPick.showToast(response?.message || 'Erreur lors de la complétion', 'error');
            }
        } catch (error) {
            console.error('Erreur complétion tâche:', error);
            TrustPick.showToast('Erreur de connexion', 'error');
        }
    }

    // Charger l'historique
    async function loadTasksHistory() {
        try {
            const response = await fetch(TrustPick.API_BASE + '/tasks-history.php?limit=10');
            const data = await response.json();

            const container = document.getElementById('tasks-history');

            if (data.success && data.history && data.history.length > 0) {
                const historyHTML = data.history.map(item => `
                <div class="transaction-item">
                    <div class="transaction-info">
                        <h6>${item.task_name}</h6>
                        <small>${TrustPick.formatRelativeTime(item.completed_at)}</small>
                    </div>
                    <div class="transaction-amount positive">
                        ${TrustPick.formatFCFA(item.reward_earned)}
                    </div>
                </div>
            `).join('');
                container.innerHTML = historyHTML;
            } else {
                container.innerHTML = '<p class="text-center text-muted">Aucune tâche complétée pour le moment.</p>';
            }
        } catch (error) {
            console.error('Erreur chargement historique:', error);
        }
    }

    // Initialiser
    window.addEventListener('load', () => {
        loadTasksStats();
        loadAvailableTasks();
        loadTasksHistory();
    });
</script>

<?php include '../layouts/footer.php'; ?>