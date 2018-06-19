import $ from 'jquery';
import Swiper from 'swiper';

$('.swiper-container').each(function() {
    var $this = $(this);
    var slidesPerView = $this.data('slides-per-view');
    if (slidesPerView > 1) {
        var spaceBetween = 30;
    } else {
        var spaceBetween = 0;
    }
    new Swiper($this, {
        // Optional parameters
        slidesPerView: slidesPerView,
        spaceBetween: spaceBetween,
        keyboard: {
            enabled: true,
        },

        // If we need pagination
        pagination: {
            el: '.swiper-pagination',
            // dynamicBullets: true,
            clickable: true
        },

        // Navigation arrows
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
    });
});
