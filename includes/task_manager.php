<?php
/**
 * TrustPick V2 - Gestionnaire de Tâches Stratégique
 * 
 * Logique métier progressive :
 * 1. daily_login       → Auto-validée à la connexion (100 FCFA)
 * 2. leave_review      → Laisser un avis (500 FCFA)
 * 3. like_review       → Aimer un avis (50 FCFA)
 * 4. recommend_product → Recommander un produit (200 FCFA)
 * 5. deposit_5000      → Dépôt min 1000 FCFA (débloqué quand gains cumulés >= 20 000 FCFA)
 * 
 * Le dépôt est la DERNIÈRE tâche, accessible uniquement après avoir accumulé
 * suffisamment de gains via les autres tâches (seuil configurable, défaut 20 000 FCFA).
 * Toutes les vérifications sont côté serveur pour éviter toute manipulation.
 */

class TaskManager
{
    /** Seuil de gains cumulés requis avant d'accéder au dépôt (en FCFA) */
    const DEPOSIT_EARNINGS_THRESHOLD = 20000;

    /** Ordre des tâches obligatoires quotidiennes */
    private static $taskOrder = [
        'daily_login' => 1,
        'leave_review' => 2,
        'like_review' => 3,
        'recommend_product' => 4,
        'deposit_5000' => 5
    ];

    /**
     * Vérifier si l'utilisateur peut exécuter une tâche (validation backend)
     */
    public static function canExecuteTask($user_id, $task_code, $pdo)
    {
        // daily_login : toujours exécutable si pas encore fait aujourd'hui
        if ($task_code === 'daily_login') {
            if (self::isTaskCompletedToday($user_id, $task_code, $pdo)) {
                return ['can_execute' => false, 'message' => 'Connexion quotidienne déjà validée.'];
            }
            return ['can_execute' => true, 'message' => ''];
        }

        // deposit_5000 : vérification du seuil de gains cumulés
        if ($task_code === 'deposit_5000') {
            $eligibility = self::checkDepositEligibility($user_id, $pdo);
            if (!$eligibility['eligible']) {
                return ['can_execute' => false, 'message' => $eligibility['message']];
            }
        }

        $taskPosition = self::$taskOrder[$task_code] ?? 999;

        // Vérifier les tâches précédentes (sauf deposit qui ne bloque pas les autres)
        foreach (self::$taskOrder as $code => $order) {
            if ($order >= $taskPosition)
                break;
            if ($code === 'deposit_5000')
                continue;

            if (!self::isTaskCompletedToday($user_id, $code, $pdo)) {
                return [
                    'can_execute' => false,
                    'message' => 'Vous devez d\'abord compléter: ' . self::getTaskName($code, $pdo)
                ];
            }
        }

        // Vérifier si déjà faite aujourd'hui
        if (self::isTaskCompletedToday($user_id, $task_code, $pdo)) {
            return ['can_execute' => false, 'message' => 'Vous avez déjà complété cette tâche aujourd\'hui.'];
        }

        return ['can_execute' => true, 'message' => ''];
    }

    /**
     * Vérifier l'éligibilité au dépôt (gains cumulés >= seuil)
     */
    public static function checkDepositEligibility($user_id, $pdo)
    {
        $threshold = self::DEPOSIT_EARNINGS_THRESHOLD;

        // Gains cumulés des tâches
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(reward_earned), 0) FROM user_tasks WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $totalEarnings = floatval($stmt->fetchColumn());

        // Ajouter les gains de parrainage
        try {
            $refStmt = $pdo->prepare("SELECT COALESCE(SUM(reward_amount), 0) FROM referrals WHERE referrer_id = ? AND is_rewarded = TRUE");
            $refStmt->execute([$user_id]);
            $totalEarnings += floatval($refStmt->fetchColumn());
        } catch (Exception $e) { /* table peut ne pas exister */
        }

        if ($totalEarnings < $threshold) {
            $remaining = $threshold - $totalEarnings;
            return [
                'eligible' => false,
                'total_earnings' => $totalEarnings,
                'threshold' => $threshold,
                'remaining' => $remaining,
                'message' => sprintf(
                    'Vous devez accumuler %s de gains avant de pouvoir effectuer un dépôt. Il vous reste %s à gagner.',
                    formatFCFA($threshold),
                    formatFCFA($remaining)
                )
            ];
        }

