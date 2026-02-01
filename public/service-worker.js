/**
 * TrustPick V2 - Service Worker
 * Pour PWA installable sur iOS et Android
 */

const CACHE_NAME = 'trustpick-v2';
const ASSETS_TO_CACHE = [
  '/TrustPick/public/',
  '/TrustPick/public/index.php',
  '/TrustPick/public/assets/css/app.css',
  '/TrustPick/public/assets/css/ui-enhancements.css',
  '/TrustPick/public/assets/js/app.js',
  '/TrustPick/public/assets/img/icon-192.png',
  '/TrustPick/public/assets/img/icon-512.png'
];

// Installation du Service Worker
self.addEventListener('install', event => {
  console.log('[ServiceWorker] Installation...');
  event.waitUntil(
    caches
      .open(CACHE_NAME)
      .then(cache => {
        console.log('[ServiceWorker] Mise en cache des ressources');
        return cache.addAll(ASSETS_TO_CACHE);
      })
      .catch(err => {
        console.log('[ServiceWorker] Erreur de cache:', err);
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
    })
  );
  self.clients.claim();
});

// Stratégie de cache: Network First, Cache Fallback
self.addEventListener('fetch', event => {
  // Ignorer les requêtes non-GET et les requêtes API
  if (event.request.method !== 'GET' || event.request.url.includes('/api/')) {
    return;
  }

  event.respondWith(
    fetch(event.request)
      .then(response => {
        // Mettre en cache la réponse fraîche
        if (response.status === 200) {
          const responseClone = response.clone();
          caches.open(CACHE_NAME).then(cache => {
            cache.put(event.request, responseClone);
          });
        }
        return response;
      })
      .catch(() => {
        // En cas d'erreur réseau, utiliser le cache
        return caches.match(event.request).then(cachedResponse => {
          if (cachedResponse) {
            return cachedResponse;
          }
          // Page hors ligne par défaut
          if (event.request.headers.get('accept').includes('text/html')) {
            return caches.match('/TrustPick/public/');
          }
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
