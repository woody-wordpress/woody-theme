import $ from 'jquery';

window.dataLayer = window.dataLayer || [];
window.dataLayer.push({ langue: window.siteConfig.current_lang });

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
    $('.sharing-links .sharing-button__link').click(function() {
        var socialshare = 'facebook';

        if ($(this).hasClass('twitter')) {
            socialshare = 'twitter';
        } else if ($(this).hasClass('pinterest')) {
            socialshare = 'pinterest';
        } else if ($(this).hasClass('email')) {
            socialshare = 'email';
        }

        var obj = {
            eventCategory: 'Pages',
            eventAction: 'Clic bouton réseaux sociaux',
            eventLabel: socialshare,
            eventValue: '',
            page: {
                name: globals.post_title
            }
        };
        window.dataLayer.push(obj);
    });
}

$('.woody-component-claims-block .claim-content .claim-link .button').click(function() {
    window.dataLayer.push({
        event: 'Clic claim button',
    });
    if (typeof ga !== 'undefined' && ga != null) {
        ga('rc.send', 'event', 'claim', 'CLIC_CLAIM_BUTTON', 'Clic claim button', undefined);
    }
});
