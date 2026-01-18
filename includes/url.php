<?php
/**
 * URL Helper — Fonctionne en local et en production
 */

// Détection du protocole
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';

// Host (localhost, domaine, sous-domaine…)
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Script en cours (/trustpick/public/index.php ou /index.php)
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/';

// Dossier du script
$scriptDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

// Si l'app est dans un dossier "public", on remonte d'un niveau
if (basename($scriptDir) === 'public') {
    $basePath = dirname($scriptDir);
} else {
    $basePath = $scriptDir;
}

// Normalisation
$basePath = ($basePath === '/' || $basePath === '.') ? '' : $basePath;

// BASE_URL finale (TOUJOURS ABSOLUE)
define('BASE_URL', $protocol . '://' . $host . $basePath . '/');

// Chemin disque du projet
define('BASE_PATH', realpath(__DIR__ . '/..'));

/**
 * Génère une URL absolue correcte
 */
function url(string $path = ''): string
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

/**
 * Redirection HTTP
 */
function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

/**
 * Chemin absolu côté serveur
 */
function base_path(string $path = ''): string
{
    return BASE_PATH . ($path ? DIRECTORY_SEPARATOR . ltrim($path, '/') : '');
}
