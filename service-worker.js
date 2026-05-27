const CACHE_NAME = 'gestion-empresas-v1';
const APP_SHELL = [
    '/plugin/gestion_empresas/login',
    '/plugin/gestion_empresas/panel',
    '/plugin/gestion_empresas/public/assets/css/app.css',
    '/plugin/gestion_empresas/public/assets/js/app.js',
    '/plugin/gestion_empresas/public/assets/img/logo-dynamica.jpeg',
    '/plugin/gestion_empresas/public/assets/pwa/icon-192.png',
    '/plugin/gestion_empresas/public/assets/pwa/icon-512.png',
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

    event.respondWith(
        fetch(request)
            .then((response) => {
                const cloned = response.clone();
                caches.open(CACHE_NAME).then((cache) => cache.put(request, cloned));
                return response;
            })
            .catch(() => caches.match(request).then((cached) => cached || caches.match('/plugin/gestion_empresas/login')))
    );
});
