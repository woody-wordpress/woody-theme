import $ from 'jquery';

$('#post').each(function() {
    $('#post-body-content').append('<div id="tpls_popin" class="hidden"><ul></ul></div>');

   $(document).ready( function() {
        // AJAX to get all woody_tpl

        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: ajaxurl,
            data: {
                action: 'woody_tpls',
            },
            success: function(data) {
                for (let [key, value] of Object.entries(data)) {
                    $('#tpls_popin ul').append('<li>' + value + '</li>');
                }
            },
            error: function() {},
        });
    });
});
