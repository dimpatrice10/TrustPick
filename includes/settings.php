<?php
/**
 * TrustPick V2 - Gestionnaire de paramètres système
 * Lit et écrit dans la table system_settings
 */

require_once __DIR__ . '/db.php';

class Settings
{
    private static $cache = null;

    /**
     * Charger tous les paramètres en cache
     */
    private static function loadAll()
    {
        if (self::$cache !== null) {
            return;
        }
        try {
            $pdo = Database::getInstance()->getConnection();
            $rows = $pdo->query("SELECT setting_key, setting_value FROM system_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
            self::$cache = $rows ?: [];
        } catch (Exception $e) {
            self::$cache = [];
        }
    }

    /**
     * Obtenir la valeur d'un paramètre
     *
     * @param string $key Clé du paramètre
     * @param mixed $default Valeur par défaut
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        self::loadAll();
        return self::$cache[$key] ?? $default;
    }

    /**
     * Obtenir une valeur numérique
     */
    public static function getInt(string $key, int $default = 0): int
    {
        return intval(self::get($key, $default));
    }

    /**
     * Mettre à jour ou créer un paramètre
     *
     * @param string $key Clé
     * @param string $value Valeur
     * @param string|null $description Description (uniquement pour la création)
     * @return bool
     */
    public static function set(string $key, string $value, ?string $description = null): bool
    {
        try {
            $pdo = Database::getInstance()->getConnection();

            // Vérifier si la clé existe
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM system_settings WHERE setting_key = ?");
            $stmt->execute([$key]);
            $exists = $stmt->fetchColumn() > 0;

            if ($exists) {
                $stmt = $pdo->prepare("UPDATE system_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?");
                $stmt->execute([$value, $key]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value, description) VALUES (?, ?, ?)");
                $stmt->execute([$key, $value, $description ?? '']);
            }

            // Invalider le cache
            self::$cache = null;
            return true;
        } catch (Exception $e) {
            error_log('Settings::set error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Mettre à jour plusieurs paramètres en une fois
     *
     * @param array $settings ['key' => 'value', ...]
     * @return bool
     */
    public static function setMany(array $settings): bool
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            $pdo->beginTransaction();

            foreach ($settings as $key => $value) {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM system_settings WHERE setting_key = ?");
                $stmt->execute([$key]);
                $exists = $stmt->fetchColumn() > 0;

                if ($exists) {
                    $stmt = $pdo->prepare("UPDATE system_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?");
                    $stmt->execute([$value, $key]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value, description) VALUES (?, ?, '')");
                    $stmt->execute([$key, $value]);
                }
            }

            $pdo->commit();
            self::$cache = null;
            return true;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Settings::setMany error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtenir tous les paramètres
     *
     * @return array
     */
    public static function getAll(): array
    {
        self::loadAll();
        return self::$cache;
    }

    /**
     * Obtenir tous les paramètres avec descriptions
     *
     * @return array
     */
    public static function getAllWithDetails(): array
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            return $pdo->query("SELECT * FROM system_settings ORDER BY setting_key ASC")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}
