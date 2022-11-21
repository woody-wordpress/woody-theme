// Register SW
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/woody-sw.js').then(function(reg) {
        window.caches.open('sitekey').then(cache => cache.add(window.siteConfig.site_key));
    }).catch(function(error) {
        console.log('Registration failed with ' + error);
      });
}
