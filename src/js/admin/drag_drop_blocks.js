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

                    if (blockPosY < rowPosY + 25 || blockPosY + 35  > rowPosY + maxHeight) {
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
                        updateSection(block, blockPosY);

                    } else {
                        // Inside
                    }
                }
            });
        });
    });

var updateSection = function(block, blockPosY){
    $('#acf-group_5afd260eeb4ab .acf-field[data-name="section"] .acf-table tbody')
        .first()
        .children('.acf-row:not(.acf-clone)')
        .each(function() {
            var row = $(this);
            var rowPosY = row.offset().top;
            var maxHeight = row.innerHeight();


            if (blockPosY > rowPosY && blockPosY < (rowPosY + maxHeight)) {
                var clone = block;
                clone.removeAttr('style');


                block.remove();
                row.find('.values').append(clone);

            }
    });
}
