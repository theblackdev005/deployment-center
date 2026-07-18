const CACHE_VERSION = 'deploy-center-v2';
const OFFLINE_URL = '/offline.html';
const INSTALL_ASSETS = [
    OFFLINE_URL,
    '/manifest.webmanifest',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/icons/icon-maskable-512.png',
    '/icons/apple-touch-icon.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_VERSION)
            .then((cache) => cache.addAll(INSTALL_ASSETS))
            .then(() => self.skipWaiting()),
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((key) => key !== CACHE_VERSION).map((key) => caches.delete(key))))
            .then(() => self.clients.claim()),
    );
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') {
        return;
    }

    const requestUrl = new URL(event.request.url);

    if (event.request.mode === 'navigate') {
        event.respondWith(fetch(event.request).catch(() => caches.match(OFFLINE_URL)));
        return;
    }

    const isStaticAsset = requestUrl.origin === self.location.origin
        && (requestUrl.pathname.startsWith('/build/') || requestUrl.pathname.startsWith('/icons/'));

    if (isStaticAsset) {
        event.respondWith(
            caches.match(event.request).then((cached) => cached || fetch(event.request).then((response) => {
                if (response.ok) {
                    const copy = response.clone();
                    caches.open(CACHE_VERSION).then((cache) => cache.put(event.request, copy));
                }

                return response;
            })),
        );
    }
});
