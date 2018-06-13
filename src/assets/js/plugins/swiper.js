import Swiper from 'swiper';

var woodySwiperShow1 = new Swiper('.swiper-container', {
    // Optional parameters
    keyboard: {
        enabled: true,
    },

    // If we need pagination
    pagination: {
        el: '.swiper-pagination',
        dynamicBullets: true,
        clickable: true
    },

    // Navigation arrows
    navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
    },
});

var woodySwiperShow2 = new Swiper('.swiper-container.slidesPerView-3', {
    // Optional parameters
    slidesPerView: 3,
    spaceBetween: 30,
    keyboard: {
        enabled: true,
    },

    // If we need pagination
    pagination: {
        el: '.swiper-pagination',
        dynamicBullets: true,
        clickable: true
    },

    // Navigation arrows
    navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
    },
})
