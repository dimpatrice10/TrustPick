<?php
/**
 * TrustPick - Système d'Authentification CAU
 * Code d'Accès Utilisateur (CAU)
 */

class AuthCAU
{
    private $db;

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    /**
     * Génère un CAU unique
     * Format: PREFIX + 6 chiffres aléatoires
     * Exemple: USER001234, TECH005678, ADMIN000001
     */
    public function generateCAU($role = 'user')
    {
        $prefix = match ($role) {
            'super_admin' => 'ADMIN',
            'admin_entreprise' => 'TECH',
            'user' => 'USER',
            default => 'USER'
        };

        $maxAttempts = 10;
        $attempt = 0;

        do {
            $randomNumber = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $cau = $prefix . $randomNumber;

            // Vérifier si le CAU existe déjà
            $stmt = $this->db->prepare("SELECT id FROM users WHERE cau = ?");
            $stmt->execute([$cau]);
            $exists = $stmt->fetch();

            $attempt++;

            if (!$exists) {
                return $cau;
            }
        } while ($attempt < $maxAttempts);

        // Si échec après 10 tentatives, utiliser timestamp
        return $prefix . substr(time(), -6);
    }

    /**
     * Génère un code de parrainage unique
     * Format: 10 caractères alphanumériques
     */
    public function generateReferralCode()
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // Sans O, 0, I, 1 pour éviter confusion
        $maxAttempts = 10;
        $attempt = 0;

        do {
            $code = '';
            $length = 10;
            for ($i = 0; $i < $length; $i++) {
                $code .= $characters[random_int(0, strlen($characters) - 1)];
            }

            // Vérifier si le code existe déjà
            $stmt = $this->db->prepare("SELECT id FROM users WHERE referral_code = ?");
            $stmt->execute([$code]);
            $exists = $stmt->fetch();

            $attempt++;

            if (!$exists) {
                return $code;
            }
        } while ($attempt < $maxAttempts);

