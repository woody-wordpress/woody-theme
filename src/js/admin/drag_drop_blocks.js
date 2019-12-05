import $ from 'jquery';

var setSortableEmptyValues = function() {
    $(this).on('mousemove', function(){
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

    $(".values").sortable({
        connectWith: ".values",
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

    // check if the dropped element is within a repeater field
    if ($($el).parents('.acf-input > .acf-repeater').length) {

        // get column_num from closest acf-row
        var column_num = $($el).closest('.acf-row').attr('data-id');

        // loop all (input) fields within dropped element and change / fix name
        $($el).find('[name^="acf[field_"]').each(function() {
            var field_name = $(this).attr('name');
            field_name = field_name.match(/\[([a-zA-Z0-9_-]+\])/g); // split name attribute
            field_name[1] = '[' + column_num + ']'; // set the new row name
            var new_name = $(this).parent().hasClass('acf-gallery-attachment') ? 'acf' + field_name.join('') + '[]' : 'acf' + field_name.join('');
            $(this).attr('name', new_name);
        });

        $($el).find('[id^="acf-field_"]').each(function() {
            var field_id = $(this).attr('id');
            field_id = field_id.match(/-([a-zA-Z0-9_]+)/g); // split name attribute
            field_id[1] = '-' + column_num ; // set the new row name
            var new_id = 'acf' + field_id.join('');
            $(this).attr('id', new_id);
        });

        $($el).find('[for^="acf-field_"]').each(function() {
            var field_for = $(this).attr('for');
            field_for = field_for.match(/-([a-zA-Z0-9_]+)/g); // split name attribute
            field_for[1] = '-' + column_num ; // set the new row name
            var new_for = 'acf' + field_for.join('');
            $(this).attr('for', new_for);
        });

        // get closest flexible-content-field and loop all layouts within this flexible-content-field
        $($el).closest('.acf-field.acf-field-flexible-content').find('.acf-input > .acf-flexible-content > .values > .layout').each(function(index) {

            // update order number
            $(this).find('.acf-fc-layout-order:first').html(index + 1);

            // loop all (input) fields within dropped element and change / fix name
            $(this).find('[name^="acf[field_"]').each(function() {
                var field_name = $(this).attr('name');
                field_name = field_name.match(/\[([a-zA-Z0-9_-]+\])/g); // split name attribute
                var tempIndex = parseInt(field_name[3].match(/([0-9]+)/g)); // hacky code
                field_name[3] = field_name[3].replace(tempIndex, index); // set the new index
                var new_name = $(this).parent().hasClass('acf-gallery-attachment') ? 'acf' + field_name.join('') + '[]' : 'acf' + field_name.join('');
                $(this).attr('name', new_name);
            });

            $(this).find('[id^="acf-field_"]').each(function() {
                var field_id = $(this).attr('id');
                field_id = field_id.match(/-([a-zA-Z0-9_]+)/g); // split name attribute
                field_id[3] = '-' + index; // set the new index
                var new_id = 'acf' + field_id.join('');
                $(this).attr('id', new_id);
            });

            $(this).find('[for^="acf-field_"]').each(function() {
                var field_for = $(this).attr('for');
                field_for = field_for.match(/-([a-zA-Z0-9_]+)/g); // split name attribute
                field_for[3] = '-' + index; // set the new index
                var new_for = 'acf' + field_for.join('');
                $(this).attr('for', new_for);
            });

            // click already selected buttons to trigger conditional logics
            $(this).find('.acf-button-group label.selected').trigger('click');
        });
    }
});
