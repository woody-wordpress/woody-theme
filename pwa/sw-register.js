// Register SW
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/woody-sw.js').then(function(reg) {
    }).catch(function(error) {
        console.log('Registration failed with ' + error);
    });
}
let deferredPrompt = null;
const isPWA = ['standalone'].some((displayMode) => window.matchMedia('(display-mode: ' + displayMode + ')').matches);
if (!isPWA && window.innerWidth < 1024) {
    console.log('Not PWA');
    console.log('Mobile device');

    // check if user has already refused to install PWA
    let refused = false;
    const cookieName = 'pwarefused';
    document.cookie.split(';').forEach((cookie) => {
    if (cookie.includes(cookieName)) {
        refused = true;
    }
    });

    if (!refused) {

        console.log('check if app is installed');

        window.addEventListener('appinstalled', () => {
            console.log('App is installed');
            document.getElementById('pwaInstallBanner').remove();
        });

        let iOS = !!window.navigator.userAgent.match(/iPad/i) || !!window.navigator.userAgent.match(/iPhone/i);

        if(iOS){
            console.log('is iOS');
            displayBanner(null, iOS);
        } else {
            console.log('isn\'t iOS');
            window.addEventListener('beforeinstallprompt', (deferredPrompt) => {
                console.log('beforeinstallprompt');
                deferredPrompt.preventDefault();
                displayBanner(deferredPrompt);
            });
        }

    }

    function displayBanner(deferredPrompt = null, iOS = false){
        console.log('displayBanner');
        window.addEventListener('DOMContentLoaded', () => {

            console.log('DOMContentLoaded');

            let pwaInstallBanner = document.getElementById('pwaInstallBanner');

            console.log(pwaInstallBanner);

            pwaInstallBanner.classList.remove('invisible');
            document.getElementById('closePwaInstall').addEventListener('click', closeBanner);

            if(iOS){
                let iOSSafari = iOS && window.safari !== undefined;
                pwaInstallBanner.querySelector('#triggerPwaInstall').remove();
                if(iOSSafari){
                    pwaInstallBanner.querySelector('.notSafari').remove();
                }
            } else {
                pwaInstallBanner.querySelector('.iosOnly').remove();
            }

            if(deferredPrompt){
                console.log('Bind triggerPwaInstall');
                document.getElementById('triggerPwaInstall').addEventListener('click', function(){
                    installPWA(deferredPrompt)
                });
            }
        });
    }

    function installPWA(deferredPrompt) {
        deferredPrompt.prompt();
    }

    function closeBanner() {
        // mask modal
        document.getElementById('pwaInstallBanner').remove();

        // set cookie if refused do not ask again.
        const date = new Date();
        date.setTime(date.getTime() + 365 * 24 * 60 * 60);
        let expires = 'expires' + date.toUTCString();
        document.cookie = 'pwarefused' + '=1;' + expires + ';path=/';
    }
}
