import $ from 'jquery';

$('#acf-group_5afd260eeb4ab .acf-field[data-name="section"] .acf-table tbody')
    .first()
    .children('.acf-row:not(.acf-clone)')
    .each(function() {
        var row = $(this);
        row.find('.acf-field-5b043f0525968 .values .layout').each(function() {
            var block = $(this);

            var controls = block.find('.acf-fc-layout-controls');
            controls.prepend('<a class="acf-icon small light" href="#" data-name="drag-layout">drag</a>');

            block.find('a[data-name="drag-layout"]').click(function() {
                var clone = block;

                // Make block draggable
                block.draggable({
                    drag: function(e, ui) {
                        var blockPosY = block.offset().top;
                        var rowPosY = row.offset().top;
                        var maxHeight = row.innerHeight();

                        if (blockPosY < rowPosY + 25 || blockPosY + 35 > rowPosY + maxHeight) {
                            // Outside
                            block.css('z-index', '10000');
                        } else {
                            // Inside
                            block.css('z-index', 'unset');
                        }
                    },
                    stop: function() {
                        var blockPosY = block.offset().top;
                        var rowPosY = row.offset().top;
                        var maxHeight = row.innerHeight();

                        if (blockPosY < rowPosY + 35 || blockPosY + 35 > rowPosY + maxHeight) {
                            // Outside
                            updateSection(block, blockPosY, row, clone);

                        } else {
                            // Inside
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
                    var layout_id = row.find('.values .layout').length;
                    clone.removeAttr('style')
                        .attr('data-id', layout_id)
                        .find('input')
                        .first()
                        .attr('name', 'acf[field_5afd2c6916ecb][' + row_id + '][field_5b043f0525968][' + layout_id + '][acf_fc_layout]');
                    clone.find('.acf-fc-layout-handle span').text(layout_id + 1);
                    clone.find('*').each(function() {
                        var attr = $(this).attr('name');
                        if (typeof attr !== typeof undefined && attr !== false) {
                            var regex = /acf\[field_5afd2c6916ecb\]\[[0-9]+\]\[field_5b043f0525968\]\[[0-9]+\]/;
                            var replacement = 'acf[field_5afd2c6916ecb][' + row_id + '][field_5b043f0525968][' + layout_id + ']';
                            var new_attr = attr.replace(regex, replacement);

                            $(this).attr('name', attr);
                        }
                    });

                    block.remove();
                    row.find('.values')
                        .append(clone);

                    if(row.find('.no-value-message').css("display") != "none"){
                        row.find('.no-value-message').css("display", 'none');
                    }


                    // var layout_type = clone.find('input').first().val();
                    // $.when(row.find('.acf-actions .acf-button[data-name="add-layout"]').trigger('click')).then(function(){
                    //     $.when($('.acf-tooltip.acf-fc-popup a[data-layout="'+ layout_type +'"]').trigger('click')).then(function(){
                    //         // Get created layout Replace content
                    //         var new_block = row.find('.acf-field-5b043f0525968 .values .layout').last();
                    //         new_block.find('.acf-fields').first().replaceWith(clone.find('.acf-fields').first());
                    //     });

                    //     if (original_row.find('.values .layout').length < 1) {
                    //         original_row.find('.no-value-message').css("display", 'block');
                    //     }
                    // });
                }
            }
        });
}
