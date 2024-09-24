const checkBalise = () => {
    let displayAlert = false;

    const checkPageTeaserTitle = document.querySelector('[id=acf-field_5b2bbb4aec6b1]');
    const pageTeaserTitleSwitch = checkPageTeaserTitle !== null ? checkPageTeaserTitle.nextElementSibling : '';

    if (pageTeaserTitleSwitch !== null && !pageTeaserTitleSwitch.className.includes('-on')) {
        const heroTitle = document.querySelector('[id=acf-field_5b052bbab3867-field_5b052bbab3867_field_5b041d61adb72]');
        const heroImg = document.querySelector('[name="acf[field_5b0e5ddfd4b1b]"]');
        if ((heroTitle !== null && !heroTitle.value) && (heroImg !== null && !heroImg.value)) {
            displayAlert = true;
        }
    }

    if (displayAlert) {
        alert("Le champ \"Titre\" du visuel et accroche n'est pas rempli ou \"Afficher le titre de la page\" de l'en-tête de page n'est pas coché. Vous n'aurez pas de balise <h1> sur votre page. Cela impacte négativement votre SEO.");
    }
};

if (!document.body.classList.contains('post-type-acf-field-group')) {
    if (document.getElementById('publish')) {
        document.getElementById('publish').addEventListener('click', () => { checkBalise(); });
    }
    if (document.getElementById('save-post')) {
        document.getElementById('save-post').addEventListener('click', () => { checkBalise(); });
    }

    window.onbeforeunload = null;
}
