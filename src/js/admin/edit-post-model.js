!(function ($) {
    $('#post').each(function () {

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

        var updateCurrentPostMeta = function (model_id) {
            let current_undefined = false;
            var current_id = getUrlParameter('post');
            if (typeof current_id == 'undefined') {
                $('body').removeClass('windowReady');
                $.ajax({
                    type: "POST",
                    url: window.location.origin + '/wp/wp-admin/post.php?post=' + $("#post_ID").val() + '&action=edit',
                    data: { save: 'save', post_id: $("#post_ID").val() },
                    error: function (error) {
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
                    success: function (data) {
                        if (current_undefined) {
                            $(window).off('beforeunload.edit-post');
                            window.location.replace(window.location.origin + '/wp/wp-admin/post.php?post=' + current_id + '&action=edit');
                        } else {
                            window.location.reload();
                        }
                    },
                    error: function (error) {
                        console.error('post-with-meta', error);
                        $('body').addClass('windowReady');

                    },
                });
            } else {
                $('body').addClass('windowReady');
            }
        };

        var addApplyModelButton = function () {
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
                success: function (response) {
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
                            $('.acf-field-5a61fa38b704f #apply-model-link').click(function () {
                                if (window.confirm('Souhaitez vous vraiment appliquer le modèle sur cette page ?  Tout le contenu déjà saisi sera remplacé par celui du modèle')) {
                                    $('body').removeClass('windowReady');
                                    updateCurrentPostMeta(response.posts[0].ID);
                                }
                            });
                        } else {
                            // CASE MULTIPLE MODELS FOR ONE PAGE TYPE, OPEN POPUP
                            $('.acf-field-5a61fa38b704f')
                                .append('<a href="#" id="apply-model-link" class="button button-primary"><span alt="f135" class="dashicons dashicons-align-right"></span> Appliquer le modèle ' + response.term + '</a>');

                            $('.acf-field-5a61fa38b704f #apply-model-link').click(function (e) {

                                $('#apply-model-popup ul li').remove();

                                if ($('#apply-model-popup').length == 0) {
                                    $('#post-body-content').append('<div id="apply-model-popup" class="apply-model-list"><form><ul></ul></form><div class="actions"></div></div>');

                                    // ADD ROW IN POPUP
                                    response.posts.forEach(function (element) {
                                        $('#apply-model-popup form ul').append('<li><div><input type="radio" name="model" value="' + element.ID + '">' + element.title + '</div><a class="clickable view btn" target="_blank" href="' + element.link + '"><span class="clickable dashicons dashicons-visibility"></span></a></li>');
                                    });
                                    $('#apply-model-popup .actions').append('<a href="#" id="apply-model-popup-button" class="button button-primary">Appliquer</a>');
                                    $('#apply-model-popup .actions').append('<a href="#" id="abort-apply-model" class="button">Annuler</a>');

                                    $('#abort-apply-model').click(function () {
                                        $('#apply-model-popup').hide();
                                        $('#apply-model-popup ul li').remove();
                                    });

                                    // POPUP APPLY-MODEL-BUTTON EVENT
                                    $('#apply-model-popup-button').click(function () {
                                        var model_id = $('#apply-model-popup input:checked').val();

                                        if (model_id != "undefined" && model_id != null) {
                                            updateCurrentPostMeta(model_id);

                                            $('#apply-model-popup').hide();
                                            $('#apply-model-popup ul li').remove();
                                        }
                                    });
                                } else {
                                    response.posts.forEach(function (element) {
                                        $('#apply-model-popup form ul').append('<li><div><input type="radio" name="model" value="' + element.ID + '">' + element.title + '</div><a class="clickable view btn" target="_blank" href="' + element.link + '"><span class="dashicons dashicons-visibility"></span></a></li>');
                                    });
                                    $('#apply-model-popup').show();
                                }
                            });
                        }
                    }
                },
                error: function (error) {
                    console.error(error);
                }
            });
        }

        $('.acf-field-5a61fa38b704f #acf-field_5a61fa38b704f').ready(addApplyModelButton);
        $('.acf-field-5a61fa38b704f #acf-field_5a61fa38b704f').change(addApplyModelButton);

    })
})(jQuery);
