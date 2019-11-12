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

                    var layout_id = row.find('.acf-flexible-content').first().children('.values').children('.layout').last().data('id');

                    // Trigger add row add block;
                    var block_type = clone.attr('data-layout');
                    $.when(row.find('.acf-actions .acf-button[data-name="add-layout"]').trigger('click')).then(function() {
                        $.when($('.acf-tooltip.acf-fc-popup a[data-layout="' + block_type + '"]').trigger('click')).then(function() {
                            var fields = clone.find('.acf-fields .acf-field');
                            var index = 0;

                            var new_block = row.find('.acf-flexible-content').first().children('.values').children('.layout').last();
                            new_block.find('.acf-fields .acf-field').each(function() {
                                $(this).replaceWith(fields[index]);
                                index++;
                            });

                            var layoutNameFields = new_block.find('[name^="acf[field_5afd2c6916ecb]"]');
                            layoutNameFields.each(function() {
                                var name = layoutNameFields.attr('name');
                                var regex = /acf\[field_5afd2c6916ecb\]\[[0-9]+\]\[field_5b043f0525968\]\[\w+\]/;
                                if (name.match(regex)) {
                                    var replacement = 'acf[field_5afd2c6916ecb][' + row_id + '][field_5b043f0525968][' + layout_id + ']';
                                    var new_name = name.replace(regex, replacement);

                                    $(this).attr('name', new_name);
                                } else {
                                    console.log(name, 'name unmatch');
                                }
                            });

                            var layoutForFields = new_block.find('[for^="acf-field_5afd2c6916ecb-"]');
                            layoutForFields.each(function() {
                                var forattr = layoutForFields.attr('for');
                                var regex = /acf-field_5afd2c6916ecb-[0-9]+-field_5b043f0525968-\w+-/;
                                if (forattr.match(regex)) {
                                    var replacement = 'acf-field_5afd2c6916ecb-' + row_id + '-field_5b043f0525968-' + layout_id + '-';
                                    var new_for = forattr.replace(regex, replacement);

                                    $(this).attr('for', new_for);
                                } else {
                                    console.log(forattr, 'for unmatch');
                                }
                            });

                            var layoutIdFields = new_block.find('[id^="acf-field_5afd2c6916ecb-"]');
                            layoutIdFields.each(function() {
                                var id = layoutIdFields.attr('id');
                                var regex = /acf-field_5afd2c6916ecb-[0-9]+-field_5b043f0525968-\w+-/;

                                if (id.match(regex)) {
                                    var replacement = 'acf-field_5afd2c6916ecb-' + row_id + '-field_5b043f0525968-' + layout_id + '-';
                                    var new_id = id.replace(regex, replacement);

                                    $(this).attr('id', new_id);
                                } else {
                                    console.log(id, 'id unmatch');
                                }
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
