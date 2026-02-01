<?php
/**
 * manifest.json dynamique
 * Génère le manifest PWA avec les bonnes URLs selon l'environnement
 */

require_once __DIR__ . '/../includes/url.php';

// Définir le type de contenu JSON
header('Content-Type: application/manifest+json');
header('Cache-Control: public, max-age=86400'); // Cache 24h

// Récupérer la base URL publique
$baseUrl = defined('PUBLIC_URL') ? PUBLIC_URL : url('');
$baseUrl = rtrim($baseUrl, '/');

// Générer le manifest
$manifest = [
    'name' => "TrustPick - Plateforme d'Avis & Récompenses",
    'short_name' => 'TrustPick',
    'description' => "Gagnez de l'argent en laissant des avis sur vos produits préférés. Application disponible sur iOS, Android, Windows et Mac.",
    'start_url' => $baseUrl . '/index.php?page=home',
    'id' => $baseUrl . '/',
    'display' => 'standalone',
    'display_override' => ['window-controls-overlay', 'standalone', 'minimal-ui'],
    'background_color' => '#ffffff',
    'theme_color' => '#0066cc',
    'orientation' => 'portrait-primary',
    'icons' => [
        [
            'src' => $baseUrl . '/assets/img/icon-192.png',
            'sizes' => '192x192',
            'type' => 'image/png',
            'purpose' => 'any'
        ],
        [
            'src' => $baseUrl . '/assets/img/icon-192.png',
            'sizes' => '192x192',
            'type' => 'image/png',
            'purpose' => 'maskable'
        ],
        [
            'src' => $baseUrl . '/assets/img/icon-512.png',
            'sizes' => '512x512',
            'type' => 'image/png',
            'purpose' => 'any'
        ],
        [
            'src' => $baseUrl . '/assets/img/icon-512.png',
            'sizes' => '512x512',
            'type' => 'image/png',
            'purpose' => 'maskable'
        ]
    ],
    'screenshots' => [
        [
            'src' => $baseUrl . '/assets/img/icon-512.png',
            'sizes' => '512x512',
            'type' => 'image/png',
            'form_factor' => 'narrow',
            'label' => "Écran d'accueil TrustPick"
        ],
        [
            'src' => $baseUrl . '/assets/img/icon-512.png',
            'sizes' => '512x512',
            'type' => 'image/png',
            'form_factor' => 'wide',
            'label' => 'TrustPick sur Desktop'
        ]
    ],
    'categories' => ['finance', 'shopping', 'lifestyle', 'social'],
    'lang' => 'fr-FR',
    'dir' => 'ltr',
    'scope' => $baseUrl . '/',
    'prefer_related_applications' => false,
    'shortcuts' => [
        [
            'name' => 'Catalogue',
            'short_name' => 'Catalogue',
            'description' => 'Voir tous les produits',
            'url' => $baseUrl . '/index.php?page=catalog',
            'icons' => [['src' => $baseUrl . '/assets/img/icon-192.png', 'sizes' => '192x192']]
        ],
        [
            'name' => 'Mon Portefeuille',
            'short_name' => 'Portefeuille',
            'description' => 'Gérer mes gains',
            'url' => $baseUrl . '/index.php?page=wallet',
            'icons' => [['src' => $baseUrl . '/assets/img/icon-192.png', 'sizes' => '192x192']]
        ],
        [
            'name' => 'Mes Avis',
            'short_name' => 'Avis',
            'description' => 'Voir mes avis',
            'url' => $baseUrl . '/index.php?page=user_dashboard',
            'icons' => [['src' => $baseUrl . '/assets/img/icon-192.png', 'sizes' => '192x192']]
        ]
    ],
    'related_applications' => [],
    'handle_links' => 'preferred',
    'launch_handler' => [
        'client_mode' => 'navigate-existing'
    ]
];

// Sortie JSON
echo json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
