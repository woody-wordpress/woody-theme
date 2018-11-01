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
        $this->registerHooks();
        $this->addImageSizes();
    }

    protected function registerHooks()
    {
        // Actions
        add_action('add_attachment', [$this, 'addDefaultMediaType']);

        // Filters
        add_filter('intermediate_image_sizes_advanced', [$this, 'removeAutoThumbs'], 10, 2);
        add_filter('image_size_names_choose', [$this, 'imageSizeNamesChoose'], 10, 1);
        add_filter('wp_read_image_metadata', [$this, 'readImageMetadata'], 10, 4);
        add_filter('wp_generate_attachment_metadata', [$this, 'generateAttachmentMetadata'], 10, 2);

        add_action('rest_api_init', function () {
            register_rest_route('woody', '/crop/(?P<attachment_id>[0-9]{1,10})/(?P<ratio>\S+)', array(
              'methods' => 'GET',
              'callback' => [$this, 'cropImageAPI']
            ));
        });

        // add_action('rest_api_init', function () {
        //     register_rest_route('woody', '/crop_debug', array(
        //       'methods' => 'GET',
        //       'callback' => [$this, 'cropImageAPIDebug']
        //     ));
        // });
    }

    public function addImageSizes()
    {
        // Ratio 8:1 => Pano A
        add_image_size('ratio_8_1_small', 360, 45, true);
        add_image_size('ratio_8_1_medium', 640, 80, true);
        add_image_size('ratio_8_1_large', 1200, 150, true);
        add_image_size('ratio_8_1', 1920, 240, true);

        // Ratio 4:1 => Pano B
        add_image_size('ratio_4_1_small', 360, 90, true);
        add_image_size('ratio_4_1_medium', 640, 160, true);
        add_image_size('ratio_4_1_large', 1200, 300, true);
        add_image_size('ratio_4_1', 1920, 480, true);

        // Ratio 3:1 => Pano C
        add_image_size('ratio_3_1_small', 360, 120, true);
        add_image_size('ratio_3_1_medium', 640, 214, true);
        add_image_size('ratio_3_1_large', 1200, 400, true);
        add_image_size('ratio_3_1', 1920, 640, true);

        // Ratio 2:1 => Paysage A
        add_image_size('ratio_2_1_small', 360, 180, true);
        add_image_size('ratio_2_1_medium', 640, 320, true);
        add_image_size('ratio_2_1_large', 1200, 600, true);
        add_image_size('ratio_2_1', 1920, 960, true);

        // Ratio 16:9 => Paysage B
        add_image_size('ratio_16_9_small', 360, 203, true);
        add_image_size('ratio_16_9_medium', 640, 360, true);
        add_image_size('ratio_16_9_large', 1200, 675, true);
        add_image_size('ratio_16_9', 1920, 1080, true);

        // Ratio 4:3 => Paysage C
        add_image_size('ratio_4_3_small', 360, 270, true);
        add_image_size('ratio_4_3_medium', 640, 480, true);
        add_image_size('ratio_4_3_large', 1200, 900, true);
        add_image_size('ratio_4_3', 1920, 1440, true);

        // Ratio 3:4 => Portrait A
        add_image_size('ratio_3_4_small', 360, 480, true);
        add_image_size('ratio_3_4_medium', 640, 854, true);
        add_image_size('ratio_3_4', 1200, 1600, true);

        // Ratio 10:16 => Portrait B
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
        add_image_size('ratio_free_large', 1200);
        add_image_size('ratio_free', 1920);
    }

    public function addDefaultMediaType($post_id)
    {
        $terms_list = wp_get_post_terms($post_id, 'attachment_types');
        if (!empty($terms_list)) {
            wp_set_object_terms($post_id, 'Média ajouté manuellement', 'attachment_types', true);
        }
    }

    // Remove default image sizes here.
    public function removeAutoThumbs($sizes, $metadata)
    {
        return [];
    }

    // Register the new image sizes for use in the add media modal in wp-admin
    // This is the place where you can set readable names for images size
    public function imageSizeNamesChoose($sizes)
    {
        return array(
            'ratio_8_1' => __('Pano A (1920x240)'),
            'ratio_4_1' => __('Pano B (1920x480)'),
            'ratio_3_1' => __('Pano C (1920x640)'),
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

    /* ------------------------ */
    /* Read EXIF/IPTC Metadatas */
    /* ------------------------ */

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

    private function calc($val)
    {
        $val = explode('/', $val);
        return $val[0] / $val[1];
    }

    private function dmsToDecimal($deg, $min, $sec, $ref)
    {
        $direction = 1;
        if (strtoupper($ref) == "S" || strtoupper($ref) == "W" || $deg < 0) {
            $direction = -1;
            $deg = abs($deg);
        }
        return ($deg + ($min / 60) + ($sec / 3600)) * $direction;
    }

    /* ------------------------ */
    /* Default Metadatas        */
    /* ------------------------ */

    // define the wp_generate_attachment_metadata callback
    public function generateAttachmentMetadata($metadata, $attachment_id)
    {
        if (wp_attachment_is_image($attachment_id)) {

            // Get current post
            $post = get_post($attachment_id);

            // Create an array with the image meta (Title, Caption, Description) to be updated
            // Note:  comment out the Excerpt/Caption or Content/Description lines if not needed
            $my_image_meta = [];
            $my_image_meta['ID'] = $attachment_id; // Specify the image (ID) to be updated

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
            update_post_meta($attachment_id, '_wp_attachment_image_alt', $new_description);

            // Set the image meta (e.g. Title, Excerpt, Content)
            wp_update_post($my_image_meta);

            // Set ACF Fields (Credit)
            if (!empty($metadata['image_meta']['credit'])) {
                update_field('media_author', $metadata['image_meta']['credit'], $attachment_id);
            }

            if (!empty($metadata['image_meta']['latitude'])) {
                update_field('media_lat', $metadata['image_meta']['latitude'], $attachment_id);
            }

            if (!empty($metadata['image_meta']['longitude'])) {
                update_field('media_lng', $metadata['image_meta']['longitude'], $attachment_id);
            }

            // Crop API
            global $_wp_additional_image_sizes;

            // Added default sizes
            $_wp_additional_image_sizes['thumbnail'] = ['height' => 150, 'width' => 150, 'crop' => true];
            $_wp_additional_image_sizes['medium'] = ['height' => 300, 'width' => 300, 'crop' => true];
            $_wp_additional_image_sizes['large'] = ['height' => 1024, 'width' => 1024, 'crop' => true];

            // Get Mime-Type
            $mime_type = mime_content_type(WP_UPLOAD_DIR . '/' . $metadata['file']);

            foreach ($_wp_additional_image_sizes as $ratio => $size) {
                if (empty($metadata['sizes'][$ratio])) {
                    $metadata['sizes'][$ratio] = [
                        'file' => '../../../../../wp-json/woody/crop/' . $attachment_id . '/' . $ratio,
                        'height' => $size['height'],
                        'width' => $size['width'],
                        'mime-type' => $mime_type,
                    ];
                }
            }
        }

        return $metadata;
    }

    /* ------------------------ */
    /* CROP API                 */
    /* ------------------------ */

    public function cropImageAPI(WP_REST_Request $request)
    {
        /**
        * Exemple : http://www.superot.wp.rc-dev.com/wp-json/woody/crop/382/ratio_square
        */
        global $_wp_additional_image_sizes;

        $params = $request->get_params();
        $ratio_name = $params['ratio'];
        $attachment_id = $params['attachment_id'];
        $image_url = '';

        // Added default sizes
        $_wp_additional_image_sizes['thumbnail'] = ['height' => 150, 'width' => 150, 'crop' => true];
        $_wp_additional_image_sizes['medium'] = ['height' => 300, 'width' => 300, 'crop' => true];
        $_wp_additional_image_sizes['large'] = ['height' => 1024, 'width' => 1024, 'crop' => true];

        if (!empty($_wp_additional_image_sizes[$ratio_name])) {
            $size = $_wp_additional_image_sizes[$ratio_name];
            $attachment_metadata = maybe_unserialize(wp_get_attachment_metadata($attachment_id));
            $img_path = WP_UPLOAD_DIR . '/' . $attachment_metadata['file'];
            if (file_exists($img_path)) {
                if (empty($attachment_metadata['sizes'][$ratio_name]) || strpos($attachment_metadata['sizes'][$ratio_name]['file'], 'wp-json') !== false) {
                    $image_crop = $this->cropImage($img_path, $size);
                    if (!empty($image_crop)) {
                        $attachment_metadata['sizes'][$ratio_name]['file'] = $image_crop;
                        wp_update_attachment_metadata($attachment_id, $attachment_metadata);
                    }
                }

                $image_url = wp_get_attachment_image_url($attachment_id, $ratio_name);
            } else {
                $image_url = 'https://api.tourism-system.com/resize/force_clip/' . $size['width'] . '/' . $size['height'] . '/70/aHR0cHM6Ly9hcGkudG91cmlzbS1zeXN0ZW0uY29tL3N0YXRpYy9hc3NldHMvaW1hZ2VzL3Jlc2l6ZXIvaW1nXzQwNC5qcGc=/404.jpg';
            }
        }

        if (!empty($image_url)) {
            wp_redirect($image_url, 301);
        } else {
            header('HTTP/1.0 404 Not Found');
        }
        exit;
    }

    // public function cropImageAPIDebug()
    // {
    //     global $_wp_additional_image_sizes;

    //     header('Content-type: text/html');
    //     foreach ($_wp_additional_image_sizes as $ratio => $size) {
    //         if (strpos($ratio, 'small') !== false) {
    //             continue;
    //         }
    //         if (strpos($ratio, 'medium') !== false) {
    //             continue;
    //         }
    //         if (strpos($ratio, 'large') !== false) {
    //             continue;
    //         }
    //         print '<h2>' . $ratio . '</h2>';
    //         print '<p><img style="max-width:50%" src="/wp-json/woody/crop/440/' . $ratio . '" title="' . $ratio . '" alt="' . $ratio . '"></p>';
    //     }
    // }

    private function cropImage($img_path, $size, $debug = false)
    {
        $return = '';

        // Get infos from original image
        $img_path_parts = pathinfo($img_path);

        // get the size of the image
        list($width_orig, $height_orig) = getimagesize($img_path);
        $ratio_orig = (float) $height_orig / $width_orig;

        // Ratio Free
        if ($size['height'] == 0) {
            $req_width = $width_orig;
            $req_height = $height_orig;

            if ($ratio_orig != 1) {
                $size['height'] = round($size['width'] * $ratio_orig);
            } elseif ($ratio_orig == 1) {
                $size['height'] = $size['width'];
            }
        }

        // Get ratio diff
        $ratio_expect = (float) $size['height'] / $size['width'];
        $ratio_diff = $ratio_orig - $ratio_expect;

        // Calcul du crop size
        if ($ratio_diff > 0) {
            $req_width = $width_orig;
            $req_height = round($width_orig * $ratio_expect);
            $req_x = 0;
            $req_y = round(($height_orig - $req_height)/2);
        } elseif ($ratio_diff < 0) {
            $req_width = round($height_orig / $ratio_expect);
            $req_height = $height_orig;
            $req_x = round(($width_orig - $req_width)/2);
            $req_y = 0;
        } elseif ($ratio_diff == 0) {
            $req_width = $width_orig;
            $req_height = $height_orig;
            $req_x = 0;
            $req_y = 0;
        }

        // Set filename
        $cropped_image_filename = $img_path_parts['filename'] . '-' . $size['width'] . 'x' . $size['height'] . '.' . $img_path_parts['extension'];
        $cropped_image_path = $img_path_parts['dirname'] . '/' . $cropped_image_filename;
        unlink($cropped_image_path);

        // Crop
        $img_editor = wp_get_image_editor($img_path);
        if (!is_wp_error($img_editor)) {
            $img_editor->crop($req_x, $req_y, $req_width, $req_height, $size['width'], $size['height'], false);
            $img_editor->set_quality(75);
            $img_editor->save($cropped_image_path);

            // Get Image cropped data
            $img_cropped_parts = pathinfo($cropped_image_path);
            $return = $img_cropped_parts['basename'];
        }
        unset($img_editor);

        return $return;
    }
}
