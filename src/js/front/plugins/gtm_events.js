import $ from 'jquery';

window.dataLayer = window.dataLayer || [];
var eventCategory = 'PAGE|' + globals.post_title.trim() + '|' + globals.post_id,
eventPrefix = 'woody_',
page = {
    id_page: globals.post_id,
    name: globals.post_title,
    lang: window.siteConfig.current_lang,
    season: window.siteConfig.current_season,
    page_type: globals.page_type,
    tags: globals.tags
};

window.dataLayer.push({
    "event": eventPrefix +'page_view',
    "data": {
        "ga": {
            "category": eventCategory,
            "action": 'Page vue',
            "label": globals.post_title.trim() + '|' + globals.post_id,
        },
        "page": page
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

        window.dataLayer.push({
            "event": eventPrefix +'click_social_network',
            "data": {
                "ga": {
                    "category": eventCategory,
                    "action":  'Clic bouton partager réseaux sociaux',
                    "label": socialshare
                },
                "page": page
            }
        });
    });
}

if ($('.menu-social').length) {
    $('.menu-social').find('[data-network]').click(function(){
        window.dataLayer.push({
            "event": eventPrefix +'click_follow_network',
            "data": {
                "ga": {
                    "category": eventCategory,
                    "action": 'Clic bouton follow réseaux sociaux',
                    "label": $(this).attr('data-network'),
                },
                "page": page
            }
        });
    });
}

$('.woody-component-claims-block .claim-content .claim-link .button').click(function() {
    window.dataLayer.push({
        "event": eventPrefix +'click_claim',
        "data": {
            "ga": {
                "category": eventCategory,
                "action": 'Clic sur bloc de publicité',
                "label": $(this).closest('.claim-link').find('a').first().attr('href'),
            },
            "page": page
        }
    });

    if (typeof ga !== 'undefined' && ga != null) {
        ga('rc.send', 'event', 'claim', 'CLIC_CLAIM_BUTTON', 'Clic claim button', undefined);
    }
});
