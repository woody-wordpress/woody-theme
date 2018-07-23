<?php

/**
 * Images
 *
 * @link https://www.advancedcustomfields.com/resources/acf-settings
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_Images
{
    public function __construct()
    {
        $this->register_hooks();
    }

    protected function register_hooks()
    {
        // Ratio 8:1 => Panoramique 1
        add_image_size('ratio_8_1_small', 360, 45, true);
        add_image_size('ratio_8_1_medium', 640, 80, true);
        add_image_size('ratio_8_1', 1200, 150, true);
        add_image_size('ratio_8_1_xlarge', 1920, 240, true);

        // Ratio 4:1 => Panoramique 2
        add_image_size('ratio_4_1_small', 360, 90, true);
        add_image_size('ratio_4_1_medium', 640, 160, true);
        add_image_size('ratio_4_1', 1200, 300, true);
        add_image_size('ratio_4_1_xlarge', 1920, 480, true);

        // Ratio 2:1 => Paysage 1
        add_image_size('ratio_2_1_small', 360, 180, true);
        add_image_size('ratio_2_1_medium', 640, 320, true);
        add_image_size('ratio_2_1', 1200, 600, true);
        add_image_size('ratio_2_1_xlarge', 1920, 960, true);

        // Ratio 16:9 => Paysage 2
        add_image_size('ratio_16_9_small', 360, 203, true);
        add_image_size('ratio_16_9_medium', 640, 360, true);
        add_image_size('ratio_16_9', 1200, 675, true);
        add_image_size('ratio_16_9_xlarge', 1920, 1080, true);

        // Ratio 4:3 => Paysage 3
        add_image_size('ratio_4_3_small', 360, 270, true);
        add_image_size('ratio_4_3_medium', 640, 480, true);
        add_image_size('ratio_4_3', 1200, 900, true);
        add_image_size('ratio_4_3_xlarge', 1920, 1440, true);

        // Carré
        add_image_size('ratio_square_small', 360, 360, true);
        add_image_size('ratio_square_medium', 640, 640, true);
        add_image_size('ratio_square', 1200, 1200, true);

        // Ratio 3:4 => Portrait 1
        add_image_size('ratio_3_4_small', 360, 480, true);
        add_image_size('ratio_3_4_medium', 640, 854, true);
        add_image_size('ratio_3_4', 1200, 1600, true);

        // Ratio 10:16 => Portrait 2
        add_image_size('ratio_10_16_small', 360, 576, true);
        add_image_size('ratio_10_16_medium', 640, 1024, true);
        add_image_size('ratio_10_16', 1200, 1920, true);

        // Ratio A4 => Brochure papier
        add_image_size('ratio_a4_small', 360, 509, true);
        add_image_size('ratio_a4_medium', 640, 905, true);
        add_image_size('ratio_a4', 1200, 1697, true);

        // Free => Proportions libre
        add_image_size('ratio_free_small', 360);
        add_image_size('ratio_free_medium', 640);
        add_image_size('ratio_free', 1200);
        add_image_size('ratio_free_xlarge', 1920);

        // Filters
        add_filter('image_size_names_choose', array($this, 'woody_custom_sizes'));
        add_filter('wp_generate_attachment_metadata', array($this, 'woody_custom_attachment_metadata'), 10, 2);

        // add_action('rest_api_init', function () {
        //     register_rest_route('woody', '/crop/(?P<width>[0-9]{1,4})/(?P<height>[0-9]{1,4})/(?P<url>[-=\w]+)', array(
        //       'methods' => 'GET',
        //       'callback' => array('WoodyTheme_Images', 'imagemagick')
        //     ));
        // });
    }

    // Register the new image sizes for use in the add media modal in wp-admin
    // This is the place where you can set readable names for images size
    public function woody_custom_sizes($sizes)
    {
        return array(
            'ratio_8_1' => __('Panoramique 1 (1200x150px)'),
            'ratio_4_1' => __('Panoramique 2 (1200x300px)'),
            'ratio_2_1' => __('Paysage 1 (1200x600px)'),
            'ratio_16_9' => __('Paysage 2 (1200x675px)'),
            'ratio_4_3' => __('Paysage 3 (1200x900px)'),
            'ratio_square' => __('Carré'),
            'ratio_3_4_medium' => __('Portrait 1 (640x853px)'),
            'ratio_10_16_medium' => __('Portrait 2 (640x1024px)'),
            'ratio_a4_medium' => __('Format A4 (640x905px)'),
            'ratio_free' => __('Proportions libres')
        );
    }

    // define the wp_generate_attachment_metadata callback
    public function woody_custom_attachment_metadata($metadata, $post_ID)
    {
        if (wp_attachment_is_image($post_ID)) {

            // Get current post
            $post = get_post($post_ID);

            // Create an array with the image meta (Title, Caption, Description) to be updated
            // Note:  comment out the Excerpt/Caption or Content/Description lines if not needed
            $my_image_meta = [];
            $my_image_meta['ID'] = $post_ID; // Specify the image (ID) to be updated

            if (empty($metadata['image_meta']['title'])) {
                $new_title = ucwords(strtolower(preg_replace('%\s*[-_\s]+\s*%', ' ', $post->post_title)));
                $my_image_meta['post_title'] = $new_title;
            } else {
                $new_title = $metadata['image_meta']['title'];
            }

            if (empty($post->post_excerpt)) {
                $new_description = $new_title;
                $my_image_meta['post_excerpt'] = $new_description;
            } else {
                $new_description = $post->post_excerpt;
            }

            if (empty($post->post_content)) {
                $my_image_meta['post_content'] = $new_description;
            }

            // Set the image Alt-Text
            update_post_meta($post_ID, '_wp_attachment_image_alt', $new_description);

            // Set the image meta (e.g. Title, Excerpt, Content)
            wp_update_post($my_image_meta);
        }

        return $metadata;
    }

    // public static function imagemagick(WP_REST_Request $request)
    // {
    //     /**
    //      * Exemple : http://www.superot.wp.rc-dev.com/wp-json/woody/crop/50/100/aHR0cDovL3d3dy5zdXBlcm90LndwLnJjLWRldi5jb20vYXBwL3VwbG9hZHMvc3VwZXJvdC8yMDE4LzA3L3Blb3BsZS1tYW4tMi5qcGc=
    //      */

    //     // Get parameters
    //     $params = $request->get_params();
    //     $width = $params['width'];
    //     $height = $params['height'];

    //     // Filename
    //     $url = parse_url(base64_decode($params['url']));
    //     $filename = WP_WEBROOT_DIR . $url['path'];
    //     if (!file_exists($filename)) {
    //         die('Image introuvale');
    //     }

    //     // New Filename
    //     $ext = pathinfo($filename, PATHINFO_EXTENSION);
    //     $new_filename = str_replace('.'.$ext, '-'.$width.'x'.$height.'.'.$ext, $filename);

    //     // Imagick execution
    //     if (!file_exists($new_filename)) {
    //         $image = new Imagick($filename);
    //         $w = $image->getImageWidth();
    //         $h = $image->getImageHeight();

    //         if ($w == $h) {
    //             if ($width >= $height) {
    //                 $resize_w = $width;
    //                 $resize_h = $width;
    //             } else {
    //                 $resize_w = $height;
    //                 $resize_h = $height;
    //             }
    //         } elseif ($w > $h) {
    //             $resize_w = $w * $height / $h;
    //             $resize_h = $height;
    //         } else {
    //             $resize_w = $width;
    //             $resize_h = $h * $width / $w;
    //         }

    //         $image->resizeImage($resize_w, $resize_h, Imagick::FILTER_LANCZOS, 0.9);
    //         $image->cropImage($width, $height, ($resize_w - $width) / 2, ($resize_h - $height) / 2);
    //         $image->writeImage($new_filename);
    //     }

    //     if (file_exists($new_filename)) {
    //         header('Content-type: ' . mime_content_type($new_filename));
    //         header('Cache-Control: max-age=315360000');
    //         header('Expires: Thu, 31 Dec 2037 23:55:55 GMT');
    //         print file_get_contents($new_filename);
    //     } else {
    //         die('Erreur de génération de la miniature');
    //     }
    // }
}

// Execute Class
new WoodyTheme_Images();
