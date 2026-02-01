/**
 * TrustPick V2 - Service Worker
 * PWA installable sur iOS, Android, Windows, macOS, Linux
 * Version: 2.1.0
 */

const CACHE_NAME = 'trustpick-v2.1';
const OFFLINE_URL = '/TrustPick/public/offline.html';

// Ressources essentielles à mettre en cache
const ASSETS_TO_CACHE = [
  '/TrustPick/public/',
  '/TrustPick/public/index.php',
  '/TrustPick/public/index.php?page=home',
  '/TrustPick/public/offline.html',
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
        console.log('[ServiceWorker] Mise en cache des ressources essentielles');
        // Utiliser addAll avec gestion d'erreur individuelle
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
  // Activer immédiatement sans attendre
  self.skipWaiting();
});

// Activation du Service Worker
self.addEventListener('activate', event => {
  console.log('[ServiceWorker] Activation...');
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
  // Prendre le contrôle de toutes les pages immédiatement
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
    // Essayer d'abord le réseau
    fetch(request)
      .then(response => {
        // Vérifier que la réponse est valide
        if (!response || response.status !== 200 || response.type === 'opaque') {
          return response;
        }

        // Cloner la réponse pour le cache
        const responseToCache = response.clone();

        // Mettre en cache de manière asynchrone
        caches.open(CACHE_NAME).then(cache => {
          cache.put(request, responseToCache);
        });

        return response;
      })
      .catch(async () => {
        // En cas d'erreur réseau, chercher dans le cache
        const cachedResponse = await caches.match(request);

        if (cachedResponse) {
          return cachedResponse;
        }

        // Si c'est une navigation, afficher la page hors-ligne
        if (request.mode === 'navigate' || request.headers.get('accept')?.includes('text/html')) {
          const offlineResponse = await caches.match(OFFLINE_URL);
          if (offlineResponse) {
            return offlineResponse;
          }
        }

        // Retourner une réponse d'erreur générique
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
    icon: '/TrustPick/public/assets/img/icon-192.png',
    badge: '/TrustPick/public/assets/img/icon-192.png',
    vibrate: [100, 50, 100],
    data: {
      dateOfArrival: Date.now(),
      primaryKey: 1
    },
    actions: [
      { action: 'explore', title: 'Voir', icon: '/TrustPick/public/assets/img/icon-192.png' },
      { action: 'close', title: 'Fermer' }
    ]
  };

  event.waitUntil(self.registration.showNotification('TrustPick', options));
});

// Clic sur notification
self.addEventListener('notificationclick', event => {
  event.notification.close();
  if (event.action === 'explore') {
    event.waitUntil(clients.openWindow('/TrustPick/public/index.php?page=notifications'));
  }
});
