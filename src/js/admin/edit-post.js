import $ from 'jquery';

$('#post').each(function() {
    // On ferme toutes les metaboxes ACF
    $('.acf-postbox').addClass('closed');

    // On masque les metaboxes de taxonomies dans l'edition des posts (on les rajoutera ensuite dans des champs ACF)
    $('[id^="tagsdiv-"').hide();

    // On referme les metaboxes par défaut sur l'édition d'un post
    $('#pageparentdiv, #revisionsdiv, #wpseo_meta, #members-cp').addClass('closed');

    // On toggle la description de chaque template dans les champs woody_tpl
    $('.tpl-choice-wrapper').each(function() {
        var $this = $(this);

        $this.find('.toggle-desc').click(function(e) {
            e.stopPropagation();
            $this.find('.tpl-desc').toggleClass('hidden');
            $this.find('.desc-backdrop').toggleClass('hidden');
        });

        $this.find('.close-desc').click(function() {
            $this.find('.tpl-desc').addClass('hidden');
            $this.find('.desc-backdrop').addClass('hidden');
        });

        $this.find('.desc-backdrop').click(function() {
            $this.find('.tpl-desc').addClass('hidden');
            $(this).addClass('hidden');
        });
    });

    var countElements = function(field) {
        var count = 0;

        // add class to this field
        field.$el
            .parents('.acf-field-5b0d20457c829')
            .find('.acf-table').each(function() {
                count = $(this).find('.acf-row').length - 1;
            })
            .end()
            .end()
            .parents('.layout').find('.acf-field-5b0d21aa7c82f .tpl-choice-wrapper').each(function() {
                if (!$(this).hasClass('fittedfor-' + count) && !$(this).hasClass('fittedfor-0')) {
                    $(this).removeClass('fit');
                    $(this).addClass('notfit');
                } else {
                    $(this).removeClass('notfit');
                    $(this).addClass('fit');
                }
            });
    };

    acf.addAction('ready_field/key=field_5b22415792db0', countElements);
    acf.addAction('append_field/key=field_5b22415792db0', countElements);
    acf.addAction('remove_field/key=field_5b22415792db0', countElements);

})
