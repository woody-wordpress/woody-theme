import $ from 'jquery';

$('body.themes-php').each(function() {
    $('.theme-actions').remove();
});

// Bugfix  Enhanced media buttons
if (typeof window.wp.media != 'undefined') {
    window.wp.media.view.l10n.cancelSelection = window.wp.media.view.l10n.cancel;
    window.wp.media.view.l10n.trashSelected = window.wp.media.view.l10n.trash;
    window.wp.media.view.l10n.deleteSelected = window.wp.media.view.l10n.deletePermanently;
}

if ($('#wp-media-grid')) {
    if ($('.media-frame-toolbar').length == 0) {
        setTimeout(function() {
            $('<button type="button" class="button media-button button-primary edit-attachments-tag hidden">Appliquer un tag</button>').insertAfter('.media-toolbar .media-toolbar-secondary .button-primary');
            $('.edit-attachments-tag')

            // Show popup
            var popup = '<div class="add-medias-tag hidden"><ul class="themes">Thématiques</ul><ul class="places">Lieux</ul><ul class="seasons">Saisons</ul><button class="button button-primary apply">Appliquer un tag</button><button class=" button close">Annuler</button></div>';
            $('#wpbody-content').append(popup);
            $('.add-medias-tag .close').click(function() {
                $('.add-medias-tag').addClass('hidden');
            });

            $('.add-medias-tag .apply').click(function() {
                var selected = $('.media-frame-content .attachments li.selected');
                selected.each(function() {
                    var selected_el = $(this);
                    selected_el.trigger('click');
                    $('.add-medias-tag input:checked').each(function() {
                        var term_id = $(this).val();
                        var tax = $(this).closest('ul').attr('class');
                        $('.add-medias-tag').addClass('hidden');
                        if ($('.term-list #' + tax + '-' + term_id + ' .selectit').find('input').last().prop('checked') == false) {
                            $('.term-list #' + tax + '-' + term_id + ' .selectit').trigger('click');
                        }
                    });
                });

                $('.attachments li.selected').removeClass('selected');
            });

            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: ajaxurl,
                data: {
                    action: 'get_all_tags',
                },
                success: function(data) {
                    data.themes.forEach(function(element) {
                        $('.add-medias-tag .themes').append('<li> <input type="checkbox" value="' + element.id + '"> ' + element.name + '</li>');
                    });
                    data.places.forEach(function(element) {
                        $('.add-medias-tag .places').append('<li> <input type="checkbox" value="' + element.id + '">' + element.name + '</li>');
                    });
                    data.seasons.forEach(function(element) {
                        $('.add-medias-tag .seasons').append('<li> <input type="checkbox" value="' + element.id + '">' + element.name + '</li>');
                    });
                }
            });

            $('.edit-attachments-tag').click(function() {
                // display form
                $('.add-medias-tag').removeClass('hidden');
            });
        }, 3000);
    }
}
