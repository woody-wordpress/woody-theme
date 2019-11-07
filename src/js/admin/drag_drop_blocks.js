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

                    if (blockPosY < rowPosY || blockPosY > rowPosY + maxHeight) {
                        // Outside
                        // TODO: create div to insert block
                    } else {
                        // Inside
                        // TODO: remove div to drop block

                    }
                },
                stop: function() {
                    var blockPosY = block.offset().top;
                    var rowPosY = row.offset().top;
                    var maxHeight = row.innerHeight();

                    if (blockPosY < rowPosY || blockPosY > rowPosY + maxHeight) {
                        // Outsid
                        // TODO: Append block inside selected section
                    } else {
                        // Inside
                    }
                }
            });
        });
    });
