const CACHE_NAME = 'fuel-budget-pwa-v1';

self.addEventListener('install', (event) => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(self.clients.claim());
});

self.addEventListener('fetch', (event) => {
    // We strictly use network-first approach for live dashboard data.
    // This fetch handler is primarily here to satisfy PWA installability requirements.
    event.respondWith(
        fetch(event.request).catch(() => {
            return new Response('Offline. Please check your connection.', {
                status: 503,
                statusText: 'Service Unavailable',
                headers: new Headers({ 'Content-Type': 'text/plain' })
            });
        })
    );
});
