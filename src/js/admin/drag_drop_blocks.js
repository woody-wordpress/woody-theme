import $ from 'jquery';

$('#acf-group_5afd260eeb4ab .acf-field[data-name="section"] .acf-table tbody')
    .first()
    .children('.acf-row:not(.acf-clone)')
    .each(function() {
        var row = $(this);
        row.find('.acf-field-5b043f0525968 .values .layout').each(function() {
            var block = $(this);

            var controls = block.find('.acf-fc-layout-controls');
            controls.prepend('<a class="acf-icon small light dashicons dashicons-move" href="#" data-name="drag-layout"></a>');

            block.find('a[data-name="drag-layout"]').click(function() {
                var clone = block;

                block.addClass('dragging-layout');

                // Make block draggable
                block.draggable({
                    disabled: false,
                    drag: function(e, ui) {
                        var blockPosY = block.offset().top;
                        var rowPosY = row.offset().top;
                        var maxHeight = row.innerHeight();

                        if (blockPosY < rowPosY + 25 || blockPosY + 35 > rowPosY + maxHeight) {
                            // Outside
                        } else {
                            // Inside
                        }
                    },
                    stop: function() {
                        var blockPosY = block.offset().top;
                        var rowPosY = row.offset().top;
                        var maxHeight = row.innerHeight();
                        // block.draggable("disabled", 1);

                        if (blockPosY < rowPosY + 35 || blockPosY + 35 > rowPosY + maxHeight) {
                            // Outside
                            updateSection(block, blockPosY, row, clone);

                        } else {
                            // Inside : remove draggable actions
                            $('.dragging-layout').removeClass('dragging-layout');
                            block.removeAttr('style');
                        }
                    }
                });
            });
        });
    });

var updateSection = function(block, blockPosY, original_row, clone) {
    $('#acf-group_5afd260eeb4ab .acf-field[data-name="section"] .acf-table tbody')
        .first()
        .children('.acf-row:not(.acf-clone)')
        .each(function() {
            var row = $(this);

            if (row != original_row) {
                var rowPosY = row.offset().top;
                var maxHeight = row.innerHeight();

                if (blockPosY > rowPosY && blockPosY < (rowPosY + maxHeight)) {
                    var row_id = row.data('id');
                    block.remove();

                    // Trigger add row add block;
                    var block_type = clone.attr('data-layout');
                    $.when(row.find('.acf-actions .acf-button[data-name="add-layout"]').trigger('click')).then(function(){
                        $.when($('.acf-tooltip.acf-fc-popup a[data-layout="'+ block_type +'"]').trigger('click')).then(function(){
                            // Change acf
                            var new_block = row.find('.acf-flexible-content').first().children('.values').children('.layout').last();
                            var layout_id = new_block.attr('data-id');

                            clone.find('*').each(function() {
                                var attr = $(this).attr('name');
                                if (typeof attr !== typeof undefined && attr !== false) {
                                    var regex = /acf\[field_5afd2c6916ecb\]\[[0-9]+\]\[field_5b043f0525968\]\[[0-9]+\]/;
                                    var replacement = 'acf[field_5afd2c6916ecb][' + row_id + '][field_5b043f0525968][' + layout_id + ']';
                                    var new_attr = attr.replace(regex, replacement);
                                    $(this).attr('name', new_attr);
                                }

                                var forattr = $(this).attr('for');
                                if (typeof forattr !== typeof undefined && forattr !== false) {
                                    var regex2 = /field_5afd2c6916ecb-[0-9]+-field_5b043f0525968/;
                                    var replacement2 = 'field_5afd2c6916ecb-' + row_id + '-field_5b043f0525968';
                                    new_attr = forattr.replace(regex2, replacement2);
                                    $(this).attr('for', new_attr);
                                }
                            });

                            var fields = clone.find('.acf-fields .acf-field');
                            var index = 0;

                            new_block.find(' .acf-fields .acf-field').each(function(){
                                $(this).replaceWith(fields[index]);
                                index++;
                            });

                            if (original_row.find('.values .layout').length < 1) {
                                original_row.find('.no-value-message').css("display", 'block');
                            }
                        });
                    });
                }
            }
        });
}
