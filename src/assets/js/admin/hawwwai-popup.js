import $ from 'jquery';

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
    console.info('Contexte : ' + context + ', conf_id : ' + conf_id + ', Playlist name : ' + playlist_name);
    // Construction et affichage de l'iframe de l'éditeur de playlist
    if (conf_id.length == 0) {
        var editorUrl = 'https://api.tourism-system.rc-preprod.com/render/facetconfs/choix-playlist/crt-reunion/fr?context=' + context + '&name=' + playlist_name;
    } else {
        var editorUrl = 'https://api.tourism-system.rc-preprod.com/render/facetconfs/cles-config/' + conf_id + '/crt-reunion/fr';
    }
    var iframe = '<div class="playlist-editor" data-role="popup"><iframe src="' + editorUrl + '"><p>Your browser does not support iframes.</p></iframe><span class="close-iframe dashicons dashicons-no-alt"></span></div>';
    $('#acf-group_5b33890e6fa0b').append(iframe);

});
