import $ from 'jquery';
import Swiper from 'swiper';

var $i = 0;

$('.swiper-container').each(function() {
    var $this = $(this);

    $this.addClass('swiper-' + $i);

    if (typeof($this.data('slides-per-view')) !== undefined) {
        var slidesPerView = $this.data('slides-per-view');
    } else {
        var slidesPerView = 1;
    }

    if (typeof($this.data('slides-spacing')) !== undefined) {
        var spaceBetween = $this.data('slides-spacing');
    } else {
        var spaceBetween = 30;
    }

    if (typeof($this.data('slides-loop')) !== undefined) {
        var loop = $this.data('slides-loop');
    } else {
        var loop = false;
    }

    if (typeof($this.data('slides-center')) !== undefined) {
        var centeredSlides = $this.data('slides-center');
    } else {
        var centeredSlides = false;
    }

    // if (typeof($this.data('swiperargs')) !== undefined) {
    //     var swiperArgs = $this.data('swiperargs');
    //     console.warn($this.data('swiperargs'));
    // } else {
    //     var swiperArgs = '';
    // }

    // console.warn(swiperArgs);

    new Swiper('.swiper-' + $i, {
        // Optional parameters
        slidesPerView: slidesPerView,
        spaceBetween: spaceBetween,
        freeMode: true,
        loop: loop,
        centeredSlides: centeredSlides,
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
            nextEl: '.' + $this.data('nav') + ' .swiper-button-next',
            prevEl: '.' + $this.data('nav') + ' .swiper-button-prev',
        },
    });

    $i++;
});
