<?php
/**
 * Classe Database - Singleton pour connexion PDO
 * Compatible MySQL (InfinityFree/XAMPP) et PostgreSQL (Render)
 * 
 * Inclut un mécanisme de retry pour les hébergements mutualisés 
 * (InfinityFree) où les connexions MySQL peuvent être instables.
 */
class Database
{
    private static $instance = null;
    private $pdo;
    private $driver;
    private $config;

    /** Nombre de tentatives de connexion */
    const MAX_RETRIES = 3;
    /** Délai entre tentatives (microsecondes) */
    const RETRY_DELAY_US = 300000; // 300ms

    private function __construct()
    {
        $this->config = require __DIR__ . '/config.php';
        $this->driver = $this->config['db_driver'] ?? 'mysql';

        $lastError = null;

        for ($attempt = 1; $attempt <= self::MAX_RETRIES; $attempt++) {
            try {
                $this->pdo = $this->createConnection();
                return; // Connexion réussie
            } catch (Exception $e) {
                $lastError = $e;
                if ($attempt < self::MAX_RETRIES) {
                    usleep(self::RETRY_DELAY_US * $attempt); // Backoff progressif
                }
            }
        }

        // Toutes les tentatives ont échoué
        $errorInfo = [
            'error' => $lastError->getMessage(),
            'host' => $this->config['db_host'] ?? '(not set)',
            'db' => $this->config['db_name'] ?? '(not set)',
            'user' => $this->config['db_user'] ?? '(not set)',
            'source' => $this->config['_config_source'] ?? 'unknown',
            'env_exists' => $this->config['_env_file_exists'] ?? 'unknown',
            'attempts' => self::MAX_RETRIES,
        ];

        // En mode JSON (appel AJAX), retourner du JSON
        $isAjax = (
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) || (
            isset($_SERVER['HTTP_ACCEPT']) &&
            strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false
        ) || (
            isset($_SERVER['CONTENT_TYPE']) &&
            strpos($_SERVER['CONTENT_TYPE'], 'application/x-www-form-urlencoded') !== false
        );

        http_response_code(500);
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status' => 'error',
                'message' => 'Connexion à la base de données impossible.',
                'debug' => $errorInfo,
            ]);
        } else {
            echo 'Database connection failed: ' . htmlspecialchars($lastError->getMessage());
            echo ' [host=' . htmlspecialchars($errorInfo['host']) . ', source=' . htmlspecialchars($errorInfo['source']) . ']';
        }
        exit;
    }

    /**
     * Crée la connexion PDO selon le driver configuré
     */
    private function createConnection(): PDO
    {
        if ($this->driver === 'pgsql') {
            $port = $this->config['db_port'] ?? 5432;
            $dsn = "pgsql:host={$this->config['db_host']};port={$port};dbname={$this->config['db_name']}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];
            $hasDbUrl = getenv('DATABASE_URL') || ($_ENV['DATABASE_URL'] ?? '') || ($_SERVER['DATABASE_URL'] ?? '');
            if ($hasDbUrl) {
                $dsn .= ";sslmode=require";
            }
        } else {
            $port = $this->config['db_port'] ?? 3306;
            $dsn = "mysql:host={$this->config['db_host']};port={$port};dbname={$this->config['db_name']};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                PDO::ATTR_TIMEOUT => 10,
            ];
        }

        return new PDO($dsn, $this->config['db_user'], $this->config['db_pass'], $options);
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