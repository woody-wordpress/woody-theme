<?php

namespace WoodyProcess\Tools;

/**
 * Tools for Woody data processing
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.10.0
 * @author Jeremy Legendre - Benoit Bouchaud
 */

class WoodyTheme_WoodyProcessTools
{
    /**
     *
     * Nom : getMinMax
     * Auteur : Benoit Bouchaud - Jeremy Legendre
     * Return : Retourne les valeurs min/max d'un groupe de champ donné (_min + _max) pour un post donné
     * @param    post_data - La donnée du layout acf sous forme de tableau
     * @param    context - Le contexte global de la page sous forme de tableau
     * @return   return - Code HTML
     *
     */

    // Permet de mettre à jour les filtres en fonctions des paramètres GET
    public function getMinMax($post_data, $data_key)
    {
        $minmax = [
            'min' => 0,
            'max' => ''
        ];
        if (strpos($data_key, 'max')) {
            $minmax['max'] = $post_data[$data_key];
            $minmax['min'] = empty($post_data[str_replace('max', 'min', $data_key)]) ? 0 : $post_data[str_replace('max', 'min', $data_key)];
        } else {
            $minmax['min'] = $post_data[$data_key];
            $minmax['max'] = $post_data[str_replace('min', 'max', $data_key)] ?? '';
        }

        return $minmax;
    }

    // Permet de récuperer les valueur min et max d'un champ donné parmi tout le site
    public function getMinMaxWoodyFieldValues($query_vars = [], $field, $minormax = 'max')
    {
        $return = 0;

        if (empty($query_vars) || empty($field)) {
            return;
        }

        $query_vars['meta_key'] = $field;
        $query_vars['posts_per_page'] = 1;
        $query_vars['paged'] = false;
        $query_vars['orderby'] = 'meta_value_num';
        $query_vars['order'] = ($minormax == 'max') ? 'DESC' : 'ASC';

        $query_result = new \WP_Query($query_vars);

        if (!empty($query_result->posts)) {
            $return = get_field($field, $query_result->posts[0]->ID);
            $return = (empty($return)) ? 1 : $return;
        }

        return $return;
    }

    /**
     *
     * Nom : getFocusBlockTitles
     * Auteur : Benoit Bouchaud
     * Return : Retourne les données d'es champs titre du bloc
     * @param    layout - data du layout focus en tableau
     * @param    name - prefix for buttons context
     * @return   data - Un tableau de données
     *
     */
    public function getBlockTitles($wrapper, $prefix = '', $name = 'focus_', $opts = [])
    {
        $data = [];
        $opts['hide_description'] = empty($opts['hide_description']) ? false : $opts['hide_description'];

        $data['title'] = (empty($wrapper[$prefix . 'title'])) ? '' : $wrapper[$prefix . 'title'];
        $data['pretitle'] = (empty($wrapper[$prefix . 'pretitle'])) ? '' : $wrapper[$prefix . 'pretitle'];
        $data['subtitle'] = (empty($wrapper[$prefix . 'subtitle'])) ? '' : $wrapper[$prefix . 'subtitle'];
        $data['icon_type'] = (empty($wrapper[$prefix . 'icon_type'])) ? '' : $wrapper[$prefix . 'icon_type'];
        $data['icon_img'] = (empty($wrapper[$prefix . 'icon_img'])) ? '' : $wrapper[$prefix . 'icon_img'];
        $data['woody_icon'] = (empty($wrapper[$prefix . 'woody_icon'])) ? '' : $wrapper[$prefix . 'woody_icon'];
        if ($opts['hide_description'] !== true) {
            $data['description'] = (empty($wrapper[$prefix . 'description'])) ? '' : $wrapper[$prefix . 'description'];
        }

        $data['fullwidth'] = (empty($wrapper[$name.'block_title_fullwidth'])) ? false : 'fullwidth';
        if (!empty($wrapper[$name.'buttons']) && !empty($wrapper[$name.'buttons']['links'])) {
            $data[$name.'buttons'] = $wrapper[$name.'buttons'];
        }

        return $data;
    }

