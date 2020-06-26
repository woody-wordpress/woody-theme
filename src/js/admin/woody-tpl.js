import $ from 'jquery';

$('#post').each(function() {
    const accepted_post = [
        'page',
        'woody_claims',
        'woody_rdbk_leaflets',
        'woody_model',
        'tourtrip_step'
    ];

    if (accepted_post.includes($('#post_type').val())) {
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

        var openTplChoices = function(button) {
            field_key = button.data('key').substr(7);

            let tpl_value = button.parent().find('[data-key="'+ field_key +'"] input').val();
            let pattern = new RegExp("group_[a-z0-9]+");
            let res = pattern.exec(button.attr('class'));
            let group = res != 'undefined' && res != null ? res[0] : '';

            if (group == '') {
                pattern = new RegExp("field_[a-z0-9]+");
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
        }

        // AJAX to get all woody_tpl only the first time
        $(document).one('click', '.woody-tpl-button', function() {
            button = $(this);
            $('#tpls_popin').addClass('ajax-load');
            $('#tpls_popin').each(function() {
                $.ajax({
                    type: 'POST',
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
