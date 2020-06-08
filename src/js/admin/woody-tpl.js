import $ from 'jquery';

$('#post').each(function() {
    const accepted_post = ['page', 'claims', 'woody_rdbk_leaflets'];

    if (accepted_post.includes($('#post_type').val())) {
        var button = '';
        var field_key = '';

        $('#post-body-content').append('<div id="tpls_popin"><a href="#" class="close">Fermer</a> <a href="#" class="save">Enregistrer</a><ul></ul></div>');

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
    }

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
                        var tpl_value = button.parent().find('[data-key="'+ field_key +'"] input').val();

                        var group = "";
                        var classes = button.attr('class').split(' ');
                        $.each(classes, function(index, value) {
                            if(value.indexOf('group') == 0) {
                                group = value;
                            }
                        });

                        $('#tpls_popin li .tpl-choice-wrapper').each(function(){
                            if (!($(this).hasClass(group))) {
                                $(this).parent('li').addClass('hidden');
                            } else if($(this).data('value') == tpl_value) {
                                $(this).addClass('selected');
                            }
                        })

                        $('#tpls_popin').addClass('opened');
                    });

                    $('#tpls_popin li').on('click', function(){
                        var chosen = $(this).find('.tpl-choice-wrapper');
                        $('.tpl-choice-wrapper.selected').removeClass('selected');
                        chosen.addClass('selected');
                    });
                },
                error: function() {},
            });
        });
    });
});
