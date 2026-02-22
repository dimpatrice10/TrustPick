<?php
/**
 * TrustPick - Système de Notifications
 * Minimum 2 notifications par jour par utilisateur
 */

class NotificationSystem
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    /**
     * Créer une notification
     */
    public function create($userId, $type, $title, $message, $link = null)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO notifications (user_id, type, title, message, link)
                VALUES (?, ?, ?, ?, ?)
            ");

            $stmt->execute([$userId, $type, $title, $message, $link]);

            return [
                'success' => true,
                'notification_id' => $this->db->lastInsertId()
            ];

        } catch (Exception $e) {
            error_log("Erreur create notification: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Obtenir les notifications d'un utilisateur
     */
    public function getNotifications($userId, $limit = 20, $offset = 0, $unreadOnly = false)
    {
        try {
            $sql = "
                SELECT 
                    id,
                    type,
                    title,
                    message,
                    link,
                    is_read,
                    created_at,
                    DATE_FORMAT(created_at, '%d/%m/%Y %H:%i') as formatted_date,
                    CASE 
                        WHEN created_at >= NOW() - INTERVAL 1 HOUR THEN 'Il y a quelques minutes'
                        WHEN created_at >= NOW() - INTERVAL 24 HOUR THEN CONCAT('Il y a ', FLOOR(TIMESTAMPDIFF(SECOND, created_at, NOW()) / 3600), 'h')
                        WHEN created_at >= NOW() - INTERVAL 7 DAY THEN CONCAT('Il y a ', FLOOR(TIMESTAMPDIFF(SECOND, created_at, NOW()) / 86400), 'j')
                        ELSE DATE_FORMAT(created_at, '%d/%m/%Y %H:%i')
                    END as relative_time
                FROM notifications
                WHERE user_id = ?
            ";

            if ($unreadOnly) {
                $sql .= " AND is_read = FALSE";
            }

            $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId, $limit, $offset]);
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Compter les notifications non lues
            $stmt = $this->db->prepare("
                SELECT COUNT(*) 
                FROM notifications 
                WHERE user_id = ? AND is_read = FALSE
            ");
            $stmt->execute([$userId]);
            $unreadCount = $stmt->fetchColumn();

            return [
                'success' => true,
                'notifications' => $notifications,
                'unread_count' => $unreadCount,
                'total' => count($notifications)
            ];

        } catch (Exception $e) {
            error_log("Erreur getNotifications: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead($notificationId, $userId = null)
    {
        try {
            $sql = "UPDATE notifications SET is_read = TRUE WHERE id = ?";
            $params = [$notificationId];

            if ($userId) {
                $sql .= " AND user_id = ?";
                $params[] = $userId;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return [
                'success' => true,
                'message' => 'Notification marquée comme lue'
            ];

        } catch (Exception $e) {
            error_log("Erreur markAsRead: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead($userId)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE notifications 
                SET is_read = TRUE 
                WHERE user_id = ? AND is_read = FALSE
            ");
            $stmt->execute([$userId]);

            return [
                'success' => true,
                'message' => 'Toutes les notifications ont été marquées comme lues'
            ];

        } catch (Exception $e) {
            error_log("Erreur markAllAsRead: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Supprimer une notification
     */
    public function delete($notificationId, $userId = null)
    {
        try {
            $sql = "DELETE FROM notifications WHERE id = ?";
            $params = [$notificationId];

            if ($userId) {
                $sql .= " AND user_id = ?";
                $params[] = $userId;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return [
                'success' => true,
                'message' => 'Notification supprimée'
            ];

        } catch (Exception $e) {
            error_log("Erreur delete notification: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Générer des notifications automatiques quotidiennes
     */
    public function generateDailyNotifications()
    {
        try {
            // Récupérer tous les utilisateurs actifs
            $stmt = $this->db->query("
                SELECT id, name 
                FROM users 
                WHERE is_active = TRUE AND role = 'user'
            ");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $notificationTypes = [
                [
                    'type' => 'task_reminder',
                    'title' => '📋 N\'oubliez pas vos tâches !',
                    'message' => 'Vous avez des tâches quotidiennes à compléter pour gagner des FCFA.',
                    'link' => '/tasks'
                ],
                [
                    'type' => 'new_product',
                    'title' => '🆕 Nouveaux produits disponibles !',
                    'message' => 'Découvrez les nouveaux produits ajoutés aujourd\'hui et partagez votre avis.',
                    'link' => '/catalog'
                ],
                [
                    'type' => 'reward',
                    'title' => '💰 Gagnez plus de récompenses',
                    'message' => 'Invitez vos amis et gagnez des bonus de parrainage !',
                    'link' => '/referrals'
                ]
            ];

            $generatedCount = 0;

            foreach ($users as $user) {
                // Vérifier combien de notifications l'utilisateur a reçues aujourd'hui
                $stmt = $this->db->prepare("
                    SELECT COUNT(*) 
                    FROM notifications 
                    WHERE user_id = ? AND DATE(created_at) = CURRENT_DATE
                ");
                $stmt->execute([$user['id']]);
                $todayCount = $stmt->fetchColumn();

                // Récupérer le minimum requis
                $stmt = $this->db->prepare("
                    SELECT setting_value 
                    FROM system_settings 
                    WHERE setting_key = 'daily_notifications_count'
                ");
                $stmt->execute();
                $minNotifications = $stmt->fetchColumn() ?: 2;

                // Si moins que le minimum, envoyer des notifications
                $needed = $minNotifications - $todayCount;

                if ($needed > 0) {
                    // Sélectionner aléatoirement les notifications à envoyer
                    shuffle($notificationTypes);
                    $selected = array_slice($notificationTypes, 0, $needed);

                    foreach ($selected as $notif) {
                        $this->create(
                            $user['id'],
                            $notif['type'],
                            $notif['title'],
                            $notif['message'],
                            $notif['link']
                        );
                        $generatedCount++;
                    }
                }
            }

            return [
                'success' => true,
                'message' => "Génération quotidienne terminée",
                'notifications_generated' => $generatedCount
            ];

        } catch (Exception $e) {
            error_log("Erreur generateDailyNotifications: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Envoyer une notification de rappel de tâches
     */
    public function sendTaskReminder($userId)
    {
        try {
            // Récupérer les tâches non complétées
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as incomplete_tasks
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
            ");
            $stmt->execute([$userId]);
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                $this->create(
                    $userId,
                    'task_reminder',
                    '⏰ Rappel de tâches',
                    "Vous avez {$count} tâche(s) à compléter aujourd'hui. Ne manquez pas vos récompenses !",
                    '/tasks'
                );
            }

            return ['success' => true, 'tasks_pending' => $count];

        } catch (Exception $e) {
            error_log("Erreur sendTaskReminder: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Notification pour nouveau produit
     */
    public function notifyNewProduct($productId)
    {
        try {
            // Récupérer les infos du produit
            $stmt = $this->db->prepare("
                SELECT p.title, c.name as company_name
                FROM products p
                JOIN companies c ON p.company_id = c.id
                WHERE p.id = ?
            ");
            $stmt->execute([$productId]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                return ['success' => false, 'message' => 'Produit introuvable'];
            }

            // Notifier tous les utilisateurs actifs (ou un échantillon)
            $stmt = $this->db->query("
                SELECT id 
                FROM users 
                WHERE is_active = TRUE AND role = 'user'
                ORDER BY RAND()
                LIMIT 100
            ");
            $users = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $notifiedCount = 0;
            foreach ($users as $userId) {
                $this->create(
                    $userId,
                    'new_product',
                    '🆕 Nouveau produit: ' . $product['title'],
                    "Découvrez {$product['title']} de {$product['company_name']} et soyez parmi les premiers à donner votre avis !",
                    "/product/{$productId}"
                );
                $notifiedCount++;
            }

            return [
                'success' => true,
                'notified_users' => $notifiedCount
            ];

        } catch (Exception $e) {
            error_log("Erreur notifyNewProduct: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Notification pour nouvel avis sur un produit d'une entreprise
     */
    public function notifyNewReview($reviewId, $companyId)
    {
        try {
            // Récupérer les admins de l'entreprise
            $stmt = $this->db->prepare("
                SELECT id 
                FROM users 
                WHERE company_id = ? AND role = 'admin_entreprise' AND is_active = TRUE
            ");
            $stmt->execute([$companyId]);
            $admins = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Récupérer les infos de l'avis
            $stmt = $this->db->prepare("
                SELECT r.rating, p.title, u.name
                FROM reviews r
                JOIN products p ON r.product_id = p.id
                JOIN users u ON r.user_id = u.id
                WHERE r.id = ?
            ");
            $stmt->execute([$reviewId]);
            $review = $stmt->fetch(PDO::FETCH_ASSOC);

            foreach ($admins as $adminId) {
                $stars = str_repeat('⭐', $review['rating']);
                $this->create(
                    $adminId,
                    'new_review',
                    "Nouvel avis {$stars}",
                    "{$review['name']} a laissé un avis sur {$review['title']}",
                    "/review/{$reviewId}"
                );
            }

            return ['success' => true, 'admins_notified' => count($admins)];

        } catch (Exception $e) {
            error_log("Erreur notifyNewReview: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Obtenir le nombre de notifications non lues
     */
    public function getUnreadCount($userId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) 
                FROM notifications 
                WHERE user_id = ? AND is_read = FALSE
            ");
            $stmt->execute([$userId]);
            $count = $stmt->fetchColumn();

            return [
                'success' => true,
                'unread_count' => $count
            ];

        } catch (Exception $e) {
            error_log("Erreur getUnreadCount: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
