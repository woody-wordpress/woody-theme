import $ from 'jquery';

$('#post').each(function() {
    const accepted_post = ['page', 'claims', 'woody_rdbk_leaflets'];

    if (accepted_post.includes($('#post_type').val())) {
        $('#post-body-content').append('<div id="tpls_popin"><a href="#" class="close">Fermer</a> <a href="#" class="save">Enregistrer</a><ul></ul></div>');
        $('#tpls_popin .close').on('click', function() {
            $('#tpls_popin').removeClass('opened');
            $('#tpls_popin li').removeClass('hidden');
            $('#tpls_popin li').removeClass('selected');
        });

        $('#tpls_popin .save').on('click', function() {
            var button = $(this);
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

                    $('.woody-tpl-button').on('click', function() {
                        var button = $(this);
                        var group = "";
                        $.each(button.attr("classList"), function(index, value) {
                            if(value.indexOf('group', 0)) {
                                console.log(value, "group");
                                group = value;
                            }
                        });

                        console.log(group, "final group");

                        $('#tpls_popin').addClass('opened');
                    });
                },
                error: function() {},
            });
        });
    });
});
