import $ from 'jquery';

var setSortableEmptyValues = function() {
    $(this).on('mousemove', function() {
        $('.acf-flexible-content.-empty .values').each(function() {
            $(this).addClass('droppable-area');
            $(this).closest('.acf-flexible-content').removeClass('-empty');
        });
    });
};

var unsetSortableEmptyValues = function() {
    $('.droppable-area').each(function() {
        $(this).removeClass('droppable-area');
    });

    $('.values').each(function() {
        $(this).off('mousemove');
        var value = $(this);
        if (value.children().length < 1) {
            value.closest('.acf-flexible-content').addClass('-empty');
        }
    });
};

acf.add_action('append_field/key=field_5b0d1dc8907e7', function() {
    makeSortable();
});

acf.add_action('ready_field/key=field_5b0d1dc8907e7', function() {
    makeSortable();
});

function makeSortable() {
    $('.values').on('click mousedown', setSortableEmptyValues);
    $('.values').on('click mouseup', unsetSortableEmptyValues);

    $(".acf-flexible-content > .values").sortable({
        connectWith: ".acf-flexible-content > .values",
        dropOnEmpty: true,
        tolerance: "pointer",
        cursor: "move",
        start: function(event, ui) {
            acf.do_action('sortstart', ui.item, ui.placeholder);
        },
        stop: function(event, ui) {
            acf.do_action('sortstop', ui.item, ui.placeholder);
            $(this).find('.mce-tinymce').each(function() {
                tinyMCE.execCommand('mceRemoveControl', true, $(this).attr('id'));
                tinyMCE.execCommand('mceAddControl', true, $(this).attr('id'));
            });
        }
    });
}

acf.add_action('sortstop', function($el) {
    unsetSortableEmptyValues();

    if ($el.find('input[name$="[acf_fc_layout]"]').first().length > 0) {
        var repeater_id = $el.find('input[name$="[acf_fc_layout]"]').attr('name').match(/\[([a-zA-Z0-9_-]+\])/g)[1][1];

        // check if the dropped element is within a repeater field
        if ($($el).parents('.acf-input > .acf-repeater').length) {

            // get column_num from closest acf-row
            var row_index = $($el).closest('.acf-row').attr('data-id');
            var parent_row = $($el).closest('.acf-row');

            if (repeater_id != row_index) {
                var layout_index = 0;

                parent_row.find('.values .layout').each(function() {
                    $(this).find('[name^="acf[field_"]').each(function() {
                        var field_name = $(this).attr('name');
                        field_name = field_name.match(/\[([a-zA-Z0-9_-]+\])/g); // split name attribute
                        field_name[1] = '[' + row_index + ']'; // set the new row name
                        field_name[3] = '[' + layout_index + ']'; // set the new row name
                        var new_name = $(this).parent().hasClass('acf-gallery-attachment') ? 'acf' + field_name.join('') + '[]' : 'acf' + field_name.join('');
                        $(this).attr('name', new_name);
                    });

                    $(this).find('label.selected input').each(function(){
                        $(this).trigger('click');
                    });

                    layout_index++;

                    // console.log($(this).find('.acf-fc-layout-handle .acf-fc-layout-order'));
                    $(this).find('.acf-fc-layout-handle .acf-fc-layout-order').text(layout_index);
                });
            } else {
                // Do nothing
            }
        }
    }
});
