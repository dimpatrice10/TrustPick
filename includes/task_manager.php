<?php
/**
 * TrustPick V2 - Gestionnaire de Tâches
 * Gère l'ordre des tâches obligatoires et les récompenses
 */

class TaskManager
{
    // Ordre des tâches obligatoires (task_code => ordre)
    private static $taskOrder = [
        'deposit_5000' => 1,
        'leave_review' => 2,
        'like_review' => 3,
        'recommend_product' => 4
    ];

    /**
     * Vérifier si l'utilisateur peut exécuter une tâche
     * @param int $user_id
     * @param string $task_code
     * @param PDO $pdo
     * @return array ['can_execute' => bool, 'message' => string]
     */
    public static function canExecuteTask($user_id, $task_code, $pdo)
    {
        // Récupérer l'ordre de la tâche demandée
        $taskPosition = self::$taskOrder[$task_code] ?? 999;

        // Vérifier toutes les tâches précédentes
        foreach (self::$taskOrder as $code => $order) {
            if ($order >= $taskPosition) {
                break; // On a atteint la tâche actuelle
            }

            // Vérifier si cette tâche précédente est complétée aujourd'hui
            if (!self::isTaskCompletedToday($user_id, $code, $pdo)) {
                return [
                    'can_execute' => false,
                    'message' => 'Vous devez d\'abord compléter la tâche obligatoire précédente: ' . self::getTaskName($code, $pdo)
                ];
            }
        }

        // Vérifier si la tâche n'a pas déjà été faite aujourd'hui
        if (self::isTaskCompletedToday($user_id, $task_code, $pdo)) {
            return [
                'can_execute' => false,
                'message' => 'Vous avez déjà complété cette tâche aujourd\'hui.'
            ];
        }

        return ['can_execute' => true, 'message' => ''];
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
     * Obtenir le nom d'une tâche
     */
    public static function getTaskName($task_code, $pdo)
    {
        $names = [
            'leave_review' => 'Laisser un avis',
            'like_review' => 'Aimer un avis',
            'recommend_product' => 'Recommander un produit',
            'deposit_5000' => 'Effectuer un dépôt minimum de 5000 FCFA',
            'daily_login' => 'Connexion quotidienne',
            'invite_user' => 'Inviter un utilisateur'
        ];
        return $names[$task_code] ?? $task_code;
    }

    /**
     * Compléter une tâche
     */
    public static function completeTask($user_id, $task_code, $pdo)
    {
        // Récupérer la définition de la tâche
        $stmt = $pdo->prepare('SELECT id, reward_amount FROM tasks_definitions WHERE task_code = ?');
        $stmt->execute([$task_code]);
        $task = $stmt->fetch();

        if (!$task) {
            return false;
        }

        // Enregistrer la complétion
        $stmt = $pdo->prepare('
            INSERT INTO user_tasks (user_id, task_id, completed_at, reward_earned)
            VALUES (?, ?, NOW(), ?)
        ');
        $stmt->execute([$user_id, $task['id'], $task['reward_amount']]);

        return true;
    }

    /**
     * Vérifier si l'utilisateur peut recevoir des récompenses
     * (toutes les tâches obligatoires du jour doivent être faites)
     */
    public static function canReceiveReward($user_id, $pdo)
    {
        // Vérifier que les 4 tâches obligatoires sont complétées aujourd'hui
        foreach (self::$taskOrder as $task_code => $order) {
            if (!self::isTaskCompletedToday($user_id, $task_code, $pdo)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Obtenir le statut des tâches du jour pour un utilisateur
     */
    public static function getDailyTasksStatus($user_id, $pdo)
    {
        $tasks = [];

        // Récupérer toutes les définitions de tâches actives
        $stmt = $pdo->query('SELECT * FROM tasks_definitions WHERE is_active = TRUE ORDER BY id');
        $definitions = $stmt->fetchAll();

        foreach ($definitions as $def) {
            $isCompleted = self::isTaskCompletedToday($user_id, $def['task_code'], $pdo);
            $order = self::$taskOrder[$def['task_code']] ?? 999;

            // Déterminer si la tâche est disponible
            $canExecute = true;
            $blockedBy = null;

            foreach (self::$taskOrder as $code => $prevOrder) {
                if ($prevOrder >= $order)
                    break;
                if (!self::isTaskCompletedToday($user_id, $code, $pdo)) {
                    $canExecute = false;
                    $blockedBy = self::getTaskName($code, $pdo);
                    break;
                }
            }

            $tasks[] = [
                'id' => $def['id'],
                'task_code' => $def['task_code'],
                'task_name' => $def['task_name'],
                'description' => $def['description'],
                'reward_amount' => $def['reward_amount'],
                'is_daily' => $def['is_daily'],
                'is_completed' => $isCompleted,
                'can_execute' => !$isCompleted && $canExecute,
                'blocked_by' => $blockedBy,
                'order' => $order
            ];
        }

        // Trier par ordre
        usort($tasks, fn($a, $b) => $a['order'] <=> $b['order']);

        return $tasks;
    }

    /**
     * Vérifier si toutes les tâches obligatoires sont complétées
     */
    public static function areAllMandatoryTasksComplete($user_id, $pdo)
    {
        return self::canReceiveReward($user_id, $pdo);
    }
}
