import $ from 'jquery';

$('#acf-field_5d47d14bdf764').on('change', function() {
    var data = $(this).attr('checked') !== undefined && $(this).attr('checked') == "checked" ? 'prepare' : 'spot' ;
    var post_id = $('#post_ID').val();
    // AJAX CALL to set post term
    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: ajaxurl,
        data: {
            action: 'set_post_term',
            params: data,
            post_id: post_id
        },
        success: function ( data )
        {

        },
        error: function () { },
    });
});
