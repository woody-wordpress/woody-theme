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
            $minmax['min'] = !empty($post_data[str_replace('max', 'min', $data_key)]) ? $post_data[str_replace('max', 'min', $data_key)] : 0;
        } else {
            $minmax['min'] = $post_data[$data_key];
            $minmax['max'] = isset($post_data[str_replace('min', 'max', $data_key)]) ? $post_data[str_replace('min', 'max', $data_key)] : '';
        }

        return $minmax;
    }

    // Permet de récuperer les valueur min et max d'un champ donné parmi tout le site
    public function getMinMaxWoodyFieldValues($query_vars = array(), $field, $minormax = 'max')
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
     * @return   data - Un tableau de données
     *
     */

    public function getFocusBlockTitles($wrapper)
    {
        $data = [];

        $data['title'] = (!empty($wrapper['title'])) ? $wrapper['title'] : '';
        $data['pretitle'] = (!empty($wrapper['pretitle'])) ? $wrapper['pretitle'] : '';
        $data['subtitle'] = (!empty($wrapper['subtitle'])) ? $wrapper['subtitle'] : '';
        $data['icon_type'] = (!empty($wrapper['icon_type'])) ? $wrapper['icon_type'] : '';
        $data['icon_img'] = (!empty($wrapper['icon_img'])) ? $wrapper['icon_img'] : '';
        $data['woody_icon'] = (!empty($wrapper['woody_icon'])) ? $wrapper['woody_icon'] : '';
        $data['description'] = (!empty($wrapper['description'])) ? $wrapper['description'] : '';

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
    public function getFieldAndFallback($item, $field, $fallback_item, $fallback_field = '', $lastfallback_item = '', $lastfallback_field = '')
    {
        $value = null;

        if (!empty($item) && is_object($item)) {
            $value = get_field($field, $item->ID);
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

        $display['gridContainer'] = (empty($wrapper['display_fullwidth'])) ? 'grid-container' : '';
        $display['background_img'] = (!empty($wrapper['background_img'])) ? $wrapper['background_img'] : '';
        $display['parallax'] = (!empty($wrapper['parallax'])) ? $wrapper['parallax'] : '';
        $classes_array[] = (!empty($display['background_img'])) ? 'isRel padd-all-sm' : '';
        $classes_array[] = (!empty($wrapper['background_color'])) ? $wrapper['background_color'] . ' padd-all-sm' : '';
        $classes_array[] = (!empty($wrapper['border_color'])) ? $wrapper['border_color'] : '';
        $classes_array[] = (!empty($wrapper['background_img_opacity'])) ? $wrapper['background_img_opacity'] : '';
        $classes_array[] = (!empty($wrapper['scope_paddings']['scope_padding_top'])) ? $wrapper['scope_paddings']['scope_padding_top'] : '';
        $classes_array[] = (!empty($wrapper['scope_paddings']['scope_padding_bottom'])) ? $wrapper['scope_paddings']['scope_padding_bottom'] : '';
        $classes_array[] = (!empty($wrapper['scope_margins']['scope_margin_top'])) ?  $wrapper['scope_margins']['scope_margin_top'] : '';
        $classes_array[] = (!empty($wrapper['scope_margins']['scope_margin_bottom'])) ? $wrapper['scope_margins']['scope_margin_bottom'] : '';
        $display['section_divider'] = (!empty($wrapper['section_divider'])) ? $wrapper['section_divider'] : '';

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
    public function getAttachmentsByTerms($taxonomy, $terms = array(), $query_args = array())
    {

        // On définit certains arguments par défaut pour la requête
        $default_args = [
            'size' => -1,
            'operator' => 'IN',
            'relation' => 'OR',
            'post_mime_type' => 'image' // Could be image/gif for gif only, video, video/mp4, application, application.pdf, ...
        ];
        $query_args = array_merge($default_args, $query_args);

        // On créé la requête
        $get_attachments = [
            'post_type'      => 'attachment',
            'post_status' => 'inherit',
            'post_mime_type' => $query_args['post_mime_type'],
            'posts_per_page' => $query_args['size'],
            'nopaging' => true,
            'tax_query' => array(
                array(
                    'taxonomy' => $taxonomy,
                    'terms' => $terms,
                    'field' => 'term_id',
                    'relation' => $query_args['relation'],
                    'operator' => $query_args['operator']
                )
            )
        ];

        $attachments = new \WP_Query($get_attachments);
        $acf_attachements = [];
        foreach ($attachments->posts as $key => $attachment) {
            // On transforme chacune des images en objet image ACF pour être compatible avec le tpl Woody
            $acf_attachment = acf_get_attachment($attachment);
            $acf_attachements[] = $acf_attachment;
        }
        return $acf_attachements;
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
                    switch ($effect_key) {
                        case 'transform':
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
                            break;
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
        if (file_exists(get_stylesheet_directory() . '/views/section_banner/section_' . $filename . '.twig')) {
            $file = file_get_contents(get_stylesheet_directory() . '/views/section_banner/section_' . $filename . '.twig');
        } else {
            $file = file_get_contents(get_template_directory() . '/views/section_banner/section_' . $filename . '.twig');
        }
        return $file;
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
    public function replacePattern($str, $post = null)
    {
        $return = '';

        if ($post != null) {
            $pattern = "/%nombre%/";
            preg_match($pattern, $str, $matches);
            if (!empty($matches)) {
                $confId = get_field('playlist_conf_id', $post->ID);
                if (!empty($confId)) {
                    $playlist = apply_filters('woody_hawwwai_playlist_render', $confId, pll_current_language(), array(), 'json');
                    if (!empty($playlist)) {
                        $nbResults = !empty($playlist['playlist']['total']) ? $playlist['playlist']['total'] : false;
                        if (!$nbResults) {
                            $return = $str;
                        } else {
                            foreach ($matches as $match) {
                                $new_str = str_replace(['%nombre%'], $nbResults, $match);
                                $return = preg_replace($pattern, $new_str, $str);
                            }
                        }
                    }
                }
            } else {
                $return = $str;
            }
        } else {
            // Ne concerne pas de playlists
            $pattern = "/(%[a-zA-Z]+%)/";
            preg_match($pattern, $str, $matches);
            if (!empty($matches)) {
                foreach ($matches as $match) {
                    $new_str = str_replace(['%'], '', $match);
                    $return = preg_replace($pattern, $new_str, $str);
                }
            } else {
                $return = $str;
            }
        }

        return $return;
    }

    public function getAttachmentMoreData($attachment_id = null)
    {
        $attachment_data = [];
        if (!empty($attachment_id) && is_int($attachment_id)) {
            $attachment_data['is_instagram'] = isWoodyInstagram($attachment_id);

            if ($attachment_data['is_instagram']) {
                $attachment_data['instagram_metadata'] = $this->getInstagramMetadata($attachment_id);
            }

            $attachment_data['linked_page'] = get_field('field_5c0553157e6d0', $attachment_id);
            $attachment_data['author'] = get_field('field_5b5585503c855', $attachment_id);
            $attachment_data['lat'] = get_field('field_5b55a88e70cbf', $attachment_id);
            $attachment_data['lng'] = get_field('field_5b55a89e70cc0', $attachment_id);
        }

        return $attachment_data;
    }

    private function getInstagramMetadata($attachment_id = null)
    {
        if (!empty($attachment_id) && is_int($attachment_id)) {
            $img_all_data = get_post_meta($attachment_id);
            $img_all_metadata = (!empty($img_all_data['_wp_attachment_metadata'][0])) ? maybe_unserialize($img_all_data['_wp_attachment_metadata'][0]) : '';
            return (!empty($img_all_metadata['woody-instagram'])) ? $img_all_metadata['woody-instagram'] : '';
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
}
