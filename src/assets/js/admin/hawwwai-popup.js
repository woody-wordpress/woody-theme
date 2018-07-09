import $ from 'jquery';

// Page de type palylist
var appendEditorPage = function() {
    // Si le bouton "Configurer ma playlist existe" :
    if ($('#acf-group_5b33890e6fa0b .acf-field-5b33902f31b18').length != 0) {
        clearInterval(appendEditorPageIntval);
        $('.acf-field-5b33902f31b18').click(function() {
            var $this = $(this),
                // On récupère la valeur du champ conf_id pour la passer à l'éditeur si elle est définie
                conf_id = $this.siblings('.acf-field-5b338ff331b17').find('input[type="text"]').attr('value'),
                context = 'playlist_page',
                page_title = $('input[name="post_title"]').val(),
                playlist_name = 'WP - Playlist ' + page_title;

            // Log des résultats des variables
            console.info('Contexte : ' + context + ', conf_id : ' + conf_id + ', Playlist name : ' + playlist_name);

            var iframe = iframeConstructor($this, conf_id, context, playlist_name);
            toggleIframe(iframe, $('#acf-group_5b33890e6fa0b'));
        });
    }
}

var appendEditorPageIntval = setInterval(appendEditorPage, 100);

// Bloc de playlist dans une section
var appendEditorBlock = function() {
    $('.acf-flexible-content .acf-field-5b33902f31b18').each(function() {
        var $this = $(this);
        $this.click(function() {
            // On récupère la valeur du champ conf_id pour la passer à l'éditeur le cas échéant
            var conf_id = $this.siblings('.acf-field-5b338ff331b17').find('input[type="text"]').attr('value'),
                context = 'playlist_block',
                page_title = $('input[name="post_title"]').val(),
                section_title = $this.parents('.acf-row').find('.acf-field-5b0d1dc8907e7 input').val(),
                playlist_name = 'WP - Bloc Playlist ' + page_title + ' - ' + section_title;

            // Log des résultats des variables
            console.info('Contexte : ' + context + ', conf_id : ' + conf_id + ', Playlist name : ' + playlist_name);

            var iframe = iframeConstructor($this, conf_id, context, playlist_name);

            toggleIframe(iframe, $this.parents('.layout'));

        });
    });
}

// Ajouter une section => on réinitailise la fonction interceptAddBlockClick
var interceptAddSectionClick = function() {
    $('.acf-field-5afd2c6916ecb .acf-button').click(function() {
        setTimeout(interceptAddBlockClick, 200);
    });
}

// Ajouter un bloc => on réinitailise la fonction interceptAcfFcPopUpClick
var interceptAddBlockClick = function() {
    $('.acf-field-5b043f0525968 .acf-button').click(function() {
        setTimeout(interceptAcfFcPopUpClick, 200);
    });
}

// Choisir un bloc de type playlist => on réinitailise la fonction appendEditorBlock
var interceptAcfFcPopUpClick = function() {
    $('.acf-fc-popup li a').click(function() {
        var $this = $(this);
        if ($this.attr('data-layout') == 'playlist_bloc') {
            // alert('The data layout :' + $this.attr('data-layout'));
            setTimeout(appendEditorBlock, 200);
        }
    });
}

// Ensemble des fonctions à lancer à l'initalisation de la page
var launch = function() {
    appendEditorBlock();
    interceptAddBlockClick();
    interceptAddSectionClick();
}
launch();

// Construction de l'iframe de l'éditeur de playlist / Url différente si l'on a un conf_id ou pas
function iframeConstructor($this, conf_id, context, playlist_name) {

    if (conf_id.length == 0) {
        var editorUrl = 'https://api.tourism-system.rc-preprod.com/render/facetconfs/choix-playlist/crt-reunion/fr?context=' + context + '&name=' + playlist_name + '&login=reunion_website&pwd=9f4f5a30';
        // eventListener => On récupère le conf_id que nous envoie l'éditeur
        window.addEventListener('message',
            function(e) {
                // if (e.origin !== editorUrl) {
                //     return;
                // }
                $this.siblings('.acf-field-5b338ff331b17').find('input[type="text"]').attr('value', e.data.conf_id);
            },
            false);
    } else {
        var editorUrl = 'https://api.tourism-system.rc-preprod.com/render/facetconfs/cles-config/' + conf_id + '/crt-reunion/fr?login=reunion_website&pwd=9f4f5a30';
    }

    var iframe = '<div class="playlist-editor" data-role="popup"><iframe src="' + editorUrl + '"><p>Your browser does not support iframes.</p></iframe><span class="close-playlist-editor dashicons dashicons-no-alt"></span></div>';

    return iframe;
}

// Ouverture et fermeture de l'iframe de l'éditeur
function toggleIframe(iframe, target) {
    // Ajout de l'iframe au DOM si 1st click, sinon, class de visibilité
    if ($('.playlist-editor').length == 0) {
        target.append(iframe);
    } else {
        $('.playlist-editor').removeClass('closed').addClass('opened');
    }

    // On masque l'iframe au click sur le bouton "close"
    $('.close-playlist-editor').click(function(e) {
        e.stopPropagation();
        $('.playlist-editor').removeClass('opened').addClass('closed');
    });
}
