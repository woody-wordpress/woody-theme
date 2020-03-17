var style = getComputedStyle(document.body);
window.enableAnalyticsEvent = false;
window.enableCookiesEvent = false;

var getCookieBanner = function() {
    $.ajax({
        method: 'GET',
        url: frontendajax.ajaxurl,
        dataType: 'json',
        data: {
            action: 'get_cookie_banner'
        },
        success: function(response) {
            // append response
            $('body').prepend(response);
        },
        error: function(error) {
            console.warn('Unable to retrieve cookie options. An error has occured : ' + error);
        }
    });
};

var cookieconsent_status = Cookies.getJSON('cookieconsent_status');
if (cookieconsent_status == undefined) {
    getCookieBanner();

    // After DOM Inserted, ON CLICK functions
}

// Enable analytics
var enableAnalytics = function() {
    console.log('Enable Analytics');
    if (!window.enableAnalyticsEvent) {
        console.log('Push GTM Analytics');
        window.enableAnalyticsEvent = true;
        window.dataLayer.push({ event: 'analytics_enable' });
        window.dataLayer.push({
            event: 'analytics_enable_' + window.siteConfig.current_lang
        });
    }
};

// Disable analytics
var disableAnalytics = function() {
    console.log('Disable Analytics');
    window.dataLayer.push({ event: 'analytics_disable' });
    window.dataLayer.push({
        event: 'analytics_disable_' + window.siteConfig.current_lang
    });
};

// Enable cookies
var enableCookies = function() {
    console.log('Enable Cookies');
    if (!window.enableCookiesEvent) {
        console.log('Push GTM Cookies');
        window.enableCookiesEvent = true;
        window.dataLayer.push({ event: 'cookies_enable' });
        window.dataLayer.push({
            event: 'cookies_enable_' + window.siteConfig.current_lang
        });
    }
};

// Disable cookies
var disableCookies = function() {
    console.log('Disable Cookies');
    window.dataLayer.push({ event: 'cookies_disable' });
    window.dataLayer.push({
        event: 'cookies_disable_' + window.siteConfig.current_lang
    });
};

// if (document.cookie.indexOf('cookieconsent_status') == -1) {
//     enableAnalytics();
// }

// window.cookieconsent.initialise({
//     onInitialise: function(status) {
//         var hasConsent = this.hasConsented();
//         console.log('INITIALIZE COOKIECONSENT');
//         if (hasConsent) {
//             enableAnalytics();
//             enableCookies();
//         } else {
//             disableAnalytics();
//             disableCookies();
//         }
//     },
//     onStatusChange: function(status, chosenBefore) {
//         console.log('CHANGE COOKIECONSENT');

//         var hasConsent = this.hasConsented();
//         if (hasConsent && !window.sendPageView) {
//             enableAnalytics();
//             enableCookies();
//         } else {
//             disableAnalytics();
//             disableCookies();
//         }
//     },
//     onRevokeChoice: function() {
//         console.log('REVOKE COOKIECONSENT');
//         disableCookies();
//     },
//     theme: 'edgeless',
//     type: 'opt-out',
//     content: {
//         message: message,
//         dismiss: dismiss,
//         allow: allow,
//         deny: deny,
//         link: link,
//         href: 'https://www.cnil.fr/fr/site-web-cookies-et-autres-traceurs',
//         policy: policy
//     },
//     palette: {
//         popup: {
//             background: '#333'
//         },
//         button: {
//             background: style.getPropertyValue('--primary-color')
//         }
//     }
// });
