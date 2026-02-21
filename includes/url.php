<?php
/**
 * includes/url.php
 * 
 * Helper d'URLs/redirections PRODUCTION-READY
 * 
 * FONCTIONNE DEPUIS N'IMPORTE QUEL SOUS-DOSSIER:
 * - /actions/logout.php ✓
 * - /public/index.php ✓
 * - /admin/dashboard.php ✓
 * - /includes/... ✓
 * 
 * Le calcul de BASE_URL se fait via le filesystem (pas SCRIPT_NAME)
 * pour garantir la cohérence peu importe d'où le script est appelé.
 * 
 * Fonctions:
 * - url($path)        : URL absolue vers racine projet
 * - public_url($path) : URL absolue vers /public/ (entry point)
 * - asset($path)      : Alias de public_url pour assets
 * - redirect($path)   : Redirection HTTP vers public/index.php?page=...
 * - base_path($path)  : Chemin filesystem absolu
 */

// ============================================================================
// CALCUL DES CONSTANTES (une seule fois)
// ============================================================================

if (!defined('BASE_URL')) {

    // 1) PROTOCOLE + HOST
    // Render/Cloudflare terminate SSL at the proxy level — Apache sees HTTP internally.
    // Check X-Forwarded-Proto header first (set by reverse proxies like Render).
    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
        $protocol = $_SERVER['HTTP_X_FORWARDED_PROTO'];
    } elseif (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        $protocol = 'https';
    } else {
        $protocol = 'http';
    }
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // 2) CHEMIN FILESYSTEM DU PROJET (source de vérité)
    //    __DIR__ = includes/, donc parent = racine projet
    $projectRoot = realpath(__DIR__ . '/..') ?: dirname(__DIR__);
    $projectRoot = str_replace('\\', '/', $projectRoot); // normaliser Windows

    // 3) DOCUMENT_ROOT du serveur
    $docRoot = !empty($_SERVER['DOCUMENT_ROOT'])
        ? str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']) ?: $_SERVER['DOCUMENT_ROOT'])
        : '';

    // 4) CALCULER LE CHEMIN WEB DU PROJET
    //    On compare le chemin filesystem du projet avec DOCUMENT_ROOT
    //    Ex: docRoot=/var/www/html, project=/var/www/html/trustpick => webPath=/trustpick
    $webBasePath = '';
    if ($docRoot && strpos($projectRoot, $docRoot) === 0) {
        $webBasePath = substr($projectRoot, strlen($docRoot));
        $webBasePath = '/' . trim($webBasePath, '/');
        if ($webBasePath === '/') {
            $webBasePath = '';
        }
    } else {
        // Fallback: extraire depuis SCRIPT_NAME en cherchant des marqueurs connus
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

        // Chercher /public/, /actions/, /admin/, /api/, /includes/ dans le chemin
        $markers = ['/public/', '/actions/', '/admin/', '/api/', '/includes/', '/views/'];
        foreach ($markers as $marker) {
            if (($pos = strpos($scriptName, $marker)) !== false) {
                $webBasePath = substr($scriptName, 0, $pos);
                break;
            }
        }

        // Si toujours vide et script finit par .php à la racine
        if ($webBasePath === '' && preg_match('#^(/[^/]+)/[^/]+\.php$#', $scriptName, $m)) {
            $webBasePath = $m[1];
        }
    }

    // 5) DÉTECTER SI /public EST LE DOCUMENT_ROOT (cPanel, production)
    $publicFsPath = $projectRoot . '/public';
    $publicIsDocRoot = ($docRoot && file_exists($publicFsPath) && realpath($publicFsPath) === realpath($docRoot));

    // 6) CONSTRUIRE LES URLS
    $baseUrl = $protocol . '://' . $host . $webBasePath;
    $baseUrl = rtrim($baseUrl, '/') . '/';

    if ($publicIsDocRoot) {
        // En production (cPanel), public/ est exposé à la racine du domaine
        $publicUrl = $protocol . '://' . $host . '/';
    } else {
        // En dev, public/ est un sous-dossier
        $publicUrl = rtrim($baseUrl, '/') . '/public/';
    }

    // 7) DÉFINIR LES CONSTANTES
    define('BASE_URL', $baseUrl);
    define('PUBLIC_URL', $publicUrl);
    define('BASE_PATH', $projectRoot);
    define('PUBLIC_IS_DOCROOT', $publicIsDocRoot);
}

// ============================================================================
// FONCTIONS HELPERS
// ============================================================================