    /**
     *
     * Nom : getFieldAndFallback
     * Auteur : Benoit Bouchaud
     * Return : Retourne un tableau de classes de personnalisation d'affichage
     * @param    item - Le scope (un objet post)
     * @param    field - Le champ prioritaire
     * @param    fallback_item - Le champ de remplacement
     * @return   data - Un tableau de données
     *
     **/
    public function getFieldAndFallback($item, $field, $fallback_item, $fallback_field = '', $lastfallback_item = '', $lastfallback_field = '', $item_type = '')
    {
        $value = null;

        if (!empty($item) && is_object($item)) {
            $value = get_field($field, $item->ID);
        }

        if (empty($value) && $item_type == 'mirror_page') {
            $value = get_field($field, $lastfallback_item);
        }

        if (empty($value) && !empty($fallback_item) && !empty($fallback_field)) {
            if (is_array($fallback_item) && !empty($fallback_item[$fallback_field])) {
                $value = $fallback_item[$fallback_field];
            } elseif (is_object($fallback_item) && !empty($fallback_item->ID)) {
                $value = get_field($fallback_field, $fallback_item->ID);
            }
        }

        if (empty($value) && !empty($lastfallback_item) && !empty($lastfallback_field)) {
            if (is_array($lastfallback_item) && !empty($lastfallback_item[$lastfallback_field])) {
                $value = $lastfallback_item[$lastfallback_field];
            } elseif (is_object($lastfallback_item) && !empty($lastfallback_item->ID)) {
                $value = get_field($lastfallback_field, $lastfallback_item->ID);
            }
        }

        return $value;
    }

    /**
     *
     * Nom : getDisplayOptions
     * Auteur : Benoit Bouchaud
     * Return : Retourne un tableau de classes de personnalisation d'affichage
     * @param    scope - Le tableau contenant les infos d'affichage
     * @return   display - Un tableau de données
     *
     */

    public function getDisplayOptions($wrapper)
    {
        $display = [];
        $classes_array = [];
        $container_classes='';

        if (empty($wrapper['display_fullwidth'])) {
            if (!empty($wrapper['section_container_size'])) {
                $container_classes = 'grid-container ' . $wrapper['section_container_size'];
            } else {
                $container_classes = 'grid-container';
            }
        }

        $display['gridContainer'] = $container_classes;
        $display['display_fullwidth'] = empty($wrapper['display_fullwidth']) ? '' : $wrapper['display_fullwidth'];
        $display['background_img'] = (empty($wrapper['background_img'])) ? '' : $wrapper['background_img'];
        $display['parallax'] = (empty($wrapper['parallax'])) ? '' : $wrapper['parallax'];
        $classes_array[] = (empty($display['background_img'])) ? '' : 'isRel';
        $classes_array[] = (empty($wrapper['background_color'])) ? '' : $wrapper['background_color'];
        $classes_array[] = (empty($wrapper['background_color_opacity'])) ? '' : $wrapper['background_color_opacity'];
        $classes_array[] = (empty($wrapper['border_color'])) ? '' : $wrapper['border_color'];
        $classes_array[] = (empty($wrapper['background_img_opacity'])) ? '' : $wrapper['background_img_opacity'];
        $spacing_array[] = (empty($wrapper['scope_paddings']['scope_padding_top'])) ? '' : $wrapper['scope_paddings']['scope_padding_top'];
        $spacing_array[] = (empty($wrapper['scope_paddings']['scope_padding_bottom'])) ? '' : $wrapper['scope_paddings']['scope_padding_bottom'];
        $spacing_array[] = (empty($wrapper['scope_margins']['scope_margin_top'])) ? '' : $wrapper['scope_margins']['scope_margin_top'];
        $spacing_array[] = (empty($wrapper['scope_margins']['scope_margin_bottom'])) ? '' : $wrapper['scope_margins']['scope_margin_bottom'];
        $display['spacing_classes'] = trim(implode(' ', $spacing_array));
        $display['mobile_spacing_classes'] = trim(implode(' ', $spacing_array));
        $display['section_divider'] = (empty($wrapper['section_divider'])) ? '' : $wrapper['section_divider'];
        $display['heading_alignment'] = (empty($wrapper['heading_alignment'])) ? 'center' : $wrapper['heading_alignment'];
        $display['section_animations'] = (empty($wrapper['section_animations'])) ? '' : $wrapper['section_animations'];

        if(!empty($wrapper['custom_resp_button'])) {
            if(wp_is_mobile()) {
                $mobile_spacing_array[] = (empty($wrapper['mobile_scope_paddings']['scope_padding_top'])) ? '' : $wrapper['mobile_scope_paddings']['scope_padding_top'];
                $mobile_spacing_array[] = (empty($wrapper['mobile_scope_paddings']['scope_padding_bottom'])) ? '' : $wrapper['mobile_scope_paddings']['scope_padding_bottom'];
                $mobile_spacing_array[] = (empty($wrapper['mobile_scope_margins']['scope_margin_top'])) ? '' : $wrapper['mobile_scope_margins']['scope_margin_top'];
                $mobile_spacing_array[] = (empty($wrapper['mobile_scope_margins']['scope_margin_bottom'])) ? '' : $wrapper['mobile_scope_margins']['scope_margin_bottom'];
                $display['mobile_spacing_classes'] = trim(implode(' ', $mobile_spacing_array));
            }
        }

        // On transforme le tableau en une chaine de caractères
        $display['classes'] = trim(implode(' ', $classes_array));

        return $display;
    }


