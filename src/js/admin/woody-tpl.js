import $, { type } from 'jquery';

$('#post').each(function() {
    const accepted_post = [
        'page',
        'woody_claims',
        'woody_rdbk_leaflets',
        'woody_model',
        'tourtrip_step'
    ];

    if (accepted_post.includes($('#post_type').val())) {

        const fieldKeysFilters = {
            'focuses': [
                'type',
                'length',
                'infinite',
                'card_type',
                'img_ratio',
                'text_align'
            ]
        };

        const tplFilters = {
            'type': {
                'callback': '',
                'label': '<label for="tpl_type">Type</label>',
                'markup': '<select data-filter="type" name="tpl_type" id="tpl_type" onchange="tplFilterAction(this);"><option value="all">Tous les types</option><option value="grid">Grille</option><option value="slider">Slider</option><option value="mozaic">Mosaïque</option></select>'
            },
            'length': {
                'callback': '',
                'label': '<label for="tpl_length">Eléments visibles</label>',
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
            'img_ratio': {
                'callback': '',
                'label': '<label for="img_ratio">Format d\'image</label>',
                'markup': '<select data-filter="img_ratio" name="img_ratio" id="img_ratio"><option value="all">Tous les formats</option><option value="8_1">Pano A</option><option value="4_1">Pano B</option><option value="3_1">Pano C</option><option value="2_1">Paysage A</option><option value="16_9">Paysage B</option><option value="4_3">Paysage C</option><option value="square">Carré</option><option value="3_4">Portrait A</option><option value="10_16">Portrait B</option><option value="a4">A4</option></select>',
            },
            'text_align': {
                'callback': '',
                'label': '<label for="text_align_h">Alignement des textes</label>',
                'markup': '<select data-filter="text_align" name="text_align_h" id="text_align_h"><option value="all">Peu importe</option><option class="h-only" value="left">Gauche</option><option class="h-only" value="center">Centrés</option><option value="topleft">Haut/Gauche</option><option value="middleleft">Milieu/Gauche</option><option value="bottomleft">Bas/Gauche</option><option value="topcenter">Haut/Centre</option><option value="middlecenter">Milieu/Centre</option><option value="bottomcenter">Bas/Centre</option><option value="topright">Haut/Droite</option><option value="middleright">Milieu/Droite</option><option value="bottomright">Bas/Droite</option></select>',
            }
        }

        window.tplFilterAction = function(el) {
            var filter = $(el).data('filter');
            window.tplFiltersValues[$(el).data('filter')] = el.value;
            console.log(tplFiltersValues);

            $('#tpls_popin .tpl-choice-wrapper').each(function() {
                var $this = $(this);
                var displayOptions = $this.data('display-options');

                $this.parent().removeClass('filtered');

                if (displayOptions.length == 0) {
                    $this.parent().addClass('filtered');
                } else {
                    $.each(displayOptions[0], function(key, value) {
                        if (window.tplFiltersValues[key] != 'all' && value != window.tplFiltersValues[key] && key != 'roadbook') {
                            if (!$this.parent().hasClass('hidden'))
                                $this.parent().addClass('filtered');
                        }
                    });
                }
            });
        }

        var button = '';
        var field_key = '';

        $('#post-body-content').append(`<div id="tpls_popin">
            <div class="tpls_popin_actions">
                <span class="close">Fermer</span>
                <span class="save">Enregistrer</span>
            </div>
            <ul></ul>
        </div>`);

        $('#tpls_popin .close').on('click', function() {
            $('#tpls_popin').removeClass('opened');
            $('.tpl-choice-wrapper.selected').removeClass('selected');
            $('#tpls_popin li').removeClass('hidden');
        });

        $('#tpls_popin .save').on('click', function() {
            button.parent().find('[data-key="' + field_key + '"] input').val($('.tpl-choice-wrapper.selected').data('value'));
            $('#tpls_popin').removeClass('opened');
            $('.tpl-choice-wrapper.selected').removeClass('selected');
            $('#tpls_popin li').removeClass('hidden');
        });

        var getFilters = function(group) {
            if (group == 'group_5b0d1ed32a384' || group == 'group_5b2788b48d04c' || group == 'group_5d7908eadaa46' || group == 'group_5b33890e6fa0b' || group == 'field_5d16118093cc1') {
                var theFilters = fieldKeysFilters.focuses;
            }

            if (typeof theFilters != undefined && $('.tpls_popin_filters').length == 0) {
                $('.tpls_popin_actions').prepend('<div class="tpls_popin_filters"></div>');
                theFilters.forEach(function(element) {
                    $('.tpls_popin_filters').append('<span class="tpl-filter">' + tplFilters[element].label + tplFilters[element].markup + '</span>');
                });
            }

            window.tplFiltersValues = {};
            $('.tpl-filter select').each(function() {
                window.tplFiltersValues[$(this).data('filter')] = ($(this).val());
            });

            console.log(tplFiltersValues);
        }

        var openTplChoices = function(button) {
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

            $('#tpls_popin li .tpl-choice-wrapper').each(function() {
                if (!($(this).hasClass(group))) {
                    $(this).parent('li').addClass('hidden');
                } else if ($(this).data('value') == tpl_value) {
                    $(this).addClass('selected');
                }
            })

            $('#tpls_popin').addClass('opened');

            getFilters(group);
        }

        // AJAX to get all woody_tpl only the first time
        $(document).one('click', '.woody-tpl-button', function() {
            button = $(this);
            $('#tpls_popin').addClass('ajax-load');
            $('#tpls_popin').each(function() {
                $.ajax({
                    type: 'GET',
                    dataType: 'json',
                    url: ajaxurl,
                    data: {
                        action: 'woody_tpls',
                    },
                    success: function(data) {
                        $('#tpls_popin ul').append(data);
                        $('#tpls_popin').removeClass('ajax-load');

                        $('#tpls_popin li').on('click', function() {
                            let tpl = $(this).find('.tpl-choice-wrapper');
                            $('.tpl-choice-wrapper.selected').removeClass('selected');
                            tpl.addClass('selected');
                        });

                        openTplChoices(button);
                    },
                    error: function() {
                        $('#tpls_popin').removeClass('ajax-load');
                    },
                });
            });
        });

        $(document).on('click', '.woody-tpl-button', function() {
            button = $(this);
            openTplChoices($(this));
        });
    }
});
