import $ from 'jquery';

$('.acf-field-5b33902f31b18').click(function() {
    var $this = $(this),
        // On vérifie si l'élément et enfant du champ 'section' (acf-field-5afd2c6916ecb) pour définir la variable de contexte
        field_parents = $this.parents(),
        is_section = field_parents.find('.acf-field-5afd2c6916ecb').length,
        // On récupère la valeur du champ conf_id pour la passer à l'éditeur le cas échéant
        conf_id = $this.siblings('.acf-field-5b338ff331b17').find('input[type="text"]').attr('value');

    if (is_section == 1) {
        var context = 'playlist_block';
    } else {
        var context = 'playlist_page';
    }

    // Log des résultats des variables : OK
    console.info('Contexte : ' + context + ', conf_id : ' + conf_id);

    // Construction de l'iframe
    var editorUrl = 'https://monyssb.com/',
        iframe = '<div class="playlist-editor" data-role="popup"><iframe src="' + editorUrl + '"><p>Your browser does not support iframes.</p></iframe></div>';

    $('#acf-group_5b33890e6fa0b').append(iframe);
    // $('.playlist-editor').addClass('active');

});
