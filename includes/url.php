<?php
/**
 * URL Helper — Génère des URLs absolues correctes
 */

// Détecter automatiquement le base url d'après l'emplacement du script
// Permet de déployer l'application soit à la racine, soit dans un sous-dossier
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/';
$scriptDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

// If the front controller lives in a `public` folder, assume the app
// base is the parent directory (so assets at /TrustPick/assets/...)
if ($scriptDir !== '' && basename($scriptDir) === 'public') {
    $appBase = dirname($scriptDir);
} else {
    $appBase = $scriptDir;
}

$baseDir = $appBase === '' ? '/' : rtrim($appBase, '/') . '/';

define('BASE_URL', $baseDir);
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
