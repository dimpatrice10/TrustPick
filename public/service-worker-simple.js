/**
 * TrustPick - Service Worker Minimal
 * Version: 2.3.0
 */

const CACHE_NAME = 'trustpick-v2.3';
const OFFLINE_URL = '/offline.html';

// Ressources de base à cacher
const ASSETS = [
  '/',
  '/index.php',
  '/offline.html',
  '/assets/css/app.css',
  '/assets/js/app.js',
  '/assets/img/icon-192.png',
  '/assets/img/icon-512.png'
];

// Installation
self.addEventListener('install', event => {
  console.log('[SW] Install');
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(ASSETS).catch(() => console.log('[SW] Cache failed')))
  );
  self.skipWaiting();
});

// Activation
self.addEventListener('activate', event => {
  console.log('[SW] Activate');
  event.waitUntil(
    caches.keys().then(names => Promise.all(names.map(name => (name !== CACHE_NAME ? caches.delete(name) : null))))
  );
  self.clients.claim();
});

// Fetch
self.addEventListener('fetch', event => {
  if (event.request.method !== 'GET') return;
  if (event.request.url.includes('/api/')) return;
  if (event.request.url.includes('/actions/')) return;

  event.respondWith(
    fetch(event.request)
      .then(response => {
        if (response.status === 200) {
          const responseClone = response.clone();
          caches.open(CACHE_NAME).then(cache => cache.put(event.request, responseClone));
        }
        return response;
      })
      .catch(() => {
        return caches.match(event.request).then(cached => {
          if (cached) return cached;
          if (event.request.headers.get('accept')?.includes('text/html')) {
            return caches.match(OFFLINE_URL);
          }
          return new Response('Offline', { status: 503 });
        });
      })
  );
});
