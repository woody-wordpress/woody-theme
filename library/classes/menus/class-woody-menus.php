<?php

/**
 * Menus
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_Menus
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_theme_support('menus');
    }

    /**
     *
     * Nom : getMainMenu
     * Auteur : Benoit Bouchaud
     * Return : Retourne les liens du menu principal avec les champs utiles de la page associée
     * @param submenu_depth - Tableau des profondeurs max pour chaque sous menu
     * @param limit - Le nombre maximum d'éléments à remonter
     * @return return - Un tableau
     *
     */
    public static function getMainMenu($limit = 6, $depth_1_ids = [], $root_level = 1, $groups_nested_sublinks = false)
    {
        $current_lang = PLL_DEFAULT_LANG;
        if (function_exists('pll_current_language')) {
            $current_lang = pll_current_language();
        }

        $menu_cache_key = $current_lang . '_' . md5(serialize($depth_1_ids));
        $woody_menus_cache = [];

        if (!empty($woody_menus_cache[$menu_cache_key])) {
            $return = $woody_menus_cache[$menu_cache_key];
        } else {
            $return = [];
            if (!empty($depth_1_ids)) {
                $return = self::getMenuLinks($depth_1_ids, 0, $limit, $root_level);
            } else {
                $return = self::getMenuLinks(null, 0, $limit);
            }

            if (!empty($return) && is_array($return)) {
                foreach ($return as $key => $value) {
                    $return[$key]['submenu'] = self::getSubmenus($value['the_id'], $groups_nested_sublinks);
                }
            }
        }

        return $return;
    }

    public static function getSubmenus($post_id, $groups_nested_sublinks = false)
    {
        $return = [];
        $fields_groups_wrapper = self::getTheRightOption($post_id);

        if (empty($fields_groups_wrapper) || !is_array($fields_groups_wrapper)) {
            return;
        }

        foreach ($fields_groups_wrapper as $fields_groups) {
            if (empty($fields_groups)) {
                continue;
            }
            foreach ($fields_groups as $group_key => $field_group) {
                if (empty($field_group)) {
                    continue;
                }
                if (is_array($field_group)) {
                    foreach ($field_group as $field) {
                        if (empty($field)) {
                            continue;
                        }
                        if (!is_array($field)) {
                            if (is_object($field)) {
                                $in_menu_title = get_field('in_menu_title', $field->ID);
                                $return[$group_key]['part_title'] = (!empty($in_menu_title)) ? $in_menu_title : $field->post_title;
                                $return[$group_key]['part_title_link'] = woody_get_permalink($field->ID);
                            } else {
                                $return[$group_key]['part_title'] = $field;
                            }
                        } else {
                            foreach ($field as $field_data_key => $field_data) {
                                if ($groups_nested_sublinks && !empty($field_data)) {
                                    foreach ($field_data as $data) {
                                        $parts[$group_key][] = apply_filters('woody_custom_submenu', $data['submenu_links_objects'], $data);
                                    }
                                } else {
                                    $parts[$group_key][] = apply_filters('woody_custom_submenu', $field[$field_data_key]['submenu_links_objects'], $field_data);
                                }

                                if (!empty($field_data['submenu_sublinks'])) {
                                    foreach ($field_data['submenu_sublinks'] as $sublink) {
                                        $sublinks[$group_key][$field_data_key][] = $sublink['submenu_links_objects'];
                                    }
                                }
                            }

                            $return[$group_key]['links'] = self::getMenuLinks($parts[$group_key]);

                            foreach ($return[$group_key]['links'] as $link_key => $link) {
                                if (!empty($sublinks[$group_key][$link_key])) {
                                    $return[$group_key]['links'][$link_key]['sublinks'] = self::getMenuLinks($sublinks[$group_key][$link_key]);
                                }
                            }
                        }
                    }
                } else {
                    $return[$group_key]['links'] = '';
                }
            }
        }

        return $return;
    }

    public static function getTheRightOption($post_id = null)
    {
        $return = [];
        if (!empty($post_id) && is_numeric($post_id)) {
            $return['submenu_' . $post_id] = apply_filters('woody_get_field_option', 'submenu_' . $post_id);
        }
        return $return;
    }

    /**
     *
     * Nom : getMenuLinks
     * Auteur : Benoit Bouchaud
     * Return : Récupère les champs utiles au menu de tous les post enfants du $post_parent
     * @param posts - Un tableau de posts (optionnel)
     * @param post_parent - L'id du post parent
     * @param limit - Le nombre maximum de posts à remonter
     * @return return - Un tableau
     *
     */
    public static function getMenuLinks($posts = [], $post_parent = 0, $limit = -1, $root_level = 1)
    {
        $return = [];
        // TODO: empty($post) is usefull if depth_1 links are based on WoodyPage pages's weight +> to remove
        if (empty($posts)) {
            $args = array(
                'post_type'        => 'page',
                'post_parent'      => $post_parent,
                'post_status'      => 'publish',
                'order'            => 'ASC',
                'orderby'          => 'menu_order',
                'numberposts'      => $limit
            );
            $posts = get_posts($args);
        }

        if (!empty($posts) && is_array($posts)) {
            foreach ($posts as $post_key => $post) {
                if (is_int($post)) {
                    $post = get_post($post);
                } elseif (is_array($post) && !empty($post['url'])) {
                    $post_id = url_to_postid($post['url']);
                    if (empty($post['title']) && !empty($post_id)) {
                        $post = get_post($post_id);
                    } else {
                        $return[$post_key] = [
                            'the_id' => 'external',
                            'the_url' => $post['url'],
                            'the_target' => '_blank',
                            'the_fields' => [
                                'title' => (!empty($post['title'])) ? $post['title'] : '',
                                ]
                            ];
                    }
                }

                if (is_object($post) && $post->post_status == 'publish') {
                    $return[$post_key] = [
                        'the_id' => $post->ID,
                        'the_url' => woody_get_permalink($post->ID),
                    ];

                    // On vérifie si la page est de type mirroir
                    $page_type = get_the_terms($post->ID, 'page_type');
                    if (!empty($page_type) && $page_type[0]->slug == 'mirror_page') {
                        $mirror = get_field('mirror_page_reference', $post->ID);
                        $mirror_post = get_post($mirror);
                        if (!empty($mirror_post) && $mirror_post->post_status == 'publish') {
                            $post = $mirror_post;
                        }
                    }

                    // On retire le bordereau, la commune et l'id de fiche du titre des fiches SIT
                    if ($post->post_type == 'touristic_sheet') {
                        $sheet = woody_hawwwai_item($post->ID);
                        $return[$post_key]['the_fields']['title'] = (!empty($sheet['title'])) ? $sheet['title'] : '';
                    } else {
                        $in_menu_title = get_field('in_menu_title', $post->ID);
                        $return[$post_key]['the_fields']['title'] = (!empty($in_menu_title)) ? $in_menu_title : $post->post_title;
                    }

                    $return[$post_key]['the_fields']['woody_icon'] = get_field('in_menu_woody_icon', $post->ID);
                    $return[$post_key]['the_fields']['icon_type'] = 'picto';
                    $return[$post_key]['the_fields']['pretitle'] = get_field('in_menu_pretitle', $post->ID);
                    $return[$post_key]['the_fields']['subtitle'] = get_field('in_menu_subtitle', $post->ID);

                    $in_menu_img = get_field('in_menu_img', $post->ID);
                    $return[$post_key]['img'] = (!empty($in_menu_img)) ? $in_menu_img : get_field('field_5b0e5ddfd4b1b', $post->ID);
                }
            }

            return $return;
        }
    }

    /**
     *
     * Nom : getCompiledSubmenu
     * Auteur : Benoit Bouchaud
     * Return : Récupère les champs utiles au menu de tous les post enfants du $post_parent
     * @param menu_link - Le tableau du lien 0 avec son sous-menu
     * @param menu_display - Un tableau des tpl twigs à appliquer
     * @return return - html
     *
     */
    public static function getCompiledSubmenu($menu_link, $menu_display, $getChildren = false)
    {
        $submenu = [];
        $return = '';
        $twig_paths = getWoodyTwigPaths();
        if (!empty($menu_link['submenu']) && !empty($menu_display[$menu_link['the_id']])) {
            $the_submenu = [];
            $the_submenu['is_list'] = true;
            $the_submenu['no_padding'] = (!empty($menu_display[$menu_link['the_id']]['no_padding'])) ? $menu_display[$menu_link['the_id']]['no_padding'] : 0;
            $the_submenu['menu_part_title'] = (!empty($menu_display[$menu_link['the_id']]['menu_part_title'])) ? $menu_display[$menu_link['the_id']]['menu_part_title'] : null;
            $the_submenu['menu_part_title_link'] = (!empty($menu_display[$menu_link['the_id']]['menu_part_title_link'])) ? $menu_display[$menu_link['the_id']]['menu_part_title_link'] : null;
            $the_submenu['alignment'] = (!empty($menu_display[$menu_link['the_id']]['alignment'])) ? $menu_display[$menu_link['the_id']]['alignment'] : 'align-top';
            $submenu['display'] = $menu_display[$menu_link['the_id']];
            $i = 0;

            foreach ($menu_link['submenu'] as $key => $part) {
                if (!empty($part['links'])) {
                    $the_part = [];
                    $the_part['alignment'] = (!empty($submenu['display']['parts'][$i]['alignment'])) ? $submenu['display']['parts'][$i]['alignment'] : 'align-top';
                    $the_part['no_padding'] = (!empty($submenu['display']['parts'][$i]['no_padding'])) ? $submenu['display']['parts'][$i]['no_padding'] : 0;
                    foreach ($part['links'] as $link_key => $link) {
                        if (!empty($submenu['display']['parts'][$i]['links_tpl'])) {
                            $link_display = $submenu['display']['parts'][$i]['links_tpl'];
                            if ($getChildren) {
                                $args = [
                                    'post_parent' => $link['the_id'],
                                    'post_type'   => 'page',
                                    'post_status' => 'publish'
                                ];
                                $sublinks = get_children($args);
                                $link['sublinks'] = !empty($sublinks) ? self::getMenuLinks($sublinks) : [];
                            }
                            $part['links'][$link_key] = \Timber::compile($twig_paths[$link_display], $link);
                        }
                    }
                }

                if (!empty($submenu['display']['parts'][$i]['part_tpl'])) {
                    $part_display = $submenu['display']['parts'][$i]['part_tpl'];
                    $the_part['menu_part_title'] = !empty($part['part_title']) ? $part['part_title'] : '';
                    $the_part['menu_part_title_link'] = !empty($part['part_title_link']) ? $part['part_title_link'] : '';
                    $the_part['items'] = (!empty($part['links'])) ? $part['links'] : [];
                    $menu_link['submenu'][$key] = \Timber::compile($twig_paths[$part_display], $the_part);
                } elseif (!empty($submenu['display']['parts'][$i]['custom_function'])) {
                    $menu_link['submenu'][$key] = $submenu['display']['parts'][$i]['custom_function'];
                } else {
                    unset($menu_link['submenu'][$key]);
                }

                if (!empty($menu_link['submenu'][$key])) {
                    $the_submenu['items'][] = $menu_link['submenu'][$key];
                }
                $i++;
            }

            $return = \Timber::compile($twig_paths[$submenu['display']['grid_tpl']], $the_submenu);
        }

        return $return;
    }
}
