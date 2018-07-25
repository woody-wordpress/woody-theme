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
    protected $process;

    public function __construct()
    {
        $this->registerHooks();
        $this->process = new WoodyTheme_Process();
    }

    protected function registerHooks()
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

        // Carré
        add_image_size('ratio_square_small', 360, 360, true);
        add_image_size('ratio_square_medium', 640, 640, true);
        add_image_size('ratio_square', 1200, 1200, true);

        // Free => Proportions libre
        add_image_size('ratio_free_small', 360);
        add_image_size('ratio_free_medium', 640);
        add_image_size('ratio_free', 1200);
        add_image_size('ratio_free_xlarge', 1920);

        // Filters
        add_filter('intermediate_image_sizes_advanced', array($this, 'removeAutoThumbs'), 10, 2);
        add_filter('image_size_names_choose', array($this, 'imageSizeNamesChoose'), 10, 1);
        add_filter('wp_read_image_metadata', array($this, 'readImageMetadata'), 10, 4);
        add_filter('wp_generate_attachment_metadata', array($this, 'generateAttachmentMetadata'), 10, 2);

        // add_action('rest_api_init', function () {
        //     register_rest_route('woody', '/crop/(?P<width>[0-9]{1,4})/(?P<height>[0-9]{1,4})/(?P<url>[-=\w]+)', array(
        //       'methods' => 'GET',
        //       'callback' => array('WoodyTheme_Images', 'imagemagick')
        //     ));
        // });
    }

    // Remove default image sizes here.
    public function removeAutoThumbs($sizes, $metadata)
    {
        // Added to queue the generation of other thumbnails
        $defer_sizes = $sizes;
        unset($defer_sizes['thumbnail']);
        unset($defer_sizes['medium']);
        unset($defer_sizes['small']);
        unset($defer_sizes['large']);
        unset($defer_sizes['medium_large']);

        $item = [
            'function' => [get_class($this), 'generateThumbnails'],
            'args' => ['metadata' => $metadata, 'sizes' => $defer_sizes]
        ];
        $this->process->push_to_queue($item);
        $this->process->save()->dispatch();

        // Thumbnail only the thumbnail
        return array(
            'thumbnail' => $sizes['thumbnail'],
            'medium' => $sizes['medium']
        );
    }

    public function generateThumbnails($args)
    {
        global $wpdb;
        extract($args); // create $sizes + $metadata

        if (!empty($sizes) && !empty($metadata)) {
            $post_id = $wpdb->get_col($wpdb->prepare("SELECT post_id FROM $wpdb->postmeta WHERE meta_value='%s';", $metadata['file']));
            if (!empty($post_id) && is_numeric($post_id[0])) {
                $post_id = current($post_id);
                $attachment_metadata = maybe_unserialize(wp_get_attachment_metadata($post_id));

                $editor = wp_get_image_editor(WP_UPLOAD_DIR . '/' . $metadata['file']);
                if (!is_wp_error($editor)) {
                    $attachment_metadata['sizes'] = $editor->multi_resize($sizes);
                }

                wp_update_attachment_metadata($post_id, $attachment_metadata);
            }
        }
    }

    // Register the new image sizes for use in the add media modal in wp-admin
    // This is the place where you can set readable names for images size
    public function imageSizeNamesChoose($sizes)
    {
        return array(
            'ratio_8_1' => __('Pano A (1920x240)'),
            'ratio_4_1' => __('Pano B (1920x480)'),
            'ratio_2_1' => __('Paysage A (1920x960)'),
            'ratio_16_9' => __('Paysage B (1920x1080)'),
            'ratio_4_3' => __('Paysage C (1920x1440)'),
            'ratio_3_4_medium' => __('Portrait A (1200x1600)'),
            'ratio_10_16_medium' => __('Portrait B (1200x1920)'),
            'ratio_a4_medium' => __('Format A4'),
            'ratio_square' => __('Carré'),
            'ratio_free' => __('Proportions libres')
        );
    }

    // define the wp_generate_attachment_metadata callback
    public function readImageMetadata($meta, $file, $sourceImageType, $iptc)
    {
        if (is_callable('exif_read_data') && in_array($sourceImageType, apply_filters('wp_read_image_metadata_types', array( IMAGETYPE_JPEG, IMAGETYPE_TIFF_II, IMAGETYPE_TIFF_MM )))) {
            $exif = @exif_read_data($file);

            if (!empty($exif['GPSLatitude']) && !empty($exif['GPSLatitudeRef'])) {
                $lat_deg = $this->calc($exif['GPSLatitude'][0]);
                $lat_min = $this->calc($exif['GPSLatitude'][1]);
                $lat_sec = $this->calc($exif['GPSLatitude'][2]);
                $meta['latitude'] = $this->dmsToDecimal($lat_deg, $lat_min, $lat_sec, $exif['GPSLatitudeRef']);
            }

            if (!empty($exif['GPSLongitude']) && !empty($exif['GPSLongitudeRef'])) {
                $lng_deg = $this->calc($exif['GPSLongitude'][0]);
                $lng_min = $this->calc($exif['GPSLongitude'][1]);
                $lng_sec = $this->calc($exif['GPSLongitude'][2]);
                $meta['longitude'] = $this->dmsToDecimal($lng_deg, $lng_min, $lng_sec, $exif['GPSLongitudeRef']);
            }
        }

        return $meta;
    }

    public function calc($val)
    {
        $val = explode('/', $val);
        return $val[0] / $val[1];
    }

    public function dmsToDecimal($deg, $min, $sec, $ref)
    {
        $direction = 1;
        if (strtoupper($ref) == "S" || strtoupper($ref) == "W" || $deg < 0) {
            $direction = -1;
            $deg = abs($deg);
        }
        return ($deg + ($min / 60) + ($sec / 3600)) * $direction;
    }

    // define the wp_generate_attachment_metadata callback
    public function generateAttachmentMetadata($metadata, $wpPostId)
    {
        if (wp_attachment_is_image($wpPostId)) {

            // Get current post
            $post = get_post($wpPostId);

            // Create an array with the image meta (Title, Caption, Description) to be updated
            // Note:  comment out the Excerpt/Caption or Content/Description lines if not needed
            $my_image_meta = [];
            $my_image_meta['ID'] = $wpPostId; // Specify the image (ID) to be updated

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
            update_post_meta($wpPostId, '_wp_attachment_image_alt', $new_description);

            // Set the image meta (e.g. Title, Excerpt, Content)
            wp_update_post($my_image_meta);

            // Set ACF Fields (Credit)
            if (!empty($metadata['image_meta']['credit'])) {
                update_field('media_author', $metadata['image_meta']['credit'], $wpPostId);
            }

            if (!empty($metadata['image_meta']['latitude'])) {
                update_field('media_lat', $metadata['image_meta']['latitude'], $wpPostId);
            }

            if (!empty($metadata['image_meta']['longitude'])) {
                update_field('media_lng', $metadata['image_meta']['longitude'], $wpPostId);
            }
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
