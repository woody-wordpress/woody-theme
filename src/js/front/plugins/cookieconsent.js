var style = getComputedStyle(document.body);
var userLang = navigator.language || navigator.userLanguage;
console.log("The Navigator language is: " + userLang);

if (userLang.includes('fr-') || userLang == 'fr') {
    var message = "Ce site web active par défaut des cookies de mesure d'audience et pour des fonctionnalités anonymes.";
    var dismiss = "OK je comprends !";
    var allow = "J'accepte les cookies";
    var deny = "Je ne veux pas de cookies";
    var link = "En savoir plus";
    var policy = "Règles sur les cookies";
} else if (userLang.includes('de-') || userLang == 'de') {
    var message = "Diese Website aktiviert standardmäßig Cookies zur Zielgruppenmessung und für anonyme Funktionen.";
    var dismiss = "Okay, ich verstehe!";
    var allow = "Ich akzeptiere cookies";
    var deny = "Ich will keine cookies";
    var link = "Mehr erfahren";
    var policy = "Richtlinien für Cookies";
} else if (userLang.includes('nl-') || userLang == 'nl') {
    var message = "Deze website activeert standaard cookies voor publieksmeting en anonieme functies.";
    var dismiss = "Oké, ik begrijp het!";
    var allow = "Ik accepteer cookies";
    var deny = "Ik wil geen cookies";
    var link = "Meer informatie";
    var policy = "Cookiebeleid";
} else if (userLang.includes('es-') || userLang == 'es') {
    var message = "Este sitio web activa las cookies de forma predeterminada para la medición de la audiencia y las funciones anónimas.";
    var dismiss = "¡Bien, lo entiendo!";
    var allow = "Ik accepteer cookies";
    var deny = "No quiero galletas";
    var link = "Conozca más";
    var policy = "Política sobre Cookies";
} else if (userLang.includes('it-') || userLang == 'it') {
    var message = "Questo sito web attiva di default i cookie per la misurazione dell'audience e le funzioni anonime.";
    var dismiss = "Ok, ho capito!";
    var allow = "Accetto i cookie";
    var deny = "Non voglio i biscotti";
    var link = "Per saperne di più";
    var policy = "Politica sui cookie";
} else {
    var message = "This website activates cookies by default for audience measurement and anonymous features.";
    var dismiss = "Got it!";
    var allow = "Allow cookies";
    var deny = "Decline";
    var link = "Learn more";
    var policy = "Cookie Policy";
}

// Enable analytics
var enableAnalytics = function() {
    console.log('Enable Analytics');
    window.dataLayer.push({ 'event': 'analytics_enable' });
    window.dataLayer.push({ 'event': 'analytics_enable_' + window.siteConfig.current_lang });
}

// Disable analytics
var disableAnalytics = function() {
    console.log('Disable Analytics');
    window.dataLayer.push({ 'event': 'analytics_disable' });
    window.dataLayer.push({ 'event': 'analytics_disable_' + window.siteConfig.current_lang });
}

// Enable cookies
var enableCookies = function() {
    console.log('Enable Cookies');
    window.dataLayer.push({ 'event': 'cookies_enable' });
    window.dataLayer.push({ 'event': 'cookies_enable_' + window.siteConfig.current_lang });
}

// Disable cookies
var disableCookies = function() {
    console.log('Disable Cookies');
    window.dataLayer.push({ 'event': 'cookies_disable' });
    window.dataLayer.push({ 'event': 'cookies_disable_' + window.siteConfig.current_lang });
}

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
        if (hasConsent) {
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
    "theme": "edgeless",
    "type": "opt-out",
    "content": {
        "message": message,
        "dismiss": dismiss,
        "allow": allow,
        "deny": deny,
        "link": link,
        "href": "https://www.cnil.fr/fr/site-web-cookies-et-autres-traceurs",
        "policy": policy
    },
    "palette": {
        "popup": {
            "background": "#333"
        },
        "button": {
            "background": style.getPropertyValue('--primary-color')
        }
    }
})
