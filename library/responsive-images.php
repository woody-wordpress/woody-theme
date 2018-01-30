<?php
/**
 * Configure responsive images sizes
 *
 * @package WordPress
 * @subpackage FoundationPress
 * @since FoundationPress 2.6.0
 */

// Add featured image sizes
//
// Sizes are optimized and cropped for landscape aspect ratio
// and optimized for HiDPI displays on 'small' and 'medium' screen sizes.
add_image_size( 'featured-small', 640, 200, true ); // name, width, height, crop
add_image_size( 'featured-medium', 1280, 400, true );
add_image_size( 'featured-large', 1440, 400, true );
add_image_size( 'featured-xlarge', 1920, 400, true );

// Add additional image sizes
add_image_size( 'fp-small', 640 );
add_image_size( 'fp-medium', 1024 );
add_image_size( 'fp-large', 1200 );
add_image_size( 'fp-xlarge', 1920 );

/**
** RC Custom sizes
**/

// Ratio 8:1 => Panoramique
add_image_size('ratio_8_1_small', 360, 45, true);
add_image_size('ratio_8_1_medium', 640, 80, true);
add_image_size('ratio_8_1', 1200, 150, true);
add_image_size('ratio_8_1_xlarge', 1920, 240, true);

// Ratio 4:1 => Panoramique moyen
add_image_size('ratio_4_1_small', 360, 90, true);
add_image_size('ratio_4_1_medium', 640, 160, true);
add_image_size('ratio_4_1', 1200, 300, true);
add_image_size('ratio_4_1_xlarge', 1920, 480, true);

// Ratio 8:3 => Paysage long
add_image_size('ratio_8_3_small', 360, 135, true);
add_image_size('ratio_8_3_medium', 640, 240, true);
add_image_size('ratio_8_3', 1200, 450, true);
add_image_size('ratio_8_3_xlarge', 1920, 720, true);

// Ratio 16:9 => Paysage
add_image_size('ratio_16_9_small', 360, 200, true);
add_image_size('ratio_16_9_medium', 640, 360, true);
add_image_size('ratio_16_9', 1200, 675, true);
add_image_size('ratio_16_9_xlarge', 1920, 1080, true);

// Ratio 16:10 => Paysage haut
add_image_size('ratio_16_10_small', 360, 225, true);
add_image_size('ratio_16_10_medium', 640, 400, true);
add_image_size('ratio_16_10', 1200, 750, true);
add_image_size('ratio_16_10_xlarge', 1920, 1200, true);

// Square
add_image_size('ratio_square_small', 140, 140, true);
add_image_size('ratio_square_medium', 360, 360, true);
add_image_size('ratio_square', 660, 660, true);

// Ratio 10:16 => Portrait large
add_image_size('ratio_10_16_small', 200, 320, true);
add_image_size('ratio_10_16_medium', 360, 576, true);
add_image_size('ratio_10_16', 675, 1080, true);

// Ratio 9:16 => Portrait
add_image_size('ratio_9_16_small', 200, 360, true);
add_image_size('ratio_9_16_medium', 360, 640, true);
add_image_size('ratio_9_16', 675, 1200, true);

// Free => Proportions libre
add_image_size('ratio_free_small', 360);
add_image_size('ratio_free_medium', 640);
add_image_size('ratio_free', 1200);
add_image_size('ratio_free_xlarge', 1920);

// Register the new image sizes for use in the add media modal in wp-admin
// This is the place where you can set readable names for images size
function basetheme_custom_sizes( $sizes ) {
	return array(
        'ratio_8_1' => __( 'Panoramique' ),
        'ratio_4_1' => __( 'Panoramique moyen' ),
        'ratio_8_3' => __( 'Paysage long' ),
        'ratio_16_9' => __( 'Paysage' ),
        'ratio_16_10' => __( 'Paysage haut' ),
        'ratio_square' => __( 'Carré' ),
        'ratio_10_16' => __( 'Portrait large' ),
        'ratio_9_16' => __( 'Portrait' ),
        'ratio_free' => __( 'Proportions libres' )
	);
}
add_filter( 'image_size_names_choose', 'basetheme_custom_sizes' );

// Add custom image sizes attribute to enhance responsive image functionality for content images
function basetheme_adjust_image_sizes_attr( $sizes, $size ) {

	// Actual width of image
	$width = $size[0];

	// Full width page template
	if ( is_page_template( 'page-templates/page-full-width.php' ) ) {
		if ( 1200 < $width ) {
			$sizes = '(max-width: 1199px) 98vw, 1200px';
		} else {
			$sizes = '(max-width: 1199px) 98vw, ' . $width . 'px';
		}
	} else { // Default 3/4 column post/page layout
		if ( 770 < $width ) {
			$sizes = '(max-width: 639px) 98vw, (max-width: 1199px) 64vw, 770px';
		} else {
			$sizes = '(max-width: 639px) 98vw, (max-width: 1199px) 64vw, ' . $width . 'px';
		}
	}

	return $sizes;
}
add_filter( 'wp_calculate_image_sizes', 'basetheme_adjust_image_sizes_attr', 10 , 2 );

// Remove inline width and height attributes for post thumbnails
function remove_thumbnail_dimensions( $html, $post_id, $post_image_id ) {
    if(!strpos($html, 'attachment-shop_single')) {
        $html = preg_replace( '/^(width|height)=\"\d*\"\s/', '', $html );
    }
    return $html;
}
add_filter( 'post_thumbnail_html', 'remove_thumbnail_dimensions', 10, 3 );
