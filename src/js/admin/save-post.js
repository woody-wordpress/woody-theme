import $ from 'jquery';

$('body.post-type-page.post-php form#post').submit(function(e) {
    e.preventDefault();

    // Change Action
    $('#hiddenaction').attr('value', 'editajaxpost');

    var $button_publish = $('#publish');
    var $button_preview = $('#post-preview');
    var $spinner = $button_publish.siblings('.spinner');

    $button_preview.click(function() {
        $(this).addClass('clickAfterAjax');
    });

    $button_publish.addClass('disabled');
    $button_preview.addClass('disabled');
    $spinner.addClass('is-active');

    $.ajax({
        type: "POST",
        data: $(this).serialize(),
        url: "post.php",
        success: function(data) {
            $button_publish.removeClass('disabled');
            $button_preview.removeClass('disabled');
            $spinner.removeClass('is-active');
            window.onbeforeunload = null;

            // If preview button click, open new brower tab
            if ($button_preview.hasClass('clickAfterAjax')) {
                $button_preview.removeClass('clickAfterAjax');
                window.location.replace($button_preview.attr('href'));
            }
        },
        error: function() {
            console.log('error');
        }
    });

    return false;
});
