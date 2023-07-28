// import {installServiceWorker, installNotifications, installPwa} from '/api/v2/notif/client.mjs';
import {installServiceWorker, installNotifications, BroadcastChannels, requestPermission, NotificationPermissionStates, unsubscribe } from '/api/v2/notif/client.mjs';

if(await installServiceWorker(new URL('../woody-sw.js', import.meta.url))){
    console.log('Service worker is running');
    if(await installPwa(new URL('../woody-sw.js', import.meta.url))){
        console.log('PWA is installed');
        const installPrompt = new BroadcastChannel(BroadcastChannels.INSTALL_PROMPT);
        installPermission.addEventListener('message', event => {
            if (window.innerWidth < 1024) {
                displayBanner('pwaInstallBanner', 'install');
            }
        });
    }

    if(await installNotifications(new URL('../woody-sw.js', import.meta.url))){
        console.log('Got the permissions');
        const notificationPermission = new BroadcastChannel(BroadcastChannels.NOTIFICATION_PERMISSION);
        notificationPermission.addEventListener('message', event => {
            const data = event.data;
            if(data.need_permission){
                displayBanner('pwaNotifyBanner', 'subscribe');
            } else {
                removePermissions('pwaUnsubscribeBanner');
            }
        });
    }
};

function removePermissions(bannerID){
    let banner = document.getElementById(bannerID);
    banner.querySelector('.unsubscribe').addEventListener('click', async(e) => {

        unsubscribe();
    });
}

function displayBanner (bannerID, method){
    let banner = document.getElementById(bannerID);
    if(banner){
        banner.classList.remove('invisible');
        banner.querySelector('.dismiss').addEventListener('click', e => {
            e.preventDefault();
            banner.remove();
        });

        banner.querySelector('.confirm').addEventListener('click', async(e) => {
            e.preventDefault();
            if(method == 'subscribe'){
                // subscribeNotif();
                const permission = await requestPermission();
                if(permission === NotificationPermissionStates.GRANTED ){
                    //TODO: Récupérer la liste des Topics quans il y en aura plusieurs
                    notificationPermission.postMessage({ 'topics' : ['all'] });
                }
            } else if(method == 'install') {
                // installPwa();
            }
            banner.remove();
        });
    }
}


displayBanner('pwaInstallBanner', 'install');
// displayBanner('pwaNotifyBanner', 'subscribe');
