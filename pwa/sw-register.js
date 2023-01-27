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

        let ua = window.navigator.userAgent;
        let iOS = !!ua.match(/iPad/i) || !!ua.match(/iPhone/i);

        if(iOS){
            window.addEventListener('DOMContentLoaded', () => {
                let lang = document.documentElement.lang.substring(0, 2)
                let iOSSafari = iOS && !(/CriOS/).test(userAgent) && !(/FxiOS/).test(userAgent) && !(/OPiOS/).test(userAgent) && !(/mercury/).test(userAgent);
                howToInstallPwa(lang, iOSSafari);
                displayBanner();
            });
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

    function howToInstallPwa(lang, iOSSafari){

        let pwaInstallBanner = document.getElementById('pwaInstallBanner');
        let bannerDiv = document.createElement('div');
        pwaInstallBanner.classList.add('flex-container', 'flex-dir-column', 'aling-middle', 'align-center', 'tuto');
        bannerDiv.classList.add('flex-container', 'flex-dir-column', 'aling-middle', 'align-center', 'bg-white', 'text-center');
        bannerDiv.cssText += 'gap:20px; margin:20px;';

        if(iOSSafari){
            bannerDiv = safariBanner(bannerDiv, lang);
        } else {
            bannerDiv = iosBanner(bannerDiv, lang);
        }

        let deny = pwaInstallBanner.querySelector('#closePwaInstall');
        deny.remove();
        pwaInstallBanner.appendChild(bannerDiv);
        pwaInstallBanner.appendChild(deny);
        pwaInstallBanner.querySelector('.texts').remove();
        pwaInstallBanner.querySelector('#triggerPwaInstall').remove();
    }

    function iosBanner(bannerDiv, lang){
        let explainText = "A mobile application is available for this website.";
        let explainText2 = "To install it, open the Safari application ";
        let explainText3 = " and paste the site address in the url bar";

        if( lang == 'fr' ){
            explainText = "Une application mobile est disponible pour ce site web.";
            explainText2 = "Pour l'installer, ouvrez l'application Safari "
            explainText3 = " et collez l'adresse du site dans la barre d'url";
        } else if( lang == 'de'){
            explainText = "Für diese Website gibt es eine mobile Anwendung.";
            explainText2 = "Um sie zu installieren, öffnen Sie das Programm Safari ";
            explainText3 = " und fügen Sie die Adresse der Website in die URL-Leiste ein";
        } else if( lang == 'nl'){
            explainText = "Voor deze website is een mobiele applicatie beschikbaar.";
            explainText2 = "Om deze te installeren opent u de applicatie Safari ";
            explainText3 = " en plak het adres van de site in de url-balk";
        } else if( lang == 'es'){
            explainText = "Existe una aplicación móvil para este sitio web.";
            explainText2 = "Para instalarla, abra la aplicación Safari ";
            explainText3 = " y pegue la dirección del sitio en la barra de direcciones.";
        } else if( lang == 'it'){
            explainText = "Per questo sito web è disponibile un'applicazione mobile.";
            explainText2 = "Per installarla, aprire l'applicazione Safari ";
            explainText3 = " e incollare l'indirizzo del sito nella barra degli url";
        }

        let safariIcon = document.createElement('img');
        safariIcon.setAttribute('width', '24');
        safariIcon.setAttribute('height', '24');
        safariIcon.setAttribute('src', '/app/themes/woody-theme/pwa/icons/safari-icon.png');

        let explain = document.createElement('div');
        explain.appendChild(document.createTextNode(explainText));
        explain.appendChild(document.createElement('br'));
        explain.appendChild(document.createTextNode(explainText2));
        explain.appendChild(safariIcon);
        explain.appendChild(document.createTextNode(explainText3));

        bannerDiv.appendChild(explain);

        return bannerDiv;
    }

    function safariBanner(bannerDiv, lang){
        let explainText = "A mobile application is available for this website.";
        let explainText2 = "To install it, click on the icon ";
        let explainText3 = " at the bottom of your screen.";
        let explainText4 = "In the available options, select \"On the home screen\". The application will install directly on your phone";

        if( lang == 'fr' ){
            explainText = "Une application mobile est disponible pour ce site web.";
            explainText2 = "Pour l'installer, cliquez sur l'icone ";
            explainText3 = " en bas de votre écran.";
            explainText4 = "Dans la liste des options disponibles, sélectionnez \"Sur l'écran d'accueil\". L'application s'installera directement sur votre téléphone";
        } else if( lang == 'de'){
            explainText = "Für diese Website gibt es eine mobile Anwendung.";
            explainText2 = "Zur Installation klicken Sie auf das Symbol ";
            explainText3 = " am unteren Rand Ihres Bildschirms.";
            explainText4 = "Wählen Sie unter den verfügbaren Optionen \"Auf dem Startbildschirm\". Die Anwendung wird direkt auf Ihrem Telefon installiert";
        } else if( lang == 'nl'){
            explainText = "Voor deze website is een mobiele applicatie beschikbaar.";
            explainText2 = "Om het te installeren, klikt u op het pictogram ";
            explainText3 = " onderaan uw scherm.";
            explainText4 = "In de beschikbare opties selecteert u \"Op het beginscherm\". De applicatie wordt direct op uw telefoon geïnstalleerd";
        } else if( lang == 'es'){
            explainText = "Existe una aplicación móvil para este sitio web.";
            explainText2 = "Para instalarla, pulse el icono ";
            explainText3 = " situado en la parte inferior de la pantalla.";
            explainText4 = "En las opciones disponibles, seleccione \"En la pantalla de inicio\". La aplicación se instalará directamente en su teléfono";
        } else if( lang == 'it'){
            explainText = "Per questo sito web è disponibile un'applicazione mobile.";
            explainText2 = "Per installarla, fare clic sull'icona ";
            explainText3 = " nella parte inferiore dello schermo.";
            explainText4 = "Nelle opzioni disponibili, selezionare \"Sulla schermata iniziale\". L'applicazione verrà installata direttamente sul telefono";
        }

        let shareIcon = document.createElement('img');
        shareIcon.setAttribute('width', '24');
        shareIcon.setAttribute('height', '24');
        shareIcon.setAttribute('src', '/app/themes/woody-theme/pwa/icons/apple-share.svg');

        let explain = document.createElement('div');
        explain.appendChild(document.createTextNode(explainText));
        explain.appendChild(document.createElement('br'));
        explain.appendChild(document.createTextNode(explainText2));
        explain.appendChild(shareIcon);
        explain.appendChild(document.createTextNode(explainText3));

        let onHomeScreen = document.createElement('img');
        onHomeScreen.setAttribute('width', '280');
        onHomeScreen.setAttribute('height', '71');
        onHomeScreen.setAttribute('src', '/app/themes/woody-theme/pwa/icons/on-home-screen.jpg');
        onHomeScreen.setAttribute('style', 'margin:20px auto;');

        bannerDiv.appendChild(explain);
        bannerDiv.appendChild(onHomeScreen);
        bannerDiv.appendChild(document.createTextNode(explainText4));

        return bannerDiv;
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
