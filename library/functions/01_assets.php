<?php

use Symfony\Component\Finder\Finder;
use WoodyLibrary\Library\WoodyLibrary\WoodyLibrary;

/**
 *
 * Nom : getAcfGroupFields
 * Auteur : Benoit Bouchaud
 * Return : Retourne un tableau avec les valeurs des champs d'un groupe ACF poyr un post donné
 * @param    group_id - La clé du groupe ACF
 * @return   page_teaser_fields - Un tableau de données
 *
 */
function getAcfGroupFields($group_id, $post = null, $display_empty = false)
{
    if (is_null($post)) {
        $post = get_post();
    }

    if (!empty($post)) {
        $post_id = $post->ID;
        $the_fields = [];
        $fields = acf_get_fields($group_id);

        if (!empty($fields)) {
            foreach ($fields as $field) {
                $field_value = false;
                if (!empty($field['name'])) {
                    $field_value = get_field($field['name'], $post_id);
                }

                if ($display_empty) {
                    $the_fields[$field['name']] = $field_value;
                } elseif ($field_value && !empty($field_value)) {
                    $the_fields[$field['name']] = $field_value;
                }
            }
        }

        return $the_fields;
    }
}

/**
 *
 * Nom : getTermsSlugs
 * Auteur : Benoit Bouchaud
 * Return : Retourne un tableau de termes
 * @param    postId - Le post dans lequel on recherche
 * @param    taxonomy - Le slug du vocabulaire dans lequel on recherche
 * @param    implode - Booleén => retourne une chaine de caractères si true
 * @return   slugs - Un tableau de slugs de termes
 *
 */
function getTermsSlugs($postId, $taxonomy, $implode = false)
{
    $return = [];

    $terms = get_the_terms($postId, $taxonomy);
    if (!empty($terms)) {
        foreach ($terms as $term) {
            $return[] = $term->slug;
        }

        if ($implode == true) {
            $return = implode(' ', $return);
        }
    }


    return $return;
}

/**
 *
 * Nom : humanDays
 * Auteur : Benoit Bouchaud
 * Return : Retourne une chaine de caractères (jours) en fonction d'un nombre donné
 * @param    number - int
 * @return   human_string - Un chaine de caractères
 *
 */

function humanDays($number)
{
    $return = '';

    $week = __('semaine', 'woody-theme');
    $weeks = __('semaines', 'woody-theme');
    $day = __('jour', 'woody-theme');
    $days = __('jours', 'woody-theme');

    if ($number % 7 === 0) {
        $week_number = $number / 7;
        if ($week_number > 1) {
            $return = $week_number . ' ' . $weeks;
        } else {
            $return = $week_number . ' ' . $week;
        }
    } else {
        if ($number > 1) {
            $return = $number . ' ' . $days;
        } else {
            $return = $number . ' ' . $day;
        }
    }

    return $return;
}

/**
 *
 * Nom : getWoodyIcons
 * Auteur : Benoit Bouchaud
 * Return : Un tableau
 * @return   the_icons - La liste de tous les icones du site
 *
 */
function getWoodyIcons()
{
    $return = [];

    //TODO: Récupérer une variable globale en fonction du set d'icones choisis dans le thème pour remplacer '/src/icons/icons_set_01'
    $core_icons = woodyIconsFolder(get_template_directory() . '/src/icons/icons_set_01');

    $stations = ['superot'];
    $stations = apply_filters('woody_icons_stations', $stations);

    $station_icons = [];

    if ((in_array(WP_SITE_KEY, $stations))) {
        $station_icons = woodyIconsFolder(get_template_directory() . '/src/icons/icons_set_stations');
    }

    $site_icons = woodyIconsFolder(get_stylesheet_directory() . '/src/icons');

    $return = array_merge($core_icons, $site_icons, $station_icons);

    apply_filters('woody_add_more_icons', $return);

    return $return;
}

