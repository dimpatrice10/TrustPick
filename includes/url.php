<?php
/**
 * includes/url.php
 *
 * Helper d'URLs/chemins qui supporte :
 * - point d'entrée dans public/index.php
 * - cPanel (public comme DOCUMENT_ROOT)
 * - dev local sous /projet/public/index.php
 *
 * Fonctions fournies :
 * - url($path)        : URL absolue de base du projet (racine web détectée)
 * - public_url($path) : URL absolue pointant vers /public/ (entrypoint)
 * - asset($path)      : URL absolue d'un asset (CSS/JS/images) sous public
 * - path($path)       : chemin relatif pour href internes (sans slash initial)
 * - redirect($path)   : redirection HTTP (utilise public_url par défaut)
 * - base_path($path)  : chemin disque absolu côté serveur (équivalent à realpath project root)
 */

// protocole + host
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// script courant (ex: /trustpick/public/index.php ou /index.php ou /trustpick/actions/login.php)
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/';
$scriptDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

// 1) déterminer base web (chemin relatif depuis la racine du domaine vers la racine du projet)
$basePath = '';
if (strpos($scriptName, '/public/') !== false) {
    // cas fréquent en dev : http://host/project/public/index.php
    $basePath = substr($scriptName, 0, strpos($scriptName, '/public/'));
    $basePath = rtrim($basePath, '/');
} else {
    // cas où public est déjà DOCUMENT_ROOT (cPanel) ou index.php est à la racine
    // on prend le dossier parent du script comme base candidate (peut être '')
    $candidate = rtrim($scriptDir, '/');
    $candidate = ($candidate === '/' || $candidate === '.') ? '' : $candidate;
    $basePath = $candidate;
}

// 2) chemins filesystem pour déduire si 'public' est document root sur le serveur
$projectFs = realpath(__DIR__ . '/..');            // ex: /home/user/.../trustpick
$publicFs = $projectFs . '/public';
$publicFsReal = $publicFs && file_exists($publicFs) ? realpath($publicFs) : false;
$docRootFs = !empty($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;

// 3) construire BASE_URL (absolue) pointant vers la racine web du projet détectée
//    ex: http://localhost/trustpick/   OR http://example.com/
$basePathForUrl = $basePath === '' ? '' : '/' . ltrim($basePath, '/');
$baseUrl = $protocol . '://' . $host . $basePathForUrl . '/';

// 4) déterminer PUBLIC_URL (URL absolue vers le dossier public exposé)
//    cas A: public est DOCUMENT_ROOT -> PUBLIC_URL = http(s)://host/
//    cas B: public apparaît dans l'URL -> PUBLIC_URL = BASE_URL + 'public/'
//    cas C: public existe en FS mais n'est pas docroot -> PUBLIC_URL = BASE_URL + 'public/'
//    cas D: fallback -> PUBLIC_URL = BASE_URL
if ($publicFsReal && $docRootFs && $publicFsReal === $docRootFs) {
    // public est le document root (cPanel ou déploiement où public/ est déjà exposé)
    $publicUrl = $protocol . '://' . $host . '/';
} elseif (strpos($scriptName, '/public/') !== false) {
    // public présent explicitement dans l'URL (dev)
    $publicUrl = rtrim($baseUrl, '/') . '/public/';
} elseif ($publicFsReal) {
    // public existe sur disque mais n'est pas docroot : on assume /{basePath}/public/
    $publicUrl = rtrim($baseUrl, '/') . '/public/';
} else {
    // pas de dossier public détecté ; fallback sur BASE_URL
    $publicUrl = $baseUrl;
}

// normalisations finales
$BASE_URL = rtrim($baseUrl, '/') . '/';
$PUBLIC_URL = rtrim($publicUrl, '/') . '/';
define('BASE_URL', $BASE_URL);
define('PUBLIC_URL', $PUBLIC_URL);
define('BASE_PATH', $projectFs ?: realpath(__DIR__ . '/..'));

/**
 * url() : URL absolue vers la racine du projet
 * ex: url('index.php?page=home') => http://host/trustpick/index.php?page=home
 */
function url(string $path = ''): string
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

/**
 * public_url() : URL absolue pointant vers le dossier public (entry point)
 * ex: public_url('index.php?page=home') => http://host/trustpick/public/index.php?page=home
 * si public est docroot, renverra http://host/index.php?page=home
 */
function public_url(string $path = ''): string
{
    return rtrim(PUBLIC_URL, '/') . '/' . ltrim($path, '/');
}

/**
 * asset() : URL absolue pour assets (css/js/img) dans public/
 * ex: asset('assets/css/app.css') => http://host/.../public/assets/css/app.css   (ou /assets/css/app.css si public docroot)
 */
function asset(string $path = ''): string
{
    return rtrim(PUBLIC_URL, '/') . '/' . ltrim($path, '/');
}

/**
 * path() : chemin relatif pour liens internes (href) si tu veux sans base
 * ex: path('index.php?page=login') => 'index.php?page=login'
 */
function path(string $path = ''): string
{
    return ltrim($path, '/');
}

/**
 * redirect() : redirige proprement.
 * - Si $path commence par 'http' -> redirect absolue vers cette URL.
 * - Si $path commence par '/' -> redirect root-based (ex: '/index.php')
 * - Sinon -> par défaut on redirige vers public_url($path) car entrypoint = public/index.php
 */
/**
 * Redirection HTTP propre
 */
function redirect(string $path): void
{
    // Si c'est déjà une URL complète, on l'utilise
    if (preg_match('#^https?://#i', $path)) {
        header('Location: ' . $path);
        exit;
    }

    // On construit l'URL complète vers public/index.php
    $target = public_url($path);

    // Envoie du header avec URL absolue
    header('Location: ' . $target);
    exit;
}


/**
 * base_path() : chemin disque absolu côté serveur
 * ex: base_path('storage/logs') => /home/.../trustpick/storage/logs
 */
function base_path(string $path = ''): string
{
    return rtrim(BASE_PATH, DIRECTORY_SEPARATOR) . ($path ? DIRECTORY_SEPARATOR . ltrim($path, '/') : '');
}
