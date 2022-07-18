<?php

/**
 * Images
 *
 * @link https://www.advancedcustomfields.com/resources/acf-settings
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */
use Symfony\Component\Finder\Finder;

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
        add_action('edit_attachment', [$this, 'applyMediaTerms'], 50);

        // Lors de la suppression d'une langue on doit supprimer tous ses médias pour éviter qu'ils ne passent dans la langue par défaut
        // Pour cela on passe par une commande CLI et on ne veut surtout pas supprimer les traductions des médias supprimés
        // On ne supprime pas les traductions d'une image si la suppression se fait en CLI
        if (!defined('WP_CLI')) {
            add_action('delete_attachment', [$this, 'deleteAttachment'], 1);
        }

        add_action('wp_ajax_get_all_tags', [$this, 'getAllTags']);
        add_action('wp_ajax_set_attachments_terms', [$this, 'setAttachmentsTerms']);
        add_action('woody_theme_update', [$this, 'woodyInsertTerms']);

        // Enable Media Replace Plugin
        add_action('enable-media-replace-upload-done', [$this, 'mediaReplaced'], 10, 3);

        // Filters
        add_filter('timber_render', [$this, 'timberRender'], 1);
        add_filter('wp_image_editors', [$this, 'wpImageEditors']);
        add_filter('intermediate_image_sizes_advanced', [$this, 'removeAutoThumbs'], 10, 2);
        add_filter('image_size_names_choose', [$this, 'imageSizeNamesChoose'], 10, 1);
        add_filter('wp_read_image_metadata', [$this, 'readImageMetadata'], 10, 4);
        add_filter('wp_generate_attachment_metadata', [$this, 'generateAttachmentMetadata'], 10, 2);
        add_filter('wp_handle_upload_prefilter', [$this, 'maxUploadSize']);
        add_filter('upload_mimes', [$this, 'uploadMimes'], 10, 1);
        add_filter('big_image_size_threshold', [$this, 'bigImageSizeThreshold'], 10, 4);
        add_filter('wp_handle_upload_overrides', [$this, 'handleOverridesForGeoJSON'], 10, 2);
    }

    public function mediaReplaced($target_url, $source_url, $post_id)
    {
        if (wp_attachment_is_image($post_id)) {
            $attachment_metadata = maybe_unserialize(wp_get_attachment_metadata($post_id));
            if (!empty($attachment_metadata['file'])) {
                $posts[] = [
                    'id' => $post_id,
                    'title' => get_the_title($post_id),
                    'lang' => pll_get_post_language($post_id),
                    'file' => $attachment_metadata['file'],
                    'metadata' => $attachment_metadata
                ];
            }

            print_r($posts);
            die;
        }
    }

    public function bigImageSizeThreshold()
    {
        // Désactive la duplication  de photo (filename-scaled.jpg) depuis WP 5.3
        return false;
    }

    public function wpImageEditors()
    {
        return ['WP_Image_Editor_GD'];
    }

    public function uploadMimes($mime_types)
    {
        $mime_types['gpx'] = 'text/xml';
        $mime_types['kml'] = 'text/xml';
        $mime_types['kmz'] = 'text/xml';
        $mime_types['xliff'] = 'text/xml';
        $mime_types['json'] = 'text/plain';
        $mime_types['geojson'] = 'text/plain';

        return $mime_types;
    }

    public function handleOverridesForGeoJSON($overrides, $file)
    {
        if ($file['type'] == "application/geo+json") {
            $overrides['test_type'] = false;
        }

        return $overrides;
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
     * Get all tags to create form
     */
    public function getAllTags()
    {
        $tags = [];
        $taxonomies = ['themes', 'places', 'seasons'];

        foreach ($taxonomies as $taxonomy) {
            $terms = get_terms(array(
                'taxonomy' => $taxonomy,
                'hide_empty' => false
            ));
            foreach ($terms as $term) {
                if (!is_wp_error($term)) {
                    $tags[$taxonomy][] = [
                        'id' => $term->term_id,
                        'name' => $term->name
                    ];
                }
            }
        }

        wp_send_json($tags);
    }

    /**
     * Add terms to attachment post
     */
    public function setAttachmentsTerms()
    {
        $attach_ids = filter_input(INPUT_POST, 'attach_ids', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $term_ids = filter_input(INPUT_POST, 'term_ids', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

        if (!empty($attach_ids) && !empty($term_ids)) {
            foreach ($attach_ids as $attach_id) {
                foreach ($term_ids as $term_id) {
                    $term = get_term($term_id);

                    if (!is_wp_error($term)) {
                        wp_set_post_terms($attach_id, $term->term_id, $term->taxonomy, true);
                    }
                }
            }
            wp_send_json(true);
        } else {
            wp_send_json(false);
        }
    }

    public function timberRender($render)
    {
        return preg_replace('/http(s?):\/\/([a-zA-Z0-9-_.]*)\/app\/uploads\/([^\/]*)\/([0-9]*)\/([0-9]*)\/..\/..\/..\/..\/..\/wp-json\/woody\/crop\/([0-9]*)\/ratio_([a-z0-9-_]*)/', 'http$1://$2/wp-json/woody/crop/$6/ratio_$7', $render);
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
            'ratio_free' => __('Proportions libres'),
            'medium' => __('Moyenne')
        );
    }

    /* ------------------------ */
    /* Read EXIF/IPTC Metadatas */
    /* ------------------------ */

    public function readImageMetadata($meta, $file, $sourceImageType, $iptc)
    {
        // XMP
        $content        = file_get_contents($file);
        $xmp_data_start = strpos($content, '<x:xmpmeta');
        if ($xmp_data_start !== false) {
            $xmp_data_end   = strpos($content, '</x:xmpmeta>');
            $xmp_length     = $xmp_data_end - $xmp_data_start;
            $xmp_data       = substr($content, $xmp_data_start, $xmp_length + 12);
            $xmp_arr        = $this->getXMPArray($xmp_data);

            $meta['title']          = !empty($xmp_arr['Title']) ? $xmp_arr['Title'][0] : '';
            $meta['city']           = !empty($xmp_arr['City']) ? $xmp_arr['City'][0] : '';
            $meta['credit']         = !empty($xmp_arr['Creator']) ? $xmp_arr['Creator'][0] : '';
            $meta['copyright']      = !empty($xmp_arr['Rights']) ? $xmp_arr['Rights'][0] : '';
            $meta['description']    = !empty($xmp_arr['Description']) ? $xmp_arr['Description'][0] : '';
            $meta['caption']        = $meta['description'];
            $meta['country']        = !empty($xmp_arr['Country']) ? $xmp_arr['Country'][0] : '';
            $meta['state']          = !empty($xmp_arr['State']) ? $xmp_arr['State'][0] : '';
            $meta['keywords']       = !empty($xmp_arr['Keywords']) ? $xmp_arr['Keywords'][0] : '';
        }

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

            // Titre
            if ((empty($meta['title']) || $meta['title'] == $meta['caption']) && !empty($iptc['2#085'])) {
                $meta['title'] = ucfirst(strtolower(current($iptc['2#085'])));
            }

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

        if (empty($meta['credit']) && !empty($meta['copyright'])) {
            $meta['credit'] = $meta['copyright'];
        } elseif (empty($meta['copyright']) && !empty($meta['credit'])) {
            $meta['copyright'] = $meta['credit'];
        }

        return $meta;
    }

    private function getXMPArray($xmp_data)
    {
        $xmp_arr = array();
        foreach (array(
                'Creator Email' => '<Iptc4xmpCore:CreatorContactInfo[^>]+?CiEmailWork="([^"]*)"',
                'Owner Name'    => '<rdf:Description[^>]+?aux:OwnerName="([^"]*)"',
                'Creation Date' => '<rdf:Description[^>]+?xmp:CreateDate="([^"]*)"',
                'Modification Date'     => '<rdf:Description[^>]+?xmp:ModifyDate="([^"]*)"',
                'Label'         => '<rdf:Description[^>]+?xmp:Label="([^"]*)"',
                'Credit'        => '<rdf:Description[^>]+?photoshop:Credit="([^"]*)"',
                'Source'        => '<rdf:Description[^>]+?photoshop:Source="([^"]*)"',
                'Headline'      => '<rdf:Description[^>]+?photoshop:Headline="([^"]*)"',
                'City'          => '<rdf:Description[^>]+?photoshop:City="([^"]*)"',
                'State'         => '<rdf:Description[^>]+?photoshop:State="([^"]*)"',
                'Country'       => '<rdf:Description[^>]+?photoshop:Country="([^"]*)"',
                'Country Code'  => '<rdf:Description[^>]+?Iptc4xmpCore:CountryCode="([^"]*)"',
                'Location'      => '<rdf:Description[^>]+?Iptc4xmpCore:Location="([^"]*)"',
                'Title'         => '<dc:title>\s*<rdf:Alt>\s*(.*?)\s*<\/rdf:Alt>\s*<\/dc:title>',
                'Rights'         => '<dc:rights>\s*<rdf:Alt>\s*(.*?)\s*<\/rdf:Alt>\s*<\/dc:rights>',
                'Description'   => '<dc:description>\s*<rdf:Alt>\s*(.*?)\s*<\/rdf:Alt>\s*<\/dc:description>',
                'Creator'       => '<dc:creator>\s*<rdf:Seq>\s*(.*?)\s*<\/rdf:Seq>\s*<\/dc:creator>',
                'Keywords'      => '<dc:subject>\s*<rdf:Bag>\s*(.*?)\s*<\/rdf:Bag>\s*<\/dc:subject>',
                'Hierarchical Keywords' => '<lr:hierarchicalSubject>\s*<rdf:Bag>\s*(.*?)\s*<\/rdf:Bag>\s*<\/lr:hierarchicalSubject>'
        ) as $key => $regex) {

            // get a single text string
            $xmp_arr[$key] = preg_match("/$regex/is", $xmp_data, $match) ? $match[1] : '';

            // if string contains a list, then re-assign the variable as an array with the list elements
            $xmp_arr[$key] = preg_match_all("/<rdf:li[^>]*>([^>]*)<\/rdf:li>/is", $xmp_arr[$key], $match) ? $match[1] : $xmp_arr[$key];

            // hierarchical keywords need to be split into a third dimension
            if (! empty($xmp_arr[$key]) && $key == 'Hierarchical Keywords') {
                foreach ($xmp_arr[$key] as $li => $val) {
                    $xmp_arr[$key][$li] = explode('|', $val);
                }
                unset($li, $val);
            }
        }

        return $xmp_arr;
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
        // Added attachment_types
        wp_set_object_terms($attachment_id, 'Média ajouté manuellement', 'attachment_types', false);

        // Duplicate all medias
        $this->saveAttachment($attachment_id);
    }

    public function deleteAttachment($attachment_id)
    {
        remove_action('delete_attachment', [$this, 'deleteAttachment']);

        $deleted_attachement = wp_cache_get('woody_deleted_attachement', 'woody');
        if (empty($deleted_attachement)) {
            $deleted_attachement = [];
        }

        if (wp_attachment_is_image($attachment_id) && is_array($deleted_attachement) && !in_array($attachment_id, $deleted_attachement)) {
            // Remove translations
            $translations = pll_get_post_translations($attachment_id);
            $deleted_attachement = array_merge($deleted_attachement, array_values($translations));
            wp_cache_set('woody_deleted_attachement', $deleted_attachement, 'woody');

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
            $translations = pll_get_post_translations($attachment_id);
            $source_lang = pll_get_post_language($attachment_id);

            $languages = pll_languages_list();
            foreach ($languages as $target_lang) {

                // Duplicate media with Polylang Method
                if (!array_key_exists($target_lang, $translations)) {
                    $t_attachment_id = woody_pll_create_media_translation($attachment_id, $source_lang, $target_lang);
                } else {
                    $t_attachment_id = $translations[$target_lang];
                }

                // Sync Meta and fields
                if (!empty($t_attachment_id) && $source_lang != $target_lang) {
                    $this->syncAttachmentMetadata($attachment_id, $t_attachment_id, $target_lang);
                }
            }
        }
    }

    private function syncAttachmentMetadata($attachment_id, $t_attachment_id, $target_lang)
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

            // Si on lance une traduction en masse de la médiathèque, il faut lancer ce hook qui va synchroniser les taxonomies themes et places
            if (defined('WP_CLI') && \WP_CLI) {
                do_action('pll_translate_media', $attachment_id, $t_attachment_id, $target_lang);
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
            if (empty($metadata['sizes'])) {

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
                if (!empty($metadata['image_meta']['city']) || !empty($metadata['image_meta']['state']) || !empty($metadata['image_meta']['country']) || !empty($metadata['image_meta']['keywords'])) {
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
                    if (!empty($terms_attachment_categories)) {
                        foreach ($terms_attachment_categories as $term_attachment_categories) {
                            foreach ($metadata['image_meta']['keywords'] as $keyword) {
                                if (sanitize_title($keyword) == $term_attachment_categories->slug) {
                                    wp_set_object_terms($attachment_id, $term_attachment_categories->slug, 'attachment_categories', true);
                                }
                            }
                        }
                    }

                    $terms_themes = get_terms('themes', ['hide_empty' => false]);
                    if (!empty($terms_themes)) {
                        foreach ($terms_themes as $term_themes) {
                            foreach ($metadata['image_meta']['keywords'] as $keyword) {
                                if (sanitize_title($keyword) == $term_themes->slug) {
                                    wp_set_object_terms($attachment_id, $term_themes->slug, 'themes', true);
                                }
                            }
                        }
                    }

                    $terms_seasons = get_terms('seasons', ['hide_empty' => false]);
                    if (!empty($terms_seasons)) {
                        foreach ($terms_seasons as $term_seasons) {
                            foreach ($metadata['image_meta']['keywords'] as $keyword) {
                                if (sanitize_title($keyword) == $term_seasons->slug) {
                                    wp_set_object_terms($attachment_id, $term_seasons->slug, 'seasons', true);
                                }
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

    public function woodyInsertTerms()
    {
        wp_insert_term('Vidéo externe', 'attachment_types', array('slug' => 'media_linked_video'));
    }

    public function applyMediaTerms($attachment_id)
    {
        $mediaLinkedVideo = get_field('media_linked_video', $attachment_id);
        $terms = get_the_terms($attachment_id, 'attachment_types');
        $terms = (empty($terms)) ? [] : $terms;
        $newTerms = [];

        if (!empty($mediaLinkedVideo)) {
            foreach ($terms as $term) {
                if ($term->slug != 'media_linked_video') {
                    array_push($newTerms, $term->name);
                }
            }
            array_push($newTerms, 'Vidéo externe');
            wp_set_object_terms($attachment_id, $newTerms, 'attachment_types', false);
        } else {
            foreach ($terms as $key => $term) {
                if ($term->slug === 'media_linked_video') {
                    unset($terms[$key]);
                } else {
                    array_push($newTerms, $term->name);
                }
            }
            wp_set_object_terms($attachment_id, $newTerms, 'attachment_types', false);
        }
    }
}