/**
 * Retourne l'URL absolue vers la racine du projet
 * 
 * @param string $path Chemin relatif à ajouter
 * @return string URL complète
 * 
 * Exemple: url('api/users.php') => http://localhost/trustpick/api/users.php
 */
function url(string $path = ''): string
{
    if ($path === '') {
        return BASE_URL;
    }
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

/**
 * Retourne l'URL absolue vers le dossier public (point d'entrée)
 * 
 * @param string $path Chemin relatif à ajouter
 * @return string URL complète
 * 
 * Exemple: public_url('index.php?page=home') => http://localhost/trustpick/public/index.php?page=home
 * En prod:  public_url('index.php?page=home') => https://monsite.com/index.php?page=home
 */
function public_url(string $path = ''): string
{
    if ($path === '') {
        return PUBLIC_URL;
    }
    return rtrim(PUBLIC_URL, '/') . '/' . ltrim($path, '/');
}

/**
 * Retourne l'URL d'un asset (CSS, JS, images) dans public/
 * Alias de public_url() pour la clarté du code
 * 
 * @param string $path Chemin de l'asset
 * @return string URL complète
 * 
 * Exemple: asset('assets/css/app.css') => http://localhost/trustpick/public/assets/css/app.css
 */
function asset(string $path = ''): string
{
    return public_url($path);
}

/**
 * Retourne un chemin relatif (sans base URL)
 * Utile pour les liens href internes
 * 
 * @param string $path Chemin
 * @return string Chemin nettoyé
 */
function path(string $path = ''): string
{
    return ltrim($path, '/');
}

/**
 * Effectue une redirection HTTP vers le point d'entrée public/index.php
 * 
 * TOUJOURS redirige vers PUBLIC_URL, jamais vers /actions/ ou autre
 * 
 * @param string $path Destination (ex: 'index.php?page=home')
 * @return never
 * 
 * Exemples d'appels et résultats:
 *   redirect('index.php?page=home')     => Location: http://localhost/trustpick/public/index.php?page=home
 *   redirect('index.php?page=login')    => Location: http://localhost/trustpick/public/index.php?page=login
 *   redirect('https://google.com')      => Location: https://google.com
 */
function redirect(string $path): never
{
    // 1) URL absolue externe : on redirige directement
    if (preg_match('#^https?://#i', $path)) {
        header('Location: ' . $path);
        exit;
    }

    // 2) Construire l'URL vers public/
    $target = public_url($path);

    // 3) Sécurité : intercepter les anciens chemins /dashboard et les convertir
    //    Ceci empêche toute redirection accidentelle vers des fichiers legacy
    $legacyMappings = [
        '#/dashboard/?$#' => 'index.php?page=user_dashboard',
        '#/user/dashboard\.php#' => 'index.php?page=user_dashboard',
        '#/admin/dashboard\.php#' => 'index.php?page=admin_dashboard',
        '#/superadmin/dashboard\.php#' => 'index.php?page=superadmin_dashboard',
    ];

    foreach ($legacyMappings as $pattern => $replacement) {
        if (preg_match($pattern, $target)) {
            $target = public_url($replacement);
            break;
        }
    }

    // 4) Envoyer le header et terminer
    header('Location: ' . $target);
    exit;
}

/**
 * Retourne le chemin filesystem absolu vers un fichier/dossier du projet
 * 
 * @param string $path Chemin relatif depuis la racine projet
 * @return string Chemin absolu
 * 
 * Exemple: base_path('storage/logs') => /var/www/html/trustpick/storage/logs
 */
function base_path(string $path = ''): string
{
    if ($path === '') {
        return BASE_PATH;
    }
    $separator = DIRECTORY_SEPARATOR;
    return BASE_PATH . $separator . ltrim(str_replace(['/', '\\'], $separator, $path), $separator);
}

/**
 * Fonction de debug (à supprimer en production)
 * Affiche les constantes calculées
 */
function debug_urls(): void
{
    if (headers_sent()) {
        echo "<pre>";
    }
    echo "BASE_URL:          " . BASE_URL . "\n";
    echo "PUBLIC_URL:        " . PUBLIC_URL . "\n";
    echo "BASE_PATH:         " . BASE_PATH . "\n";
    echo "PUBLIC_IS_DOCROOT: " . (PUBLIC_IS_DOCROOT ? 'true' : 'false') . "\n";
    echo "SCRIPT_NAME:       " . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . "\n";
    echo "DOCUMENT_ROOT:     " . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . "\n";
    if (headers_sent()) {
        echo "</pre>";
    }
}