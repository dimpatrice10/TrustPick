<?php
/**
 * TrustPick - Gestionnaire de Permissions et Rôles
 */

class PermissionManager
{
    private $db;

    // Définition des permissions par rôle
    private const PERMISSIONS = [
        'super_admin' => [
            // Entreprises
            'create_company',
            'edit_company',
            'delete_company',
            'view_all_companies',

            // Utilisateurs
            'create_admin',
            'create_user',
            'edit_any_user',
            'delete_user',
            'view_all_users',
            'reset_cau',

            // Produits
            'view_all_products',
            'edit_any_product',
            'delete_any_product',

            // Avis
            'view_all_reviews',
            'delete_any_review',
            'moderate_reviews',

            // Système
            'manage_settings',
            'view_all_stats',
            'manage_tasks',
            'manage_withdrawals',
            'view_logs',

            // Transactions
            'add_bonus',
            'apply_penalty',
        ],

        'admin_entreprise' => [
            // Utilisateurs de son entreprise
            'create_user',
            'view_company_users',
            'edit_company_user',

            // Produits de son entreprise
            'create_product',
            'edit_own_product',
            'delete_own_product',
            'view_company_products',

            // Avis de son entreprise
            'view_company_reviews',
            'respond_to_review',

            // Tâches
            'create_task',
            'view_company_tasks',

            // Stats de son entreprise
            'view_company_stats',
        ],

        'user' => [
            // Profil
            'view_own_profile',
            'edit_own_profile',

            // Avis
            'create_review',
            'edit_own_review',
            'delete_own_review',
            'like_review',
            'dislike_review',

            // Produits
            'view_products',
            'recommend_product',

            // Tâches
            'view_own_tasks',
            'complete_task',

            // Portefeuille
            'view_own_wallet',
            'request_withdrawal',

            // Parrainage
            'invite_users',
            'view_own_referrals',

            // Notifications
            'view_own_notifications',
        ]
    ];

    public function __construct($pdo)
    {
        $this->db = $pdo;
    }

    /**
     * Vérifier si l'utilisateur a une permission
     */
    public function hasPermission($permission, $userId = null)
    {
        $userId = $userId ?? ($_SESSION['user_id'] ?? null);

        if (!$userId) {
            return false;
        }

        // Récupérer le rôle de l'utilisateur
        $stmt = $this->db->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $role = $stmt->fetchColumn();

        if (!$role) {
            return false;
        }

        // Vérifier si le rôle a la permission
        return in_array($permission, self::PERMISSIONS[$role] ?? []);
    }

    /**
     * Vérifier plusieurs permissions (AND)
     */
    public function hasAllPermissions(array $permissions, $userId = null)
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission, $userId)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Vérifier plusieurs permissions (OR)
     */
    public function hasAnyPermission(array $permissions, $userId = null)
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission, $userId)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Vérifier si l'utilisateur peut gérer une ressource
     */
    public function canManageResource($resourceType, $resourceId, $userId = null)
    {
        $userId = $userId ?? ($_SESSION['user_id'] ?? null);

        if (!$userId) {
            return false;
        }

        $stmt = $this->db->prepare("SELECT role, company_id FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return false;
        }

        // Super admin peut tout faire
        if ($user['role'] === 'super_admin') {
            return true;
        }

        // Vérifications spécifiques par type de ressource
        switch ($resourceType) {
            case 'product':
                return $this->canManageProduct($resourceId, $user);

            case 'user':
                return $this->canManageUser($resourceId, $user);

            case 'review':
                return $this->canManageReview($resourceId, $user);

            case 'company':
                return $this->canManageCompany($resourceId, $user);

            default:
                return false;
        }
    }

    /**
     * Vérifier si l'utilisateur peut gérer un produit
     */
    private function canManageProduct($productId, $user)
    {
        // Admin entreprise peut gérer les produits de son entreprise
        if ($user['role'] === 'admin_entreprise') {
            $stmt = $this->db->prepare("SELECT company_id FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            $productCompanyId = $stmt->fetchColumn();

            return $productCompanyId && $productCompanyId == $user['company_id'];
        }

        return false;
    }

    /**
     * Vérifier si l'utilisateur peut gérer un autre utilisateur
     */
    private function canManageUser($targetUserId, $user)
    {
        // Admin entreprise peut gérer les users de son entreprise
        if ($user['role'] === 'admin_entreprise') {
            $stmt = $this->db->prepare("SELECT company_id, role FROM users WHERE id = ?");
            $stmt->execute([$targetUserId]);
            $targetUser = $stmt->fetch(PDO::FETCH_ASSOC);

            // Peut gérer uniquement les users (pas les autres admins) de son entreprise
            return $targetUser
                && $targetUser['role'] === 'user'
                && $targetUser['company_id'] == $user['company_id'];
        }

        // Un user peut uniquement gérer son propre profil
        if ($user['role'] === 'user') {
            return $targetUserId == $user['id'];
        }

        return false;
    }

    /**
     * Vérifier si l'utilisateur peut gérer un avis
     */
    private function canManageReview($reviewId, $user)
    {
        $stmt = $this->db->prepare("
            SELECT r.user_id, r.product_id, p.company_id
            FROM reviews r
            JOIN products p ON r.product_id = p.id
            WHERE r.id = ?
        ");
        $stmt->execute([$reviewId]);
        $review = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$review) {
            return false;
        }

        // Admin entreprise peut modérer les avis de ses produits
        if ($user['role'] === 'admin_entreprise') {
            return $review['company_id'] == $user['company_id'];
        }

        // Un user peut gérer uniquement ses propres avis
        if ($user['role'] === 'user') {
            return $review['user_id'] == $user['id'];
        }

        return false;
    }

    /**
     * Vérifier si l'utilisateur peut gérer une entreprise
     */
    private function canManageCompany($companyId, $user)
    {
        // Admin entreprise peut voir/modifier son entreprise
        if ($user['role'] === 'admin_entreprise') {
            return $companyId == $user['company_id'];
        }

        return false;
    }

    /**
     * Obtenir toutes les permissions d'un rôle
     */
    public function getRolePermissions($role)
    {
        return self::PERMISSIONS[$role] ?? [];
    }

    /**
     * Middleware pour vérifier les permissions
     */
    public function requirePermission($permission, $redirectUrl = '/')
    {
        if (!$this->hasPermission($permission)) {
            $_SESSION['error_message'] = 'Vous n\'avez pas la permission d\'accéder à cette ressource.';
            header("Location: $redirectUrl");
            exit;
        }
    }

    /**
     * Middleware pour vérifier le rôle
     */
    public function requireRole($role, $redirectUrl = '/')
    {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== $role) {
            $_SESSION['error_message'] = 'Accès non autorisé.';
            header("Location: $redirectUrl");
            exit;
        }
    }

    /**
     * Obtenir le niveau de rôle (pour hiérarchie)
     */
    public function getRoleLevel($role)
    {
        $levels = [
            'super_admin' => 3,
            'admin_entreprise' => 2,
            'user' => 1
        ];

        return $levels[$role] ?? 0;
    }

    /**
     * Vérifier si un rôle est supérieur à un autre
     */
    public function isRoleHigherThan($role1, $role2)
    {
        return $this->getRoleLevel($role1) > $this->getRoleLevel($role2);
    }
}
