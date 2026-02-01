<?php
/**
 * build-pwa.php
 * Script pour générer les fichiers PWA statiques avec les bonnes URLs
 */

require_once __DIR__ . '/../includes/url.php';

echo "🔧 Génération des fichiers PWA...\n";

// Récupérer la base URL publique
$baseUrl = defined('PUBLIC_URL') ? PUBLIC_URL : url('');
$baseUrl = rtrim($baseUrl, '/');

// Obtenir le chemin relatif pour le scope
$scopePath = parse_url($baseUrl, PHP_URL_PATH) ?: '/';
$scopePath = rtrim($scopePath, '/') . '/';

// 1. Générer manifest.json
echo "📄 Génération de manifest.json...\n";

$manifest = [
    'name' => "TrustPick - Plateforme d'Avis & Récompenses",
    'short_name' => 'TrustPick',
    'description' => "Gagnez de l'argent en laissant des avis sur vos produits préférés. Application disponible sur iOS, Android, Windows et Mac.",
    'start_url' => $scopePath . 'index.php?page=home',
    'id' => $scopePath,
    'display' => 'standalone',
    'display_override' => ['window-controls-overlay', 'standalone', 'minimal-ui'],
    'background_color' => '#ffffff',
    'theme_color' => '#0066cc',
    'orientation' => 'portrait-primary',
    'icons' => [
        [
            'src' => $scopePath . 'assets/img/icon-192.png',
            'sizes' => '192x192',
            'type' => 'image/png',
            'purpose' => 'any'
        ],
        [
            'src' => $scopePath . 'assets/img/icon-192.png',
            'sizes' => '192x192',
            'type' => 'image/png',
            'purpose' => 'maskable'
        ],
        [
            'src' => $scopePath . 'assets/img/icon-512.png',
            'sizes' => '512x512',
            'type' => 'image/png',
            'purpose' => 'any'
        ],
        [
            'src' => $scopePath . 'assets/img/icon-512.png',
            'sizes' => '512x512',
            'type' => 'image/png',
            'purpose' => 'maskable'
        ]
    ],
    'screenshots' => [
        [
            'src' => $scopePath . 'assets/img/icon-512.png',
            'sizes' => '512x512',
            'type' => 'image/png',
            'form_factor' => 'narrow',
            'label' => "Écran d'accueil TrustPick"
        ],
        [
            'src' => $scopePath . 'assets/img/icon-512.png',
            'sizes' => '512x512',
            'type' => 'image/png',
            'form_factor' => 'wide',
            'label' => 'TrustPick sur Desktop'
        ]
    ],
    'categories' => ['finance', 'shopping', 'lifestyle', 'social'],
    'lang' => 'fr-FR',
    'dir' => 'ltr',
    'scope' => $scopePath,
    'prefer_related_applications' => false,
    'shortcuts' => [
        [
            'name' => 'Catalogue',
            'short_name' => 'Catalogue',
            'description' => 'Voir tous les produits',
            'url' => $scopePath . 'index.php?page=catalog',
            'icons' => [['src' => $scopePath . 'assets/img/icon-192.png', 'sizes' => '192x192']]
        ],
        [
            'name' => 'Mon Portefeuille',
            'short_name' => 'Portefeuille',
            'description' => 'Gérer mes gains',
            'url' => $scopePath . 'index.php?page=wallet',
            'icons' => [['src' => $scopePath . 'assets/img/icon-192.png', 'sizes' => '192x192']]
        ],
        [
            'name' => 'Mes Avis',
            'short_name' => 'Avis',
            'description' => 'Voir mes avis',
            'url' => $scopePath . 'index.php?page=user_dashboard',
            'icons' => [['src' => $scopePath . 'assets/img/icon-192.png', 'sizes' => '192x192']]
        ]
    ],
    'related_applications' => [],
    'handle_links' => 'preferred',
    'launch_handler' => [
        'client_mode' => 'navigate-existing'
    ]
];

$manifestJson = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
file_put_contents(__DIR__ . '/manifest.json', $manifestJson);

// 2. Générer service-worker.js
echo "⚙️  Génération de service-worker.js...\n";

$serviceWorkerContent = <<<JS
/**
 * TrustPick V2 - Service Worker
 * PWA installable sur iOS, Android, Windows, macOS, Linux
 * Version: 2.2.0 - Généré le " . date('Y-m-d H:i:s') . "
 * Scope: {$scopePath}
 */

