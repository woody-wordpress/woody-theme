import $ from 'jquery';

// Corrige le problème de fermeture / ouverture des champs ACF lors de la création d'un nouveau post
$('body.post-new-php').each(function() {
    $('#post').each(function () {
        $('#post-body-content').on('click', '.postbox .handle-actions .handlediv', function() {
            if (!$(this).closest('.postbox').hasClass('closed')) {
                $(this).attr('aria-expanded', 'false');
                $(this).closest('.postbox').addClass('closed');
            } else {
                $(this).attr('aria-expanded', 'true');
                $(this).closest('.postbox').removeClass('closed');
            }
        });
    });
});
