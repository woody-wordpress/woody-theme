import $ from 'jquery';

$('.claims-blocks-wrapper').each(function() {
    var $this = $(this),
        url = window.location.href;
    $.ajax({
        type: 'POST',
        url: '/wp-json/woody/claims-blocks',
        data: url,
        success: function(data) {
            var item = data[Math.floor(Math.random() * data.length)];
            $this.append(item);
            var swiper_options = $this.find('.claims-swiper').data('options');
            var claimSwiper = new Swiper('.claims-swiper', swiper_options);
            $('.woody-component-claims-block').each(function() {
                var $this = $(this),
                    closeClaim = $this.find('.claim-close-button');
                console.log(closeClaim);
                // TODO: set cookie on click
                closeClaim.click(function() {
                    console.log($this);
                    $this.remove();
                });
            });
        },
        error: function(data) {
            console.error('claim', data);
        },
    });
});