const CACHE_NAME = 'trustpick-v2.2';
const SCOPE_PATH = '{$scopePath}';
const OFFLINE_URL = SCOPE_PATH + 'offline.html';

// Ressources essentielles à mettre en cache
const ASSETS_TO_CACHE = [
  SCOPE_PATH,
  SCOPE_PATH + 'index.php',
  SCOPE_PATH + 'index.php?page=home',
  SCOPE_PATH + 'offline.html',
  SCOPE_PATH + 'assets/css/app.css',
  SCOPE_PATH + 'assets/css/demo.css',
  SCOPE_PATH + 'assets/css/ui-enhancements.css',
  SCOPE_PATH + 'assets/js/app.js',
  SCOPE_PATH + 'assets/js/ui-enhancements.js',
  SCOPE_PATH + 'assets/js/pwa-install.js',
  SCOPE_PATH + 'assets/img/icon-192.png',
  SCOPE_PATH + 'assets/img/icon-512.png',
  SCOPE_PATH + 'assets/img/logo.png'
];

// Installation du Service Worker
self.addEventListener('install', event => {
  console.log('[ServiceWorker] Installation v2.2...');
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('[ServiceWorker] Mise en cache des ressources essentielles');
        return Promise.allSettled(
          ASSETS_TO_CACHE.map(url =>
            cache.add(url).catch(err => {
              console.warn('[ServiceWorker] Échec cache pour:', url);
              return null;
            })
          )
        );
      })
      .then(() => {
        console.log('[ServiceWorker] Installation terminée');
      })
  );
  self.skipWaiting();
});

// Activation du Service Worker
self.addEventListener('activate', event => {
  console.log('[ServiceWorker] Activation...');
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            console.log('[ServiceWorker] Suppression ancien cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    }).then(() => {
      console.log('[ServiceWorker] Activation terminée');
    })
  );
  self.clients.claim();
});

// Stratégie de cache: Network First avec fallback Cache
self.addEventListener('fetch', event => {
  const request = event.request;
  
  // Ignorer les requêtes non-GET
  if (request.method !== 'GET') {
    return;
  }
  
  // Ignorer les requêtes API et actions
  if (request.url.includes('/api/') || request.url.includes('/actions/')) {
    return;
  }
  
  // Ignorer les extensions Chrome, etc.
  if (request.url.startsWith('chrome-extension://')) {
    return;
  }

  event.respondWith(
    fetch(request)
      .then(response => {
        if (!response || response.status !== 200 || response.type === 'opaque') {
          return response;
        }

        const responseToCache = response.clone();
        caches.open(CACHE_NAME).then(cache => {
          cache.put(request, responseToCache);
        });

        return response;
      })
      .catch(async () => {
        const cachedResponse = await caches.match(request);
        
        if (cachedResponse) {
          return cachedResponse;
        }

        if (request.mode === 'navigate' || 
            request.headers.get('accept')?.includes('text/html')) {
          const offlineResponse = await caches.match(OFFLINE_URL);
          if (offlineResponse) {
            return offlineResponse;
          }
        }

        return new Response('Contenu non disponible hors ligne', {
          status: 503,
          statusText: 'Service Unavailable',
          headers: new Headers({
            'Content-Type': 'text/plain'
          })
        });
      })
  );
});

// Notification push
self.addEventListener('push', event => {
  const options = {
    body: event.data ? event.data.text() : 'Nouvelle notification TrustPick',
    icon: SCOPE_PATH + 'assets/img/icon-192.png',
    badge: SCOPE_PATH + 'assets/img/icon-192.png',
    vibrate: [100, 50, 100],
    data: {
      dateOfArrival: Date.now(),
      primaryKey: 1
    },
    actions: [
      { action: 'explore', title: 'Voir', icon: SCOPE_PATH + 'assets/img/icon-192.png' },
      { action: 'close', title: 'Fermer' }
    ]
  };

  event.waitUntil(self.registration.showNotification('TrustPick', options));
});

// Clic sur notification
self.addEventListener('notificationclick', event => {
  event.notification.close();
  if (event.action === 'explore') {
    event.waitUntil(clients.openWindow(SCOPE_PATH + 'index.php?page=notifications'));
  }
});
JS;

file_put_contents(__DIR__ . '/service-worker.js', $serviceWorkerContent);

echo "✅ Fichiers PWA générés avec succès !\n";
echo "📂 Fichiers créés :\n";
echo "   - manifest.json (scope: {$scopePath})\n";
echo "   - service-worker.js (scope: {$scopePath})\n";
echo "\n🚀 L'application peut maintenant être installée !\n";