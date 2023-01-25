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
            pwaBanner.remove();
        });

        if(!window.MSStream && /iPad|iPhone|iPod/.test(navigator.userAgent)){
            let lang = document.documentElement.lang.substring(0, 2)
            let isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
            howToInstallPwa(lang, isSafari);
            displayBanner();
        } else {
            window.addEventListener('beforeinstallprompt', (deferredPrompt) => {
                e.preventDefault();
                displayBanner(deferredPrompt);
            });
        }

    }

    function displayBanner(deferredPrompt = null){
        document.getElementById('pwaInstallBanner').classList.remove('invisible');
        document.getElementById('closePwaInstall').addEventListener('click', closeBanner);

        if(deferredPrompt){
            document.getElementById('triggerPwaInstall').addEventListener('click', function(){
                installPWA(deferredPrompt)
            });
        }
    }

    function howToInstallPwa(lang, isSafari){

        let text = '';

        if( lang == 'fr' ){
            console.log('Lang is fr');
            if(isSafari){
                text = "Pour installer l'application, cliquez sur l'icone de partage puis \"Sur l'écran d'accueil\""
            } else {
                text = "Pour installer l'application, ouvrez le site avec Safari, cliquez sur l'icone de partage puis \"Sur l'écran d'accueil\""
            }
        } else if( lang == 'de'){
            if(isSafari){
                text = "Um die Anwendung zu installieren, klicken Sie auf das Teilen-Symbol und dann auf \"Auf dem Startbildschirm\""
            } else {
                text = "Um die Anwendung zu installieren, öffnen Sie die Website mit Safari, klicken Sie auf das Teilen-Symbol und dann auf \"Auf dem Startbildschirm\""
            }
        } else if( lang == 'nl'){
            if(isSafari){
                text = "Om de applicatie te installeren, klik op het share icoontje en vervolgens op \"Op het beginscherm\""
            } else {
                text = "Om de toepassing te installeren, opent u de site met Safari, klikt u op het deelpictogram en vervolgens op \"Op het beginscherm\""
            }
        } else if( lang == 'es'){
            if(isSafari){
                text = "Para instalar la aplicación, haga clic en el icono de compartir y luego en \"En la pantalla de inicio\""
            } else {
                text = "Para instalar la aplicación, abra el sitio en Safari, haga clic en el icono de compartir y luego en \"En la pantalla de inicio\""
            }
        } else if( lang == 'it'){
            if(isSafari){
                text = "Per installare l'applicazione, fare clic sull'icona di condivisione e poi su \"Sulla schermata iniziale\""
            } else {
                text = "Per installare l'applicazione, aprire il sito in Safari, fare clic sull'icona di condivisione e poi su \"Sulla schermata iniziale\""
            }
        } else {
            if(isSafari){
                text = "To install the application, click on the share icon and then \"To the home screen\""
            } else {
                text = "To install the application, open the site in Safari, click on the share icon and then \"To the home screen\""
            }
        }

        document.getElementById('pwaInstallBanner').querySelector('.texts').innerHTML = text;
        document.getElementById('triggerPwaInstall').remove();
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