        // Fallback avec timestamp
        return 'REF' . strtoupper(bin2hex(random_bytes(4)));
    }

    /**
     * Connexion avec CAU uniquement
     */
    public function loginWithCAU($cau)
    {
        try {
            // Chercher l'utilisateur
            $stmt = $this->db->prepare("
                SELECT 
                    u.*,
                    c.name as company_name,
                    c.slug as company_slug
                FROM users u
                LEFT JOIN companies c ON u.company_id = c.id
                WHERE u.cau = ? AND u.is_active = TRUE
            ");
            $stmt->execute([$cau]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Code d\'accès invalide ou compte désactivé'
                ];
            }

            // Mettre à jour le last_login
            $updateStmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $updateStmt->execute([$user['id']]);

            // Enregistrer dans l'historique
            $this->logLogin($user['id']);

            // Créer la session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_cau'] = $user['cau'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['company_id'] = $user['company_id'];
            $_SESSION['referral_code'] = $user['referral_code'];

            return [
                'success' => true,
                'message' => 'Connexion réussie',
                'user' => $user
            ];

        } catch (Exception $e) {
            error_log("Erreur login CAU: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur lors de la connexion'
            ];
        }
    }

    /**
     * Créer un nouvel utilisateur (par admin)
     */
    public function createUser($data)
    {
        try {
            // Valider les données
            if (empty($data['name'])) {
                return ['success' => false, 'message' => 'Le nom est requis'];
            }

            $role = $data['role'] ?? 'user';
            $companyId = $data['company_id'] ?? null;
            $createdBy = $_SESSION['user_id'] ?? null;
            $referredBy = $data['referred_by'] ?? null;

            // Générer CAU et code de parrainage
            $cau = $this->generateCAU($role);
            $referralCode = $this->generateReferralCode();

            // Insérer l'utilisateur
            $stmt = $this->db->prepare("
                INSERT INTO users 
                (cau, name, phone, role, company_id, referral_code, referred_by, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $cau,
                $data['name'],
                $data['phone'] ?? null,
                $role,
                $companyId,
                $referralCode,
                $referredBy,
                $createdBy
            ]);

            $userId = $this->db->lastInsertId('users_id_seq');

            // Si référencé par quelqu'un, créer le lien de parrainage
            if ($referredBy) {
                $this->createReferral($referredBy, $userId);
            }

            // Logger l'action
            $this->logActivity($createdBy, 'create_user', 'user', $userId, "Création utilisateur: $cau");

            return [
                'success' => true,
                'message' => 'Utilisateur créé avec succès',
                'user_id' => $userId,
                'cau' => $cau,
                'referral_code' => $referralCode
            ];

        } catch (Exception $e) {
            error_log("Erreur création utilisateur: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur lors de la création: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Créer un lien de parrainage
     */
    private function createReferral($referrerId, $referredId)
    {
        try {
            // Récupérer la récompense depuis les paramètres
            $stmt = $this->db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = 'referral_reward'");
            $stmt->execute();
            $rewardAmount = $stmt->fetchColumn() ?: 5000;

            // Créer l'entrée de parrainage
            $stmt = $this->db->prepare("
                INSERT INTO referrals (referrer_id, referred_id, reward_amount)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$referrerId, $referredId, $rewardAmount]);

            // Mettre à jour le solde du parrain
            $this->db->prepare("UPDATE users SET balance = balance + ? WHERE id = ?")->execute([$rewardAmount, $referrerId]);

            // Créer la transaction
            $this->createTransaction($referrerId, 'referral', $rewardAmount, "Parrainage utilisateur #$referredId");

            // Marquer comme récompensé
            $this->db->prepare("
                UPDATE referrals 
                SET is_rewarded = TRUE, rewarded_at = NOW() 
                WHERE referrer_id = ? AND referred_id = ?
            ")->execute([$referrerId, $referredId]);

            // Créer une notification
            $this->createNotification(
                $referrerId,
                'referral',
                'Nouveau parrainage !',
                "Vous avez gagné $rewardAmount FCFA pour votre parrainage !"
            );

            return true;
        } catch (Exception $e) {
            error_log("Erreur création parrainage: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vérifier les permissions
     */
    public function checkPermission($requiredRole)
    {
        if (!isset($_SESSION['user_role'])) {
            return false;
        }

        $roleHierarchy = [
            'super_admin' => 3,
            'admin_entreprise' => 2,
            'user' => 1
        ];

        $userLevel = $roleHierarchy[$_SESSION['user_role']] ?? 0;
        $requiredLevel = $roleHierarchy[$requiredRole] ?? 0;

        return $userLevel >= $requiredLevel;
    }

    /**
     * Logger une connexion
     */
    private function logLogin($userId)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO login_history (user_id, ip_address, user_agent)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([
                $userId,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
        } catch (Exception $e) {
            error_log("Erreur log login: " . $e->getMessage());
        }
    }

    /**
     * Logger une activité
     */
    private function logActivity($userId, $action, $entityType = null, $entityId = null, $details = null)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO activity_logs (user_id, action, entity_type, entity_id, details, ip_address)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $userId,
                $action,
                $entityType,
                $entityId,
                $details,
                $_SERVER['REMOTE_ADDR'] ?? null
            ]);
        } catch (Exception $e) {
            error_log("Erreur log activity: " . $e->getMessage());
        }
    }

    /**
     * Créer une transaction
     */
    private function createTransaction($userId, $type, $amount, $description, $referenceType = null, $referenceId = null)
    {
        try {
            // Récupérer le solde actuel
            $stmt = $this->db->prepare("SELECT balance FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $balance = $stmt->fetchColumn();

            $stmt = $this->db->prepare("
                INSERT INTO transactions 
                (user_id, type, amount, description, reference_type, reference_id, balance_after)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $userId,
                $type,
                $amount,
                $description,
                $referenceType,
                $referenceId,
                $balance
            ]);
        } catch (Exception $e) {
            error_log("Erreur création transaction: " . $e->getMessage());
        }
    }

    /**
     * Créer une notification
     */
    private function createNotification($userId, $type, $title, $message, $link = null)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO notifications (user_id, type, title, message, link)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $type, $title, $message, $link]);
        } catch (Exception $e) {
            error_log("Erreur création notification: " . $e->getMessage());
        }
    }

    /**
     * Déconnexion
     */
    public function logout()
    {
        session_destroy();
        session_start();
        return ['success' => true, 'message' => 'Déconnexion réussie'];
    }

    /**
     * Obtenir l'utilisateur par son code de parrainage
     */
    public function getUserByReferralCode($referralCode)
    {
        try {
            $stmt = $this->db->prepare("SELECT id, name, referral_code FROM users WHERE referral_code = ? AND is_active = TRUE");
            $stmt->execute([$referralCode]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erreur getUserByReferralCode: " . $e->getMessage());
            return null;
        }
    }
}