function woodyIconsFolder($folder)
{
    $return = [];
    $icons_folder = wp_cache_get('woody_icons_folder', 'woody');
    if (empty($icons_folder) || !array_key_exists($folder, $icons_folder)) {
        $icons_finder = new Finder();
        $icons_finder->files()->name('*.svg')->in($folder);
        foreach ($icons_finder as $key => $icon) {
            $icon_name = str_replace('.svg', '', $icon->getRelativePathname());
            $icon_class = 'wicon-' . $icon_name;
            $icon_human_name = str_replace('-', ' ', $icon_name);
            $icon_human_name = substr($icon_human_name, 4);
            $icon_human_name = ucfirst($icon_human_name);
            $return[$icon_class] = $icon_human_name;
        }
        $icons_folder[$folder] = $return;
        wp_cache_set('woody_icons_folder', $icons_folder, 'woody');
    } else {
        $return = $icons_folder[$folder];
    }

    return $return;
}

/**
 *
 * Nom : getWoodyTwigPaths
 * Auteur : Benoit Bouchaud
 * Return : Un tableau
 * @return   the_icons - La liste de tous les icones du site
 *
 */
function getWoodyTwigPaths()
{
    $woodyTwigsPaths = wp_cache_get('woody_twig_paths', 'woody');
    if (empty($woodyTwigsPaths)) {
        $woodyLibrary = new WoodyLibrary();
        $woodyComponents = getWoodyComponents();
        $woodyTwigsPaths = $woodyLibrary->getTwigsPaths($woodyComponents);
        wp_cache_set('woody_twig_paths', $woodyTwigsPaths, 'woody');
    }
    return $woodyTwigsPaths;
}

/**
 *
 * Nom : getWoodyComponents
 * Auteur : Léo Poiroux
 * Return : Un tableau
 * @return   the_icons - La liste de tous les icones du site
 *
 */
function getWoodyComponents()
{
    $woodyComponents = wp_cache_get('woody_components', 'woody');
    if (empty($woodyComponents)) {
        $woodyLibrary = new WoodyLibrary();
        $woodyComponents = $woodyLibrary->getComponents();
        wp_cache_set('woody_components', $woodyComponents, 'woody');
    }
    return $woodyComponents;
}

function getPageTaxonomies()
{
    $taxonomies = wp_cache_get('woody_website_pages_taxonomies', 'woody');
    if (empty($taxonomies)) {
        $taxonomies = get_object_taxonomies('page', 'objects');
        unset($taxonomies['language']);
        unset($taxonomies['page_type']);
        unset($taxonomies['post_translations']);
        wp_cache_set('woody_website_pages_taxonomies', $taxonomies, 'woody');
    }

    return $taxonomies;
}

function getPageTerms($post_id)
{
    $return = [];

    $taxonomies = getPageTaxonomies();
    foreach ($taxonomies as $taxonomy) {
        $terms = wp_get_post_terms($post_id, $taxonomy->name);
        if (!is_wp_error($terms)) {
            foreach ($terms as $term) {
                $return[] = 'term-' . $term->slug;
            }
        }
    }

    return $return;
}

function getPrimaryTerm($taxonomy, $post_id, $fields = [])
{
    $return = null;

    $fieldPrimaryTax = get_field('field_5d7bada38eedf', $post_id);
    if (!empty($fieldPrimaryTax['primary_' . $taxonomy])) {
        $primary_term = get_term($fieldPrimaryTax['primary_' . $taxonomy]);
        if (!is_wp_error($primary_term) && !empty($primary_term)) {
            if (empty($fields)) {
                $return = $primary_term;
            } else {
                $return = [];
                foreach ($fields as $field) {
                    $return[$field] = $primary_term->$field;
                }
            }
        }
    }

    return $return;
}

/**
 *
 * Nom : getPostRootAncestor
 * Auteur : Thomas Navarro
 * Return : Retourne le parent racine d'un post
 * @param    postID INT : id d'une page enfant
 * @return   return - INT : l'id d'un parent
 *
 */
function getPostRootAncestor($postID, $root_level = 1)
{
    $return = 0;
    $ancestors = get_post_ancestors($postID);
    if (!empty($ancestors)) {
        // Get last ancestors
        $root = count($ancestors) - $root_level;
        if ($root < 0) {
            return;
        } else {
            $return = $ancestors[$root];
        }
    }

    return $return;
}

/**
 *
 * Nom : isWoodyInstagram
 * Auteur : Benoit Bouchaud
 * Return : Booleen
 * @param    taxonomy - Le slug du vocabulaire dans lequel on recherche
 * @param    media_item - Le media (WP post)
 * @return   is_instagram - Booléen
 *
 */
