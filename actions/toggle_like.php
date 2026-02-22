<?php
/**
 * TrustPick V2 - Action: Toggle Like/Unlike sur un avis
 * Compatible avec review_likes OU review_reactions
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/url.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/task_manager.php';

header('Content-Type: application/json');

// Vérifier connexion utilisateur
if (empty($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Vous devez être connecté pour aimer un avis.'
    ]);
    exit;
}

$user_id = intval($_SESSION['user_id']);
$review_id = intval($_POST['review_id'] ?? 0);

// Validation
if (!$review_id) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Avis invalide.'
    ]);
    exit;
}

try {
    $pdo = Database::getInstance()->getConnection();

    // Vérifier que l'avis existe
    $stmt = $pdo->prepare('SELECT id, user_id, likes_count FROM reviews WHERE id = ?');
    $stmt->execute([$review_id]);
    $review = $stmt->fetch();

    if (!$review) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Avis introuvable.'
        ]);
        exit;
    }

    // Ne pas liker son propre avis
    if ($review['user_id'] == $user_id) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Vous ne pouvez pas aimer votre propre avis.'
        ]);
        exit;
    }

    // Déterminer quelle table utiliser (review_likes ou review_reactions)
    $useReviewLikes = true;
    try {
        $pdo->query('SELECT 1 FROM review_likes LIMIT 1');
    } catch (Exception $e) {
        $useReviewLikes = false;
    }

    $pdo->beginTransaction();

    if ($useReviewLikes) {
        // Utiliser review_likes (table simple)
        $checkStmt = $pdo->prepare('SELECT id FROM review_likes WHERE user_id = ? AND review_id = ?');
        $checkStmt->execute([$user_id, $review_id]);
        $existingLike = $checkStmt->fetch();

        if ($existingLike) {
            // UNLIKE
            $pdo->prepare('DELETE FROM review_likes WHERE id = ?')->execute([$existingLike['id']]);
            $pdo->prepare('UPDATE reviews SET likes_count = GREATEST(likes_count - 1, 0) WHERE id = ?')
                ->execute([$review_id]);
            $action = 'unliked';
        } else {
            // LIKE
            $pdo->prepare('INSERT INTO review_likes (user_id, review_id, created_at) VALUES (?, ?, NOW())')
                ->execute([$user_id, $review_id]);
            $pdo->prepare('UPDATE reviews SET likes_count = likes_count + 1 WHERE id = ?')
                ->execute([$review_id]);
            $action = 'liked';
        }
    } else {
        // Utiliser review_reactions (table existante)
        $checkStmt = $pdo->prepare('SELECT id, reaction_type FROM review_reactions WHERE user_id = ? AND review_id = ?');
        $checkStmt->execute([$user_id, $review_id]);
        $existingReaction = $checkStmt->fetch();

        if ($existingReaction) {
            if ($existingReaction['reaction_type'] === 'like') {
                // UNLIKE
                $pdo->prepare('DELETE FROM review_reactions WHERE id = ?')->execute([$existingReaction['id']]);
                $pdo->prepare('UPDATE reviews SET likes_count = GREATEST(likes_count - 1, 0) WHERE id = ?')
                    ->execute([$review_id]);
                $action = 'unliked';
            } else {
                // Changer dislike en like
                $pdo->prepare("UPDATE review_reactions SET reaction_type = 'like' WHERE id = ?")
                    ->execute([$existingReaction['id']]);
                $pdo->prepare('UPDATE reviews SET likes_count = likes_count + 1, dislikes_count = GREATEST(dislikes_count - 1, 0) WHERE id = ?')
                    ->execute([$review_id]);
                $action = 'liked';
            }
        } else {
            // Nouveau LIKE
            $pdo->prepare("INSERT INTO review_reactions (review_id, user_id, reaction_type, created_at) VALUES (?, ?, 'like', NOW())")
                ->execute([$review_id, $user_id]);
            $pdo->prepare('UPDATE reviews SET likes_count = likes_count + 1 WHERE id = ?')
                ->execute([$review_id]);
            $action = 'liked';
        }
    }

    $reward = 0;
    $rewardMessage = '';

    // Récompense uniquement pour un nouveau like
    if ($action === 'liked') {
        $canExecute = TaskManager::canExecuteTask($user_id, 'like_review', $pdo);

        if ($canExecute['can_execute']) {
            $reward = 200;

            $pdo->prepare('UPDATE users SET balance = balance + ? WHERE id = ?')
                ->execute([$reward, $user_id]);

            $balanceStmt = $pdo->prepare('SELECT balance FROM users WHERE id = ?');
            $balanceStmt->execute([$user_id]);
            $newBalance = $balanceStmt->fetchColumn();

            $pdo->prepare("
                INSERT INTO transactions (user_id, type, amount, description, reference_type, balance_after, created_at)
                VALUES (?, 'reward', ?, 'Tâche: Aimer un avis', 'like_review', ?, NOW())
            ")->execute([$user_id, $reward, $newBalance]);

            TaskManager::completeTask($user_id, 'like_review', $pdo);

            $_SESSION['balance'] = $newBalance;

            $rewardMessage = ' +' . formatFCFA($reward) . ' crédités !';
        }

        // Notification pour l'auteur de l'avis
        if ($review['user_id'] != $user_id) {
            $pdo->prepare("
                INSERT INTO notifications (user_id, type, title, message, created_at)
                VALUES (?, 'system', 'Nouveau like', 'Votre avis a reçu un nouveau like !', NOW())
            ")->execute([$review['user_id']]);
        }
    }

    $pdo->commit();

    // Récupérer le nouveau nombre de likes
    $countStmt = $pdo->prepare('SELECT likes_count FROM reviews WHERE id = ?');
    $countStmt->execute([$review_id]);
    $newCount = intval($countStmt->fetchColumn());

    $message = $action === 'liked' ? 'Avis aimé !' . $rewardMessage : 'Like retiré.';

    echo json_encode([
        'status' => $action,
        'likes' => $newCount,
        'reward' => $reward,
        'message' => $message
    ]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode([
        'status' => 'error',
        'message' => 'Erreur: ' . $e->getMessage()
    ]);
}
