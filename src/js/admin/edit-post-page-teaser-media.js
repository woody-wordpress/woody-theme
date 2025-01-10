!(function ($) {
    $('#post').each(function () {

        // Permet d'éviter d'avoir un média et un profil en même temps
        // TODO: A améliorer
        $('.acf-field-5b44bfc2e2e21 .acf-switch:not(-on)').on('click', function () {
            $('.acf-field-5c614d83a4e9b .acf-switch.-on').trigger('click');
        });

        $('.acf-field-5c614d83a4e9b .acf-switch:not(-on)').on('click', function () {
            $('.acf-field-5b44bfc2e2e21 .acf-switch.-on').trigger('click');
        });
    })
})(jQuery);
