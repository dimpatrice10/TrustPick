<?php
// ============================================================
// TRUSTPICK V2 - GESTION DES SESSIONS
// ============================================================

// session_start();

// Configuration
define('SESSION_LIFETIME', 3600 * 24); // 24 heures
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900); // 15 minutes

/**
 * Classe de gestion des sessions
 */
class SessionManager
{

    /**
     * Vérifier si l'utilisateur est connecté
     */
    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['user_id']) && isset($_SESSION['cau']);
    }

    /**
     * Obtenir l'utilisateur actuel
     */
    public static function getCurrentUser(): ?array
    {
        if (!self::isLoggedIn()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'],
            'cau' => $_SESSION['cau'],
            'name' => $_SESSION['user_name'],
            'role' => $_SESSION['user_role'],
            'company_id' => $_SESSION['company_id'] ?? null,
            'balance' => $_SESSION['balance'] ?? 0,
            'referral_code' => $_SESSION['referral_code'] ?? null
        ];
    }

    /**
     * Créer une session utilisateur
     */
    public static function create(array $userData): void
    {
        // Régénérer l'ID de session (sécurité)
        session_regenerate_id(true);

        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['cau'] = $userData['cau'];
        $_SESSION['user_name'] = $userData['name'];
        $_SESSION['user_role'] = $userData['role'];
        $_SESSION['company_id'] = $userData['company_id'] ?? null;
        $_SESSION['balance'] = $userData['balance'] ?? 0;
        $_SESSION['referral_code'] = $userData['referral_code'] ?? null;
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    /**
     * Détruire la session
     */
    public static function destroy(): void
    {
        $_SESSION = [];

        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        session_destroy();
    }

    /**
     * Vérifier la validité de la session
     */
    public static function validate(): bool
    {
        if (!self::isLoggedIn()) {
            return false;
        }

        // Vérifier l'expiration
        if (isset($_SESSION['last_activity'])) {
            if (time() - $_SESSION['last_activity'] > SESSION_LIFETIME) {
                self::destroy();
                return false;
            }
            $_SESSION['last_activity'] = time();
        }

        // Vérifier l'IP (optionnel, peut causer des problèmes avec IP dynamiques)
        /*
        if (isset($_SESSION['ip_address'])) {
            if ($_SESSION['ip_address'] !== ($_SERVER['REMOTE_ADDR'] ?? 'unknown')) {
                self::destroy();
                return false;
            }
        }
        */

        return true;
    }

    /**
     * Vérifier le rôle de l'utilisateur
     */
    public static function hasRole(string $role): bool
    {
        if (!self::isLoggedIn()) {
            return false;
        }

        return $_SESSION['user_role'] === $role;
    }

    /**
     * Vérifier les rôles multiples
     */
    public static function hasAnyRole(array $roles): bool
    {
        if (!self::isLoggedIn()) {
            return false;
        }

        return in_array($_SESSION['user_role'], $roles);
    }

    /**
     * Protéger une page (redirection si non connecté)
     */
    public static function requireLogin(string $redirectTo = 'login.php'): void
    {
        if (!self::validate()) {
            if (headers_sent()) {
                echo '<script>window.location.href="' . $redirectTo . '";</script>';
                echo '<noscript><meta http-equiv="refresh" content="0;url=' . $redirectTo . '"></noscript>';
            } else {
                header("Location: $redirectTo");
            }
            exit;
        }
    }

    /**
     * Protéger une page par rôle
     */
    public static function requireRole(string $role, string $redirectTo = 'login.php'): void
    {
        self::requireLogin($redirectTo);

        if (!self::hasRole($role)) {
            if (headers_sent()) {
                echo '<script>window.location.href="index.php?page=403";</script>';
                echo '<noscript><meta http-equiv="refresh" content="0;url=index.php?page=403"></noscript>';
            } else {
                header("Location: index.php?page=403");
            }
            exit;
        }
    }

    /**
     * Protéger une page par rôles multiples
     */
    public static function requireAnyRole(array $roles, string $redirectTo = 'login.php'): void
    {
        self::requireLogin($redirectTo);

        if (!self::hasAnyRole($roles)) {
            header("Location: index.php?page=403");
            exit;
        }
    }

    /**
     * Rediriger selon le rôle
     */
    public static function redirectByRole(): void
    {
        if (!self::isLoggedIn()) {
            header("Location: login.php");
            exit;
        }

        $role = $_SESSION['user_role'];

        switch ($role) {
            case 'super_admin':
                header("Location: index.php?page=superadmin_dashboard");
                break;
            case 'admin_entreprise':
                header("Location: index.php?page=admin_dashboard");
                break;
            case 'user':
            default:
                header("Location: index.php?page=home");
                break;
        }
        exit;
    }

    /**
     * Gestion des tentatives de connexion (protection brute force)
     */
    public static function recordLoginAttempt(string $cau, bool $success): void
    {
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = [];
        }

        if ($success) {
            // Réinitialiser les tentatives en cas de succès
            unset($_SESSION['login_attempts'][$cau]);
            return;
        }

        // Enregistrer l'échec
        if (!isset($_SESSION['login_attempts'][$cau])) {
            $_SESSION['login_attempts'][$cau] = [
                'count' => 0,
                'last_attempt' => 0
            ];
        }

        $_SESSION['login_attempts'][$cau]['count']++;
        $_SESSION['login_attempts'][$cau]['last_attempt'] = time();
    }

    /**
     * Vérifier si le compte est bloqué
     */
    public static function isLocked(string $cau): bool
    {
        if (!isset($_SESSION['login_attempts'][$cau])) {
            return false;
        }

        $attempts = $_SESSION['login_attempts'][$cau];

        if ($attempts['count'] >= MAX_LOGIN_ATTEMPTS) {
            $elapsed = time() - $attempts['last_attempt'];

            if ($elapsed < LOCKOUT_TIME) {
                return true;
            } else {
                // Débloquer après le délai
                unset($_SESSION['login_attempts'][$cau]);
                return false;
            }
        }

        return false;
    }

    /**
     * Obtenir le temps restant de blocage
     */
    public static function getLockoutTime(string $cau): int
    {
        if (!isset($_SESSION['login_attempts'][$cau])) {
            return 0;
        }

        $elapsed = time() - $_SESSION['login_attempts'][$cau]['last_attempt'];
        $remaining = LOCKOUT_TIME - $elapsed;

        return max(0, $remaining);
    }

    /**
     * Mettre à jour le solde en session
     */
    public static function updateBalance(float $newBalance): void
    {
        if (self::isLoggedIn()) {
            $_SESSION['balance'] = $newBalance;
        }
    }
}