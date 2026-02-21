<?php
/**
 * TrustPick V2 - Action: Liker un avis
 * Récompense: 50 FCFA
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/url.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/task_manager.php';

header('Content-Type: application/json');

// Vérifier connexion
if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Connectez-vous pour réagir.']);
    exit;
}

$user_id = intval($_SESSION['user_id']);
$review_id = intval($_POST['review_id'] ?? $_GET['review_id'] ?? 0);
$reaction = $_POST['reaction'] ?? $_GET['reaction'] ?? 'like';

if (!$review_id) {
    echo json_encode(['success' => false, 'message' => 'Avis invalide.']);
    exit;
}

try {
    $pdo = Database::getInstance()->getConnection();

    // Vérifier que l'avis existe
    $stmt = $pdo->prepare('SELECT id, user_id FROM reviews WHERE id = ?');
    $stmt->execute([$review_id]);
    $review = $stmt->fetch();

    if (!$review) {
        echo json_encode(['success' => false, 'message' => 'Avis introuvable.']);
        exit;
    }

    // Ne pas liker son propre avis
    if ($review['user_id'] == $user_id) {
        echo json_encode(['success' => false, 'message' => 'Vous ne pouvez pas réagir à votre propre avis.']);
        exit;
    }

    // Vérifier si l'utilisateur a déjà réagi à cet avis
    $checkStmt = $pdo->prepare('SELECT id, reaction_type FROM review_reactions WHERE user_id = ? AND review_id = ?');
    $checkStmt->execute([$user_id, $review_id]);
    $existingReaction = $checkStmt->fetch();

    $pdo->beginTransaction();

    if ($existingReaction) {
        if ($existingReaction['reaction_type'] === $reaction) {
            // Supprimer la réaction
            $pdo->prepare('DELETE FROM review_reactions WHERE id = ?')->execute([$existingReaction['id']]);

            // Mettre à jour le compteur
            $countColumn = $reaction === 'like' ? 'likes_count' : 'dislikes_count';
            $pdo->prepare("UPDATE reviews SET $countColumn = GREATEST($countColumn - 1, 0) WHERE id = ?")->execute([$review_id]);

            $pdo->commit();
            echo json_encode(['success' => true, 'action' => 'removed', 'message' => 'Réaction retirée.']);
            exit;
        } else {
            // Changer la réaction
            $pdo->prepare('UPDATE review_reactions SET reaction_type = ? WHERE id = ?')
                ->execute([$reaction, $existingReaction['id']]);

            // Mettre à jour les compteurs
            if ($reaction === 'like') {
                $pdo->prepare('UPDATE reviews SET likes_count = likes_count + 1, dislikes_count = GREATEST(dislikes_count - 1, 0) WHERE id = ?')->execute([$review_id]);
            } else {
                $pdo->prepare('UPDATE reviews SET dislikes_count = dislikes_count + 1, likes_count = GREATEST(likes_count - 1, 0) WHERE id = ?')->execute([$review_id]);
            }

            $pdo->commit();
            echo json_encode(['success' => true, 'action' => 'changed', 'message' => 'Réaction modifiée.']);
            exit;
        }
    }

    // Nouvelle réaction
    $pdo->prepare('INSERT INTO review_reactions (review_id, user_id, reaction_type, created_at) VALUES (?, ?, ?, NOW())')
        ->execute([$review_id, $user_id, $reaction]);

    // Mettre à jour le compteur
    $countColumn = $reaction === 'like' ? 'likes_count' : 'dislikes_count';
    $pdo->prepare("UPDATE reviews SET $countColumn = $countColumn + 1 WHERE id = ?")->execute([$review_id]);

    // Vérifier si l'utilisateur peut recevoir la récompense pour la tâche like_review
    $canExecute = TaskManager::canExecuteTask($user_id, 'like_review', $pdo);
    $reward = 50;

    if ($canExecute['can_execute'] && $reaction === 'like') {
        // Créditer la récompense
        $pdo->prepare('UPDATE users SET balance = balance + ? WHERE id = ?')
            ->execute([$reward, $user_id]);

        // Récupérer nouveau solde
        $balanceStmt = $pdo->prepare('SELECT balance FROM users WHERE id = ?');
        $balanceStmt->execute([$user_id]);
        $newBalance = $balanceStmt->fetchColumn();

        // Enregistrer la transaction
        $pdo->prepare("
            INSERT INTO transactions (user_id, type, amount, description, reference_type, balance_after, created_at)
            VALUES (?, 'reward', ?, 'Like sur un avis', 'like_review', ?, NOW())
        ")->execute([$user_id, $reward, $newBalance]);

        // Compléter la tâche
        TaskManager::completeTask($user_id, 'like_review', $pdo);

        // Mettre à jour la session
        $_SESSION['balance'] = $newBalance;

        $pdo->commit();
        echo json_encode([
            'success' => true,
            'action' => 'added',
            'message' => 'Avis liké ! +' . formatFCFA($reward) . ' crédités.',
            'reward' => $reward
        ]);
    } else {
        $pdo->commit();
        $msg = $reaction === 'like' ? 'Avis liké !' : 'Avis disliké !';
        if (!$canExecute['can_execute'] && $reaction === 'like') {
            $msg .= ' (Pas de récompense: ' . $canExecute['message'] . ')';
        }
        echo json_encode(['success' => true, 'action' => 'added', 'message' => $msg]);
    }

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
