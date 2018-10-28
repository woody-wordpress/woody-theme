import $ from 'jquery';

$('#post').each(function() {

    // Show on scroll
    var $preview_button = $('#minor-publishing-actions .preview.button');
    $(window).scroll(function() {
        if ($(window).scrollTop() < 800) {
            $preview_button.removeClass('sticky');
        } else {
            $preview_button.addClass('sticky');
        }
    });

    // On ferme toutes les metaboxes ACF
    // $('.acf-postbox').addClass('closed');

    // On masque les metaboxes de taxonomies dans l'edition des posts (on les rajoutera ensuite dans des champs ACF)
    // $('[id^="tagsdiv-"').hide();

    // On referme les metaboxes par défaut sur l'édition d'un post
    // $('#pageparentdiv, #revisionsdiv, #wpseo_meta, #members-cp').addClass('closed');

    // Action sur les focus
    var toggleChoiceAction = function($bigparent) {
        $bigparent.find('.tpl-choice-wrapper').each(function() {
            var $this = $(this);

            // On toggle la description de chaque template dans les champs woody_tpl
            $this.find('.toggle-desc').click(function(e) {
                e.stopPropagation();
                $this.find('.tpl-desc').toggleClass('hidden');
                $this.find('.desc-backdrop').toggleClass('hidden');
            });

            $this.find('.close-desc').click(function() {
                $this.find('.tpl-desc').addClass('hidden');
                $this.find('.desc-backdrop').addClass('hidden');
            });

            $this.find('.desc-backdrop').click(function() {
                $this.find('.tpl-desc').addClass('hidden');
                $(this).addClass('hidden');
            });
        });
    }

    // Action sur les focus
    var fitChoiceAction = function($bigparent, count) {

        $bigparent.find('.tpl-choice-wrapper').each(function() {
            var $this = $(this);

            var fittedfor = $this.data('fittedfor');
            var acceptsmax = $this.data('acceptsmax');
            if (fittedfor == 'all') fittedfor = 0;

            // On affiche un état en fonction du nombre d'élément
            if (count >= fittedfor && count <= acceptsmax) {
                $this.removeClass('notfit');
                $this.addClass('fit');
            } else {
                $this.removeClass('fit');
                $this.addClass('notfit');
            }
        });
    }

    var countElements = function(field) {
        var $parent = field.parent().$el;
        var $bigparent = field.parent().parent().$el;

        // add class to this field
        $parent.each(function() {
            toggleChoiceAction($bigparent);

            setTimeout(() => {
                var count = $(this).find('.acf-table .acf-row').length - 1;
                fitChoiceAction($bigparent, count);
            }, 2000);
        });
    };

    acf.addAction('ready_field/key=field_5b22415792db0', countElements);
    acf.addAction('append_field/key=field_5b22415792db0', countElements);
    acf.addAction('remove_field/key=field_5b22415792db0', countElements);

    // **
    // Update tpl-choice-wrapper classes for autofocus layout
    // **
    var getAutoFocusData = function($parent) {
        var query_params = {};
        query_params['current_post'] = $('#post_ID').val();
        //console.log($parent);
        $parent.find('input:checked, select option, input[type="number"]').each(function() {
            var name = $(this).parents('.acf-field').data('name');

            if (!query_params[name]) query_params[name] = [];
            query_params[name].push($(this).val());

        });

        $.ajax({
            type: 'POST',
            url: '/wp-json/woody/autofocus-count',
            data: query_params,
            success: function(data) {
                fitChoiceAction($parent, data);
                var message_wrapper = $parent.find('.acf-tab-wrap');
                var $count_message = $parent.find('.woody-count-message');
                if (data === 0) {
                    var the_message = '<div class="woody-count-message"><span class="count alert"><small>Aucune mise en avant ne correspond à votre sélection. Merci de modifier vos paramètres</small></span></div>';
                } else {
                    var the_message = '<div class="woody-count-message"><small>Nombre d\'éléments mis en avant :</small><span class="count">' + data + '</span></div>';
                }
                if ($count_message.length == 0) {
                    message_wrapper.append(the_message);
                } else {
                    $count_message.html(the_message);
                }
                return data;
            },
            error: function() {
                console.log('Endpoint doesn\'t exists');
            },
        });
    }

    var getAutoFocusQuery = function(field) {
        var $parent = field.$el.parent();
        var $bigparent = field.parent().$el;

        //console.warn($bigparent);

        $parent.each(function() {
            var $this = $(this);
            toggleChoiceAction($bigparent);

            getAutoFocusData($this);

            $this.find('input[type="checkbox"], input[type="radio"], select, option').change(function() {
                getAutoFocusData($this);
            });

            $this.find('input[type="number"]').keyup(function() {
                getAutoFocusData($this);
            });
        });

    }

    acf.addAction('ready_field/key=field_5b27890c84ed3', getAutoFocusQuery);
    acf.addAction('append_field/key=field_5b27890c84ed3', getAutoFocusQuery);
    //acf.addAction('remove_field/key=field_5b27890c84ed3', getAutoFocusQuery);

});

$('#acf-group_5bd0227a1bda3').each(function() {
    var getShortLinkData = function(field) {
        var post_id = field.val();

        $.ajax({
            type: 'POST',
            url: '/wp-json/woody/short-link',
            data: post_id,
            success: function(data) {
                if (data.length !== 0) {
                    if (data['page_type'] == 'playlist_tourism' && data['conf_id'] !== "") {
                        filter_field.show();
                        window.hawwwai = {};
                        window.hawwwai.short_link_conf_id = data['conf_id'];
                    } else {
                        filter_field.hide();
                    }
                }
                return data;
            },
            error: function() {
                console.log('Endpoint doesn\'t exists');
            },
        });
    }

    window.filter_field = acf.getField('field_5bd023a8daa52');
    filter_field.hide();
    var short_link_url_field = acf.getField('field_5bd022eddaa51');
    short_link_url_field.on('change', function() {
        $('#acf-field_5bd060e1dde02').attr('value', '');
        getShortLinkData(short_link_url_field);
    });
    acf.addAction('ready_field/key=field_5bd022eddaa51', getShortLinkData);
});
