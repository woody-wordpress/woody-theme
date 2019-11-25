import $ from 'jquery';

window.dataLayer = window.dataLayer || [];
window.dataLayer.push({ langue: $('html').attr('lang') });

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

if ($('.sharing-links').length) {
    $('.sharing-links .sharing-button__link').each(function() {
        $(this).click(function() {
            var socialshare = 'facebook';

            if ($(this).hasClass('twitter')) {
                socialshare = 'twitter';
            } else if ($(this).hasClass('pinterest')) {
                socialshare = 'pinterest';
            } else if ($(this).hasClass('email')) {
                socialshare = 'email';
            }

            window.dataLayer.push({
                event: 'Clic social',
                socialShare: socialshare,
                provider: globals.post_title
            });
        });
    });
}


$('.woody-component-claims-block .claim-content .claim-link .button').each(function(){
    $(this).click(function() {
        window.dataLayer.push({
            event: 'Clic claim button',
        });
    });
});