    /**
     *
     * Nom : getAttchmentsByTerms
     * Auteur : Benoit Bouchaud
     * Return : Retourne un tableau d'objets image au format acf_image
     * @param    taxonomy - Le slug du vocabulaire dans lequel on recherche
     * @param    terms - Les termes ciblés dans le vocabulaire
     * @param    query_args - Un tableau d'arguments pour la wp_query
     * @return   attachements - Un tableau d'objets images au format "ACF"
     *
     */
    public function getAttachmentsByTerms($taxonomy, $terms = [], $query_args = [])
    {
        // On créé la requête
        $default_args = [
            'order' => 'DESC',
            'orderby' => 'date',
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'post_mime_type' => 'image',
            'posts_per_page' => 14,
            'tax_query' => array(
                array(
                    'taxonomy' => $taxonomy,
                    'terms' => $terms,
                    'field' => 'term_id',
                    'relation' => 'OR',
                    'operator' => 'IN'
                )
            )
        ];

        $query_args = array_merge($default_args, $query_args);
        $attachments = new \WP_Query($query_args);

        $acf_attachements = [];
        foreach ($attachments->posts as $attachment) {
            // On transforme chacune des images en objet image ACF pour être compatible avec le tpl Woody
            $acf_attachment = acf_get_attachment($attachment);
            $acf_attachements[] = $acf_attachment;
        }

        return $acf_attachements;
    }

    public function getAttachmentsByMultipleTerms($term_ids, $andor = 'OR', $limit = 9)
    {
        $return = [];

        $tax_query = [];
        $custom_tax = [];
        foreach ($term_ids as $term_id) {
            $term = get_term($term_id);
            if (!empty($term) && !is_wp_error($term) && is_object($term)) {
                $custom_tax[$term->taxonomy][] = $term_id;
            }
        }

        foreach ($custom_tax as $taxo => $terms) {
            foreach ($terms as $term) {
                $tax_query[] = array(
                    'taxonomy' => $taxo,
                    'terms' => [$term],
                    'field' => 'term_id',
                    'operator' => 'IN'
                );
            }
        }

        $query_results = new \WP_Query(array(
            'posts_per_page'    => $limit,
            'post_type'         => 'attachment',
            'post_status'       => 'inherit',
            'tax_query'         => array(
                'relation' =>  $andor,
                $tax_query
            )
        ));

        foreach ($query_results->posts as $post) {
            $return[] = acf_get_attachment($post);
        }

        return $return;
    }

