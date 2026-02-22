<?php
/**
 * TrustPick - Système de Tâches Quotidiennes
 * Règle stricte: Un utilisateur ne peut PAS faire plusieurs fois la même tâche
 */

class TaskSystem
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    /**
     * Obtenir les tâches disponibles pour un utilisateur
     */
    public function getAvailableTasks($userId)
    {
        try {
            // Récupérer toutes les tâches actives
            $stmt = $this->db->prepare("
                SELECT 
                    td.*,
                    CASE 
                        WHEN td.is_daily = TRUE THEN
                            (SELECT COUNT(*) 
                             FROM user_tasks ut 
                             WHERE ut.user_id = ? 
                             AND ut.task_id = td.id 
                             AND DATE(ut.completed_at) = CURRENT_DATE) > 0
                        ELSE
                            (SELECT COUNT(*) 
                             FROM user_tasks ut 
                             WHERE ut.user_id = ? 
                             AND ut.task_id = td.id) > 0
                    END as is_completed
                FROM tasks_definitions td
                WHERE td.is_active = TRUE
                ORDER BY td.reward_amount DESC
            ");

            $stmt->execute([$userId, $userId]);
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Formater les tâches
            foreach ($tasks as &$task) {
                $task['is_completed'] = (bool) $task['is_completed'];
                $task['can_complete'] = !$task['is_completed'];
                $task['reward_amount'] = number_format($task['reward_amount'], 0, ',', ' ') . ' FCFA';
            }

            return [
                'success' => true,
                'tasks' => $tasks
            ];

        } catch (Exception $e) {
            error_log("Erreur getAvailableTasks: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Vérifier si un utilisateur peut compléter une tâche
     */
    public function canCompleteTask($userId, $taskCode)
    {
        try {
            // Récupérer la tâche
            $stmt = $this->db->prepare("
                SELECT id, is_daily 
                FROM tasks_definitions 
                WHERE task_code = ? AND is_active = TRUE
            ");
            $stmt->execute([$taskCode]);
            $task = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$task) {
                return false;
            }

            // Vérifier si déjà complétée
            if ($task['is_daily']) {
                // Tâche quotidienne: vérifier aujourd'hui
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) 
                    FROM user_tasks 
                    WHERE user_id = ? 
                    AND task_id = ? 
                    AND DATE(completed_at) = CURRENT_DATE
                ");
            } else {
                // Tâche unique: vérifier toute l'histoire
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) 
                    FROM user_tasks 
                    WHERE user_id = ? 
                    AND task_id = ?
                ");
            }

            $stmt->execute([$userId, $task['id']]);
            $count = $stmt->fetchColumn();

            return $count == 0;

        } catch (Exception $e) {
            error_log("Erreur canCompleteTask: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Compléter une tâche
     */
    public function completeTask($userId, $taskCode, $referenceId = null, $referenceType = null)
    {
        try {
            $this->db->beginTransaction();

            // Vérifier si la tâche peut être complétée
            if (!$this->canCompleteTask($userId, $taskCode)) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => 'Cette tâche a déjà été complétée'
                ];
            }

            // Récupérer la tâche
            $stmt = $this->db->prepare("
                SELECT id, task_name, reward_amount 
                FROM tasks_definitions 
                WHERE task_code = ? AND is_active = TRUE
            ");
            $stmt->execute([$taskCode]);
            $task = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$task) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Tâche introuvable'];
            }

            // Enregistrer la complétion
            $stmt = $this->db->prepare("
                INSERT INTO user_tasks (user_id, task_id, reward_earned)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$userId, $task['id'], $task['reward_amount']]);

            // Mettre à jour le solde de l'utilisateur
            $stmt = $this->db->prepare("
                UPDATE users 
                SET balance = balance + ? 
                WHERE id = ?
            ");
            $stmt->execute([$task['reward_amount'], $userId]);

            // Récupérer le nouveau solde
            $stmt = $this->db->prepare("SELECT balance FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $newBalance = $stmt->fetchColumn();

            // Créer la transaction
            $stmt = $this->db->prepare("
                INSERT INTO transactions 
                (user_id, type, amount, description, reference_type, reference_id, balance_after)
                VALUES (?, 'reward', ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $userId,
                $task['reward_amount'],
                "Tâche complétée: {$task['task_name']}",
                $referenceType,
                $referenceId,
                $newBalance
            ]);

            // Créer une notification
            $stmt = $this->db->prepare("
                INSERT INTO notifications 
                (user_id, type, title, message)
                VALUES (?, 'reward', ?, ?)
            ");
            $stmt->execute([
                $userId,
                'Tâche complétée !',
                "Vous avez gagné {$task['reward_amount']} FCFA pour: {$task['task_name']}"
            ]);

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Tâche complétée avec succès',
                'reward' => $task['reward_amount'],
                'new_balance' => $newBalance
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erreur completeTask: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Obtenir l'historique des tâches d'un utilisateur
     */
    public function getUserTasksHistory($userId, $limit = 50)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    ut.*,
                    td.task_name,
                    td.task_code,
                    DATE_FORMAT(ut.completed_at, '%d/%m/%Y %H:%i') as completed_date
                FROM user_tasks ut
                JOIN tasks_definitions td ON ut.task_id = td.id
                WHERE ut.user_id = ?
                ORDER BY ut.completed_at DESC
                LIMIT ?
            ");

            $stmt->execute([$userId, $limit]);
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'tasks' => $tasks,
                'total' => count($tasks)
            ];

        } catch (Exception $e) {
            error_log("Erreur getUserTasksHistory: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Obtenir les statistiques des tâches pour un utilisateur
     */
    public function getUserTasksStats($userId)
    {
        try {
            // Total des tâches complétées
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total_tasks, SUM(reward_earned) as total_earned
                FROM user_tasks
                WHERE user_id = ?
            ");
            $stmt->execute([$userId]);
            $overall = $stmt->fetch(PDO::FETCH_ASSOC);

            // Tâches complétées aujourd'hui
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as today_tasks, SUM(reward_earned) as today_earned
                FROM user_tasks
                WHERE user_id = ? AND DATE(completed_at) = CURRENT_DATE
            ");
            $stmt->execute([$userId]);
            $today = $stmt->fetch(PDO::FETCH_ASSOC);

            // Tâches complétées cette semaine
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as week_tasks, SUM(reward_earned) as week_earned
                FROM user_tasks
                WHERE user_id = ? AND EXTRACT(WEEK FROM completed_at) = EXTRACT(WEEK FROM NOW()) AND EXTRACT(YEAR FROM completed_at) = EXTRACT(YEAR FROM NOW())
            ");
            $stmt->execute([$userId]);
            $week = $stmt->fetch(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'stats' => [
                    'total' => [
                        'tasks' => $overall['total_tasks'] ?? 0,
                        'earned' => $overall['total_earned'] ?? 0
                    ],
                    'today' => [
                        'tasks' => $today['today_tasks'] ?? 0,
                        'earned' => $today['today_earned'] ?? 0
                    ],
                    'week' => [
                        'tasks' => $week['week_tasks'] ?? 0,
                        'earned' => $week['week_earned'] ?? 0
                    ]
                ]
            ];

        } catch (Exception $e) {
            error_log("Erreur getUserTasksStats: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Créer une nouvelle définition de tâche (admin)
     */
    public function createTaskDefinition($data)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO tasks_definitions 
                (task_code, task_name, description, reward_amount, is_daily, is_active)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $data['task_code'],
                $data['task_name'],
                $data['description'] ?? null,
                $data['reward_amount'],
                $data['is_daily'] ?? true,
                $data['is_active'] ?? true
            ]);

            return [
                'success' => true,
                'task_id' => $this->db->lastInsertId(),
                'message' => 'Tâche créée avec succès'
            ];

        } catch (Exception $e) {
            error_log("Erreur createTaskDefinition: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Rappel automatique des tâches non complétées (pour système de notifications)
     */
    public function getIncompleteTasks($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    td.*
                FROM tasks_definitions td
                WHERE td.is_active = TRUE
                AND td.is_daily = TRUE
                AND NOT EXISTS (
                    SELECT 1 
                    FROM user_tasks ut 
                    WHERE ut.user_id = ? 
                    AND ut.task_id = td.id 
                    AND DATE(ut.completed_at) = CURRENT_DATE
                )
                ORDER BY td.reward_amount DESC
            ");

            $stmt->execute([$userId]);
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'tasks' => $tasks,
                'count' => count($tasks)
            ];

        } catch (Exception $e) {
            error_log("Erreur getIncompleteTasks: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
