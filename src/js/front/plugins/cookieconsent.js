var style = getComputedStyle(document.body);
window.enableAnalyticsEvent = false;
window.enableCookiesEvent = false;

if (window.siteConfig.current_lang.indexOf('fr') != -1) {
    var message =
        "Ce site web active par défaut des cookies de mesure d'audience et pour des fonctionnalités anonymes.";
    var dismiss = 'OK je comprends !';
    var allow = "J'accepte les cookies";
    var deny = 'Je ne veux pas de cookies';
    var link = 'En savoir plus';
    var policy = 'Règles sur les cookies';
} else if (window.siteConfig.current_lang.indexOf('de') != -1) {
    var message =
        'Diese Website nutzt für ihre Funktion und Analyse anoynm Cookies.';
    var dismiss = 'Okay, ich verstehe!';
    var allow = 'Cookies akzeptieren';
    var deny = 'Keine Cookies';
    var link = 'Mehr erfahren';
    var policy = 'Richtlinien für Cookies';
} else if (window.siteConfig.current_lang.indexOf('nl') != -1) {
    var message =
        'Deze website maakt gebruik van anonieme functionele en analytische cookies.';
    var dismiss = 'Oké, ik begrijp het!';
    var allow = 'Cookies accepteren';
    var deny = 'Cookies weigeren';
    var link = 'Meer informatie';
    var policy = 'Cookiebeleid';
} else if (window.siteConfig.current_lang.indexOf('es') != -1) {
    var message =
        'Esta página web activa las cookies de forma predeterminada para la medición de la audiencia y las funciones anónimas.';
    var dismiss = '¡Bien, lo entiendo!';
    var allow = 'Acepto cookies';
    var deny = 'No acepto cookies';
    var link = 'Más información';
    var policy = 'Política sobre Cookies';
} else if (window.siteConfig.current_lang.indexOf('it') != -1) {
    var message =
        "Questo sito web attiva di default i cookies per la misurazione dell'audience e le funzioni anonime.";
    var dismiss = 'Ok, ho capito!';
    var allow = 'Accetto i cookies';
    var deny = 'Non voglio i cookies';
    var link = 'Per saperne di più';
    var policy = 'Politica sui cookies';
} else if (window.siteConfig.current_lang.indexOf('ja') != -1) {
    var message =
        'このWebサイトは、デフォルトでオーディエンス測定および匿名機能のためにCookieをアクティブにします。';
    var dismiss = 'わかりました';
    var allow = 'クッキーを受け入れる';
    var deny = 'クッキーを受け入れない';
    var link = 'もっと知りたい';
    var policy = 'クッキーポリシー';
} else {
    var message =
        'This website activates cookies by default for audience measurement and anonymous features.';
    var dismiss = 'Got it!';
    var allow = 'Allow cookies';
    var deny = 'Decline';
    var link = 'Learn more';
    var policy = 'Cookies Policy';
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

if (document.cookie.indexOf('cookieconsent_status') == -1) {
    enableAnalytics();
}

window.cookieconsent.initialise({
    onInitialise: function(status) {
        var hasConsent = this.hasConsented();
        if (hasConsent) {
            enableAnalytics();
            enableCookies();
        } else {
            disableAnalytics();
            disableCookies();
        }
    },
    onStatusChange: function(status, chosenBefore) {
        var hasConsent = this.hasConsented();
        if (hasConsent && !window.sendPageView) {
            enableAnalytics();
            enableCookies();
        } else {
            disableAnalytics();
            disableCookies();
        }
    },
    onRevokeChoice: function() {
        disableCookies();
    },
    theme: 'edgeless',
    type: 'opt-out',
    content: {
        message: message,
        dismiss: dismiss,
        allow: allow,
        deny: deny,
        link: link,
        href: 'https://www.cnil.fr/fr/site-web-cookies-et-autres-traceurs',
        policy: policy
    },
    palette: {
        popup: {
            background: '#333'
        },
        button: {
            background: style.getPropertyValue('--primary-color')
        }
    }
});
