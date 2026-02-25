<?php
/**
 * TrustPick V2 - Action: Toggle Like/Unlike sur un avis
 * Utilise review_reactions (table standard)
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
    echo json_encode(['status' => 'error', 'message' => 'Vous devez être connecté pour aimer un avis.']);
    exit;
}

$user_id = intval($_SESSION['user_id']);
$review_id = intval($_POST['review_id'] ?? 0);

if (!$review_id) {
    echo json_encode(['status' => 'error', 'message' => 'Avis invalide.']);
    exit;
}

try {
    $pdo = Database::getInstance()->getConnection();

    // Vérifier que l'avis existe
    $stmt = $pdo->prepare('SELECT id, user_id, likes_count FROM reviews WHERE id = ?');
    $stmt->execute([$review_id]);
    $review = $stmt->fetch();

    if (!$review) {
        echo json_encode(['status' => 'error', 'message' => 'Avis introuvable.']);
        exit;
    }

    // Ne pas liker son propre avis
    if ($review['user_id'] == $user_id) {
        echo json_encode(['status' => 'error', 'message' => 'Vous ne pouvez pas aimer votre propre avis.']);
        exit;
    }

    // Vérifier réaction existante (table review_reactions)
    $checkStmt = $pdo->prepare('SELECT id, reaction_type FROM review_reactions WHERE user_id = ? AND review_id = ?');
    $checkStmt->execute([$user_id, $review_id]);
    $existing = $checkStmt->fetch();

    if ($existing) {
        if ($existing['reaction_type'] === 'like') {
            // UNLIKE : retirer le like
            $pdo->prepare('DELETE FROM review_reactions WHERE id = ?')->execute([$existing['id']]);
            $pdo->prepare('UPDATE reviews SET likes_count = GREATEST(likes_count - 1, 0) WHERE id = ?')
                ->execute([$review_id]);
            $action = 'unliked';
        } else {
            // Changer dislike → like
            $pdo->prepare("UPDATE review_reactions SET reaction_type = 'like' WHERE id = ?")
                ->execute([$existing['id']]);
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

    $reward = 0;
    $rewardMessage = '';

    // Récompense uniquement pour un nouveau like
    if ($action === 'liked') {
        try {
            $canExecute = TaskManager::canExecuteTask($user_id, 'like_review', $pdo);
            if ($canExecute['can_execute']) {
                $result = TaskManager::completeTask($user_id, 'like_review', $pdo);
                if ($result['success']) {
                    $reward = floatval($result['reward'] ?? 50);
                    $rewardMessage = ' +' . formatFCFA($reward) . ' crédités !';
                }
            }
        } catch (Exception $taskErr) {
            // La récompense échoue mais le like est déjà enregistré — on continue
        }

        // Notification pour l'auteur de l'avis
        try {
            if ($review['user_id'] != $user_id) {
                $pdo->prepare("
                    INSERT INTO notifications (user_id, type, title, message, created_at)
                    VALUES (?, 'system', 'Nouveau like', 'Votre avis a reçu un nouveau like !', NOW())
                ")->execute([$review['user_id']]);
            }
        } catch (Exception $notifErr) {
            // Notification échoue silencieusement — non bloquant
        }
    }

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
    echo json_encode([
        'status' => 'error',
        'message' => 'Erreur: ' . $e->getMessage()
    ]);
}
