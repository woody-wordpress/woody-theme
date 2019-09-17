<?php

/**
 * Images
 *
 * @link https://www.advancedcustomfields.com/resources/acf-settings
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */
use Woody\Utils\Output;

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
        add_action('add_attachment', [$this, 'addAttachment'], 50);
        add_filter('attachment_fields_to_save', [$this, 'attachmentFieldsToSave'], 12, 2); // Priority 12 ater polylang
        add_action('save_attachment', [$this, 'saveAttachment'], 50);
        add_action('delete_attachment', [$this, 'deleteAttachment'], 1);

        // Filters
        add_filter('intermediate_image_sizes_advanced', [$this, 'removeAutoThumbs'], 10, 2);
        add_filter('image_size_names_choose', [$this, 'imageSizeNamesChoose'], 10, 1);
        add_filter('wp_read_image_metadata', [$this, 'readImageMetadata'], 10, 4);
        add_filter('wp_generate_attachment_metadata', [$this, 'generateAttachmentMetadata'], 10, 2);
        add_filter('wp_handle_upload_prefilter', [$this, 'maxUploadSize']);
        add_filter('upload_mimes', [$this, 'uploadMimes'], 10, 1);
        // add_filter('wp_handle_upload', [$this, 'convertFileToGeoJSON'], 100, 1);

        // API Crop
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

    public function uploadMimes($mime_types)
    {
        $mime_types['gpx'] = 'application/xml';
        $mime_types['kml'] = 'application/xml';
        $mime_types['kmz'] = 'application/xml';
        $mime_types['json'] = 'text/plain';
        $mime_types['geojson'] = 'text/plain';

        return $mime_types;
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

    // Remove default image sizes here.
    public function removeAutoThumbs($sizes, $metadata)
    {
        return [];
    }

    public function maxUploadSize($file)
    {
        if (WP_SITE_KEY == 'crt-bretagne') {
            $limit = 20000;
            $limit_output = '20Mo';
        } else {
            $limit = 10000;
            $limit_output = '10Mo';
        }

        $size = $file['size'];
        $size = $size / 1024;
        $type = $file['type'];
        $is_image = strpos($type, 'image') !== false;
        if ($is_image && $size > $limit) {
            $file['error'] = 'Une image doit faire moins de ' . $limit_output;
        }

        return $file;
    }

    /**
     * Convert a kml or a gpx to GeoJSON
     * @author : Jérémy Legendre
     * @param   file
     * @return  return      file content converted to geoJSON
     */
    public function convertFileToGeoJSON($file)
    {
        if (strpos($file['file'], 'gpx') || strpos($file['file'], 'kml')) {
            $url = "http://ogre.adc4gis.com/convert";
            $curl = curl_init();

            $params = [
                'upload' => $file['url'],
                'skipFailures' => true
            ];

            $params_string = http_build_query($params);
            $opts = [
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $params_string,
                CURLOPT_TIMEOUT => 1000,
                CURLOPT_CONNECTTIMEOUT => 1000
            ];
            curl_setopt_array($curl, $opts);

            $response = curl_exec($curl);
            curl_close($curl);
        }

        return $file;
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
        // EXIF
        if (is_callable('exif_read_data') && in_array($sourceImageType, apply_filters('wp_read_image_metadata_types', array(IMAGETYPE_JPEG, IMAGETYPE_TIFF_II, IMAGETYPE_TIFF_MM)))) {
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

        // IPTC
        $size = getimagesize($file, $info);
        if (!empty($info['APP13'])) {
            $iptc = iptcparse($info['APP13']);

            // Places
            if (empty($meta['city']) && !empty($iptc['2#090'])) {
                $meta['city'] = ucfirst(strtolower(current($iptc['2#090'])));
            }

            if (empty($meta['state']) && !empty($iptc['2#095'])) {
                $meta['state'] = ucfirst(strtolower(current($iptc['2#095'])));
            }

            if (empty($meta['country']) && !empty($iptc['2#101'])) {
                $meta['country'] = ucfirst(strtolower(current($iptc['2#101'])));
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
    /* Sync attachment data     */
    /* ------------------------ */

    public function addAttachment($attachment_id)
    {
        if (pll_get_post_language($attachment_id) == PLL_DEFAULT_LANG) {
            // Added attachment_types
            wp_set_object_terms($attachment_id, 'Média ajouté manuellement', 'attachment_types', false);

            // Duplicate all medias
            $this->saveAttachment($attachment_id);
        }
    }

    public function deleteAttachment($attachment_id)
    {
        remove_action('delete_attachment', [$this, 'deleteAttachment']);

        $deleted_attachement = get_transient('woody_deleted_attachement');
        if (empty($deleted_attachement)) {
            $deleted_attachement = [];
        }

        if (wp_attachment_is_image($attachment_id) && is_array($deleted_attachement) && !in_array($attachment_id, $deleted_attachement)) {
            $translations = pll_get_post_translations($attachment_id);
            $deleted_attachement = array_merge($deleted_attachement, array_values($translations));
            set_transient('woody_deleted_attachement', $deleted_attachement);

            foreach ($translations as $t_attachment_id) {
                if ($t_attachment_id != $attachment_id) {
                    wp_delete_attachment($t_attachment_id);
                }
            }
        }
    }

    public function attachmentFieldsToSave($post, $attachment)
    {
        if (!empty($post['ID'])) {
            $this->saveAttachment($post['ID']);
        }

        return $post;
    }

    public function saveAttachment($attachment_id)
    {
        if (wp_attachment_is_image($attachment_id)) {

            // Only if current edit post is default (FR)
            $languages = pll_languages_list();
            $current_lang = pll_get_post_language($attachment_id);

            if ($current_lang == PLL_DEFAULT_LANG) {
                foreach ($languages as $lang) {
                    if ($lang == PLL_DEFAULT_LANG) {
                        continue;
                    }

                    $t_attachment_id = pll_get_post($attachment_id, $lang);
                    if (empty($t_attachment_id)) {
                        // Duplicate media with Polylang Method
                        $t_attachment_id = apply_filters('woody_pll_create_media_translation', $attachment_id, $lang);
                    }

                    // Sync Meta and fields
                    if (!empty($t_attachment_id)) {
                        $this->syncAttachmentMetadata($attachment_id, $t_attachment_id);
                    }
                }
            } else {
                $t_attachment_id = $attachment_id;
                $attachment_id = pll_get_post($t_attachment_id, PLL_DEFAULT_LANG);

                // Sync Meta and fields
                if (!empty($attachment_id)) {
                    $this->syncAttachmentMetadata($attachment_id, $t_attachment_id);
                }
            }
        }
    }

    private function syncAttachmentMetadata($attachment_id = null, $t_attachment_id = null)
    {
        if (!empty($t_attachment_id) && !empty($attachment_id)) {

            // Get metadatas (crop sizes)
            $attachment_metadata = wp_get_attachment_metadata($attachment_id);

            // Updated metadatas (crop sizes)
            if (!empty($attachment_metadata)) {
                wp_update_attachment_metadata($t_attachment_id, $attachment_metadata);
            }

            // Get ACF Fields (Author, Lat, Lng)
            $fields = get_fields($attachment_id);

            // Update ACF Fields (Author, Lat, Lng)
            if (!empty($fields)) {
                foreach ($fields as $selector => $value) {
                    if ($selector == 'media_linked_page') {
                        continue;
                    }
                    update_field($selector, $value, $t_attachment_id);
                }
            }

            // Sync attachment taxonomies
            $tags = [];
            $sync_taxonomies = ['attachment_types', 'attachment_hashtags', 'attachment_categories'];
            foreach ($sync_taxonomies as $taxonomy) {
                $terms = wp_get_post_terms($attachment_id, $taxonomy);
                $tags[$taxonomy] = [];
                if (!empty($terms)) {
                    foreach ($terms as $term) {
                        $tags[$taxonomy][] = $term->name;
                    }

                    // Si la photo a le tag Instagram, elle n'a que celui-là;
                    if (in_array('Instagram', $tags[$taxonomy])) {
                        $tags[$taxonomy] = ['Instagram'];
                    }

                    wp_set_post_terms($attachment_id, $tags[$taxonomy], $taxonomy, false);
                }
            }

            // Synchro Terms
            if (!empty($tags)) {
                foreach ($tags as $taxonomy => $keywords) {
                    wp_set_post_terms($t_attachment_id, $keywords, $taxonomy, false);
                }
            }
        }
    }

    /* ------------------------ */
    /* Default Metadatas        */
    /* ------------------------ */

    // define the wp_generate_attachment_metadata callback
    public function generateAttachmentMetadata($metadata, $attachment_id)
    {
        if (wp_attachment_is_image($attachment_id)) {
            $attachment_metadata = wp_get_attachment_metadata($attachment_id);

            if (!empty($attachment_metadata)) {
                $metadata = $attachment_metadata;
            } else {
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

                // Import tags
                if (!empty($metadata['image_meta']['city']) || !empty($metadata['image_meta']['state']) || !empty($metadata['image_meta']['country'])) {
                    $terms_places = get_terms('places', ['hide_empty' => false]);
                    foreach ($terms_places as $term_places) {
                        if (!empty($metadata['image_meta']['city']) && sanitize_title($metadata['image_meta']['city']) == $term_places->slug) {
                            wp_set_object_terms($attachment_id, $term_places->slug, 'places', true);
                        } elseif (!empty($metadata['image_meta']['state']) && sanitize_title($metadata['image_meta']['state']) == $term_places->slug) {
                            wp_set_object_terms($attachment_id, $term_places->slug, 'places', true);
                        } elseif (!empty($metadata['image_meta']['country']) && sanitize_title($metadata['image_meta']['country']) == $term_places->slug) {
                            wp_set_object_terms($attachment_id, $term_places->slug, 'places', true);
                        } elseif (!empty($metadata['image_meta']['keywords'])) {
                            foreach ($metadata['image_meta']['keywords'] as $keyword) {
                                if (sanitize_title($keyword) == $term_places->slug) {
                                    wp_set_object_terms($attachment_id, $term_places->slug, 'places', true);
                                }
                            }
                        }
                    }
                }

                if (!empty($metadata['image_meta']['keywords'])) {
                    $terms_attachment_categories = get_terms('attachment_categories', ['hide_empty' => false]);
                    foreach ($terms_attachment_categories as $term_attachment_categories) {
                        foreach ($metadata['image_meta']['keywords'] as $keyword) {
                            if (sanitize_title($keyword) == $term_attachment_categories->slug) {
                                wp_set_object_terms($attachment_id, $term_attachment_categories->slug, 'attachment_categories', true);
                            }
                        }
                    }
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

                // Added full size
                $filename = explode('/', $metadata['file']);
                $filename = end($filename);
                $metadata['sizes']['full'] = [
                    'file' => $filename,
                    'height' => $metadata['height'],
                    'width' => $metadata['width'],
                    'mime-type' => $mime_type
                ];
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

                        // Save metadata to all languages
                        $current_lang = pll_get_post_language($attachment_id);
                        if ($current_lang == PLL_DEFAULT_LANG) {
                            do_action('save_attachment', $attachment_id);
                        }
                    }
                }

                $image_url = wp_get_attachment_image_url($attachment_id, $ratio_name);
            } else {
                $image_url = 'https://api.tourism-system.com/resize/clip/' . $size['width'] . '/' . $size['height'] . '/70/aHR0cHM6Ly9hcGkudG91cmlzbS1zeXN0ZW0uY29tL3N0YXRpYy9hc3NldHMvaW1hZ2VzL3Jlc2l6ZXIvaW1nXzQwNC5qcGc=/404.jpg';
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
        // Get infos from original image
        $img_path_parts = pathinfo($img_path);

        // get the size of the image
        list($width_orig, $height_orig) = getimagesize($img_path);
        if (!empty($width_orig) && !empty($height_orig)) {
            $ratio_orig = (float)$height_orig / $width_orig;

            // Ratio Free
            if ($size['height'] == 0) {
                $req_width = $width_orig;
                $req_height = $height_orig;

                if ($ratio_orig == 1) {
                    $size['height'] = $size['width'];
                } else {
                    $size['height'] = round($size['width'] * $ratio_orig);
                }
            }

            // Get ratio diff
            $ratio_expect = (float)$size['height'] / $size['width'];
            $ratio_diff = $ratio_orig - $ratio_expect;

            // Calcul du crop size
            if ($ratio_diff > 0) {
                $req_width = $width_orig;
                $req_height = round($width_orig * $ratio_expect);
                $req_x = 0;
                $req_y = round(($height_orig - $req_height) / 2);
            } elseif ($ratio_diff < 0) {
                $req_width = round($height_orig / $ratio_expect);
                $req_height = $height_orig;
                $req_x = round(($width_orig - $req_width) / 2);
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

            // Remove image before recreate
            if (file_exists($cropped_image_path)) {
                //unlink($cropped_image_path);
                $img_cropped_parts = pathinfo($cropped_image_path);
                return $img_cropped_parts['basename'];
            }

            // Crop
            $img_editor = wp_get_image_editor($img_path);
            if (!is_wp_error($img_editor)) {
                $img_editor->crop($req_x, $req_y, $req_width, $req_height, $size['width'], $size['height'], false);
                $img_editor->set_quality(75);
                $img_editor->save($cropped_image_path);

                // Get Image cropped data
                if (file_exists($cropped_image_path)) {
                    $img_cropped_parts = pathinfo($cropped_image_path);
                    return $img_cropped_parts['basename'];
                }
            }
            unset($img_editor);
        }
    }
}
