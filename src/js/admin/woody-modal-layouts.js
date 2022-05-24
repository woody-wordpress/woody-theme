const layoutGroupsObject = [
    {
        name: 'custom',
        title: 'Blocs personnalisés',
        layouts: []
    },
    {
        name: 'components',
        title: 'Composants',
        layouts: [
            'links',
            'accordion',
            'tabs_group',
            'geo_map',
            'spacer_block'
        ]
    },
    {
        name: 'edito',
        title: 'Éditorial',
        layouts: [
            'free_text',
            'call_to_action',
            'feature',
            'quote',
            'page_summary',
            'testimonials',
            'story'
        ]
    },
    {
        name: 'focus',
        title: 'Mise en avant',
        layouts: [
            'manual_focus',
            'auto_focus',
            'auto_focus_sheets',
            'sheet_summary',
            'manual_focus_minisheet',
            'content_list',
            'semantic_view',
            'profile_focus',
            'auto_focus_topics'
        ]
    },
    {
        name: 'multimedia',
        title: 'Multimédia',
        layouts: [
            'gallery',
            'interactive_gallery',
            'movie',
            'audio_player',
            'eye_candy_img'
        ]
    },
    {
        name: 'social',
        title: 'Social',
        layouts: [
            'socialwall',
            'link_social_shares',
            'socialize',
            'disqus_block'
        ]
    },
    {
        name: 'widgets',
        title: 'Widgets',
        layouts: [
            'snowflake',
            'claims',
            'favorites',
            'sitemap',
            'weather',
            'tides',
            'deal',
            'quotation'
        ]
    }
];

// On crée un nouvel objet à partir du précédent, sous la forme suivante : {[ name => [layouts] ]}
const simpleLayoutGroupsObject = Object.assign(...layoutGroupsObject.map(({ name, layouts }) => ({ [name]: layouts })));

