import $ from 'jquery';

$('#acf-group_5afd260eeb4ab .acf-field[data-name="section"] .acf-table tbody')
    .first()
    .children('.acf-row:not(.acf-clone)')
    .each(function() {
        var row = $(this);
        row.find('.acf-field-5b043f0525968 .values .layout').each(function() {
            var block = $(this);

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
                        updateSection(block, blockPosY, row);

                    } else {
                        // Inside
                    }
                }
            });
        });
    });

var updateSection = function(block, blockPosY, original_row) {
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
                    var clone = block;
                    clone.removeAttr('style')
                        .attr('data-id', layout_id)
                        .find('input')
                        .first()
                        .attr('name', 'acf[field_5afd2c6916ecb][' + row_id + '][field_5b043f0525968][' + layout_id + '][acf_fc_layout]');
                    clone.find('.acf-fc-layout-handle span').text(layout_id + 1);

                    block.remove();
                    row.find('.values')
                        .append(clone);

                    if(row.find('.no-value-message').css("display") != "none"){
                        row.find('.no-value-message').css("display", 'none');
                    }

                    if (original_row.find('.values .layout').length < 1) {
                        original_row.find('.no-value-message').css("display", 'block');
                    }
                }
            }
        });
}
