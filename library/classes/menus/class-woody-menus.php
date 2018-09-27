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
    public static function getMainMenu($submenu_depth = array(), $limit = 6)
    {
        $return = [];
        $return = self::getMenuLinks(0, $limit);

        foreach ($return as $key => $item_depth1) {
            if (empty($submenu_depth[$key])) {
                continue;
            }
            $return[$key]['subitems'] = self::getSubmenuData($item_depth1, $submenu_depth[$key]);
        }

        return $return;
    }

    /**
    *
    * Nom : getSubmenu
    * Auteur : Benoit Bouchaud
    * Return : Retourne les menu de x niveaux inférieurs en fonction du paramètre $max_depth
    * @param item_depth1 - L'item de menu parent
    * @param limit - Le nombre maximum de niveaux inférieurs à remonter
    * @return return - Un tableau
    *
    */
    public static function getSubmenuData($item_depth1, $max_depth = 1)
    {
        $return = [];
        $return = self::getMenuLinks($item_depth1['the_id']);
        if (!empty($return) && $max_depth >= 3) {
            foreach ($return as $key_depth2 => $item_detph2) {
                $return[$key_depth2]['subitems'] = self::getMenuLinks($item_detph2['the_id']);
                if (!empty($return[$key_depth2]['subitems']) && $max_depth >= 4) {
                    foreach ($return[$key_depth2]['subitems'] as $key_depth3 => $item_detph3) {
                        $return[$key_depth2]['subitems'][$key_depth3]['subitems'] = self::getMenuLinks($item_detph3['the_id']);
                    }
                }
            }
        }
        return $return;
    }

    /**
    *
    * Nom : getMenuLinks
    * Auteur : Benoit Bouchaud
    * Return : Récupère les champs utiles au menu de tous les post enfants du $post_parent
    * @param post_parent - L'id du post parent
    * @param limit - Le nombre maximum de posts à remonter
    * @return return - Un tableau
    *
    */
    public static function getMenuLinks($post_parent = 0, $limit = -1)
    {
        $return = [];

        $args = array(
            'post_type'        => 'page',
            'post_parent'      => $post_parent,
            'post_status'      => 'publish',
            'order'            => 'ASC',
            'orderby'          => 'menu_order',
            'numberposts'      => $limit
        );

        $posts = get_posts($args);

        foreach ($posts as $key => $post) {
            $return[$key] = [
                'the_id' => $post->ID,
                'the_url' => $post->guid,
            ];

            $return[$key]['the_fields']['title'] = (!empty(get_field('in_menu_title', $post->ID))) ? get_field('in_menu_title', $post->ID) : $post->post_title;
            $return[$key]['the_fields']['icon'] = (!empty(get_field('in_menu_woody_icon', $post->ID))) ? get_field('in_menu_woody_icon', $post->ID) : '';
            $return[$key]['the_fields']['pretitle'] = (!empty(get_field('in_menu_pretitle', $post->ID))) ? get_field('in_menu_pretitle', $post->ID) : get_field('field_5b87f20257a1d', $post->ID);
            $return[$key]['the_fields']['subtitle'] = (!empty(get_field('in_menu_subtitle', $post->ID))) ? get_field('in_menu_subtitle', $post->ID) : get_field('field_5b87f23b57a1e', $post->ID);
            $return[$key]['img'] = (!empty(get_field('in_menu_img', $post->ID))) ? get_field('in_menu_img', $post->ID) : get_field('field_5b0e5ddfd4b1b', $post->ID);
        }

        return $return;
    }

    /**
    *
    * Nom : getCompiledSubmenu
    * Auteur : Benoit Bouchaud
    * Return : Retourne le sous-menu sous forme de html (twig compilé)
    * @param items - Tableau des liens du sous-menu
    * @param args - Les paramètres du sous menu (template + template de chaque partie du sous menu)
    * @return return - Une chaine de caractère
    *
    */
    public static function getCompiledSubmenu($items, $args)
    {
        $return = '';

        foreach ($args['submenu_parts'] as $key => $part) {
            $submenu_parts['items'][] = self::getCompiledSubmenuPart(array_slice($items, $part['from'], $part['to']), $part['grid'], $part['items_tpl']);
        }

        $return = Timber::compile($args['grid'], $submenu_parts);
        return $return;
    }

    /**
    *
    * Nom : getCompiledSubmenuPart
    * Auteur : Benoit Bouchaud
    * Return : Retourne une partie de sous-menu sous forme de html (twig compilé)
    * @param items - Tableau des liens de la partie
    * @param grid_tpl - Le template Woody à utiliser pour rendre la partie
    * @param items_tpl - Le template Woody à utiliser pour rendre les items de la partie
    * @return return - Une chaine de caractère
    *
    */
    public static function getCompiledSubmenuPart($items, $grid_tpl, $items_tpl)
    {
        $return = '';

        // rcd($items, true);
        foreach ($items as $key => $item) {
            $part_items['items'][] = Timber::compile($items_tpl, $item);
        }

        $return = Timber::compile($grid_tpl, $part_items);

        return $return;
    }
}
