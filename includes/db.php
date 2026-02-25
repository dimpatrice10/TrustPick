<?php
/**
 * Classe Database - Singleton pour connexion PDO
 * Compatible MySQL (InfinityFree/XAMPP) et PostgreSQL (Render)
 */
class Database
{
    private static $instance = null;
    private $pdo;
    private $driver;

    private function __construct()
    {
        $config = require __DIR__ . '/config.php';
        try {
            $this->driver = $config['db_driver'] ?? 'mysql';

            if ($this->driver === 'pgsql') {
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
                $port = $config['db_port'] ?? 3306;
                $dsn = "mysql:host={$config['db_host']};port={$port};dbname={$config['db_name']};charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                ];
            }

            $this->pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], $options);
        } catch (Exception $e) {
            http_response_code(500);
            // Si la requête attend du JSON (AJAX), répondre en JSON
            $isAjax = (
                (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
                (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
                (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/x-www-form-urlencoded') !== false && isset($_SERVER['HTTP_REFERER']))
            );
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['status' => 'error', 'success' => false, 'message' => 'Service temporairement indisponible. Réessayez dans quelques instants.']);
            } else {
                echo 'Database connection failed: ' . htmlspecialchars($e->getMessage());
            }
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

    public function getDriver()
    {
        return $this->driver;
    }
}

// Rétrocompatibilité : créer aussi $pdo global
$pdo = Database::getInstance()->getConnection();