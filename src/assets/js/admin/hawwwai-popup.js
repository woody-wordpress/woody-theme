import $ from 'jquery';

var appendEditor = function() {
    if ($('#acf-group_5b33890e6fa0b .acf-field-5b33902f31b18').length != 0) {
        clearInterval(appendEditorIntval);
        $('.acf-field-5b33902f31b18').click(function() {
            var $this = $(this),
                // On récupère la valeur du champ conf_id pour la passer à l'éditeur le cas échéant
                conf_id = $this.siblings('.acf-field-5b338ff331b17').find('input[type="text"]').attr('value'),
                // On vérifie si l'élément et enfant du champ 'section' (acf-field-5afd2c6916ecb) pour définir la variable de contexte
                field_parents = $this.parents(),
                is_section = field_parents.find('.acf-field-5afd2c6916ecb').length;

            if (is_section == 1) {
                var context = 'playlist_block';
                var playlist_name = '';
            } else {
                var context = 'playlist_page',
                    page_title = $('#post-body input[name="post_title"]').attr('value'),
                    playlist_name = 'WP - Playlist ' + page_title;
            }

            // Log des résultats des variables : OK
            // console.info('Contexte : ' + context + ', conf_id : ' + conf_id + ', Playlist name : ' + playlist_name);

            // Construction de l'iframe de l'éditeur de playlist / Url différente si l'on a un conf_id ou pas
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

            // Ajout de l'iframe au DOM si 1st click, sinon, class de visibilité
            if ($('.playlist-editor').length == 0) {
                $('#acf-group_5b33890e6fa0b').append(iframe);
            } else {
                $('.playlist-editor').removeClass('closed').addClass('opened');
            }

            // On masque l'iframe au click sur le bouton "close"
            $('.close-playlist-editor').click(function() {
                $('.playlist-editor').removeClass('opened').addClass('closed');
            });

        });
    }
}

var appendEditorIntval = setInterval(appendEditor, 100);
