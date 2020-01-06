import $ from 'jquery';

window.dataLayer = window.dataLayer || [];

var obj = {
    eventCategory: 'Pages',
    eventAction: 'Page vue',
    eventLabel: globals.post_title + '|' + globals.post_id,
    eventValue: '',
    page: {
        name: globals.post_title,
        langue: window.siteConfig.current_lang,
        lieu: ''
    }
};

var getPlace = function() {
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
                obj.lieu =  response[0];
            }
        },
        error: function(err) {
            console.error(err);
        }
    });
}

$.when(getPlace()).then( function() {
    window.dataLayer.push(obj);
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
    // TODO: voir comment réecrire l'event GTM concernant les blocs de pub
    // var obj = {
    //     eventCategory: 'Pages',
    //     eventAction: 'Clic sur bloc de publicité',
    //     eventLabel: 'Clic claim button',
    //     eventValue: '',
    //     page: {
    //         name: globals.post_title
    //     }
    // };

    window.dataLayer.push({
        event: 'Clic claim button',
    });
    if (typeof ga !== 'undefined' && ga != null) {
        ga('rc.send', 'event', 'claim', 'CLIC_CLAIM_BUTTON', 'Clic claim button', undefined);
    }
});
