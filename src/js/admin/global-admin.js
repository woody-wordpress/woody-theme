import $ from 'jquery';

$('body.themes-php').each(function() {
    $('.theme-actions').remove();
});

// Bugfix  Enhanced media buttons
if (typeof window.wp.media != 'undefined' && typeof window.wp.media.view.l10n != 'undefined') {
    window.wp.media.view.l10n.cancelSelection = window.wp.media.view.l10n.cancel;
    window.wp.media.view.l10n.trashSelected = window.wp.media.view.l10n.trash;
    window.wp.media.view.l10n.deleteSelected = window.wp.media.view.l10n.deletePermanently;
}

$(document).one('click', '.attachments .attachment', function() {
    $('<button type="button" class="button media-button button-primary edit-attachments-tag">Ajouter des tags</button>').insertAfter('.media-toolbar .media-toolbar-secondary .button-primary');

    $('.media-frame-content .attachments li').on('click', function() {
        if ($('.media-toolbar-secondary .button.media-button.button-large').hasClass('hidden')) {
            $('.edit-attachments-tag').addClass('hidden');
        } else {
            $('.edit-attachments-tag').removeClass('hidden');
        }
    });
    $('.media-toolbar-secondary .button.media-button.button-large').click(function() {
        $('.edit-attachments-tag').addClass('hidden');
    });

    // Show popup
    var popup = '<div class="add-medias-tag hidden"><div class="choices"><ul class="themes"><p>Thématiques</p></ul><ul class="places"><p>Lieux</p></ul><ul class="seasons"><p>Saisons</p></ul></div><div class="actions"><button class="button button-primary apply">Valider</button><button class=" button close">Annuler</button></div></div>';
    $('#wpbody-content').append(popup);
    $('.add-medias-tag .close').click(function() {
        $('.add-medias-tag').addClass('hidden');
    });

    $('.add-medias-tag .apply').click(function() {

        var attach_ids = [];
        var term_ids = [];
        $('.media-frame-content .attachments li.selected').each(function() {
            attach_ids.push($(this).data('id'));
        });
        $('.add-medias-tag input:checked').each(function() {
            term_ids.push($(this).val());
        });

        console.log(attach_ids, "attach_ids");
        console.log(term_ids, "term_ids");

        // TODO: AJAX Call
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: ajaxurl,
            data: {
                action: 'set_attachments_terms',
                attach_ids: attach_ids,
                term_ids: term_ids
            },
            success: function(response) {
                if(response){
                    location.reload();
                }
            },
            error: function(err) {
                console.error(err);
            }
        });
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
})
