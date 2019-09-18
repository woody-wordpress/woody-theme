import $ from 'jquery';

$('.tools .prepare_onspot_switcher input').on('click', function(){
    var switcher = $(this).prop('checked');

    $.ajax({
        type: 'POST',
        dataType: 'json',
        url: frontendajax.ajaxurl,
        data: {
            action: 'redirect_prepare_onspot',
            params: switcher,
            post_id: globals.post_id
        },
        success: function(url){
            window.location.replace(url);
        }
    });
});
