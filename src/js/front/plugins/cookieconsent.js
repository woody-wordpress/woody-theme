var style = getComputedStyle(document.body);
window.enableAnalyticsEvent = false;
window.enableCookiesEvent = false;

var getCookieBanner = function() {
    if (typeof frontendajax != "undefined" && frontendajax != null) {
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
                // After DOM Inserted, ON CLICK functions
                initialiseCookieEvents();
            },
            error: function(error) {
                console.warn('Unable to create cookie banner. An error has occured : ' + error);
            }
        });
    } else {
        console.log("no ajaxurl");
    }
};


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

// On click events, set cookieconsent_status + enable or disable cookies
var initialiseCookieEvents = function() {
    $('.cc-allow').on('click', function() {
        Cookies.set('cookieconsent_status', true);
        console.log('ALLOW COOKIECONSENT');
        enableAnalytics();
        enableCookies();

        // Hide window
        $('.cc-window').css("display", "none");
    });

    $('.cc-deny').on('click', function(){
        Cookies.set('cookieconsent_status', false);
        console.log('REVOKE COOKIECONSENT');
        disableAnalytics();
        disableCookies();

        // Hide window
        $('.cc-window').css("display", "none");
    });

    $('.cc-personalize').on('click', function(){
        Cookies.set('cookieconsent_status', true);
        Cookies.set('cookies_options_enabled', {});

        $('.cc-option').each(function() {
            var cookie_options = Cookies.getJSON('cookies_options_enabled');
            var input = $(this).find('.switch-input');
            var name = input.attr('name');

            if (input.prop("checked") == true) {
                // Enable
                window.dataLayer.push({
                    event: name + '_enable'
                });
                window.dataLayer.push({
                    event: name + '_enable_' + window.siteConfig.current_lang
                });
                cookie_options[name] = true;
            } else {
                // Disable
                window.dataLayer.push({
                    event: name + '_disable'
                });
                window.dataLayer.push({
                    event: name + '_disable_' + window.siteConfig.current_lang
                });
                cookie_options[name] = false;
            }
            Cookies.set('cookies_options_enabled', cookie_options);
        });

        // Hide window
        $('.cc-window').css("display", "none");
    });
};

var cookieconsent_status = Cookies.getJSON('cookieconsent_status');
if (typeof cookieconsent_status == "undefined" || cookieconsent_status == null ) {
    getCookieBanner();
} else {
    if(cookieconsent_status == false){
        disableAnalytics();
        disableCookies();
    } else {
        enableAnalytics();
        enableCookies();
        var cookie_options = Cookies.getJSON('cookies_options_enabled');

        $.each(cookie_options, function(key, value){
            if (value == true) {
                // Enable
                window.dataLayer.push({
                    event: key + '_enable'
                });
                window.dataLayer.push({
                    event: key + '_enable_' + window.siteConfig.current_lang
                });
            } else {
                // Disable
                window.dataLayer.push({
                    event: key + '_disable'
                });
                window.dataLayer.push({
                    event: key + '_disable_' + window.siteConfig.current_lang
                });
            }
        });

    }
}

