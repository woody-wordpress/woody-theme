// Register SW
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/woody-sw.js').then(function(reg) {
        console.log('Registration succeeded. Scope is ' + reg.scope);
    }).catch(function(error) {
        console.log('Registration failed with ' + error);
    });
}

const isPWA = ['fullscreen', 'standalone', 'minimal-ui'].some((displayMode) => window.matchMedia('(display-mode: ' + displayMode + ')').matches);
if (!isPWA) {
    // check if user has already refused to install PWA
    let refused = false;
    const cookieName = 'pwarefused';
    document.cookie.split(';').forEach((cookie) => {
    if (cookie.includes(cookieName)) {
        refused = true;
    }
    });

    if (!refused) {
        window.addEventListener('appinstalled', () => {
            console.log('app has been installed on desktop !');
            // dans ce cas ne pas afficher la modal
        });

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            // Afficher la modal ici
        });
    }
}

function closeBanner() {
    // mask modal 

    // set cookie if refused do not ask again.
    const date = new Date();
    date.setTime(date.getTime() + 365 * 24 * 60 * 60 * 1000);
    let expires = 'expires' + date.toUTCString();
    document.cookie = 'pwarefused' + '=1;' + expires + ';path=/';
}