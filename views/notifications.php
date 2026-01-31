<?php
/**
 * Page Notifications - TrustPick V2
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/url.php';
require_once __DIR__ . '/../includes/helpers.php';

if (session_status() === PHP_SESSION_NONE)
    session_start();

if (empty($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Connectez-vous pour voir vos notifications.';
    header('Location: ' . url('index.php?page=login'));
    exit;
}

$uid = intval($_SESSION['user_id']);

// Marquer comme lues si demandé
if (isset($_GET['mark_read']) && $_GET['mark_read'] === 'all') {
    $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?')->execute([$uid]);
    if (headers_sent()) {
        echo '<script>window.location.href="' . url('index.php?page=notifications') . '";</script>';
        echo '<noscript><meta http-equiv="refresh" content="0;url=' . url('index.php?page=notifications') . '"></noscript>';
    } else {
        header('Location: ' . url('index.php?page=notifications'));
    }
    exit;
}

// Récupérer les notifications
$page = max(1, intval($_GET['p'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$countStmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ?');
$countStmt->execute([$uid]);
$total = intval($countStmt->fetchColumn());
$totalPages = ceil($total / $perPage);

// FIX: LIMIT et OFFSET doivent être des entiers injectés directement
$limit = (int) $perPage;
$off = (int) $offset;
$notifSql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT $limit OFFSET $off";
$notifStmt = $pdo->prepare($notifSql);
$notifStmt->execute([$uid]);
$notifications = $notifStmt->fetchAll();

// Compteur non lues
$unreadStmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
$unreadStmt->execute([$uid]);
$unreadCount = intval($unreadStmt->fetchColumn());

// Types d'icônes Bootstrap Icons
$typeIcons = [
    'task' => 'bi-check-circle-fill',
    'task_reminder' => 'bi-alarm-fill',
    'reward' => 'bi-coin',
    'referral' => 'bi-people-fill',
    'system' => 'bi-bell-fill',
    'review' => 'bi-star-fill',
    'new_review' => 'bi-star-fill',
    'product' => 'bi-box-seam-fill',
    'new_product' => 'bi-box-seam-fill',
    'withdrawal' => 'bi-wallet2'
];

$typeColors = [
    'task' => 'success',
    'task_reminder' => 'warning',
    'reward' => 'warning',
    'referral' => 'info',
    'system' => 'secondary',
    'review' => 'primary',
    'new_review' => 'primary',
    'product' => 'dark',
    'new_product' => 'dark',
    'withdrawal' => 'danger'
];
?>

<main class="container py-4">
    <!-- En-tête -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h1 class="h2 mb-1"><i class="bi bi-bell"></i> Notifications</h1>
                    <p class="text-muted mb-0">
                        <?php if ($unreadCount > 0): ?>
                        <span class="badge bg-danger"><?= $unreadCount ?></span> non
                        lue<?= $unreadCount > 1 ? 's' : '' ?>
                        <?php else: ?>
                        Toutes les notifications sont lues
                        <?php endif; ?>
                    </p>
                </div>
                <?php if ($unreadCount > 0): ?>
                <a href="<?= url('index.php?page=notifications&mark_read=all') ?>"
                    class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-check-all"></i> Tout marquer comme lu
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Liste des notifications -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <?php if (empty($notifications)): ?>
                    <div class="text-center py-5">
                        <div class="display-1 mb-3 text-muted"><i class="bi bi-bell-slash"></i></div>
                        <h5 class="text-muted">Aucune notification</h5>
                        <p class="text-muted">Vos notifications apparaîtront ici</p>
                    </div>
                    <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($notifications as $notif):
                                $type = $notif['type'] ?? 'system';
                                $icon = $typeIcons[$type] ?? 'bi-bell-fill';
                                $color = $typeColors[$type] ?? 'secondary';
                                $isUnread = empty($notif['is_read']);
                                ?>
                        <div
                            class="list-group-item py-3 <?= $isUnread ? 'bg-light border-start border-primary border-3' : '' ?>">
                            <div class="d-flex gap-3">
                                <div class="notification-icon flex-shrink-0 bg-<?= $color ?> bg-opacity-10 text-<?= $color ?>"
                                    style="width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;">
                                    <i class="bi <?= $icon ?>"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <h6 class="mb-1 <?= $isUnread ? 'fw-bold' : '' ?>">
                                            <?= htmlspecialchars($notif['title']) ?>
                                            <?php if ($isUnread): ?>
                                            <span class="badge bg-primary ms-2">Nouveau</span>
                                            <?php endif; ?>
                                        </h6>
                                        <small class="text-muted">
                                            <?= timeAgo($notif['created_at']) ?>
                                        </small>
                                    </div>
                                    <p class="mb-0 text-muted"><?= htmlspecialchars($notif['message']) ?></p>
                                    <?php if (!empty($notif['link'])): ?>
                                    <a href="<?= url($notif['link']) ?>" class="btn btn-link btn-sm p-0 mt-1">
                                        Voir détails →
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <div class="card-footer bg-white py-3">
                        <nav>
                            <ul class="pagination pagination-sm justify-content-center mb-0">
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= url('index.php?page=notifications&p=' . $i) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
// Fonction helper pour le temps relatif
function timeAgo($datetime)
{
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->y > 0)
        return $diff->y . ' an' . ($diff->y > 1 ? 's' : '');
    if ($diff->m > 0)
        return $diff->m . ' mois';
    if ($diff->d > 0)
        return $diff->d . ' jour' . ($diff->d > 1 ? 's' : '');
    if ($diff->h > 0)
        return $diff->h . 'h';
    if ($diff->i > 0)
        return $diff->i . ' min';
    return 'À l\'instant';
}
?>