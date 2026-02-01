/**
 * TrustPick V2 - Service Worker
 * PWA installable sur Android, iOS et Desktop
 * Version: 2.1.0
 */

const CACHE_NAME = 'trustpick-v2.1';
const OFFLINE_URL = '/TrustPick/public/index.php?page=home';

// Ressources à mettre en cache lors de l'installation
const ASSETS_TO_CACHE = [
  '/TrustPick/public/',
  '/TrustPick/public/index.php',
  '/TrustPick/public/index.php?page=home',
  '/TrustPick/public/index.php?page=catalog',
  '/TrustPick/public/assets/css/app.css',
  '/TrustPick/public/assets/css/demo.css',
  '/TrustPick/public/assets/css/ui-enhancements.css',
  '/TrustPick/public/assets/js/app.js',
  '/TrustPick/public/assets/js/ui-enhancements.js',
  '/TrustPick/public/assets/js/pwa-install.js',
  '/TrustPick/public/assets/img/icon-192.png',
  '/TrustPick/public/assets/img/icon-512.png',
  '/TrustPick/public/assets/img/logo.png',
  '/TrustPick/public/manifest.json'
];

// Installation du Service Worker
self.addEventListener('install', event => {
  console.log('[ServiceWorker] Installation v2.1...');

  event.waitUntil(
    caches
      .open(CACHE_NAME)
      .then(cache => {
        console.log('[ServiceWorker] Mise en cache des ressources');
        // Cache progressif - ne pas bloquer si une ressource échoue
        return Promise.allSettled(
          ASSETS_TO_CACHE.map(url =>
            cache.add(url).catch(err => {
              console.log('[ServiceWorker] Impossible de cacher:', url, err);
            })
          )
        );
      })
      .then(() => {
        console.log('[ServiceWorker] Installation terminée');
      })
  );

  // Activer immédiatement sans attendre
  self.skipWaiting();
});

// Activation du Service Worker
self.addEventListener('activate', event => {
  console.log('[ServiceWorker] Activation v2.1...');

  event.waitUntil(
    caches
      .keys()
      .then(cacheNames => {
        return Promise.all(
          cacheNames.map(cacheName => {
            if (cacheName !== CACHE_NAME) {
              console.log('[ServiceWorker] Suppression ancien cache:', cacheName);
              return caches.delete(cacheName);
            }
          })
        );
      })
      .then(() => {
        console.log('[ServiceWorker] Activation terminée');
      })
  );

  // Prendre le contrôle immédiatement
  self.clients.claim();
});

// Stratégie de cache: Network First avec fallback Cache
self.addEventListener('fetch', event => {
  const request = event.request;

  // Ignorer les requêtes non-GET
  if (request.method !== 'GET') {
    return;
  }

  // Ignorer les requêtes vers d'autres domaines
  if (!request.url.startsWith(self.location.origin)) {
    return;
  }

  // Ignorer les requêtes API (toujours en réseau)
  if (request.url.includes('/api/') || request.url.includes('/actions/')) {
    return;
  }

  // Ignorer les extensions de développement
  if (request.url.includes('browser-sync') || request.url.includes('livereload')) {
    return;
  }

  event.respondWith(
    // Essayer le réseau d'abord
    fetch(request)
      .then(response => {
        // Vérifier si la réponse est valide
        if (!response || response.status !== 200 || response.type !== 'basic') {
          return response;
        }

        // Cloner la réponse pour la mettre en cache
        const responseToCache = response.clone();

        caches.open(CACHE_NAME).then(cache => {
          cache.put(request, responseToCache);
        });

        return response;
      })
      .catch(() => {
        // En cas d'erreur réseau, utiliser le cache
        return caches.match(request).then(cachedResponse => {
          if (cachedResponse) {
            return cachedResponse;
          }

          // Si c'est une page HTML, retourner la page hors-ligne
          if (request.headers.get('accept')?.includes('text/html')) {
            return caches.match(OFFLINE_URL);
          }

          // Sinon, retourner une réponse vide
          return new Response('', {
            status: 408,
            statusText: 'Request Timeout'
          });
        });
      })
  );
});
