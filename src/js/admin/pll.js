if (document.body.classList.contains('langs-to-hide')) {
    document.body.classList.forEach(
        function(value, key) {
            if (value.includes('hide-')) {
                var lang = value.substr(5);
                var htrLang = document.getElementById('htr_lang_' + lang);
                if (htrLang) {
                    var htrLangParent = document.getElementById('htr_lang_' + lang).closest('tr');
                    if (htrLangParent) {
                        htrLangParent.remove();
                    }
                }
                var columnLanguage = document.getElementsByClassName('column-language_' + lang);
                for (let item of columnLanguage) {
                    item.innerHTML = '';;
                }

            }

        }
    )
}
