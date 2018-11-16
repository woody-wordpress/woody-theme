window.addEventListener("load", function() {
    var userLang = navigator.language || navigator.userLanguage;
    console.log("The Navigator language is: " + userLang);

    if (userLang.includes('fr-')) {
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

    window.cookieconsent.initialise({
        "palette": {
            "popup": {
                "background": "#252e39"
            },
            "button": {
                "background": "#14a7d0"
            }
        },
        onInitialise: function(status) {
            var type = this.options.type;
            var didConsent = this.hasConsented();
            if (type == 'opt-in' && didConsent) {
                // Enable cookies
                console.log('Enable cookies');
            }
            if (type == 'opt-out' && !didConsent) {
                // Disable cookies
                console.log('Disable cookies');
            }
        },
        onStatusChange: function(status, chosenBefore) {
            var didConsent = this.hasConsented();
            if (didConsent) {
                // Enable cookies
                console.log('Enable cookies');
            } else {
                // Disable cookies
                console.log('Disable cookies');
            }
        },
        onRevokeChoice: function() {
            var type = this.options.type;
            var didConsent = this.hasConsented();
            if (type == 'opt-in' && didConsent) {
                // Enable cookies
                console.log('Disable cookies');
            }
            if (type == 'opt-out' && !didConsent) {
                // Disable cookies
                console.log('Enable cookies');
            }
        },
        "type": "opt-out",
        "theme": "edgeless",
        "dismissOnScroll": true,
        "content": {
            "message": message,
            "dismiss": dismiss,
            "allow": allow,
            "deny": deny,
            "link": link,
            "href": "https://www.cnil.fr/fr/site-web-cookies-et-autres-traceurs",
            "policy": policy
        }
    })
});
