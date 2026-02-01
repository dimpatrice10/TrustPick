/**
 * TrustPick - Service Worker Ultra Simple
 * Version: 2.3.2 - Anti-Redirect
 */

const CACHE_NAME = 'trustpick-v2.3.2';
const OFFLINE_URL = '/offline.html';

// Ressources minimales
const ASSETS = [
  '/',
  '/index.php',
  '/offline.html'
];

// Installation simple
self.addEventListener('install', event => {
  console.log('[SW] Install v2.3.2');
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        return cache.addAll(ASSETS);
      })
      .catch(err => {
        console.log('[SW] Cache failed:', err);
      })
  );
  self.skipWaiting();
});

// Activation simple
self.addEventListener('activate', event => {
  console.log('[SW] Activate');
  event.waitUntil(
    caches.keys().then(names => 
      Promise.all(names.map(name => name !== CACHE_NAME ? caches.delete(name) : null))
    )
  );
  self.clients.claim();
});

// Fetch basique
self.addEventListener('fetch', event => {
  if (event.request.method !== 'GET') return;
  if (event.request.url.includes('/api/')) return;
  if (event.request.url.includes('/actions/')) return;
  
  event.respondWith(
    fetch(event.request)
      .then(response => response)
      .catch(() => {
        return caches.match(event.request).then(cached => {
          return cached || new Response('Offline', {status: 503});
        });
      })
  );
});