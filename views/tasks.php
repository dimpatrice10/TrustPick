<?php
/**
 * Page Tâches Quotidiennes - TrustPick V2
 * Affiche les tâches disponibles dans l'ordre obligatoire
 * UTILISE TaskManager pour l'ordre strict
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/url.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/task_manager.php';

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

// UTILISER TaskManager pour obtenir les tâches ordonnées
$dailyTasksStatus = TaskManager::getDailyTasksStatus($uid, $pdo);

// Récupérer les définitions de tâches (pour compatibilité)
$tasksDefStmt = $pdo->query('SELECT * FROM tasks_definitions WHERE is_active = TRUE ORDER BY id');
$tasksDef = $tasksDefStmt->fetchAll();

// Mapper par task_code pour référence facile
$tasksDefByCode = [];
foreach ($tasksDef as $td) {
    $tasksDefByCode[$td['task_code']] = $td;
}

// Récupérer les tâches complétées par l'utilisateur aujourd'hui
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

    <!-- Tâches disponibles - ORDONNÉES -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-list-ol me-2"></i>Tâches Quotidiennes Obligatoires</h5>
                        <span class="badge bg-info"><i class="bi bi-info-circle me-1"></i>Respectez l'ordre</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($dailyTasksStatus)): ?>
                        <div class="text-center py-5">
                            <div class="display-4 mb-3 text-muted"><i class="bi bi-calendar-x"></i></div>
                            <p class="text-muted">Aucune tâche disponible pour le moment</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php
                            $stepNumber = 1;
                            foreach ($dailyTasksStatus as $taskStatus):
                                $taskCode = $taskStatus['task_code'];
                                $isCompleted = $taskStatus['is_completed'];
                                $canExecute = $taskStatus['can_execute'];
                                $blockedBy = $taskStatus['blocked_by'] ?? null;
                                $reward = $taskStatus['reward_amount'];
                                $taskName = $taskStatus['task_name'];
                                $description = $taskStatus['description'];

                                // Icônes par type de tâche
                                $taskIcons = [
                                    'leave_review' => 'bi-star',
                                    'recommend_product' => 'bi-hand-thumbs-up',
                                    'like_review' => 'bi-heart',
                                    'deposit_5000' => 'bi-wallet',
                                    'invite_user' => 'bi-person-plus',
                                    'daily_login' => 'bi-door-open'
                                ];
                                $taskIcon = $taskIcons[$taskCode] ?? 'bi-check-square';

                                // Couleurs selon le statut
                                if ($isCompleted) {
                                    $statusClass = 'bg-success';
                                    $statusText = '<i class="bi bi-check-circle me-1"></i>Complété';
                                    $cardClass = 'bg-success bg-opacity-10';
                                } elseif ($canExecute) {
                                    $statusClass = 'bg-primary';
                                    $statusText = '<i class="bi bi-play-circle me-1"></i>Disponible';
                                    $cardClass = '';
                                } else {
                                    $statusClass = 'bg-secondary';
                                    $statusText = '<i class="bi bi-lock me-1"></i>Verrouillé';
                                    $cardClass = 'bg-light';
                                }
                                ?>
                                <div class="list-group-item py-3 <?= $cardClass ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center gap-3">
                                            <!-- Numéro d'étape -->
                                            <div class="step-number <?= $isCompleted ? 'bg-success' : ($canExecute ? 'bg-primary' : 'bg-secondary') ?> text-white"
                                                style="width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:bold;font-size:16px;">
                                                <?php if ($isCompleted): ?>
                                                    <i class="bi bi-check"></i>
                                                <?php else: ?>
                                                    <?= $stepNumber ?>
                                                <?php endif; ?>
                                            </div>
                                            <!-- Icône de la tâche -->
                                            <div class="task-icon <?= $isCompleted ? 'bg-success' : ($canExecute ? 'bg-primary' : 'bg-secondary') ?> bg-opacity-10 text-<?= $isCompleted ? 'success' : ($canExecute ? 'primary' : 'secondary') ?>"
                                                style="width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;">
                                                <i class="bi <?= $taskIcon ?>"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1 <?= !$canExecute && !$isCompleted ? 'text-muted' : '' ?>">
                                                    <?= htmlspecialchars($taskName) ?>
                                                </h6>
                                                <p class="mb-0 small text-muted">
                                                    <?= htmlspecialchars($description ?? '') ?>
                                                </p>
                                                <span class="badge <?= $statusClass ?> mt-1">
                                                    <?= $statusText ?>
                                                </span>
                                                <?php if ($blockedBy && !$isCompleted): ?>
                                                    <span class="badge bg-warning text-dark ms-1">
                                                        <i class="bi bi-arrow-left me-1"></i>Faire d'abord:
                                                        <?= htmlspecialchars($blockedBy) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold text-success mb-2">+<?= formatFCFA($reward) ?></div>
                                            <?php if ($isCompleted): ?>
                                                <button class="btn btn-sm btn-success" disabled>
                                                    <i class="bi bi-check-circle me-1"></i>Fait
                                                </button>
                                            <?php elseif ($canExecute): ?>
                                                <button class="btn btn-sm btn-primary btn-do-task"
                                                    data-task-code="<?= htmlspecialchars($taskCode) ?>">
                                                    Faire <i class="bi bi-arrow-right"></i>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-secondary" disabled>
                                                    <i class="bi bi-lock me-1"></i>Verrouillé
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php $stepNumber++; endforeach; ?>
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
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Historique des Tâches</h5>
                </div>
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
                const taskCode = this.dataset.taskCode;

                // Rediriger vers la page appropriée selon le type de tâche
                switch (taskCode) {
                    case 'leave_review':
                        window.location.href = '<?= url("index.php?page=catalog") ?>';
                        break;
                    case 'recommend_product':
                        window.location.href = '<?= url("index.php?page=catalog") ?>';
                        break;
                    case 'like_review':
                        window.location.href = '<?= url("index.php?page=catalog") ?>';
                        break;
                    case 'deposit_5000':
                        window.location.href = '<?= url("index.php?page=wallet") ?>';
                        break;
                    case 'invite_user':
                        window.location.href = '<?= url("index.php?page=referrals") ?>';
                        break;
                    case 'daily_login':
                        // Compléter automatiquement la tâche de connexion quotidienne
                        completeTask(taskCode);
                        break;
                    default:
                        // Rediriger vers le catalogue par défaut
                        window.location.href = '<?= url("index.php?page=catalog") ?>';
                }
            });
        });

        function completeTask(taskCode) {
            fetch('<?= url("actions/complete_task.php") ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'task_code=' + taskCode
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