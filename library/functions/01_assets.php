<?php

use Symfony\Component\Finder\Finder;

/**
 *
 * Nom : getAcfGroupFields
 * Auteur : Benoit Bouchaud
 * Return : Retourne un tableau avec les valeurs des champs d'un groupe ACF poyr un post donné
 * @param    group_id - La clé du groupe ACF
 * @return   page_teaser_fields - Un tableau de données
 *
 */
function getAcfGroupFields($group_id, $post = null)
{
    if (is_null($post)) {
        $post = get_post();
    }

    if (!empty($post)) {
        $post_id = $post->ID;
        $the_fields = array();
        $fields = acf_get_fields($group_id);

        if (!empty($fields)) {
            foreach ($fields as $field) {
                $field_value = false;
                if (!empty($field['name'])) {
                    $field_value = get_field($field['name'], $post_id);
                }

                if ($field_value && !empty($field_value)) {
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

    if ($number % 7 === 0) {
        $week_number = $number / 7;
        if ($week_number > 1) {
            $return = $week_number . ' semaines';
        } else {
            $return = $week_number . ' semaine';
        }
    } else {
        if ($number > 1) {
            $return = $number . ' jours';
        } else {
            $return = $number . ' jour';
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

    // TODO : récuperer variable station de l'ERP
    $stations = ['superot', 'champsaur-valgaudemar', 'hautemaurienne'];
    $station_icons= array();

    if ((in_array(WP_SITE_KEY, $stations))) {
        $station_icons = woodyIconsFolder(get_template_directory() . '/src/icons/icons_set_stations');
    }

    $site_icons = woodyIconsFolder(get_stylesheet_directory() . '/src/icons');

    $return = array_merge($core_icons, $site_icons, $station_icons);

    return $return;
}

function woodyIconsFolder($folder)
{
    $return = [];
    $icons_folder = get_transient('woody_icons_folder');
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
        set_transient('woody_icons_folder', $icons_folder);
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
    $woodyTwigsPaths = [];
    $woodyComponents = get_transient('woody_components');
    if (empty($woodyComponents)) {
        $woodyComponents = Woody::getComponents();
        set_transient('woody_components', $woodyComponents);
    }

    $woodyTwigsPaths = Woody::getTwigsPaths($woodyComponents);

    return $woodyTwigsPaths;
}


/**
 *
 * Nom : getMinMaxWoodyFieldValues
 * Auteur : Benoit Bouchaud
 * Return : Retourne le html d'une mise en avant de contenu
 * @param    query_vars Les champs de tris pour rechercher les champ dans une selection de posts
 * @param    field le champ dans lequel on cherche
 * @param    minormax Sting => 'min' ou 'max' (Default max)
 * @return   return - Un nombre
 *
 */

function getMinMaxWoodyFieldValues($query_vars = array(), $field, $minormax = 'max')
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

    $query_result = new WP_Query($query_vars);

    if (!empty($query_result->posts)) {
        $return = get_field($field, $query_result->posts[0]->ID);
        $return = (empty($return)) ? 1 : $return;
    }

    return $return;
}

function getPageTerms($post_id)
{
    $return = [];

    $taxonomies = get_transient('woody_website_pages_taxonomies');
    if (empty($taxonomies)) {
        $taxonomies = get_object_taxonomies('page', 'objects');
        unset($taxonomies['language']);
        unset($taxonomies['page_type']);
        unset($taxonomies['post_translations']);
        set_transient('woody_website_pages_taxonomies', $taxonomies);
    }

    foreach ($taxonomies as $taxonomy) {
        $terms = wp_get_post_terms($post_id, $taxonomy->name);
        foreach ($terms as $term) {
            $return[] = 'term-' . $term->slug;
        }
    }

    return $return;
}

function getPrimaryTerm($taxonomy, $post_id, $fields = [])
{
    $return = '';
    // $field values can be : count, description, filter, name, perent, slug, taxonomy, term_group, term_id, term_taxonomy_id
    if (class_exists('WPSEO_Primary_Term')) {
        $wpseo_primary_term = new WPSEO_Primary_Term($taxonomy, $post_id);
        $primary_id = $wpseo_primary_term->get_primary_term();
        $primary_term = get_term($primary_id);
        if (!is_wp_error($primary_term) && !empty($primary_term)) {
            if (empty($fields)) {
                $return = $primary_term;
            } else {
                foreach ($fields as $field) {
                    $return[$field] = $primary_term->$field;
                }
            }
        }
    } else {
        return false;
    }

    return $return;
}

/**
 *
 * Nom : getPostAncestors
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
        $return = $ancestors[$root];
    }

    return $return;
}

function getAttachmentMoreData($attachment_id)
{
    $attachment_data = [];
    $attachment_data['is_instagram'] = isWoodyInstagram($attachment_id);

    if ($attachment_data['is_instagram']) {
        $attachment_data['instagram_metadata'] = getInstagramMetadata($attachment_id);
    }

    $attachment_data['linked_page'] = get_field('field_5c0553157e6d0', $attachment_id);
    $attachment_data['author'] = get_field('field_5b5585503c855', $attachment_id);
    $attachment_data['lat'] = get_field('field_5b55a88e70cbf', $attachment_id);
    $attachment_data['lng'] = get_field('field_5b55a89e70cc0', $attachment_id);

    return $attachment_data;
}

function getInstagramMetadata($attachment_id)
{
    $img_all_data = get_post_meta($attachment_id);
    $img_all_metadata = (!empty($img_all_data['_wp_attachment_metadata'][0])) ? maybe_unserialize($img_all_data['_wp_attachment_metadata'][0]) : '';
    return (!empty($img_all_metadata['woody-instagram'])) ? $img_all_metadata['woody-instagram'] : '';
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
