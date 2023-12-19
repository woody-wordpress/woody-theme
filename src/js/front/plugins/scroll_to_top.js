!(function ($) {
    var $scroll_to_top = $('<div>').append('<div id="scroll_to_top"><div class="inner"></div></div>').children();

    $scroll_to_top.click(function () {
        $('html,body').animate({ scrollTop: 0 }, 'slow');
    });

    // Append div
    $('body').append($scroll_to_top);

    // Show on scroll
    $(window).scroll(function () {
        if ($(window).scrollTop() < 800) {
            $scroll_to_top.css("opacity", 0);
        } else {
            $scroll_to_top.css("opacity", 1);
        }
    });
})(jQuery);
