<?php
/**
 * deploy-pwa.php
 * Script pour déployer les bons fichiers PWA selon l'environnement
 */

$environment = $argv[1] ?? 'auto';

// Détecter automatiquement l'environnement
if ($environment === 'auto') {
    $host = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? 'localhost');
    $environment = (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) ? 'local' : 'production';
}

echo "🚀 Déploiement PWA pour environnement: $environment\n";

if ($environment === 'production') {
    // Utiliser les fichiers avec scope racine
    echo "📄 Application des fichiers production (scope: /)...\n";
    
    if (file_exists(__DIR__ . '/manifest-production.json')) {
        copy(__DIR__ . '/manifest-production.json', __DIR__ . '/manifest.json');
        echo "  ✓ manifest.json mis à jour\n";
    }
    
    if (file_exists(__DIR__ . '/service-worker-production.js')) {
        copy(__DIR__ . '/service-worker-production.js', __DIR__ . '/service-worker.js');
        echo "  ✓ service-worker.js mis à jour\n";
    }
    
} else {
    // Générer les fichiers avec le scope local
    echo "📄 Génération des fichiers pour local (scope: /public/)...\n";
    
    require_once __DIR__ . '/../includes/url.php';
    
    $baseUrl = defined('PUBLIC_URL') ? PUBLIC_URL : url('');
    $baseUrl = rtrim($baseUrl, '/');
    $scopePath = parse_url($baseUrl, PHP_URL_PATH) ?: '/';
    $scopePath = rtrim($scopePath, '/') . '/';
    
    // Utiliser le script de build existant
    include __DIR__ . '/build-pwa.php';
}

echo "✅ Déploiement PWA terminé !\n";
echo "🔄 Veuillez :\n";
echo "  1. Vider le cache du navigateur (Ctrl+Shift+R)\n";
echo "  2. Désinscrire l'ancien Service Worker si nécessaire\n";
echo "  3. Recharger la page\n";