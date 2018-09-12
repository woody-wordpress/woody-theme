import $ from 'jquery';

$('form#post').each(function() {
    var $button = $('input#publish[value="Mettre à jour"]');
    $button.addClass('woody-update');

    $button.parents('form').submit(function(e) {
        var $spinner = $button.siblings('.spinner');

        $button.addClass('disabled');
        $spinner.addClass('is-active');
        $('#hiddenaction').attr('value', 'editajaxpost');

        $.ajax({
            type: "POST",
            data: $(this).serialize(),
            url: "post.php",
            success: function(data) {
                $button.removeClass('disabled');
                $spinner.removeClass('is-active');
            },
            error: function() {
                alert('error')
            }
        });

        e.preventDefault();
    });
});
