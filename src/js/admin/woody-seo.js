!(function ($) {
    $('#acf-group_5d7f7cd5615c0').each(function () {

        var $group = $(this);
        var toKenized = false;

        function toKenize() {

            if (toKenized == false) {
                var $tokenize = $group.find('.tokenize');

                var tokens = {
                    '%site_name%': 'Nom du site',
                    '%post_title%': 'Titre de la page',
                    '%hero_title%': 'Titre visuel & accroche',
                    '%hero_desc%': 'Description visuel & accroche',
                    '%teaser_desc%': 'Description en-tête de page',
                    '%focus_title%': 'Titre de mise en avant',
                    '%focus_desc%': 'Description de mise en avant',
                    '|': 'Séparateur'
                };

                $tokenize.each(function () {
                    var $this = $(this);

                    // On ajoute une div pour remplacer l'input masqué + liste des tokens disponibles
                    $this.append('<div class="tokens-div"></div>')
                        .append('<div class="tokens-toggle button">Ajouter une variable</div>')
                        .append('<ul class="tokens-list" style="display:none"></ul>');

                    $.each(tokens, function (key, token) {
                        $this.find('.tokens-list').append('<li class="token" data-field="' + key + '">' + token + '</li>');
                    });

                    // On toggle la liste de tokens
                    $this.find('.tokens-toggle').click(function () {
                        $this.find('.tokens-list').toggle();
                    });

                    var $tokensDiv = $this.find('.tokens-div');
                    var $input = $this.find('input[type="text"]');

                    // Pousse la valeur de chacun des span de $tokensDiv dans la value de l'input masqué
                    function pushVal() {
                        var currentText = [];
                        var sanitizedText = [];
                        // On pousse dans un tableau les valeurs de chacun des spans token-val
                        $tokensDiv.find('span').each(function () {
                            if ($(this).hasClass('token-val')) {
                                currentText.push($(this).data('field'));
                            } else if ($(this).hasClass('editable')) {
                                currentText.push($(this).html());
                            }
                        });

                        // A good way to strip tags in js :)
                        // See https://stackoverflow.com/questions/5002111/how-to-strip-html-tags-from-string-in-javascript
                        currentText.forEach(function (item) {
                            var div = document.createElement("div");
                            div.innerHTML = item;
                            sanitizedText.push(div.innerText);
                        });

                        // On rassemble les clés en une chaine et on remplace les espaces multiples par un espace simple
                        sanitizedText = sanitizedText.join(' ');
                        sanitizedText = sanitizedText.replace(/ +(?= )/g, '');

                        // On pousse la chaine finale dans $input
                        $input.val(sanitizedText)
                    }

                    // Permet de rendre éditable chacun des <span class="editable">
                    function makeEditable() {
                        var $editable = $tokensDiv.find('.editable');

                        $editable.attr('contentEditable', true);
                        $editable.keypress(function (e) {
                            // Si on clic sur la touche Enter => pas de saut de ligne et on pousse la nouvelle valeur dans $input
                            if (e.which == 13) {
                                e.preventDefault();
                                pushVal();
                            }
                        });
                    }

                    // Répertorie les token dans $tokensDiv et répertorie les actions de suppression d'un token
                    function removeToken() {
                        $tokensDiv.find($('.token-val small')).click(function () {

                            // Si le premier span après le token retiré est vide, on le supprime
                            if ($(this).parent().next().html() == '') {
                                $(this).parent().next().remove();
                            }
                            // On supprime le token
                            $(this).parent().remove();

                            // Pour chaque span .editable, si le suivant est aussi .editable, on fusionne les 2 <span>
                            $tokensDiv.find('.editable').each(function () {
                                if ($(this).next().hasClass('editable')) {
                                    $(this).html($(this).html() + ' ' + $(this).next().html())
                                    $(this).next().remove();
                                }
                            });

                            // On met à jour les données de $input
                            pushVal();
                        });
                    }

                    // On parcours la valeur de $input
                    // Si un élément correspond à la clé d'un token on ajoute un span token-val,
                    // sinon, on ajoute un span editable
                    var regex = /(%.*?%)/g;
                    var arrayVal = $input.val().split(regex);
                    var arrayValSize = Object.keys(arrayVal).length;
                    var $i = 0;
                    arrayVal.forEach(key => {
                        if (key in tokens) {
                            $tokensDiv.append('<span class="token-val" data-field="' + key + '">' + tokens[key] + '<small>x</small></span>');
                            // Si le dernier élément correspond à un token, on créé un span editable juste après
                            if ($i == arrayValSize) {
                                $tokensDiv.append('<span class="editable">' + key + '</span>');
                            }
                        } else {
                            console.log
                            $tokensDiv.append('<span class="editable">' + key + '</span>');
                        }
                        $i++
                    });

                    makeEditable();
                    removeToken();

                    // Au clic sur un token, on ajoute un span token dans $tokensDiv + un span editable et on pousse la valeur de $tokensDiv dans $input
                    $this.find('.token').click(function () {
                        $tokensDiv.append('<span class="token-val" data-field="' + $(this).data('field') + '">' + $(this).html() + '<small>x</small></span>')
                            .append('<span class="editable"></span>');
                        pushVal();
                        makeEditable();
                        removeToken();
                    });

                    $tokensDiv.focusout(function () {
                        pushVal();
                    });
                });

                toKenized = true;
            }
        }

        // Lancement de la fonction de création des tokens lorsque le champ "Meta titre" est affiché
        acf.addAction('load_field/key=field_5d7f7dea20bb1', toKenize);
        acf.addAction('show_field/key=field_5d7f7dea20bb1', toKenize);

    });
})(jQuery);
