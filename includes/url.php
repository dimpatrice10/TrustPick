<?php
/**
 * URL Helper — Génère des URLs absolues correctes
 */

// Détection automatique de BASE_URL selon l'environnement
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';

// Logique pour définir BASE_URL selon l'environnement
if ($host === 'localhost') {
    // En local : index.php dans public/, assets dans public/assets/
    define('BASE_URL', $protocol . '://' . $host . '/trustpick/public/');
} else {
    // En production : index.php à la racine, assets dans /assets/
    define('BASE_URL', $protocol . '://' . $host . '/');
}

define('BASE_PATH', __DIR__ . '/..');

/**
 * Génère une URL absolue basée sur `BASE_URL`
 */
function url($path = '')
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

/**
 * Redirige vers une URL
 */
function redirect($path)
{
    header('Location: ' . url($path));
    exit;
}

/**
 * Chemin absolu du projet
 */
function base_path($path = '')
{
    return BASE_PATH . ($path ? '/' . ltrim($path, '/') : '');
}
