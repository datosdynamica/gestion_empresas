const CACHE_NAME = 'gestion-empresas-v3';
const APP_SHELL = [
    '/administrativo/login',
    '/administrativo/panel',
    '/administrativo/public/assets/css/app.css',
    '/administrativo/public/assets/js/app.js',
    '/administrativo/public/assets/img/logo-dynamica.jpeg',
    '/administrativo/public/assets/pwa/icon-192.png',
    '/administrativo/public/assets/pwa/icon-512.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(APP_SHELL)).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key)))
        ).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;

    if (request.method !== 'GET') {
        return;
    }

    const requestUrl = new URL(request.url);
    if (!['http:', 'https:'].includes(requestUrl.protocol)) {
        return;
    }

    event.respondWith(
        fetch(request)
            .then((response) => {
                if (!response || response.status !== 200 || response.type === 'opaque') {
                    return response;
                }

                const cloned = response.clone();
                caches.open(CACHE_NAME).then((cache) => cache.put(request, cloned)).catch(() => {});
                return response;
            })
            .catch(() => caches.match(request).then((cached) => cached || caches.match('/administrativo/login')))
    );
});
