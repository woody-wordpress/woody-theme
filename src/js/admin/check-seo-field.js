
import $ from 'jquery';

const checkBalise = (e, publish) => {
    $('#acf-group_5d7f7cd5615c0').each(function() {
        var $this = $(this);
        var input = $this.find('span[class="editable"]');
        var errorArray = [];
        var error = true;
        input.each(function(){
            var span = $(this);
            var textConent = span[0].textContent;
            if(textConent.replace(/\s+/g, '').length === 0){
                if(span[0].nextElementSibling !== null) {
                    errorArray.push(span[0].nextElementSibling.textContent.substring(0, span[0].nextElementSibling.textContent.length - 1));
                    error = false;
                }
            }
        })
        if(!error){
            alert("Les champs SEO '" + errorArray.join("', '") + "' ne sont pas remplis. Cela va impacter le SEO de votre site.");
        }
    })
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

