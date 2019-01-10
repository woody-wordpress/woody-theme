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
        },
        error: function(data) {
            console.error('claim', data);
        },
    });
});
