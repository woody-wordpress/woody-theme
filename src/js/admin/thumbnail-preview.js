const woodyTplFields = document.querySelectorAll(".layout:not(.acf-clone) .acf-field[data-name*='woody_tpl']");

console.log(woodyTplFields, 'tpl fields');

fetch('/wp-json/woody/components/templates').then((response) => response.json()).then((data) => {
    const woodyTemplates = data;

    // On crée la preview sur tous les champs qui contiennent 'woody_tpl' déjà présents dans le backoffice
    if (woodyTplFields.length > 0) {
        woodyTplFields.forEach(field => {
            createTemplatePreview(field, woodyTemplates);
        });
    }

    // Lorsqu'un nouvel élément (section ou bloc) est ajouté, on ajoute la preview sur ce dernier
    if (acf !== undefined && acf !== null) {
        acf.addAction('append', function ($el) {
            // console.log($el[0].find(".acf-field[data-name*='woody_tpl']"));
            // if ($el.find(".acf-field[data-name*='woody_tpl']")[0]) {
            //     createTemplatePreview($el.find(".acf-field[data-name*='woody_tpl']")[0], woodyTemplates);
            // }
            if ($el[0]) {
                console.log($el[0].querySelectorAll(".acf-field[data-name*='woody_tpl']"));

                // createTemplatePreview($el[0].find(".acf-field[data-name*='woody_tpl']"), woodyTemplates);
            }
        });
    }
}).catch((err) => {
    console.warn('Woody error : ', err);
});

const createTemplatePreview = (field, woodyTemplates) => {
    console.log(field, 'field');
    // console.log(field.children(".acf-field[data-name*='woody_tpl']"), 'all fields');

    let tplValue = field.querySelector('.acf-input input').value;

    if (tplValue) {
        const tplData = woodyTemplates[tplValue];
        let thumbnailUrl = tplData.thumbnail;
        let tlpName = tplData.name;

        // On crée le nouveau nœud dand le DOM contenant la thumbnail et le titre du template
        let previewWrapper = document.createElement('div');
        previewWrapper.classList.add('woody-template-preview-wrapper');

        let titleContainer = document.createElement('span');
        titleContainer.classList.add('woody-template-preview-title');
        titleContainer.innerHTML = tlpName;

        field.nextElementSibling.classList.remove('tpl-button-visible');

        // On crée l'image
        if (thumbnailUrl) {
            let thumbnailImage = document.createElement('img');
            thumbnailImage.src = thumbnailUrl;
            thumbnailImage.classList.add('woody-template-preview-thumbnail');
            previewWrapper.append(thumbnailImage);
        } else {
            field.nextElementSibling.classList.add('tpl-button-visible');
        }

        if (!field.nextElementSibling.querySelector('.woody-template-preview-wrapper')) {
            previewWrapper.append(titleContainer);
            field.nextElementSibling.append(previewWrapper);
        }

        // On met à jour l'image et le nom du template à chauqe changement de template
        field.nextElementSibling.addEventListener('click', (event) => {

            let buttonFieldWrapper = event.currentTarget;

            document.addEventListener('woodyTplUpdate', () => {
                let newThumbnailUrl = document.querySelector('.tpl-choice-wrapper.selected img').getAttribute('src');
                let newTplName = document.querySelector('.tpl-choice-wrapper.selected .tpl-title').innerText;

                // On met à jour le nom du template
                buttonFieldWrapper.querySelector('.woody-template-preview-title').innerText = newTplName;

                if (newThumbnailUrl) {
                    buttonFieldWrapper.classList.remove('tpl-button-visible');

                    // On met à jour l'image
                    if (newThumbnailUrl) {
                        if (!buttonFieldWrapper.querySelector('.woody-template-preview-wrapper img')) {
                            let thumbnailImage = document.createElement('img');
                            thumbnailImage.src = newThumbnailUrl;
                            thumbnailImage.classList.add('woody-thumbnail-preview');
                            buttonFieldWrapper.querySelector('.woody-template-preview-wrapper').append(thumbnailImage);
                        } else {
                            buttonFieldWrapper.querySelector('.woody-template-preview-wrapper img').src = newThumbnailUrl;
                        }
                    } else {
                        buttonFieldWrapper.classList.add('tpl-button-visible');
                    }
                }
            });
        });
    }
}