    /**
     *
     * Nom : formatVisualEffectData
     * Auteur : Benoit Bouchaud
     * Return : Retourne un tableau de transformations sous forme de strings
     * @param    effects - un tableau
     * @return   return - Un tableau
     *
     */
    public function formatVisualEffectData($effects)
    {
        $return = [];
        foreach ($effects as $effect_key => $effect) {
            if (!empty($effect)) {
                if (is_array($effect)) {
                    if ($effect_key === 'transform') {
                        foreach ($effect as $transform) {
                            switch ($transform['transform_type']) {
                                case 'trnslt-top':
                                case 'trnslt-bottom':
                                case 'trnslt-left':
                                case 'trnslt-right':
                                    $return['transform'][] = $transform['transform_type'] . '-' . $transform['transform_trnslt_value'];
                                    break;
                                case 'rotate-left':
                                case 'rotate-right':
                                    $return['transform'][] = $transform['transform_type'] . '-' . $transform['transform_rotate_value'];
                                    break;
                            }
                        }
                    }
                } elseif ($effect_key == 'deep') {
                    $return['deep'] = 'deep-'.$effect;
                }
            }
        }

        if (!empty($return['transform'])) {
            $return['transform'] = implode('_', $return['transform']);
        }

        return $return;
    }

    /**
     *
     * Nom : getSectionBannerFiles
     * Auteur : Benoit Bouchaud
     * Return : Retourne l'url d'un fichier
     * @param    filename - nom du fichier
     * @return   file - Une url
     *
     */
    public function getSectionBannerFiles($filename)
    {
        if(is_string($filename)) {

            $lang = pll_current_language();
            $banner_paths = [
                get_stylesheet_directory() . '/views/section_banner/'. $lang .'/section_' . $filename . '.twig',
                get_stylesheet_directory() . '/views/section_banner/section_' . $filename . '.twig',
                get_template_directory() . '/views/section_banner/section_' . $filename . '.twig',
            ];

            foreach ($banner_paths as $path) {
                if (file_exists($path)) {
                    return file_get_contents($path);
                }
            }
        }
    }

    /**
     *
     * Nom : replacePattern
     * Auteur : Jérémy Legendre
     * Return : Retourne la string avec le pattern modifié (devenu le count de la playlist)
     * @param    post - Le scope (un objet post)
     * @param    str  - La phrase (titre, surtitre, sous-titre, description)
     * @return   return - La phrase modifiée
     *
     **/
    public function replacePattern($string, $post_id)
    {

        // Si la page n'est pas une playlist
        $page_type = getTermsSlugs($post_id, 'page_type', true);
        if ($page_type !== 'playlist_tourism') {
            return $string;
        }

        if (!empty($post_id)) {
            $patterns = ['%nombre%', '%playlist_count%'];

            foreach ($patterns as $pattern) {
                if (strpos($string, (string) $pattern) !== false) {
                    $confId = get_field('playlist_conf_id', $post_id);
                    if (!empty($confId)) {
                        $playlist_count = apply_filters('woody_hawwwai_playlist_count', $confId, pll_current_language(), []);
                    }

                    $string = str_replace($pattern, $playlist_count, $string);
                }
            }
        }

        return $string;
    }

    public function getAttachmentMoreData($attachment_id = null)
    {
        $attachment_data = [];
        if (!empty($attachment_id) && is_int($attachment_id)) {
            $attachment_data['is_instagram'] = isWoodyInstagram($attachment_id);

            if ($attachment_data['is_instagram']) {
                $attachment_data['instagram_metadata'] = $this->getInstagramMetadata($attachment_id);
            }

            $author = get_field('field_5b5585503c855', $attachment_id);
            $lat = get_field('field_5b55a88e70cbf', $attachment_id);
            $lng = get_field('field_5b55a89e70cc0', $attachment_id);
            $linked_page = get_field('field_5c0553157e6d0', $attachment_id);
            $linked_video = get_field('field_619f73e346813', $attachment_id);

            $attachment_data['author'] = (!empty($author) && is_string($author)) ? strip_tags($author) : '';
            $attachment_data['lat'] = (empty($lat)) ? '' : $lat;
            $attachment_data['lng'] = (empty($lng)) ? '' : $lng;
            $attachment_data['linked_page'] = (empty($linked_page)) ? [] : $linked_page;
            $attachment_data['linked_video'] = (empty($linked_video)) ? [] : $linked_video;
        }

        return $attachment_data;
    }

