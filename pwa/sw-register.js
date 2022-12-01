// Register SW
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/woody-sw.js').then(function(reg) {
        console.log('Registration succeeded. Scope is ' + reg.scope);
    }).catch(function(error) {
        console.log('Registration failed with ' + error);
    });
}
let deferredPrompt = null;
const isPWA = ['standalone'].some((displayMode) => window.matchMedia('(display-mode: ' + displayMode + ')').matches);
console.log({'isPwa' : isPWA});
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
        console.log('not refused by cookie');

        window.addEventListener('appinstalled', () => {
            console.log('app has been installed on desktop !');
            document.getElementById('pwaInstallBanner').remove();
        });

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            if(window.innerWidth < 1024){
                console.log('We are on mobile, show that banner');
                document.getElementById('pwaInstallBanner').classList.remove('invisible');
                document.getElementById('closePwaInstall').addEventListener('click', closeBanner);
                document.getElementById('triggerPwaInstall').addEventListener('click', installPWA);
            }
        });
    }
}

function installPWA() {
    console.log('Install app');

    deferredPrompt.prompt();
}

function closeBanner() {
    console.log('Close banner');

    // mask modal
    document.getElementById('pwaInstallBanner').remove();

    // set cookie if refused do not ask again.
    const date = new Date();
    date.setTime(date.getTime() + 365 * 24 * 60 * 60);
    let expires = 'expires' + date.toUTCString();
    document.cookie = 'pwarefused' + '=1;' + expires + ';path=/';
}
