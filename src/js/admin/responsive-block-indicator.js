const posts = document.querySelectorAll('#post');

if (posts.length > 0) {
    posts.forEach(() => {

        const createResponsiveIndicator = (referenceElement) => {
            let layout = referenceElement.closest('.layout:not(.acf-clone)');
            let fieldGroupResponsive = layout.querySelector("[data-name='display_block_responsive']");

            // Si le layout possède le champ 'display_block_responsive'
            if (fieldGroupResponsive != null) {
                // On crée le nouveau nœud dand le DOM contenant l'indicateur responsive pour le desktop et le mobile
                let newNode = document.createElement('div');
                newNode.classList.add('responsive-block-indicator');
                newNode.innerHTML = getResponsiveIndicatorHtml();
                referenceElement.append(newNode);

                let radios = fieldGroupResponsive.querySelectorAll("input[type=radio]");

                if (radios.length > 0) {
                    radios.forEach(radio => {
                        let choice = radio.value;
                        let desktopIndicator = layout.querySelector('.responsive-block-indicator .desktop');
                        let mobileIndicator = layout.querySelector('.responsive-block-indicator .mobile');

                        if (choice != null && desktopIndicator != null && mobileIndicator != null) {
                            // Mise à jour de l'indicateur responsive à chaque action sur un bouton radio
                            radio.addEventListener('change', function () {
                                updateResponsiveIndicatorStates(choice, desktopIndicator, mobileIndicator);
                            });

                            // Synchronisation des valeurs des boutons radio avec l'indicateur responsive au chargement de la page
                            if (radio.checked) {
                                updateResponsiveIndicatorStates(choice, desktopIndicator, mobileIndicator);
                            }
                        }
                    });

                    layout.querySelector('.acf-fc-layout-order').classList.add('has-responsive-block-indicator');
                }
            }
        }

        const updateResponsiveIndicatorStates = (radioValue, desktopButtonElement, mobileButtonElement) => {
            switch (radioValue) {
                case 'desktop':
                    desktopButtonElement.classList.add('active');
                    mobileButtonElement.classList.remove('active');
                    desktopButtonElement.setAttribute('title', 'Visible sur desktop');
                    mobileButtonElement.setAttribute('title', 'Invisible sur mobile');
                    break;
                case 'mobile':
                    mobileButtonElement.classList.add('active');
                    desktopButtonElement.classList.remove('active');
                    mobileButtonElement.setAttribute('title', 'Visible sur mobile');
                    desktopButtonElement.setAttribute('title', 'Invisible sur desktop');
                    break;
                default:
                    desktopButtonElement.classList.add('active');
                    mobileButtonElement.classList.add('active');
                    desktopButtonElement.setAttribute('title', 'Visible sur desktop');
                    mobileButtonElement.setAttribute('title', 'Visible sur mobile');
                    break;
            }
        }

        const getResponsiveIndicatorHtml = () => {
            let html = `<span class="desktop active acf-js-tooltip" data-name="responsive-block"></span><span class="mobile active acf-js-tooltip" data-name="responsive-block"></span>`;

            return html;
        }

        // Lorsqu'un nouveau bloc est ajouté dans une section, on ajoute l'indicateur responsive sur ce dernier
        if (acf !== undefined && acf !== null) {
            acf.addAction('append', function ($el) {
                if ($el.find('.acf-fc-layout-handle')[0]) {
                    createResponsiveIndicator($el.find('.acf-fc-layout-handle')[0]);
                }
            });
        }

        // On crée l'indicateur responsive sur tous les blocs déjà présents dans le backoffice
        const blocks = document.querySelectorAll("div[data-name='section_content'] .layout:not(.acf-clone)");

        if (blocks.length > 0) {
            blocks.forEach(block => {
                let acfLayoutHandle = block.querySelector('.acf-fc-layout-handle');

                if (acfLayoutHandle != null) {
                    createResponsiveIndicator(acfLayoutHandle);
                }
            });
        }
    });
}
