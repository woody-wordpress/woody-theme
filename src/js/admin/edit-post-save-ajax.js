!(function ($) {
    // function getCookie(name) {
    //     let cookie = {};
    //     document.cookie.split(';').forEach(function (el) {
    //         let [k, v] = el.split('=');
    //         cookie[k.trim()] = v;
    //     });
    //     return cookie[name];
    // }

    // function handleErrors(response) {
    //     if (!response.ok) {
    //         throw Error(response.statusText);
    //     }
    //     console.log(response);
    //     return response;
    // }

    /**
     * Save a post without page reload using AJAX
     * @param {MouseEvent} e Click Event received by the listener
     * @param {Boolean} publish Publish if original post status & post status are "draft"
     */
    const savePost = (e, publish) => {
        const form = document.querySelector('#post');
        // const requiredFields = form.querySelectorAll('.is-required'); // Champs obligatoires
        const data = new FormData(form);
        // let isValid = true;
        // let invalidField;

        // On parcours les champs obligatoires et on s'arrête si un n'est pas rempli
        // for (let i = 0; i < requiredFields.length; i++) {
        //     const field = requiredFields[i].querySelector('.acf-input select') ? requiredFields[i].querySelector('.acf-input select') : requiredFields[i].querySelector('.acf-input input[type="text"]');
        //     if (field.value === '') {
        //         isValid = false;
        //         invalidField = requiredFields[i];
        //         break;
        //     }
        // }

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

        // Si une erreur est déjà affichée on l'enève
        const fieldErrorNotices = document.querySelectorAll('.acf-notice.acf-error-message');
        if (fieldErrorNotices.length > 0) {
            fieldErrorNotices.forEach(errorNotice => errorNotice.remove());
        }

        // Si un champs obligatoire n'a pas été rempli
        // if (!isValid) {
        //     // Arrêt du spinner
        //     if (spinner) spinner.classList.remove('is-active');
        //     // Ajout de la notice d'erreur en haut de l'écran
        //     createNotice('notice-error', `Impossible d\'enregistrer la page, un champ obligatoire nécessite votre attention.`);
        //     invalidFieldNotice(invalidField);

        //     // Ouverture des éléments pour repérer facilement l'erreur
        //     // Bloc
        //     while (invalidField !== null && !invalidField.classList.contains('acf-row')) {
        //         if (invalidField.classList.contains('layout')) {
        //             invalidField.classList.remove('-collapsed');
        //         }

        //         invalidField = invalidField.parentElement;
        //     }

        //     // Section
        //     if (invalidField.classList.contains('-collapsed')) {
        //         invalidField.classList.remove('-collapsed');
        //     }

        //     return;
        // }

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

                if (res.status == 200) {
                    deleteNotices();
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

    // const invalidFieldNotice = (field) => {
    //     let errorDetails = document.createElement("div");
    //     errorDetails.classList.add("acf-notice", "-error", "acf-error-message");
    //     errorDetails.innerHTML = "<p>Ce champ est obligatoire</p>";
    //     return field.querySelector('.acf-label').appendChild(errorDetails);
    // }

    const deleteNotices = () => {
        let notices = document.querySelectorAll('#wpbody-content>.wrap>.custom-notice');
        if (notices.length > 0) {
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
})(jQuery);
