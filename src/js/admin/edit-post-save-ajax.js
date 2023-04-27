import $, { timers } from 'jquery';

function getCookie(name) {
    let cookie = {};
    document.cookie.split(';').forEach(function(el) {
        let [k,v] = el.split('=');
        cookie[k.trim()] = v;
    });
    return cookie[name];
}

function handleErrors(response){
    if (!response.ok) {
        throw Error(response.statusText);
    }
    console.log(response);
    return response;
}

/**
 * Save a post without page reload using AJAX
 * @param {MouseEvent} e Click Event received by the listener
 * @param {Boolean} publish Publish if original post status & post status are "draft"
 */
const savePost = (e, publish) => {
    const form = document.querySelector('#post');
    const data = new FormData(form);

    let spinner = publish ?
        document.querySelector('#publishing-action>.spinner') :
        document.querySelector('#save-action>.spinner');
    const originalPostStatus = document.querySelector('*[name="original_post_status"]').value;
    const postStatus = document.querySelector('*[name="post_status"]').value;
    if (spinner) {
        spinner.classList.add('is-active');
        spinner.style.display = 'inline-block';
    }

    if ((publish && postStatus === 'draft' && originalPostStatus === 'draft') || postStatus !== originalPostStatus) return;

    e.preventDefault();

    fetch(form.getAttribute("action"), {
        method: 'POST',
        body: data,
        redirect: 'follow',
        headers: {
            'Access-Control-Allow-Headers': 'Location'
        }
    })
    .then(res => {
        deleteNotices();

        if (res.url.includes('wp-login')) {
            if (spinner) spinner.classList.remove('is-active');
            createNotice('notice-error', 'Impossible d\'enregistrer la page, d\'après vos cookies, vous êtes déconnecté. Ne quittez pas cette page ou vos modifications seront perdues.</br>Pas de panique, il suffit de vous reconnecter à partir d\'un autre onglet puis d\'enregistrer cette page.');
            return;
        }

        if(res.status == 200){
            if (spinner) spinner.classList.remove('is-active');
            createNotice('notice-success', 'Page mise à jour. Vos modifications ont été enregistrées correctement');

            // Reset unload event on ACF fields
            acf.unload.reset();

            // Reset beforeunload event with vanilla
            window.addEventListener('beforeunload', () => false);
            // Reset beforeunload event with jquery
            $(window).off('beforeunload');
        } else {
            createNotice('notice-error', 'Une erreur s\'est produite.</br>Code erreur : ' + res.status + ' - Statut : ' + res.statusText + '</br>Dans le cas d\'une erreur 500 Internal Server Error, vérifiez que vous êtes toujours connecté à internet');
        }

    }).catch(err => {
        console.log(err);
        deleteNotices();
        if (spinner) spinner.classList.remove('is-active');
        createNotice('notice-error', 'Vos modification n\'ont pas pu être enregistrées en raison d\'une erreur.</br>Code erreur : ' + err.status + ' - Statut : ' + err.statusText);
    });
};

const deleteNotices = () => {
    let notices = document.querySelectorAll('#wpbody-content>.wrap>.custom-notice');
    if(notices.length > 0){
        notices.forEach(notice => {
            notice.remove();
        });
    }
}

/**
 * Creates a notice and displays it below "wp-header-end".
 * @param {String} type Notice type [(See documentation)](https://developer.wordpress.org/reference/hooks/admin_notices/)
 * @param {String} message Notice message to display.
 */
const createNotice = (type, message) => {
    let notice = document.createElement('div');
    notice.classList.add('custom-notice', 'is-fixed');
    notice.classList.add(type);
    notice.innerHTML = `
    <p>${message}</p>
    <button type="button" class="notice-dismiss">
      <span class="screen-reader-text">Dismiss this notice.</span>
    </button>`;
    notice.querySelector('.notice-dismiss').addEventListener('click', () => {
        notice.parentNode.removeChild(notice);
    });

    if (type !== 'notice-error') {
        setTimeout(() => {
            notice.animate([
                { transform: 'translateY(0)' },
                { transform: 'translateY(-100%)' }
            ], { duration: 300, iterations: 1, easing: 'ease' });
            setTimeout(() => { notice.remove(); }, 300);
        }, 10000);
    }
    document.querySelector('.wp-header-end').after(notice);
}

if (!$('body').hasClass('post-type-acf-field-group')) { // Fix for acf field groups
    if (document.getElementById('publish')) {
        document.getElementById('publish').addEventListener('click', e => { savePost(e, true); });
    }
    if (document.getElementById('save-post')) {
        document.getElementById('save-post').addEventListener('click', e => { savePost(e, false); });
    }

    window.onbeforeunload = null;
}

