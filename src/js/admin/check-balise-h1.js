
import $ from 'jquery';

const checkBalise = (e, publish) => {
    const checkTitle = document.querySelector('[id=acf-field_5b2bbb4aec6b1]');
    const checkTitleSwitch = checkTitle.nextElementSibling;
    
    if(!checkTitleSwitch.className.includes("-on")){
        const checkVisualTitle = document.querySelector('[id=acf-field_5b052bbab3867-field_5b052bbab3867_field_5b041d61adb72]');
        const checkImg = document.querySelector('[name="acf[field_5b0e5ddfd4b1b]"]');
        if(!checkVisualTitle.value || !checkImg.value) {
            alert("Le champs Titre ou l'image de l'onglet Visuel et Accroche n'est pas remplie, cela impact négativement votre SEO");
        }
    }
};

if (!$('body').hasClass('post-type-acf-field-group')) {
    if (document.getElementById('publish')) {
        document.getElementById('publish').addEventListener('click', e => { checkBalise(e, true); });
    }
    if (document.getElementById('save-post')) {
        document.getElementById('save-post').addEventListener('click', e => { checkBalise(e, false); });
    }

    window.onbeforeunload = null;
}
