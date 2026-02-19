<?php
/**
 * Classe Database - Singleton pour connexion PDO
 */
class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        $config = require __DIR__ . '/config.php';
        try {
            // Support PostgreSQL (Render) et MySQL (XAMPP local)
            // Utiliser tp_env() si disponible, sinon getenv() + $_ENV + $_SERVER
            $hasDbUrl = getenv('DATABASE_URL') || ($_ENV['DATABASE_URL'] ?? '') || ($_SERVER['DATABASE_URL'] ?? '');
            $hasPgHost = getenv('PGHOST') || ($_ENV['PGHOST'] ?? '') || ($_SERVER['PGHOST'] ?? '');
            $driver = ($hasDbUrl || $hasPgHost) ? 'pgsql' : 'mysql';

            if ($driver === 'pgsql') {
                $port = $config['db_port'] ?? 5432;
                $dsn = "pgsql:host={$config['db_host']};port={$port};dbname={$config['db_name']}";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ];
                // Render PostgreSQL nécessite SSL
                $hasDbUrl = getenv('DATABASE_URL') || ($_ENV['DATABASE_URL'] ?? '') || ($_SERVER['DATABASE_URL'] ?? '');
                if ($hasDbUrl) {
                    $dsn .= ";sslmode=require";
                }
            } else {
                $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ];
            }

            $this->pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], $options);
        } catch (Exception $e) {
            http_response_code(500);
            echo 'Database connection failed: ' . htmlspecialchars($e->getMessage());
            exit;
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->pdo;
    }
}

// Rétrocompatibilité : créer aussi $pdo global
$pdo = Database::getInstance()->getConnection();