import $ from 'jquery';

function openCloseGroup(field) {
    field.on('click', '.postbox-header > *', function() {
        if (!field.hasClass('closed')) {
            field.find('.handlediv').attr('aria-expanded', 'false');
            field.addClass('closed');
        } else {
            field.find('.handlediv').attr('aria-expanded', 'true');
            field.removeClass('closed');
        }
    });
}

acf.addAction('append', openCloseGroup);
