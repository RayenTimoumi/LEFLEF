const CACHE_NAME = 'mairie-reservation-v1';
const urlsToCache = [
    '/',
    '/reservation.html',
    '/queue.html',
    '/styles.css',
    '/reservation-script.js',
    '/queue-script.js',
    '/logo.png'
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                return cache.addAll(urlsToCache);
            })
    );
});

self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => {
                return response || fetch(event.request);
            })
    );
});

self.addEventListener('push', event => {
    const data = event.data.json();
    const options = {
        body: data.body,
        icon: 'logo.png',
        badge: 'logo.png'
    };
    
    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});