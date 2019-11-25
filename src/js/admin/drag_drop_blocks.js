import $ from 'jquery';

acf.add_action('ready', function($el){
    $(".values").sortable({
        connectWith: ".values",
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
});

acf.add_action('sortstop', function ($el) {

    // check if the dropped element is within a repeater field
    if($($el).parents('.acf-input > .acf-repeater').length) {

        // get column_num from closest acf-row
        var column_num = $($el).closest('.acf-row').attr('data-id');

        // loop all (input) fields within dropped element and change / fix name
        $($el).find('[name^="acf[field_"]').each(function() {
            var field_name 		= 	$(this).attr('name');
            field_name          =   field_name.match(/\[([a-zA-Z0-9_-]+\])/g); // split name attribute
            field_name[1]       =   '[' + column_num + ']'; // set the new row name
            var new_name        =   'acf' + field_name.join('');
            $(this).attr('name', new_name);
        });

        // get closest flexible-content-field and loop all layouts within this flexible-content-field
        $($el).closest('.acf-field.acf-field-flexible-content').find('.acf-input > .acf-flexible-content > .values > .layout').each(function(index) {

            // update order number
            $(this).find('.acf-fc-layout-order:first').html(index+1);

            // loop all (input) fields within dropped element and change / fix name
            $(this).find('[name^="acf[field_"]').each(function() {
                var field_name 		= 	$(this).attr('name');
                field_name          =   field_name.match(/\[([a-zA-Z0-9_-]+\])/g); // split name attribute
                var tempIndex       =   parseInt(field_name[3].match(/([0-9]+)/g)); // hacky code
                field_name[3]       =   field_name[3].replace(tempIndex, index); // set the new index
                var new_name        =   'acf' + field_name.join('');
                $(this).attr('name', new_name);
            });

            // click already selected buttons to trigger conditional logics
            $(this).find('.acf-button-group label.selected').trigger('click');
        });
    }
});
