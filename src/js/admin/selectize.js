// import $ from 'jquery';
// // import * as selectize from "selectize";
// // import * as arrive from "arrive";

// var attachment_selectize = function(target) {
//     //console.log('attachment OK');
//     $(target)
//         .css('overflow', 'unset')
//         .find('input:not(.selectized)').selectize({
//             plugins: ['remove_button'],
//             delimiter: ',',
//             persist: true,
//             create: function(input) {
//                 return {
//                     value: input,
//                     text: input
//                 }
//             }
//         });
// }

// var waiting = function(container, target, callback) {
//     var $container = $(container);
//     var target = target;

//     $container.arrive(target, function() {
//         $container.unbindArrive(target);
//         callback();
//     });
// }

// waiting('#wp-media-grid', '.attachment.save-ready', function() {
//     $('#wp-media-grid .attachment.save-ready').click(function() {
//         attachment_selectize('.media-sidebar .compat-field-attachment_hashtags')
//         $('.edit-attachment').click(function() {
//             waiting('body', '.media-modal-content', function() {
//                 attachment_selectize('.media-modal-content .compat-field-attachment_hashtags');
//             });
//         });
//     });
// })
