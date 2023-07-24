import {installServiceWorker} from 'https://api.tourism-system.rc-preprod.com/v2/notif/client.mjs';

if(await installServiceWorker(new URL('../woody-sw.js', import.meta.url))){
    //TODO: Ecouter le broadcast channel pour savoir quand demander la permission d'envoyer les notifications
    // if(await installPwa(new URL('../woody-sw.js', import.meta.url))){
    //TODO: Afficher la modal

    // };
};



const notificationPermission = new BroadcastChannel('notification_permission');
notificationPermission.addEventListener('message', event => {
    console.log(event);
    //Display modal + button
    //TODO: au clic appel demande de permission
});

const installPermission = new BroadcastChannel('install_permission');
notificationPermission.addEventListener('message', event => {
    if (window.innerWidth < 1024) {
        console.log(event);
        displayBanner();
    }
});

function displayBanner(){
    let pwaInstallBanner = document.getElementById('pwaInstallBanner');
    pwaInstallBanner.classList.remove('invisible');
    document.getElementById('closePwaInstall').addEventListener('click', closeBanner);
    document.getElementById('triggerPwaInstall').addEventListener('click', displayBanner); display == methode jerome
}


    function closeBanner() {
    // mask modal
    document.getElementById('pwaInstallBanner').remove();
}
