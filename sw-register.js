// Register SW
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register(window.location.origin + '/app/themes/woody-theme/pwa/woody-sw.js');
}
