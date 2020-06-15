import $ from 'jquery';

$('#post').each(function () {

    // Alert change langue
    $('#select-post-language').each(function () {
        var $this = $(this);
        var $select = $this.find('#post_lang_choice');

        // Added notAllowed on select
        $select.addClass('notAllowed');

        // Added lock button
        $this.append('<div class="button button-lock button-primary"><span class="dashicons dashicons-lock"></span></div>');
        var $lock = $this.find('.button-lock');
        var $lock_icon = $lock.find('.dashicons');

        // Popin confirm change lang
        $lock.click(function () {
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

});
