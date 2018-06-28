import $ from 'jquery';

// $(document).ready(function() {
//     alert("document ready occurred!");
// });

$(window).load(function() {
    $('body.wp-admin').addClass('windowReady');
});
