import $ from 'jquery';

$('#post').each(function () {

    // TODO: A quoi sert cette fonction ???
    $('.acf-field-5b44bfc2e2e21 .acf-switch:not(-on)').on('click', function () {
        $('.acf-field-5c614d83a4e9b .acf-switch.-on').trigger('click');
    });

    // TODO: A quoi sert cette fonction ???
    $('.acf-field-5c614d83a4e9b .acf-switch:not(-on)').on('click', function () {
        $('.acf-field-5b44bfc2e2e21 .acf-switch.-on').trigger('click');
    });
});
