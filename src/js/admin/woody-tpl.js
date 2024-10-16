!(function ($) {
    $('#post').each(function () {
        const accepted_post = [
            'page',
            'woody_claims',
            'woody_rdbk_leaflets',
            'woody_model',
            'tourtrip_step',
            'woody_section_model'
        ];

        if (accepted_post.includes($('#post_type').val()) || $("[data-name=menu_items]").length) {

            var tplFilterAction = {};
            var tplFiltersValues = {};

            const fieldKeysFilters = {
                'focuses': [
                    'type',
                    'length',
                    'infinite',
                    'card_type',
                    'img_ratio',
                    'text_align'
                ],
                'highlights': [
                    'type',
                    'length',
                    'infinite',
                    'card_type',
                    'img_ratio',
                    'text_align'
                ],
                'galleries': [
                    'type',
                    'length',
                    'infinite',
                    'img_ratio'
                ],
                'submenus': [
                    'img_ratio',
                    'text_align'
                ],
                'heroes': [
                    'img_ratio',
                    'text_align'
                ],
                'landswipers': [
                    'img_ratio',
                    'text_align'
                ],
                'feature_v2': [
                    'length',
                    'text_align'
                ]
            };

            const tplFilters = {
                'type': {
                    'callback': '',
                    'label': '<label for="tpl_type">Type</label>',
                    'markup': '<select data-filter="type" name="tpl_type" id="tpl_type"><option value="all">Tous les types</option><option value="grid">Grille</option><option value="slider">Slider</option><option value="mozaic">Mosaïque</option><option value="map">Carte</option></select>'
                },
                'length': {
                    'callback': '',
                    'label': '<label for="tpl_length">Eléments/Multiple</label>',
                    'markup': '<select data-filter="length" name="tpl_length" id="tpl_length"><option value="all">Choisir</option><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option></select>',
                },
                'infinite': {
                    'callback': '',
                    'label': '<label for="tpl_infinite">Illimité</label>',
                    'markup': '<select data-filter="infinite" name="tpl_infinite" id="tpl_infinite"><option value="all">Peu importe</option><option value="true">Oui</option><option value="false">Non</option></select>',
                },
                'card_type': {
                    'callback': '',
                    'label': '<label for="card_type">Vignette</label>',
                    'markup': '<select data-filter="card_type" name="card_type" id="card_type"><option value="all">Tous les styles</option><option value="overlay">Textes sur l\'image</option><option value="basic">Textes sous l\'image</option><option value="split">Textes à côté l\'image</option><option value="mixed">Mixte</option></select>',
                },
                'text_align': {
                    'callback': '',
                    'label': '<label for="text_align_h">Alignement des textes</label>',
                    'markup': '<select data-filter="text_align" name="text_align_h" id="text_align_h"><option value="all">Peu importe</option><option class="h-only" value="left">Gauche</option><option class="h-only" value="center">Centrés</option><option value="topleft">Haut/Gauche</option><option value="middleleft">Milieu/Gauche</option><option value="bottomleft">Bas/Gauche</option><option value="topcenter">Haut/Centre</option><option value="middlecenter">Milieu/Centre</option><option value="bottomcenter">Bas/Centre</option><option value="topright">Haut/Droite</option><option value="middleright">Milieu/Droite</option><option value="bottomright">Bas/Droite</option></select>',
                },
                'img_ratio': {
                    'callback': '',
                    'label': '<label for="img_ratio">Format d\'image</label>',
                    'markup': '<select data-filter="img_ratio" name="img_ratio" id="img_ratio"><option value="all">Tous les formats</option><option value="8_1">Pano A</option><option value="4_1">Pano B</option><option value="3_1">Pano C</option><option value="2_1">Paysage A</option><option value="16_9">Paysage B</option><option value="4_3">Paysage C</option><option value="square">Carré</option><option value="3_4">Portrait A</option><option value="10_16">Portrait B</option><option value="a4">A4</option><option value="free">Libre</option></select>',
                },
                'custom_tpl': {
                    'callback': '',
                    'label': '<label for="custom_tpl">Propre au site</label>',
                    'markup': '<select data-filter="custom_tpl" name="custom_tpl" id="custom_tpl"><option value="all">Peu importe</option><option value="true">Oui</option><option value="false">Non</option></select>',
                }
            }

            tplFilterAction = function ($el) {
                var filter = $el.data('filter');
                tplFiltersValues[$el.data('filter')] = $el.val();

                if (filter == 'card_type') {
                    if (tplFiltersValues[filter] == 'overlay') {
                        $('select[data-filter="text_align"] .h-only').css('display', 'none');
                        $('select[data-filter="text_align"] :not(.h-only)').css('display', 'block');
                    } else if (tplFiltersValues[filter] == 'all') {
                        $('select[data-filter="text_align"] .h-only').css('display', 'block');
                        $('select[data-filter="text_align"] :not(.h-only)').css('display', 'block');
                    } else {
                        $('select[data-filter="text_align"] .h-only').css('display', 'block');
                        $('select[data-filter="text_align"] :not(.h-only)').css('display', 'none');
                    }
                }

                $('#tpls_popin .tpl-choice-wrapper').each(function () {
                    var $this = $(this);
                    var displayOptions = $this.data('display-options');

                    $this.parent().removeClass('filtered');

                    if (displayOptions.length == 0 && !$this.parent().hasClass('hidden')) {
                        $this.parent().addClass('filtered');
                    } else {
                        $.each(displayOptions[0], function (key, value) {

                            if (key == 'roadbook') {
                                return;
                            } else if (key == 'img_ratio') {
                                if (tplFiltersValues[key] != 'all' && value.includes(tplFiltersValues[key]) == false) {
                                    if (!$this.parent().hasClass('hidden')) {
                                        $this.parent().addClass('filtered');
                                    }
                                }
                            } else if (tplFiltersValues[key] != 'all' && value != tplFiltersValues[key]) {
                                if (!$this.parent().hasClass('hidden')) {
                                    $this.parent().addClass('filtered');
                                }
                            }
                        });
                    }
                });
            }

            var button = '';
            var field_key = '';

            $('#wpbody-content').append(`<div id="tpls_popin">
            <div class="tpls_popin_actions">
                <span class="close">Annuler</span>
                <span class="save">OK</span>
            </div>
            <ul></ul>
        </div>`);

            $('#tpls_popin .close').on('click', function () {
                $('#tpls_popin').removeClass('opened');
                $('.tpl-choice-wrapper.selected').removeClass('selected');
                $('li.first-choice-tpl').removeClass('first-choice-tpl');
                $('#tpls_popin li').removeClass('hidden').removeClass('filtered');
                $('.tpls_popin_filters').remove();
            });

            $('#tpls_popin .save').on('click', function () {
                button.parent().find('[data-key="' + field_key + '"] input').val($('.tpl-choice-wrapper.selected').data('value'));
                $('#tpls_popin').removeClass('opened');
                $('.tpl-choice-wrapper.selected').removeClass('selected');
                $('li.first-choice-tpl').removeClass('first-choice-tpl');
                $('#tpls_popin li').removeClass('hidden').removeClass('filtered');
                $('.tpls_popin_filters').remove();
            });

            var getFilters = function (group) {
                var theFilters = 'none';
                // On récupère et on append les filtres en fonctjon du type de bloc actif
                if (group == 'group_5b0d1ed32a384' || group == 'group_5b2788b48d04c' || group == 'group_5d7908eadaa46' || group == 'group_5b33890e6fa0b' || group == 'field_5d16118093cc1' || group == 'group_shared_leaflets' || group == 'group_auto_focus_leaflets') {
                    theFilters = fieldKeysFilters.focuses;
                } else if (group == 'group_66fa7944c5427') {
                    theFilters = fieldKeysFilters.highlights;
                } else if (group == 'group_5b04314e0ec21') {
                    theFilters = fieldKeysFilters.galleries;
                } else if (group == 'group_613887bd0a56c') {
                    theFilters = fieldKeysFilters.submenus;
                } else if (group == 'group_5b052bbee40a4') {
                    theFilters = fieldKeysFilters.heroes;
                } else if (group == 'group_5bb325e8b6b43') {
                    theFilters = fieldKeysFilters.landswipers;
                } else if (group == 'group_6296243e5eb71') {
                    theFilters = fieldKeysFilters.feature_v2;
                }

                if (theFilters != 'none' && $('.tpls_popin_filters').length == 0) {

                    $('.tpls_popin_actions').prepend('<div class="tpls_popin_filters"></div>');
                    theFilters.forEach(function (element) {
                        $('.tpls_popin_filters').append('<span class="tpl-filter">' + tplFilters[element].label + tplFilters[element].markup + '</span>');
                    });
                }

                if ($('.tpl-filter select').length != 0) {
                    $('.tpl-filter select').each(function () {
                        // On met à jour l'objet contenant les filtres
                        tplFiltersValues[$(this).data('filter')] = ($(this).val());

                        // On applique le tri
                        $(this).on('change', function () {
                            tplFilterAction($(this));
                        });
                    });
                }
            }

            var openTplChoices = function (button) {
                field_key = button.data('key').substr(7);

                let tpl_value = button.parent().find('[data-key="' + field_key + '"] input').val();
                let pattern = new RegExp("group_[a-z0-9_]+");
                let res = pattern.exec(button.attr('class'));
                let group = res != 'undefined' && res != null ? res[0] : '';

                if (group == '') {
                    pattern = new RegExp("field_[a-z0-9_]+");
                    res = pattern.exec(button.attr('class'));
                    group = res != 'undefined' && res != null ? res[0] : '';
                }

                $('#tpls_popin li .tpl-choice-wrapper').each(function () {
                    // Roadbook templates
                    if ($('#post_type').val() == 'woody_rdbk_leaflets' && $(this).data('display-options').length != 0 && $(this).data('display-options')[0].roadbook == false) {
                        $(this).parent().addClass('hidden');
                    }

                    if (!($(this).hasClass(group))) {
                        $(this).parent('li').addClass('hidden');
                    } else if ($(this).data('value') == tpl_value) {
                        $(this).addClass('selected');
                        $(this).closest('li').addClass('first-choice-tpl');
                    }
                })

                $('#tpls_popin').addClass('opened');

                getFilters(group);
            }

            // AJAX to get all woody_tpl only the first time
            $(document).one('click', '.woody-tpl-button', function () {
                button = $(this);
                $('#tpls_popin').addClass('ajax-load');
                $('#tpls_popin').each(function () {
                    $.ajax({
                        type: 'GET',
                        dataType: 'json',
                        url: ajaxurl,
                        data: {
                            action: 'woody_tpls',
                        },
                        success: function (data) {
                            $('#tpls_popin ul').append(data);
                            $('#tpls_popin').removeClass('ajax-load');

                            $('#tpls_popin li').on('click', function () {
                                let tpl = $(this).find('.tpl-choice-wrapper');
                                $('.tpl-choice-wrapper.selected').removeClass('selected');
                                tpl.addClass('selected');
                            });

                            openTplChoices(button);
                        },
                        error: function () {
                            $('#tpls_popin').removeClass('ajax-load');
                        },
                    });
                });
            });

            $(document).on('click', '.woody-tpl-button', function () {
                button = $(this);
                openTplChoices($(this));
            });
        }
    });
})(jQuery);
