import {installServiceWorker, subscribeNotif, installPwa} from 'https://api.tourism-system.rc-preprod.com/v2/notif/client.mjs';

if(await installServiceWorker(new URL('../woody-sw.js', import.meta.url))){
    console.log('Service worker is running');
    if(await installPwa(new URL('../woody-sw.js', import.meta.url))){
        console.log('PWA is installed');
    }
};

const notificationPermission = new BroadcastChannel('notification_permission');
notificationPermission.addEventListener('message', event => {
    displayBanner('pwaNotifBanner', subscribeNotif);
});

const installPermission = new BroadcastChannel('install_permission');
notificationPermission.addEventListener('message', event => {
    if (window.innerWidth < 1024) {
        displayBanner('pwaInstallBanner', installPwa);
    }
});

function displayBanner(bannerID, triggerID, method){
    let pwaBanner = document.getElementById(bannerID);
    pwaInstallBanner.classList.remove('invisible');
    document.getElementById('closePwaInstall').addEventListener('click', function(){
        document.getElementById(bannerID).remove();
    });

    document.getElementById(triggerID).addEventListener('click', function(){
        document.getElementById(bannerID).remove();
        method;
    });
}
