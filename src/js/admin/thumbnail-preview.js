const createThumbnailPreview = (field) => {
    let tplValue = field.querySelector('.acf-input input').value;

    if (tplValue) {
        const themeDistUrl = document.querySelector('#woody-theme-settings-footer').getAttribute('data-theme-dist-url');
        const thumbnailPrefixUrl = `${themeDistUrl}/img/woody-library/views/`;
        const themeVersion = document.querySelector('#woody-theme-settings-footer').getAttribute('data-version');
        let thumbnailUrl = `${thumbnailPrefixUrl}${tplValue.replaceAll('-', '/')}/thumbnail.png?version=${themeVersion}`;

        // On crée le nouveau nœud dand le DOM contenant la thumbnail
        let thumbnailWrapper = document.createElement('div');
        thumbnailWrapper.classList.add('woody-thumbnail-preview-wrapper');

        field.nextElementSibling.classList.remove('tpl-button-visible');

        fetch(thumbnailUrl)
            .then(response => {
                // Si l'URL de la thumbnail est valide, on créée sa preview dans le formulaire ACF
                if (response.ok) {
                    let thumbnailImage = document.createElement('img');
                    thumbnailImage.src = thumbnailUrl;
                    thumbnailImage.classList.add('woody-thumbnail-preview');
                    thumbnailWrapper.append(thumbnailImage);
                } else {
                    field.nextElementSibling.classList.add('tpl-button-visible');
                }
            }).catch(err => console.log('Error thumbnail preview : ', err));

        if (!field.nextElementSibling.querySelector('.woody-thumbnail-preview-wrapper')) {
            field.nextElementSibling.append(thumbnailWrapper);
        }

        field.nextElementSibling.addEventListener('click', (event) => {

            let buttonFieldWrapper = event.currentTarget;

            document.addEventListener('woodyTplUpdate', () => {
                let newThumbnailUrl = document.querySelector('.tpl-choice-wrapper.selected img').getAttribute('src');

                if (newThumbnailUrl) {
                    buttonFieldWrapper.classList.remove('tpl-button-visible');

                    fetch(newThumbnailUrl)
                        .then(response => {
                            // Si l'URL de la thumbnail est valide, on met à jour la source de la preview dans le formulaire ACF
                            if (response.ok) {
                                if (!buttonFieldWrapper.querySelector('.woody-thumbnail-preview-wrapper img')) {
                                    let thumbnailImage = document.createElement('img');
                                    thumbnailImage.src = newThumbnailUrl;
                                    thumbnailImage.classList.add('woody-thumbnail-preview');
                                    buttonFieldWrapper.querySelector('.woody-thumbnail-preview-wrapper').append(thumbnailImage);
                                } else {
                                    buttonFieldWrapper.querySelector('.woody-thumbnail-preview-wrapper img').src = newThumbnailUrl;
                                }
                            } else {
                                buttonFieldWrapper.classList.add('tpl-button-visible');
                            }
                        }).catch(err => console.log('Error thumbnail preview : ', err));
                }
            });
        });
    }
}

// Lorsqu'un nouveau bloc est ajouté dans une section, on ajoute la preview sur ce dernier
if (acf !== undefined && acf !== null) {
    acf.addAction('append', function ($el) {
        if ($el.find(".acf-field[data-name*='woody_tpl']")[0]) {
            createThumbnailPreview($el.find(".acf-field[data-name*='woody_tpl']")[0]);
        }
    });
}

// On crée la preview sur tous les champs qui contiennent 'woody_tpl' déjà présents dans le backoffice
const woodyTplFields = document.querySelectorAll(".acf-field[data-name*='woody_tpl']");

if (woodyTplFields.length > 0) {
    woodyTplFields.forEach(field => {
        createThumbnailPreview(field);
    });
}
