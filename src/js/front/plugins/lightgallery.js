import $ from 'jquery';
import * as lightGallery from 'lightgallery';
import * as thumbnail from 'lg-thumbnail';

$(".woodyGallery").lightGallery({
    mode: 'lg-fade',
    selector: '.mediaCard',
    cssEasing: 'cubic-bezier(0.25, 0, 0.25, 1)',
    thumbWidth: 120,
    thumbHeight: '90px'
});