document.querySelectorAll(".acf-field[data-name='section'], [data-name='light_section_content']").forEach(() => {
    function sortWoodyLayoutsByGroups(field) {

        field.$el.find('.button[data-name="add-layout"]').on('click', () => {
            var getfcTooltip = setInterval(() => {
                let acfTooltipPopUp = document.querySelector('.acf-tooltip.acf-fc-popup');
                let acfLayoutChoices = document.querySelectorAll('.acf-tooltip.acf-fc-popup>ul>li>a[data-layout]');

                if (acfLayoutChoices.length > 0) {
                    clearInterval(getfcTooltip);

                    // Création du header contenant la recherche et les filtres
                    let layoutHeader = document.createElement('div');
                    layoutHeader.classList.add('layout-header');

                    // Création de l'input de recherche de layout
                    let layoutSearchInput = document.createElement('input');
                    layoutSearchInput.classList.add('layout-search-input');
                    layoutSearchInput.setAttribute('placeholder', 'Rechercher un bloc');
                    layoutHeader.append(layoutSearchInput);

                    // Création de la barre de filtres
                    let layoutFiltersTitle = document.createElement('span');
                    layoutFiltersTitle.classList.add('layout-filters-title');
                    layoutFiltersTitle.innerText = 'Filtrer par :';
                    layoutHeader.append(layoutFiltersTitle);

                    let layoutFiltersList = document.createElement('ul');
                    layoutFiltersList.classList.add('layout-filters-list');
                    layoutHeader.append(layoutFiltersList);

                    // Création du filtre "Tous" et application de la classe .active par défaut
                    let layoutFilterActive = document.createElement('li');
                    layoutFilterActive.classList.add('layout-filter');
                    layoutFilterActive.classList.add('layout-filter-all');
                    layoutFilterActive.classList.add('active');
                    layoutFilterActive.setAttribute('data-group', 'all');
                    layoutFilterActive.innerText = 'Tous';
                    layoutFiltersList.append(layoutFilterActive);

                    // Création de la liste de blocs
                    let layoutBlocksList = document.createElement('ul');
                    layoutBlocksList.classList.add('layout-blocks-list');

                    acfTooltipPopUp.append(layoutHeader);
                    acfTooltipPopUp.append(layoutBlocksList);

                    Object.keys(layoutGroupsObject).forEach(group => {
                        // Création du filtre associé à un groupe de blocs
                        let layoutFilter = document.createElement('li');
                        layoutFilter.classList.add('layout-filter');
                        layoutFilter.classList.add(`layout-filter-${layoutGroupsObject[group].name}`);
                        layoutFilter.setAttribute('data-group', `${layoutGroupsObject[group].name}`);
                        layoutFilter.innerText = layoutGroupsObject[group].title;
                        layoutFilter.setAttribute('data-blocks', layoutGroupsObject[group].layouts);
                        layoutFiltersList.append(layoutFilter);
                    });

                    acfLayoutChoices.forEach(layout => {
                        let layoutName = layout.getAttribute('data-layout'); // On récupère le nom du layout
                        let layoutLi = layout.closest('li');
                        layoutLi.classList.add('flexible-layout-item');
                        layoutLi.classList.add(layoutName);
                        layoutLi.setAttribute('data-layout', layoutName);

                        // On attribue un groupe à chaque choix de layout
                        for (const groupName in simpleLayoutGroupsObject) {
                            if (simpleLayoutGroupsObject[groupName].includes(layoutName)) {
                                layoutLi.setAttribute('data-group', groupName);
                            }
                        }

                        layoutBlocksList.append(layoutLi);
                    });

                    // Tous les choix de layout qui n'ont pas de [data-group] sont considérés comme des layouts appartenant au groupe "Blocs personnalisés (custom)"
                    document.querySelectorAll('.flexible-layout-item[data-layout]:not([data-group])').forEach(item => {
                        item.setAttribute('data-group', 'custom');
                    });

                    // On supprime la liste <ul></ul> vide qui ne nous sert plus
                    acfTooltipPopUp.querySelector('ul').remove();

                    // Gestion de la recherche
                    document.querySelector('.layout-search-input').addEventListener('click', (event) => {
                        event.stopImmediatePropagation(); // Empêche la fermeture de la pop up au clic dans l'input

                        // Affiche / masque les blocs selon la recherche
                        document.querySelector('.layout-search-input').addEventListener('keyup', (event) => {
                            // On supprime le filtre actif et on le remet sur "Tous"
                            document.querySelector('.layout-filter.active').classList.remove('active');
                            document.querySelector('.layout-filter-all').classList.add('active');

                            var typingText = event.target.value;
                            var expression = new RegExp(typingText, 'i'); // Le texte tapé devient notre expression régulière
                            document.querySelectorAll('.flexible-layout-item').forEach(layoutItem => {
                                if (expression.test(layoutItem.querySelector('a[data-layout]').innerText)) {
                                    layoutItem.style.display = 'initial';
                                } else {
                                    layoutItem.style.display = 'none';
                                }
                            });
                        });
                    }, true);

                    // Gestion des filtres
                    const layoutFiltersContainer = document.querySelector('.layout-filters-list');
                    const layoutItems = document.querySelectorAll('.flexible-layout-item');

                    if (layoutFiltersContainer != null && layoutItems.length > 0) {
                        layoutFiltersContainer.addEventListener('click', (event) => {
                            event.stopImmediatePropagation(); // Empêche la fermeture de la pop up au clic du filtre

                            // On nettoie l'input du champ de recherche au clic sur l'un des filtres
                            document.querySelector('.layout-search-input').value = '';

                            if (event.target.classList.contains('layout-filter')) {
                                layoutFiltersContainer.querySelector('.active').classList.remove('active');

                                event.target.classList.add('active');
                                const filterValue = event.target.getAttribute('data-group');
                                layoutItems.forEach((item) => {
                                    if (item.getAttribute('data-group') === filterValue || filterValue === 'all') {
                                        item.style.display = 'block';
                                    } else {
                                        item.style.display = 'none';
                                    }
                                });
                            }
                        }, true);
                    }
                }
            }, 100);
        });
    }

    if (acf !== undefined && acf !== null) {
        // On trie les layouts lors de l'ajout d'un bloc dans une section
        acf.addAction('load_field/name=section_content', sortWoodyLayoutsByGroups);
        acf.addAction('append_field/name=section_content', sortWoodyLayoutsByGroups);

        // On trie les layouts lors de l'ajout d'un bloc dans une section light pour les groupes d'onglets
        acf.addAction('load_field/name=light_section_content', sortWoodyLayoutsByGroups);
        acf.addAction('append_field/name=light_section_content', sortWoodyLayoutsByGroups);
    }
});
