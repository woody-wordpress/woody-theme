import $ from 'jquery';

var $button = $('#publishing-action');

$button.parents('form').submit(function() {
    var $input = $button.find('input.button');
    var $spinner = $button.find('.spinner');

    $input.addClass('disabled');
    $spinner.addClass('is-active');
    $('#hiddenaction').attr('value', 'editajaxpost');

    $.ajax({
        type: "POST",
        data: $(this).serialize(),
        url: "post.php",
        success: function(data) {
            $input.removeClass('disabled');
            $spinner.removeClass('is-active');
        },
        error: function() {
            alert('error')
        }
    });

    return false;
});
