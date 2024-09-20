const checkBalise = () => {
    const checkTitle = document.querySelector('[id=acf-field_5b2bbb4aec6b1]');
    const checkTitleSwitch = checkTitle !== null ? checkTitle.nextElementSibling : '';

    if (checkTitleSwitch !== null && !checkTitleSwitch.className.includes('-on')) {
        const checkHeroTitle = document.querySelector('[id=acf-field_5b052bbab3867-field_5b052bbab3867_field_5b041d61adb72]');
        const checkHeroImg = document.querySelector('[name="acf[field_5b0e5ddfd4b1b]"]');
        if (checkHeroTitle !== null && (!checkHeroTitle.value || !checkHeroImg.value)) {
            alert("Le champs Titre ou l'image du Visuel et accroche n'est pas rempli. Vous n'aurez pas de balise <h1> sur votre page. Cela impacte négativement votre SEO.");
        }
    }
};

if (!document.body.classList.contains('post-type-acf-field-group')) {
    if (document.getElementById('publish')) {
        document.getElementById('publish').addEventListener('click', e => { checkBalise(); });
    }
    if (document.getElementById('save-post')) {
        document.getElementById('save-post').addEventListener('click', e => { checkBalise(); });
    }

    window.onbeforeunload = null;
}
