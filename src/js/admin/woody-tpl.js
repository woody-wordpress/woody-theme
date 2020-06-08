import $ from 'jquery';

$('#post').each(function() {
    const accepted_post = ['page', 'claims', 'woody_rdbk_leaflets'];

    if (accepted_post.includes($('#post_type').val())) {
        var button = '';
        var field_key = '';

        $('#post-body-content').append(`<div id="tpls_popin">
            <div class="tpls_popin_actions">
                <a href="#" class="close">Fermer</a>
                <a href="#" class="save">Enregistrer</a>
            <div>
            <ul></ul>
        </div>`);

        $('#tpls_popin .close').on('click', function() {
            $('#tpls_popin').removeClass('opened');
            $('#tpls_popin li').removeClass('hidden');
            $('.tpl-choice-wrapper.selected').removeClass('selected');
        });

        $('#tpls_popin .save').on('click', function() {
            button.parent().find('[data-key="'+ field_key +'"] input').val($('.tpl-choice-wrapper.selected').data('value'));
            $('.tpl-choice-wrapper.selected').removeClass('selected')
            $('#tpls_popin').removeClass('opened');
        });

        $(document).ready( function() {
            // AJAX to get all woody_tpl
            $('#tpls_popin').each(function() {
                $.ajax({
                    type: 'POST',
                    dataType: 'json',
                    url: ajaxurl,
                    data: {
                        action: 'woody_tpls',
                    },
                    success: function(data) {
                        for (let [key, value] of Object.entries(data)) {
                            $('#tpls_popin ul').append('<li>' + value + '</li>');
                        }

                        $(document).on('click', '.woody-tpl-button', function() {
                            button = $(this);
                            field_key = button.data('key').substr(7);

                            let tpl_value = button.parent().find('[data-key="'+ field_key +'"] input').val();
                            let pattern = new RegExp("group_[a-z0-9]+");
                            let res = pattern.exec(button.attr('class'));
                            let group = res[0] != undefined && res[0] != null ? res[0] : '';

                            $('#tpls_popin li .tpl-choice-wrapper').each(function(){
                                if (!($(this).hasClass(group))) {
                                    $(this).parent('li').addClass('hidden');
                                } else if ($(this).data('value') == tpl_value) {
                                    $(this).addClass('selected');
                                }
                            })

                            $('#tpls_popin').addClass('opened');
                        });

                        $('#tpls_popin li').on('click', function(){
                            let tpl = $(this).find('.tpl-choice-wrapper');
                            $('.tpl-choice-wrapper.selected').removeClass('selected');
                            tpl.addClass('selected');
                        });
                    },
                    error: function() {},
                });
            });
        });
    }
});
