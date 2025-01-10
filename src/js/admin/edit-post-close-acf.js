!(function ($) {
    $('#post').each(function () {

        // On ferme certaines metaboxes ACF => Visuel et accroche, En-tête, Bloc de résa, diaporama, révisions, boxes en sidebar (sauf publier), WoodySeo
        $('#acf-group_5b052bbee40a4, #acf-group_5b2bbb46507bf, #acf-group_5c0e4121ee3ed, #acf-group_5bb325e8b6b43, #revisionsdiv, #side-sortables .postbox:not(#submitdiv), #acf-group_5d7f7cd5615c0').addClass('closed');

        // Collapse all section or layouts
        $('#acf-group_5afd260eeb4ab .acf-field.collapsing-rows').each(function () {
            var $this = $(this);
            if ($this.hasClass('acf-field-5afd2c6916ecb')) {
                var rowsType = 'les sections';
            } else if ($this.hasClass('acf-field-5b043f0525968')) {
                var rowsType = 'les blocs';
            }

            $this.prepend('<span class="woodyRowsCollapse"><span class="text">Fermer ' + rowsType + '</span><span class="dashicons dashicons-arrow-up' + '"></span></span>');

            $('.woodyRowsCollapse').click(function () {
                if ($this.hasClass('acf-field-5afd2c6916ecb')) {
                    $('.acf-field-5afd2c6916ecb > .acf-input > .acf-repeater > .acf-table > .ui-sortable > .acf-row').addClass('-collapsed');
                    $(this).siblings('.acf-input').find('.acf-field-5b043f0525968 .acf-input > .acf-flexible-content > .values .layout').addClass('-collapsed');
                } else {
                    $(this).siblings('.acf-input').find('> .acf-flexible-content > .values .layout').addClass('-collapsed');
                }
            })
        });
    });
})(jQuery);
