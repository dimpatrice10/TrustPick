<?php
/**
 * TrustPick - Système d'Invitation et Parrainage
 */

class ReferralSystem
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    /**
     * Obtenir le lien d'invitation unique d'un utilisateur
     */
    public function getReferralLink($userId)
    {
        try {
            $stmt = $this->db->prepare("SELECT referral_code FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $referralCode = $stmt->fetchColumn();

            if (!$referralCode) {
                return ['success' => false, 'message' => 'Utilisateur introuvable'];
            }

            // Générer l'URL complète
            $baseUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
            $referralLink = $baseUrl . '/register?ref=' . $referralCode;

            return [
                'success' => true,
                'referral_code' => $referralCode,
                'referral_link' => $referralLink,
                'short_code' => $referralCode
            ];

        } catch (Exception $e) {
            error_log("Erreur getReferralLink: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Obtenir les statistiques de parrainage d'un utilisateur
     */
    public function getReferralStats($userId)
    {
        try {
            // Nombre total de filleuls
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as total_referrals
                FROM users
                WHERE referred_by = ?
            ");
            $stmt->execute([$userId]);
            $totalReferrals = $stmt->fetchColumn();

            // Total des récompenses gagnées
            $stmt = $this->db->prepare("
                SELECT SUM(reward_amount) as total_rewards
                FROM referrals
                WHERE referrer_id = ? AND is_rewarded = TRUE
            ");
            $stmt->execute([$userId]);
            $totalRewards = $stmt->fetchColumn() ?? 0;

            // Filleuls actifs (connectés dans les 7 derniers jours)
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as active_referrals
                FROM users
                WHERE referred_by = ?
                AND last_login >= NOW() - INTERVAL 7 DAY
            ");
            $stmt->execute([$userId]);
            $activeReferrals = $stmt->fetchColumn();

            // Liste des filleuls récents
            $stmt = $this->db->prepare("
                SELECT 
                    u.id,
                    u.name,
                    u.created_at,
                    r.reward_amount,
                    r.is_rewarded,
                    r.rewarded_at,
                    DATE_FORMAT(u.created_at, '%d/%m/%Y') as join_date
                FROM users u
                LEFT JOIN referrals r ON r.referred_id = u.id
                WHERE u.referred_by = ?
                ORDER BY u.created_at DESC
                LIMIT 10
            ");
            $stmt->execute([$userId]);
            $recentReferrals = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'stats' => [
                    'total_referrals' => $totalReferrals,
                    'active_referrals' => $activeReferrals,
                    'total_rewards' => $totalRewards,
                    'recent_referrals' => $recentReferrals
                ]
            ];

        } catch (Exception $e) {
            error_log("Erreur getReferralStats: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Valider un code de parrainage
     */
    public function validateReferralCode($referralCode)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT id, name, referral_code 
                FROM users 
                WHERE referral_code = ? AND is_active = TRUE
            ");
            $stmt->execute([$referralCode]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return [
                    'success' => false,
                    'valid' => false,
                    'message' => 'Code de parrainage invalide'
                ];
            }

            return [
                'success' => true,
                'valid' => true,
                'referrer' => [
                    'id' => $user['id'],
                    'name' => $user['name']
                ]
            ];

        } catch (Exception $e) {
            error_log("Erreur validateReferralCode: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Créer un lien de parrainage et récompenser
     */
    public function createReferral($referrerId, $referredId)
    {
        try {
            $this->db->beginTransaction();

            // Vérifier que le filleul n'a pas déjà un parrain
            $stmt = $this->db->prepare("SELECT referred_by FROM users WHERE id = ?");
            $stmt->execute([$referredId]);
            $existingReferrer = $stmt->fetchColumn();

            if ($existingReferrer) {
                $this->db->rollBack();
                return [
                    'success' => false,
                    'message' => 'Cet utilisateur a déjà un parrain'
                ];
            }

            // Récupérer le montant de la récompense
            $stmt = $this->db->prepare("
                SELECT setting_value 
                FROM system_settings 
                WHERE setting_key = 'referral_reward'
            ");
            $stmt->execute();
            $rewardAmount = $stmt->fetchColumn() ?: 5000;

            // Créer l'entrée de parrainage
            $stmt = $this->db->prepare("
                INSERT INTO referrals (referrer_id, referred_id, reward_amount)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$referrerId, $referredId, $rewardAmount]);

            // Mettre à jour le champ referred_by dans users
            $stmt = $this->db->prepare("UPDATE users SET referred_by = ? WHERE id = ?");
            $stmt->execute([$referrerId, $referredId]);

            // Ajouter la récompense au parrain
            $stmt = $this->db->prepare("
                UPDATE users 
                SET balance = balance + ? 
                WHERE id = ?
            ");
            $stmt->execute([$rewardAmount, $referrerId]);

            // Récupérer le nouveau solde
            $stmt = $this->db->prepare("SELECT balance FROM users WHERE id = ?");
            $stmt->execute([$referrerId]);
            $newBalance = $stmt->fetchColumn();

            // Créer la transaction
            $stmt = $this->db->prepare("
                INSERT INTO transactions 
                (user_id, type, amount, description, reference_type, reference_id, balance_after)
                VALUES (?, 'referral', ?, ?, 'referral', ?, ?)
            ");
            $stmt->execute([
                $referrerId,
                $rewardAmount,
                "Parrainage réussi - Nouvel utilisateur #{$referredId}",
                $referredId,
                $newBalance
            ]);

            // Marquer le parrainage comme récompensé
            $stmt = $this->db->prepare("
                UPDATE referrals 
                SET is_rewarded = TRUE, rewarded_at = NOW() 
                WHERE referrer_id = ? AND referred_id = ?
            ");
            $stmt->execute([$referrerId, $referredId]);

            // Créer une notification pour le parrain
            $stmt = $this->db->prepare("
                INSERT INTO notifications 
                (user_id, type, title, message, link)
                VALUES (?, 'referral', ?, ?, ?)
            ");
            $stmt->execute([
                $referrerId,
                '🎉 Nouveau filleul !',
                "Félicitations ! Vous avez gagné {$rewardAmount} FCFA grâce à votre parrainage.",
                'index.php?page=wallet'
            ]);

            // Créer une notification pour le filleul
            $stmt = $this->db->prepare("
                INSERT INTO notifications 
                (user_id, type, title, message, link)
                VALUES (?, 'system', ?, ?, ?)
            ");
            $stmt->execute([
                $referredId,
                '👋 Bienvenue sur TrustPick !',
                "Vous avez été parrainé avec succès. Explorez la plateforme et gagnez des récompenses !",
                'index.php?page=user_dashboard'
            ]);

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Parrainage créé avec succès',
                'reward_amount' => $rewardAmount,
                'new_balance' => $newBalance
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erreur createReferral: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Obtenir le classement des parrains
     */
    public function getTopReferrers($limit = 10)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    u.id,
                    u.name,
                    COUNT(r.id) as total_referrals,
                    SUM(r.reward_amount) as total_earned
                FROM users u
                JOIN referrals r ON r.referrer_id = u.id
                WHERE r.is_rewarded = TRUE
                GROUP BY u.id
                ORDER BY total_referrals DESC, total_earned DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            $topReferrers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return [
                'success' => true,
                'top_referrers' => $topReferrers
            ];

        } catch (Exception $e) {
            error_log("Erreur getTopReferrers: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Partager le lien de parrainage (génère des liens pour réseaux sociaux)
     */
    public function getSocialShareLinks($userId)
    {
        try {
            $linkData = $this->getReferralLink($userId);

            if (!$linkData['success']) {
                return $linkData;
            }

            $referralLink = $linkData['referral_link'];
            $message = urlencode("Rejoignez TrustPick et gagnez de l'argent en donnant votre avis ! Utilisez mon lien: ");

            return [
                'success' => true,
                'share_links' => [
                    'whatsapp' => "https://wa.me/?text={$message}{$referralLink}",
                    'facebook' => "https://www.facebook.com/sharer/sharer.php?u={$referralLink}",
                    'twitter' => "https://twitter.com/intent/tweet?url={$referralLink}&text={$message}",
                    'telegram' => "https://t.me/share/url?url={$referralLink}&text={$message}",
                    'email' => "mailto:?subject=Rejoignez TrustPick&body={$message}{$referralLink}"
                ],
                'referral_link' => $referralLink,
                'referral_code' => $linkData['referral_code']
            ];

        } catch (Exception $e) {
            error_log("Erreur getSocialShareLinks: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}