    private function getInstagramMetadata($attachment_id = null)
    {
        if (!empty($attachment_id) && is_int($attachment_id)) {
            $img_all_data = get_post_meta($attachment_id);
            $img_all_metadata = (empty($img_all_data['_wp_attachment_metadata'][0])) ? '' : maybe_unserialize($img_all_data['_wp_attachment_metadata'][0]);
            return (empty($img_all_metadata['woody-instagram'])) ? '' : $img_all_metadata['woody-instagram'];
        }
    }

    public function countFocusResults($items, $return)
    {
        if (!empty($items['items']) && !empty($items['wp_query']->found_posts)) {
            $return['items_count'] = $items['wp_query']->found_posts;
            $return['items_count_type'] = $return['items_count'] > 1 ? 'plural' : 'singular';
        } else {
            $return['items_count_type'] = 'empty';
        }

        return $return;
    }

    /**
     *
     * Nom : getDeviceDisplayBlockResponsive
     * Auteur : Orphée Besson
     * Return : Retourne le device sélectionné pour l'affichage d'un bloc dans l'onglet "Responsive"
     * @param    layout - array
     * @return   device - string => 'desktop' || 'mobile'
     *
     */
    public function getDeviceDisplayBlockResponsive($layout)
    {
        $device = '';

        if (!empty($layout['display_block_responsive']) && $layout['display_block_responsive'] != 'all') {
            $device = $layout['display_block_responsive'];
        }

        return $device;
    }

    /**
     * Retrieve map params from acf context.
     * Load global map params from options and override them if necessary.
     *
     * @author Sébastien Chandonay
     * @param array $context acf context
     * @return array map params
     */
    public static function getMapParams($context = []) {

        // map_zoom_auto_max : Useful when automatic zooming (fitBounds) is enabled.
        // TmapsV2 library respects the initial map zoom has max level during automatic zooming when there is only one marker.

        // globals params with defaults
        $map_zoom_auto = get_field('map_zoom_auto', 'option');
        $map_zoom_auto = $map_zoom_auto !== true && $map_zoom_auto !== false ? true : $map_zoom_auto;

        $map_zoom = get_field('map_zoom', 'option');
        $map_zoom = empty($map_zoom) ? 15 : $map_zoom;

        $map_zoom_auto_max = get_field('map_zoom_auto_max', 'option');
        $map_zoom_auto_max = empty($map_zoom_auto_max) ? 15 : $map_zoom_auto_max;

        $map_provider = get_field('map_provider', 'option');
        $map_provider = empty($map_provider) ? 'tm' : $map_provider;

        $map_params = [
            'map_zoom_auto' => $map_zoom_auto,
            'map_zoom' => $map_zoom_auto === false ? $map_zoom : $map_zoom_auto_max,
            'map_provider' => $map_provider
        ];

        // specific params
        if (isset($context['map_params_enabled']) && $context['map_params_enabled'] == true) {
            // map zoom
            $map_zoom_auto = isset($context['map_params']['map_zoom_auto']) ? $context['map_params']['map_zoom_auto'] : false;
            $map_zoom = isset($context['map_params']['map_zoom']) ? $context['map_params']['map_zoom'] : null;
            $map_zoom_auto_max = isset($context['map_params']['map_zoom_auto_max']) ? $context['map_params']['map_zoom_auto_max'] : null;
            $map_params['map_zoom_auto'] = $map_zoom_auto;
            $map_params['map_zoom'] = $map_zoom_auto === false ? $map_zoom : $map_zoom_auto_max;

            // map height
            if (isset($context['map_params']['map_height']) && !empty($context['map_params']['map_height'])) {
                $map_params['map_height'] = $context['map_params']['map_height'];
            }

            // map provider
            if (isset($context['map_params']['map_provider']) && !empty($context['map_params']['map_provider'])) {
                $map_params['map_provider'] = $context['map_params']['map_provider'];
            }
        }

        return $map_params;
    }
}
