import $ from 'jquery';
import flatpickr from "flatpickr";
import { French } from "flatpickr/dist/l10n/fr.js"

$('#post').each(function() {

    // Alert change langue
    $('#select-post-language').each(function() {
        var $this = $(this);
        var $select = $this.find('#post_lang_choice');

        // Added notAllowed on select
        $select.addClass('notAllowed');

        // Added lock button
        $this.append('<div class="button button-lock button-primary"><span class="dashicons dashicons-lock"></span></div>');
        var $lock = $this.find('.button-lock');
        var $lock_icon = $lock.find('.dashicons');

        // Popin confirm change lang
        $lock.click(function() {
            if ($lock.hasClass('button-primary')) {
                var confirm = window.confirm("Êtes-vous sûr de vouloir changer la langue de cette page ?");
                if (confirm == true) {
                    $select.removeClass('notAllowed');
                    $lock.removeClass('button-primary');
                    $lock_icon.addClass('dashicons-unlock').removeClass('dashicons-lock');
                }
            } else {
                $select.addClass('notAllowed');
                $lock.addClass('button-primary');
                $lock_icon.removeClass('dashicons-unlock').addClass('dashicons-lock');
            }
        });
    });

    // Boutons d'actions en backoffice au scroll
    var $preview_button = $('#minor-publishing-actions .preview.button');
    var $save_button = $('#publishing-action');
    $(window).scroll(function() {
        if ($(window).scrollTop() < 800) {
            $preview_button.removeClass('sticky');
            $save_button.removeClass('sticky');
        } else {
            $preview_button.addClass('sticky');
            $save_button.addClass('sticky');
        }
    });

    // On ferme certaines metaboxes ACF => Visuel et accroche, En-tête, Bloc de résa, diaporama, révisions, boxes en sidebar (sauf publier), WoodySeo
    $('#acf-group_5b052bbee40a4, #acf-group_5b2bbb46507bf, #acf-group_5c0e4121ee3ed, #acf-group_5bb325e8b6b43, #revisionsdiv, #side-sortables .postbox:not(#submitdiv), #acf-group_5d7f7cd5615c0').addClass('closed');

    var countElements = function(field) {
        var $parent = field.parent().$el;
        var $bigparent = field.parent().parent().$el;

        // add class to this field
        $parent.each(function() {
            // toggleChoiceAction($bigparent);

            setTimeout(() => {
                var count = $(this).find('.acf-table .acf-row').length - 1;
                // fitChoiceAction($bigparent, count);
            }, 2000);
        });
    };

    acf.addAction('ready_field/key=field_5b22415792db0', countElements);
    acf.addAction('append_field/key=field_5b22415792db0', countElements);
    acf.addAction('remove_field/key=field_5b22415792db0', countElements);

    // **
    // Update tpl-choice-wrapper classes for autofocus layout
    // **
    var getAutoFocusData_AJAX = {};
    var getAutoFocusData = function($parent) {
        var query_params = {};

        var block_id = $parent.attr('id');
        if (typeof block_id == 'undefined') {
            block_id = "autofocusID_" + Math.random().toString(16).slice(2);
            $parent.attr('id', block_id);
        }

        // Append Message
        var $message_wrapper = $parent.find('.acf-tab-wrap');
        if ($message_wrapper.find('.woody-count-message').length == 0) {
            var $message = $('<div>').append('<div class="woody-count-message"> \
                <span class="loading"><small>Chargement du nombre d\'éléments mis en avant ...</small></span> \
                <span class="success" style="display:none;"><small>Nombre d\'éléments mis en avant :</small><span class="count"></span></span> \
                <span class="alert" style="display:none;"><small>Aucune mise en avant ne correspond à votre sélection. Merci de modifier vos paramètres</small></span> \
                </div>').children();
            $message_wrapper.append($message);
        } else {
            var $message = $message_wrapper.find('.woody-count-message');
        }

        $message
            .find('.loading').show().end()
            .find('.success').hide().end()
            .find('.alert').hide().end();

        // Create query
        query_params['current_post'] = $('#post_ID').val();
        $parent.find('input:checked, input[type="number"]').each(function() {
            var $this = $(this);
            var name = $this.parents('.acf-field').data('name');
            if (!query_params[name]) query_params[name] = [];
            query_params[name].push($this.val());
        });
        $parent.find('select').each(function() {
            var $this = $(this);
            var name = $this.parents('.acf-field').data('name');
            query_params[name] = $this.val();
        });

        // Ajax
        if (typeof getAutoFocusData_AJAX[block_id] !== 'undefined') {
            getAutoFocusData_AJAX[block_id].abort();
        }

        getAutoFocusData_AJAX[block_id] = $.ajax({
            type: 'POST',
            dataType: 'json',
            url: ajaxurl,
            data: {
                action: 'woody_autofocus_count',
                params: query_params
            },
            success: function(data) {
                delete getAutoFocusData_AJAX[block_id];

                // fitChoiceAction($parent, data);

                if (data === 0) {
                    $message
                        .find('.loading').hide().end()
                        .find('.success').hide().end()
                        .find('.alert').show().end();
                } else {
                    $message
                        .find('.loading').hide().end()
                        .find('.success').show().find('.count').html(data).end().end()
                        .find('.alert').hide().end();
                }
            },
            error: function() {},
        });

    }

    var getAutoFocusQuery = function(field) {
        var $parent = field.$el.parent();
        var $bigparent = field.parent().$el;

        $parent.each(function() {
            var $this = $(this);

            getAutoFocusData($this);

            $this.find('input[type="checkbox"], input[type="radio"], select').on('change', function() {
                getAutoFocusData($this);
            });

            $this.find('input[type="number"]').keyup(function() {
                getAutoFocusData($this);
            });
        });

    }

    acf.addAction('ready_field/key=field_5b27890c84ed3', getAutoFocusQuery);
    acf.addAction('append_field/key=field_5b27890c84ed3', getAutoFocusQuery);

    // Collapse all section or layouts
    $('#acf-group_5afd260eeb4ab .acf-field.collapsing-rows').each(function() {
        var $this = $(this);
        if ($this.hasClass('acf-field-5afd2c6916ecb')) {
            var rowsType = 'les sections';
        } else if ($this.hasClass('acf-field-5b043f0525968')) {
            var rowsType = 'les blocs';
        }

        $this.prepend('<span class="woodyRowsCollapse"><span class="text">Fermer ' + rowsType + '</span><span class="dashicons dashicons-arrow-up' + '"></span></span>');

        $('.woodyRowsCollapse').click(function() {
            if ($this.hasClass('acf-field-5afd2c6916ecb')) {
                $('.acf-field-5afd2c6916ecb > .acf-input > .acf-repeater > .acf-table > .ui-sortable > .acf-row').addClass('-collapsed');
                $(this).siblings('.acf-input').find('.acf-field-5b043f0525968 .acf-input > .acf-flexible-content > .values .layout').addClass('-collapsed');
            } else {
                $(this).siblings('.acf-input').find('> .acf-flexible-content > .values .layout').addClass('-collapsed');
            }
        })
    });

    var getUrlParameter = function getUrlParameter(sParam) {
        var sPageURL = window.location.search.substring(1),
            sURLVariables = sPageURL.split('&'),
            sParameterName,
            i;

        for (i = 0; i < sURLVariables.length; i++) {
            sParameterName = sURLVariables[i].split('=');

            if (sParameterName[0] === sParam) {
                return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
            }
        }
    };

    var updateCurrentPostMeta = function(model_id) {
        let current_undefined = false;
        var current_id = getUrlParameter('post');
        if (typeof current_id == 'undefined') {
            $('body').removeClass('windowReady');
            $.ajax({
                type: "POST",
                url: window.location.origin + '/wp/wp-admin/post.php?post=' + $("#post_ID").val() + '&action=edit',
                data: { save: 'save', post_id: $("#post_ID").val() },
                error: function(error) {
                    console.log('undefined-post', error);
                }
            });
            current_id = $("#post_ID").val();
            current_undefined = true;
        }
        var apply_model = window.confirm('Vous êtes sur le point de remplacer le contenu de votre page. Êtes-vous sur ?');
        if (apply_model) {
            $('body').removeClass('windowReady');
            $.ajax({
                type: 'GET',
                dataType: 'json',
                url: '/wp-json/woody/current-post-update?current_id=' + current_id + '&model_id=' + model_id,
                success: function(data) {
                    if (current_undefined) {
                        $(window).off( 'beforeunload.edit-post' );
                        window.location.replace(window.location.origin + '/wp/wp-admin/post.php?post=' + current_id + '&action=edit');
                    } else {
                        window.location.reload();
                    }
                },
                error: function(error) {
                    console.error('post-with-meta', error);
                    $('body').addClass('windowReady');

                },
            });
        } else {
            $('body').addClass('windowReady');
        }
    };

    var addApplyModelButton = function() {
        var term_id = Number($(this).val());

        if (term_id == 0) {
            term_id = $('#acf-field_5a61fa38b704f option').val();
        }

        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: ajaxurl,
            data: {
                action: 'get_models_for_type',
                term_id: term_id
            },
            success: function(response) {
                // Add Button
                $('.acf-field-5a61fa38b704f #apply-model-link').remove();
                $('#apply-model-popup div').remove();
                $('#apply-model-popup').hide();

                if (response.posts.length > 0) {
                    if (response.posts.length == 1) {
                        // ADD APPLY MODEL BUTTON
                        $('.acf-field-5a61fa38b704f')
                            .append('<a href="#" id="apply-model-link" class="button button-primary"><span alt="f135" class="dashicons dashicons-align-right"></span> Appliquer le modèle ' + response.term + '</a>');

                        // APPLY MODEL BUTTON EVENT
                        $('.acf-field-5a61fa38b704f #apply-model-link').click(function() {
                            if (window.confirm('Souhaitez vous vraiment appliquer le modèle sur cette page ?  Tout le contenu déjà saisi sera remplacé par celui du modèle')) {
                                $('body').removeClass('windowReady');
                                updateCurrentPostMeta(response.posts[0].ID);
                            }
                        });
                    } else {
                        // CASE MULTIPLE MODELS FOR ONE PAGE TYPE, OPEN POPUP
                        $('.acf-field-5a61fa38b704f')
                            .append('<a href="#" id="apply-model-link" class="button button-primary"><span alt="f135" class="dashicons dashicons-align-right"></span> Appliquer le modèle ' + response.term + '</a>');

                        $('.acf-field-5a61fa38b704f #apply-model-link').click(function(e) {

                            $('#apply-model-popup ul li').remove();

                            if ($('#apply-model-popup').length == 0) {
                                $('#post-body-content').append('<div id="apply-model-popup" class="apply-model-list"><form><ul></ul></form><div class="actions"></div></div>');

                                // ADD ROW IN POPUP
                                response.posts.forEach(function(element) {
                                    $('#apply-model-popup form ul').append('<li><div><input type="radio" name="model" value="' + element.ID + '">' + element.title + '</div><a class="clickable view btn" target="_blank" href="' + element.link + '"><span class="clickable dashicons dashicons-visibility"></span></a></li>');
                                });
                                $('#apply-model-popup .actions').append('<a href="#" id="apply-model-popup-button" class="button button-primary">Appliquer</a>');
                                $('#apply-model-popup .actions').append('<a href="#" id="abort-apply-model" class="button">Annuler</a>');

                                $('#abort-apply-model').click(function() {
                                    $('#apply-model-popup').hide();
                                    $('#apply-model-popup ul li').remove();
                                });

                                // POPUP APPLY-MODEL-BUTTON EVENT
                                $('#apply-model-popup-button').click(function() {
                                    var model_id = $('#apply-model-popup input:checked').val();

                                    if (model_id != "undefined" && model_id != null) {
                                        updateCurrentPostMeta(model_id);

                                        $('#apply-model-popup').hide();
                                        $('#apply-model-popup ul li').remove();
                                    }
                                });
                            } else {
                                response.posts.forEach(function(element) {
                                    $('#apply-model-popup form ul').append('<li><div><input type="radio" name="model" value="' + element.ID + '">' + element.title + '</div><a class="clickable view btn" target="_blank" href="' + element.link + '"><span class="dashicons dashicons-visibility"></span></a></li>');
                                });
                                $('#apply-model-popup').show();
                            }
                        });
                    }
                }
            },
            error: function(error) {
                console.error(error);
            }
        });
    }

    $('.acf-field-5a61fa38b704f #acf-field_5a61fa38b704f').ready(addApplyModelButton);
    $('.acf-field-5a61fa38b704f #acf-field_5a61fa38b704f').change(addApplyModelButton);

    // Add Flatpickr to Unpublish metabox
    $('#woody-unpublisher').each(function() {

        var unPublisher = flatpickr('#wUnpublisher_date', {
            enableTime: true,
            dateFormat: 'Y-m-dTH:i',
            altInput: true,
            altFormat: 'j F Y à H:i',
            locale: French,
            time_24hr: true,
            minDate: 'today'
        });

        $('.unpublisher-reset-date').click(function() {
            $(this).siblings('.flatpickr-input').val('');
        });
    })

    $('.acf-field-5b44bfc2e2e21 .acf-switch:not(-on)').on('click', function() {
        $('.acf-field-5c614d83a4e9b .acf-switch.-on').trigger('click');
    });

    $('.acf-field-5c614d83a4e9b .acf-switch:not(-on)').on('click', function() {
        $('.acf-field-5b44bfc2e2e21 .acf-switch.-on').trigger('click');
    });
});
