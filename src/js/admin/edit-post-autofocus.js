!(function ($) {
    $('#post').each(function () {

        // **
        // Update tpl-choice-wrapper classes for autofocus layout
        // **
        var getAutoFocusData_AJAX = {};
        var getAutoFocusData = function ($parent) {
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
            $parent.find('input:checked, input[type="number"]').each(function () {
                var $this = $(this);
                var name = $this.parents('.acf-field').data('name');
                if (!query_params[name]) query_params[name] = [];
                query_params[name].push($this.val());
            });
            $parent.find('select').each(function () {
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
                success: function (data) {
                    delete getAutoFocusData_AJAX[block_id];

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
                error: function () { },
            });
        }

        var getAutoFocusQuery = function (field) {
            var $parent = field.$el.parent();

            $parent.each(function () {
                var $this = $(this);

                getAutoFocusData($this);

                $this.find('input[type="checkbox"], input[type="radio"], select').on('change', function () {
                    getAutoFocusData($this);
                });

                $this.find('input[type="number"]').keyup(function () {
                    getAutoFocusData($this);
                });
            });
        }

        acf.addAction('ready_field/key=field_5b27890c84ed3', getAutoFocusQuery);
        acf.addAction('append_field/key=field_5b27890c84ed3', getAutoFocusQuery);

    });
})(jQuery);
