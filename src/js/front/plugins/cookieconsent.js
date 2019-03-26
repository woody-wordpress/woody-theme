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
} else {
    var message = "This website activates cookies by default for audience measurement and anonymous features.";
    var dismiss = "Got it!";
    var allow = "Allow cookies";
    var deny = "Decline";
    var link = "Learn more";
    var policy = "Cookie Policy";
}

// Enable cookies
var enableCookies = function() {
    console.log('Enable cookies');
    window.dataLayer.push({ 'event': 'cookies_enable' });
}

// Disable cookies
var disableCookies = function() {
    console.log('Disable cookies');
    window.dataLayer.push({ 'event': 'cookies_disable' });
}

if (document.cookie.indexOf('cookieconsent_status') == -1) {
    console.log('Starting');
    enableCookies();
}

window.cookieconsent.initialise({
    onInitialise: function(status) {
        var hasConsent = this.hasConsented();
        if (!hasConsent) {
            disableCookies();
        }
    },
    onStatusChange: function(status, chosenBefore) {
        var hasConsent = this.hasConsented();
        if (hasConsent) {
            enableCookies();
        } else {
            disableCookies();
        }
    },
    // onRevokeChoice: function() {
    //     disableCookies();
    // },
    // "dismissOnScroll": true,
    "dismissOnTimeout": 20000,
    // "dismissOnWindowClick": true,
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
