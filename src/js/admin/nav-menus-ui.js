!(function ($) {
    // Benoit Bouchaud
    // On déselectionne et on retire l'option "Menu principal" sur la page d'administration des menus
    $('.nav-menus-php #select-menu-to-edit option:contains("Menu principal")').prop('selected', false);
    $('.nav-menus-php #select-menu-to-edit option:contains("Menu principal")').remove();

    // Benoit Bouchaud
    // On retire la class active sur l'onglet Apparence lorsque l'on est sur la page nav-menus.php
    if (window.location.pathname == '/wp/wp-admin/nav-menus.php') {
        $('#adminmenu').find('#menu-appearance').removeClass('wp-has-current-submenu');
        $('#adminmenu').find('#menu-appearance a').removeClass('wp-has-current-submenu');
    }
})(jQuery);
