import $ from 'jquery';

$('#post').each(function () {

    // Boutons d'actions en backoffice au scroll
    var $preview_button = $('#minor-publishing-actions .preview.button');
    var $save_button = $('#publishing-action');
    $(window).scroll(function () {
        if ($(window).scrollTop() < 800) {
            $preview_button.removeClass('sticky');
            $save_button.removeClass('sticky');
        } else {
            $preview_button.addClass('sticky');
            $save_button.addClass('sticky');
        }
    });

});