function isWoodyInstagram($attachment_id)
{
    $attachment_types = get_the_terms($attachment_id, 'attachment_types');
    if (!empty($attachment_types)) {
        foreach ($attachment_types as $attachment_type) {
            if ($attachment_type->slug == 'instagram') {
                return true;
            }
        }
    }

    return false;
}

/**
 *
 * Nom : getPostRootAncestor
 * Auteur : Benoit Bouchaud
 * Return : Retourne une chaîne de caractères trandsformée
 * @param    $token - STR : la chaîne à transformer
 * @return   return - STR : la chaîne transfomée
 *
 */
function woody_untokenize($token)
{
    // On définit les correspondances des tokens
    $patterns = [
            '%site_name%' => get_bloginfo('name'),
            '%post_title%' => get_the_title(),
            '%hero_title%' => get_field('field_5b041d61adb72'),
            '%hero_desc%' => get_field('field_5b041dbfadb74'),
            '%teaser_desc%' => get_field('field_5b2bbbfaec6b2'),
            '%focus_title%' => get_field('field_5b0d380e04203'),
            '%focus_desc%' => get_field('field_5b0d382404204')
        ];

    // On remplace les token par les valeurs des champs correspondants
    if (!empty($token)) {
        foreach ($patterns as $pattern_key => $pattern) {
            $token = str_replace($pattern_key, $pattern, $token);
        }
    }

    // On retire les balises html (pour les descriptions essentiellement)
    $token = str_replace('&nbsp; ', '', $token);
    $token = trim(html_entity_decode(strip_tags($token)));

    // On limite la chaine à +/- 150 caractères sans couper de mot
    if (strlen($token) > 170) {
        $token = substr($token, 0, strpos($token, ' ', 150));
    }

    return $token;
}

/***************************
 * Minutes to Hours converter
 *****************************/
function minuteConvert($num)
{
    $convertedTime['hours'] = floor($num / 60);
    $convertedTime['minutes'] = round((($num / 60) - $convertedTime['hours']) * 60);
    return $convertedTime;
}

/**
 *
 * Nom : embedProviderThumbnail
 * Auteur : Benoit Bouchaud
 * Return : Retourne l'url d'une miniature de vidéo YT, DailyMotion ou Vimeo
 * @param    $embed - STR : iframe oEmbed
 * @return   return - STR : url de la miniature
 *
 */
function embedProviderThumbnail($embed)
{
    $return = '';

    // On récupère l'attribut src de l'iframe
    preg_match('/src="(.+?)"/', $embed, $embed_matches);
    $src = $embed_matches[1];

    // On définit le provider
    if (strpos($src, 'youtu') != false) { // Match youtube.com & youtu.be
        $provider = 'youtube';
    } elseif (strpos($src, 'dailymotion') != false) {
        $provider = 'dailymotion';
    } elseif (strpos($src, 'vimeo') != false) {
        $provider = 'vimeo';
    } else {
        $provider = 'unknown';
    }

    // On définit l'url de la miniature en fonction du provider
    switch ($provider) {
        case 'unknown':
            return;
            break;
        case 'youtube':
            $regex = '/(?<=\/embed\/)(.*)(?=\?feature)/';
            preg_match($regex, $src, $matches);
            if (!empty($matches[0])) {
                $return = 'https://img.youtube.com/vi/' . $matches[0] . '/maxresdefault.jpg';
            }
        break;
        case 'dailymotion':
            $regex = '/(?<=\/video\/)(.*)/';
            preg_match($regex, $src, $matches);
            if (!empty($matches[0])) {
                $return = 'https://www.dailymotion.com/thumbnail/video/' . $matches[0];
            }
        break;
        case 'vimeo':
            $regex = '/(?<=\/video\/)(.*)(?=\?dnt)/';
            preg_match($regex, $src, $matches);
            if (!empty($matches[0])) {
                $vimeo_data = unserialize(file_get_contents('https://vimeo.com/api/v2/video/'. $matches[0] .'.php'));
                if (empty($vimeo_data)) {
                    return;
                }
                $return = $vimeo_data[0]['thumbnail_large'];
            }
        break;
    }

    return $return;
}
