const cacheName = "app_woody";
const appShellFiles = [
  // e.g  '/src/assets/apis/Booking.png',
];

// add new asset to cache when Sw is intall
self.addEventListener('install', (e) => {
console.warn('[Service Worker] Install');
e.waitUntil(() => {
        precache();
        self.skipWaiting();
    });
});

// When activate clear old cache
self.addEventListener('activate', (e) => {
    console.warn('[Service Worker] activate');
    e.waitUntil(
        caches.keys().then((keyList) => {
            return Promise.all(
                keyList.map((key) => {
                    if (key === cacheName) {
                        return;
                    }
                    return caches.delete(key);
                }),
            );
        }),
    );
});

// Open a cache and use `addAll()` with an array of assets to add all of them
// to the cache. Return a promise resolving when all the assets are added.
function precache() {
    return caches.open(cacheName).then(function (cache) {
        return cache.addAll(appShellFiles);
    });
}