        return ['eligible' => true, 'total_earnings' => $totalEarnings, 'threshold' => $threshold, 'remaining' => 0, 'message' => ''];
    }

    /**
     * Auto-compléter la connexion quotidienne (appelée automatiquement)
     */
    public static function autoCompleteLogin($user_id, $pdo)
    {
        if (self::isTaskCompletedToday($user_id, 'daily_login', $pdo)) {
            return false;
        }

        $stmt = $pdo->prepare('SELECT id, reward_amount FROM tasks_definitions WHERE task_code = ? AND is_active = TRUE');
        $stmt->execute(['daily_login']);
        $task = $stmt->fetch();
        if (!$task)
            return false;

        try {
            $pdo->beginTransaction();

            $pdo->prepare('INSERT INTO user_tasks (user_id, task_id, completed_at, reward_earned) VALUES (?, ?, NOW(), ?)')
                ->execute([$user_id, $task['id'], $task['reward_amount']]);

            if ($task['reward_amount'] > 0) {
                $pdo->prepare('UPDATE users SET balance = balance + ? WHERE id = ?')
                    ->execute([$task['reward_amount'], $user_id]);

                $pdo->prepare("INSERT INTO transactions (user_id, type, amount, description, reference_type, created_at) VALUES (?, 'reward', ?, 'Connexion quotidienne', 'task', NOW())")
                    ->execute([$user_id, $task['reward_amount']]);
            }

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

    /**
     * Vérifier si une tâche est complétée aujourd'hui
     */
    public static function isTaskCompletedToday($user_id, $task_code, $pdo)
    {
        $stmt = $pdo->prepare('
            SELECT ut.id FROM user_tasks ut
            JOIN tasks_definitions td ON td.id = ut.task_id
            WHERE ut.user_id = ? AND td.task_code = ? AND DATE(ut.completed_at) = CURRENT_DATE
        ');
        $stmt->execute([$user_id, $task_code]);
        return $stmt->fetch() !== false;
    }

    /**
     * Obtenir le nom lisible d'une tâche
     */
    public static function getTaskName($task_code, $pdo)
    {
        $names = [
            'daily_login' => 'Connexion quotidienne',
            'leave_review' => 'Laisser un avis',
            'like_review' => 'Aimer un avis',
            'recommend_product' => 'Recommander un produit',
            'deposit_5000' => 'Effectuer un dépôt (min. 1 000 FCFA)',
            'invite_user' => 'Inviter un utilisateur'
        ];
        return $names[$task_code] ?? $task_code;
    }

    /**
     * Compléter une tâche avec crédit du solde, transaction et notification
     */
    public static function completeTask($user_id, $task_code, $pdo)
    {
        // Vérification backend obligatoire anti-contournement
        $check = self::canExecuteTask($user_id, $task_code, $pdo);
        if (!$check['can_execute']) {
            return ['success' => false, 'message' => $check['message']];
        }

        $stmt = $pdo->prepare('SELECT id, reward_amount, task_name FROM tasks_definitions WHERE task_code = ?');
        $stmt->execute([$task_code]);
        $task = $stmt->fetch();
        if (!$task)
            return ['success' => false, 'message' => 'Tâche non trouvée.'];

        try {
            $pdo->beginTransaction();

            $pdo->prepare('INSERT INTO user_tasks (user_id, task_id, completed_at, reward_earned) VALUES (?, ?, NOW(), ?)')
                ->execute([$user_id, $task['id'], $task['reward_amount']]);

            if ($task['reward_amount'] > 0) {
                $pdo->prepare('UPDATE users SET balance = balance + ? WHERE id = ?')
                    ->execute([$task['reward_amount'], $user_id]);

                $pdo->prepare("INSERT INTO transactions (user_id, type, amount, description, reference_type, created_at) VALUES (?, 'reward', ?, ?, 'task', NOW())")
                    ->execute([$user_id, $task['reward_amount'], 'Tâche: ' . $task['task_name']]);

                $pdo->prepare("INSERT INTO notifications (user_id, type, title, message, created_at) VALUES (?, 'reward', 'Tâche complétée', ?, NOW())")
                    ->execute([$user_id, sprintf('Vous avez gagné %s pour "%s"', formatFCFA($task['reward_amount']), $task['task_name'])]);
            }

            $pdo->commit();
            return ['success' => true, 'reward' => $task['reward_amount'], 'message' => sprintf('Tâche "%s" complétée !', $task['task_name'])];
        } catch (Exception $e) {
            $pdo->rollBack();
            return ['success' => false, 'message' => 'Erreur lors de la complétion.'];
        }
    }

    /**
     * Obtenir le statut complet des tâches du jour (optimisé: 2 requêtes au lieu de N)
     */
    public static function getDailyTasksStatus($user_id, $pdo)
    {
        $tasks = [];

        // 1 requête: toutes les définitions
        $definitions = $pdo->query('SELECT * FROM tasks_definitions WHERE is_active = TRUE ORDER BY id')->fetchAll();

        // 1 requête: toutes les complétions d'aujourd'hui
        $completedStmt = $pdo->prepare("
            SELECT td.task_code FROM user_tasks ut
            JOIN tasks_definitions td ON td.id = ut.task_id
            WHERE ut.user_id = ? AND DATE(ut.completed_at) = CURRENT_DATE
        ");
        $completedStmt->execute([$user_id]);
        $completedCodes = $completedStmt->fetchAll(PDO::FETCH_COLUMN);

        // 1 requête: éligibilité dépôt
        $depositEligibility = self::checkDepositEligibility($user_id, $pdo);

        foreach ($definitions as $def) {
            $taskCode = $def['task_code'];
            $isCompleted = in_array($taskCode, $completedCodes);
            $order = self::$taskOrder[$taskCode] ?? 999;

            $canExecute = true;
            $blockedBy = null;
            $specialMessage = null;

            if ($isCompleted) {
                $canExecute = false;
            } else {
                // Prérequis séquentiels
                foreach (self::$taskOrder as $code => $prevOrder) {
                    if ($prevOrder >= $order)
                        break;
                    if ($code === 'deposit_5000')
                        continue;
                    if (!in_array($code, $completedCodes)) {
                        $canExecute = false;
                        $blockedBy = self::getTaskName($code, $pdo);
                        break;
                    }
                }

                // Vérification spéciale pour le dépôt
                if ($taskCode === 'deposit_5000' && $canExecute) {
                    if (!$depositEligibility['eligible']) {
                        $canExecute = false;
                        $specialMessage = sprintf(
                            'Gains cumulés: %s / %s requis',
                            formatFCFA($depositEligibility['total_earnings']),
                            formatFCFA($depositEligibility['threshold'])
                        );
                    }
                }
            }

            $tasks[] = [
                'id' => $def['id'],
                'task_code' => $taskCode,
                'task_name' => $def['task_name'],
                'description' => $def['description'],
                'reward_amount' => $def['reward_amount'],
                'is_daily' => $def['is_daily'],
                'is_completed' => $isCompleted,
                'can_execute' => !$isCompleted && $canExecute,
                'blocked_by' => $blockedBy,
                'special_message' => $specialMessage,
                'order' => $order,
                'deposit_eligibility' => $taskCode === 'deposit_5000' ? $depositEligibility : null
            ];
        }

        usort($tasks, fn($a, $b) => $a['order'] <=> $b['order']);
        return $tasks;
    }

    /**
     * Vérifier si les tâches quotidiennes obligatoires sont complétées (hors dépôt)
     */
    public static function canReceiveReward($user_id, $pdo)
    {
        foreach (['daily_login', 'leave_review', 'like_review', 'recommend_product'] as $code) {
            if (!self::isTaskCompletedToday($user_id, $code, $pdo))
                return false;
        }
        return true;
    }

    public static function areAllMandatoryTasksComplete($user_id, $pdo)
    {
        return self::canReceiveReward($user_id, $pdo);
    }
}
