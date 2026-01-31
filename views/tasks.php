<?php
/**
 * Page Tâches Quotidiennes - TrustPick V2
 * Affiche les tâches disponibles et l'historique
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/url.php';
require_once __DIR__ . '/../includes/helpers.php';

if (session_status() === PHP_SESSION_NONE)
    session_start();

// Protection: utilisateur connecté requis
if (empty($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Connectez-vous pour accéder aux tâches.';
    header('Location: ' . url('index.php?page=login'));
    exit;
}

$uid = intval($_SESSION['user_id']);

// Récupérer le solde
$balStmt = $pdo->prepare('SELECT COALESCE(balance, 0) FROM users WHERE id = ?');
$balStmt->execute([$uid]);
$balance = floatval($balStmt->fetchColumn());

// Récupérer les définitions de tâches
$tasksDefStmt = $pdo->query('SELECT * FROM tasks_definitions WHERE is_active = 1 ORDER BY reward_amount DESC');
$tasksDef = $tasksDefStmt->fetchAll();

// Récupérer les tâches complétées par l'utilisateur aujourd'hui
// FIX: La colonne s'appelle task_id, pas task_definition_id
$todayStart = date('Y-m-d 00:00:00');
$completedTodayStmt = $pdo->prepare('
    SELECT task_id, COUNT(*) as count 
    FROM user_tasks 
    WHERE user_id = ? AND completed_at >= ? 
    GROUP BY task_id
');
$completedTodayStmt->execute([$uid, $todayStart]);
$completedToday = [];
while ($row = $completedTodayStmt->fetch()) {
    $completedToday[$row['task_id']] = $row['count'];
}

// Récupérer toutes les tâches complétées (pour tâches uniques)
$allCompletedStmt = $pdo->prepare('
    SELECT task_id 
    FROM user_tasks 
    WHERE user_id = ?
');
$allCompletedStmt->execute([$uid]);
$allCompleted = $allCompletedStmt->fetchAll(PDO::FETCH_COLUMN);

// Statistiques
$totalTasksStmt = $pdo->prepare('SELECT COUNT(*) FROM user_tasks WHERE user_id = ?');
$totalTasksStmt->execute([$uid]);
$totalTasks = intval($totalTasksStmt->fetchColumn());

$totalEarnedStmt = $pdo->prepare('SELECT COALESCE(SUM(reward_earned), 0) FROM user_tasks WHERE user_id = ?');
$totalEarnedStmt->execute([$uid]);
$totalEarned = floatval($totalEarnedStmt->fetchColumn());

$todayEarnedStmt = $pdo->prepare('SELECT COALESCE(SUM(reward_earned), 0) FROM user_tasks WHERE user_id = ? AND completed_at >= ?');
$todayEarnedStmt->execute([$uid, $todayStart]);
$todayEarned = floatval($todayEarnedStmt->fetchColumn());

// Historique des tâches récentes
// FIX: La colonne s'appelle task_id, pas task_definition_id
$historyStmt = $pdo->prepare('
    SELECT ut.*, td.task_name, td.description 
    FROM user_tasks ut 
    JOIN tasks_definitions td ON ut.task_id = td.id 
    WHERE ut.user_id = ? 
    ORDER BY ut.completed_at DESC 
    LIMIT 10
');
$historyStmt->execute([$uid]);
$history = $historyStmt->fetchAll();
?>

<main class="container py-4">
    <!-- En-tête -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 class="h2 mb-1"><i class="bi bi-check-square"></i> Tâches Quotidiennes</h1>
                    <p class="text-muted mb-0">Complétez des tâches pour gagner des FCFA</p>
                </div>
                <div class="text-end">
                    <span class="badge bg-success fs-6 px-3 py-2">
                        <i class="bi bi-wallet2 me-1"></i> Solde: <?= formatFCFA($balance) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #10b981, #059669);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75">Gains aujourd'hui</p>
                            <h3 class="mb-0"><?= formatFCFA($todayEarned) ?></h3>
                        </div>
                        <div class="display-4 opacity-50"><i class="bi bi-graph-up-arrow"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #0066cc, #0052a3);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75">Tâches complétées</p>
                            <h3 class="mb-0"><?= number_format($totalTasks) ?></h3>
                        </div>
                        <div class="display-4 opacity-50"><i class="bi bi-check-circle"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75">Total gagné</p>
                            <h3 class="mb-0"><?= formatFCFA($totalEarned) ?></h3>
                        </div>
                        <div class="display-4 opacity-50"><i class="bi bi-coin"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tâches disponibles -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0"><i class="bi bi-list-task me-2"></i>Tâches Disponibles</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($tasksDef)): ?>
                        <div class="text-center py-5">
                            <div class="display-4 mb-3 text-muted"><i class="bi bi-calendar-x"></i></div>
                            <p class="text-muted">Aucune tâche disponible pour le moment</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($tasksDef as $task):
                                $taskId = $task['id'];
                                $isDaily = !empty($task['is_daily']);
                                $completedCount = $completedToday[$taskId] ?? 0;
                                $everCompleted = in_array($taskId, $allCompleted);

                                // Déterminer si la tâche peut être faite
                                $canDo = true;
                                $statusText = '';
                                $statusClass = '';

                                if ($isDaily) {
                                    // Tâche quotidienne: peut être refaite chaque jour
                                    if ($completedCount > 0) {
                                        $canDo = false;
                                        $statusText = 'Fait aujourd\'hui';
                                        $statusClass = 'bg-success';
                                    } else {
                                        $statusText = 'Disponible';
                                        $statusClass = 'bg-primary';
                                    }
                                } else {
                                    // Tâche unique: ne peut être faite qu'une fois
                                    if ($everCompleted) {
                                        $canDo = false;
                                        $statusText = 'Déjà complétée';
                                        $statusClass = 'bg-secondary';
                                    } else {
                                        $statusText = 'Unique';
                                        $statusClass = 'bg-warning text-dark';
                                    }
                                }

                                // Icônes par type de tâche
                                $taskIcons = [
                                    'leave_review' => 'bi-star',
                                    'recommend_product' => 'bi-hand-thumbs-up',
                                    'like_review' => 'bi-heart',
                                    'invite_user' => 'bi-person-plus',
                                    'daily_login' => 'bi-door-open'
                                ];
                                $taskIcon = $taskIcons[$task['task_code'] ?? ''] ?? 'bi-check-square';
                                ?>
                                <div class="list-group-item py-3 <?= !$canDo ? 'bg-light' : '' ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="task-icon bg-<?= $canDo ? 'primary' : 'secondary' ?> bg-opacity-10 text-<?= $canDo ? 'primary' : 'secondary' ?>"
                                                style="width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;">
                                                <i class="bi <?= $taskIcon ?>"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1 <?= !$canDo ? 'text-muted' : '' ?>">
                                                    <?= htmlspecialchars($task['task_name']) ?>
                                                </h6>
                                                <p class="mb-0 small text-muted">
                                                    <?= htmlspecialchars($task['description'] ?? '') ?>
                                                </p>
                                                <span class="badge <?= $statusClass ?> mt-1">
                                                    <?php if (!$canDo): ?><i class="bi bi-check me-1"></i><?php endif; ?>
                                                    <?= $statusText ?>
                                                </span>
                                                <?php if ($isDaily): ?>
                                                    <span class="badge bg-info ms-1"><i
                                                            class="bi bi-arrow-repeat me-1"></i>Quotidienne</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold text-success mb-2">+<?= formatFCFA($task['reward_amount']) ?>
                                            </div>
                                            <?php if ($canDo): ?>
                                                <button class="btn btn-sm btn-primary btn-do-task" data-task-id="<?= $taskId ?>"
                                                    data-task-code="<?= htmlspecialchars($task['task_code'] ?? '') ?>">
                                                    Faire la tâche <i class="bi bi-arrow-right"></i>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-secondary" disabled>
                                                    <?= $isDaily && $completedCount > 0 ? 'Demain' : 'Terminé' ?>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Historique -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0">📜 Historique des Tâches</h5>
                </div><i class="bi bi-clock-history me-2"></i>
                <div class="card-body p-0">
                    <?php if (empty($history)): ?>
                        <div class="text-center py-4">
                            <p class="text-muted mb-0">Aucune tâche complétée pour le moment</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tâche</th>
                                        <th>Récompense</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($history as $h): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($h['task_name']) ?></strong>
                                            </td>
                                            <td class="text-success fw-bold">+<?= formatFCFA($h['reward_earned']) ?></td>
                                            <td class="text-muted"><?= date('d/m/Y H:i', strtotime($h['completed_at'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Modal pour tâches spécifiques -->
<div class="modal fade" id="taskModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Compléter la tâche</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="taskModalBody">
                <!-- Contenu dynamique -->
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Gestion des boutons de tâche
        document.querySelectorAll('.btn-do-task').forEach(btn => {
            btn.addEventListener('click', function () {
                const taskId = this.dataset.taskId;
                const taskType = this.dataset.taskType;

                // Rediriger vers la page appropriée selon le type de tâche
                switch (taskType) {
                    case 'leave_review':
                        window.location.href = '<?= url("index.php?page=catalog") ?>';
                        break;
                    case 'recommend_product':
                        window.location.href = '<?= url("index.php?page=catalog") ?>';
                        break;
                    case 'like_review':
                        window.location.href = '<?= url("index.php?page=catalog") ?>';
                        break;
                    case 'invite_user':
                        window.location.href = '<?= url("index.php?page=referrals") ?>';
                        break;
                    case 'daily_login':
                        // Compléter automatiquement la tâche de connexion quotidienne
                        completeTask(taskId);
                        break;
                    default:
                        alert('Rendez-vous sur la page correspondante pour compléter cette tâche.');
                }
            });
        });

        function completeTask(taskId) {
            fetch('<?= url("actions/complete_task.php") ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'task_id=' + taskId
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        showToast('success', 'Tâche complétée ! +' + data.reward + ' FCFA');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showToast('error', data.message || 'Erreur lors de la complétion');
                    }
                })
                .catch(() => showToast('error', 'Erreur de connexion'));
        }
    });
</script>