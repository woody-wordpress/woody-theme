import $ from 'jquery';

window.dataLayer = window.dataLayer || [];

$.ajax({
    type: 'POST',
    dataType: 'json',
    url: frontendajax.ajaxurl,
    data: {
        action: 'get_current_lang'
    },
    success: function(response) {
        window.dataLayer.push({ langue: response });
    },
    error: function(err) {
        console.error(err);
    }
});

$.ajax({
    type: 'POST',
    dataType: 'json',
    url: frontendajax.ajaxurl,
    data: {
        action: 'get_current_place',
        post_id: globals.post_id
    },
    success: function(response) {
        if (response.length > 0) {
            window.dataLayer.push({ lieu: response[0] });
        }
    },
    error: function(err) {
        console.error(err);
    }
});
