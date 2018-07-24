import $ from 'jquery';
if ($('body').hasClass('themes-php')) {
    console.warn($('.theme-actions'));
    $('.theme-actions').remove();
    console.warn($('.theme-actions'));

}
