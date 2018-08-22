import $ from 'jquery';
import * as selectize from "selectize";
import * as arrive from "arrive";

var attachment_selectize = function(target) {
    $(target)
        .css('overflow', 'unset')
        .find('input:not(.selectized)').selectize({
            plugins: ['remove_button'],
            delimiter: ',',
            persist: true,
            create: function(input) {
                return {
                    value: input,
                    text: input
                }
            }
        });
}

var waiting = function(container, target, callback) {
    var $container = $(container);
    var target = target;

    $container.arrive(target, function() {
        $container.unbindArrive(target);
        callback();
    });
}

waiting('#wp-media-grid', '.attachment.save-ready', function() {
    $('.attachment.save-ready').click(function() {
        //console.log('click save');
        waiting('.media-sidebar', '.compat-field-attachment_hashtags', attachment_selectize('.compat-field-attachment_hashtags'));

        $('.edit-attachment').click(function() {
            //console.log('click edit');
            waiting(document, '.media-modal-content', function() {
                //console.log('modal-load');
                waiting('.media-modal-content', '.compat-field-attachment_hashtags', attachment_selectize('.compat-field-attachment_hashtags'));
            });
        });
    });
})
