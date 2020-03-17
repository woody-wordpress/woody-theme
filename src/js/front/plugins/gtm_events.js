import $ from 'jquery';

var getPlace = function() {
    page.type = $('body').attr('class').match(/woodypage-[a-z_\-]+/gi) != null ? $('body').attr('class').match(/woodypage-[a-z_\-]+/gi)[0].substr(10) : '';

    if (typeof frontendajax != "undefined" && frontendajax != null) {
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
                    page.lieu =  response[0];
                }
            }
        });
    }
}

window.dataLayer = window.dataLayer || [];
var eventCategory = 'PAGE|' + globals.post_title.trim() + '|' + globals.post_id,
eventPrefix = 'woody_',
page = {
    name: globals.post_title,
    id_page: globals.post_id,
    langue: window.siteConfig.current_lang,
    type: '',
    lieu: ''
};

$.when(getPlace()).then( function() {
    window.dataLayer.push({
        eventCategory: eventCategory,
        eventAction: 'Page vue',
        eventLabel: globals.post_title.trim() + '|' + globals.post_id,
        eventValue: '',
        event: eventPrefix +'page_view',
        page: page
    });
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

        window.dataLayer.push({
            eventCategory: eventCategory,
            eventAction: 'Clic bouton partager réseaux sociaux',
            eventLabel: socialshare,
            eventValue: '',
            event: eventPrefix +'click_social_network',
            page: page
        });
    });
}

if ($('.menu-social').length) {
    $('.menu-social').find('[data-network]').click(function(){

        window.dataLayer.push({
            eventCategory: eventCategory,
            eventAction: 'Clic bouton follow réseaux sociaux',
            eventLabel: $(this).attr('data-network'),
            eventValue: '',
            event: eventPrefix +'click_follow_network',
            page: page
        });
    });
}

$('.woody-component-claims-block .claim-content .claim-link .button').click(function() {
    window.dataLayer.push({
        eventCategory: eventCategory,
        eventAction: 'Clic sur bloc de publicité',
        eventLabel: $(this).closest('.claim-link').find('a').first().attr('href'),
        eventValue: '',
        event: eventPrefix +'click_claim',
        page: page
    });

    if (typeof ga !== 'undefined' && ga != null) {
        ga('rc.send', 'event', 'claim', 'CLIC_CLAIM_BUTTON', 'Clic claim button', undefined);
    }
});
