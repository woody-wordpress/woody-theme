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
    public static function getMainMenu($limit = 6)
    {
        $return = [];
        $return = self::getMenuLinks(0, $limit);
        // foreach ($return as $key => $item_depth1) {

        //     foreach($args as $submenu_key => $submenu_args){
        //         if($submenu_key === $key){
        //             if(empty($submenu_args)){
        //                 continue;
        //             }
        //             if(!empty($submenu_args['submenu_parts'])){
        //                 foreach($submenu_args['submenu_parts'] as $part_key => $part){
        //                     if(!empty($part['has_subitems'])){
        //                         // rcd($part['max_depth']);
        //                         $return[$key]['subitems'] = self::getSubmenuData($item_depth1, $part['max_depth']);
        //                     }
        //                 }
        //             }
        //         }
        //     }
        // }
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
    // public static function getSubmenuData($item_depth1, $max_depth = 1)
    // {
    //     $return = [];
    //     $return = self::getMenuLinks($item_depth1['the_id']);
    //     if (!empty($return) && $max_depth >= 3) {
    //         foreach ($return as $key_depth2 => $item_detph2) {
    //             rcd($item_detph2);
    //             $return[$key_depth2]['subitems'] = self::getMenuLinks($item_detph2['the_id']);
    //             // if (!empty($return[$key_depth2]['subitems']) && $max_depth >= 4) {
    //             //     foreach ($return[$key_depth2]['subitems'] as $key_depth3 => $item_detph3) {
    //             //         $return[$key_depth2]['subitems'][$key_depth3]['subitems'] = self::getMenuLinks($item_detph3['the_id']);
    //             //     }
    //             // }
    //         }
    //     }
    //     return $return;
    // }

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
                'the_url' => get_permalink($post->ID),
            ];

            $return[$key]['the_fields']['title'] = (!empty(get_field('in_menu_title', $post->ID))) ? get_field('in_menu_title', $post->ID) : $post->post_title;
            $return[$key]['the_fields']['icon'] = (!empty(get_field('in_menu_woody_icon', $post->ID))) ? get_field('in_menu_woody_icon', $post->ID) : '';
            $return[$key]['the_fields']['pretitle'] = (!empty(get_field('in_menu_pretitle', $post->ID))) ? get_field('in_menu_pretitle', $post->ID) : get_field('field_5b87f20257a1d', $post->ID);
            $return[$key]['the_fields']['subtitle'] = (!empty(get_field('in_menu_subtitle', $post->ID))) ? get_field('in_menu_subtitle', $post->ID) : get_field('field_5b87f23b57a1e', $post->ID);
            $return[$key]['img'] = (!empty(get_field('in_menu_img', $post->ID))) ? get_field('in_menu_img', $post->ID) : get_field('field_5b0e5ddfd4b1b', $post->ID);
        }

        // rcd($return, true);


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
    // public static function getCompiledSubmenu($items, $args)
    // {
    //     $return = '';

    //     $twig_paths = getWoodyTwigPaths();

    //     $default = [
    //         'grid' => $twig_paths['grids_basic-grid_1_cols-tpl_01'],
    //         'custom_function' => '',
    //         'alignement' => 'align-stretch',
    //         'no_padding' => 0,
    //     ];

    //     if (empty($args)) {
    //         return;
    //     }

    //     if (is_array($args)) {
    //         $args = array_merge($default, $args);
    //     }

    //     if (!empty($args['custom_function']) && function_exists($args['custom_function'])) {
    //         $return = $args['custom_function'];
    //     } elseif (!empty($args['grid']) && !empty($args['submenu_parts'])) {
    //         foreach ($args['submenu_parts'] as $key => $part) {
    //             if (empty($part)) {
    //                 $submenu_parts['items'][] = '';
    //             } else {
    //                 $submenu_parts['items'][] = self::getCompiledSubmenuPart(array_slice($items, $part['from'], $part['length']), $part);
    //             }
    //         }
    //         $submenu_parts['is_list'] = true;
    //         $submenu_parts['alignement'] = $args['alignement'];
    //         $submenu_parts['no_padding'] = $args['no_padding'];

    //         $return = Timber::compile($args['grid'], $submenu_parts);
    //     }

    //     return $return;
    // }

    /**
    *
    * Nom : getCompiledSubmenuPart
    * Auteur : Benoit Bouchaud
    * Return : Retourne une partie de sous-menu sous forme de html (twig compilé)
    * @param items - Tableau des liens de la partie
    * @param grid_tpl - Le template Woody à utiliser pour rendre la partie
    * @param items_tpl - Le template Woody à utiliser pour rendre les items de la partie
    * @param custom_function - Une fonction permettant de surcharger le menu de base
    * @return return - Une chaine de caractère
    *
    */
    // public static function getCompiledSubmenuPart($items, $args)
    // {
    //     $return = '';
    //     if (!empty($args['custom_function']) && function_exists($args['custom_function'])) {
    //         $return = $args['custom_function'];
    //     } elseif (!empty($items) && !empty($args['grid']) && !empty($args['items_tpl'])) {
    //         foreach ($items as $key => $item) {
    //             if (!empty($item['subitems']) && $args['max_depth'] >= 3 && !empty($args['subitems_tpl'])) {
    //                 foreach ($item['subitems'] as $index => $subitem) {
    //                     $subitem['depth_index'] = '3';
    //                     $item['the_subitems'][$index] = Timber::compile($args['subitems_tpl'], $subitem);
    //                     unset($item['subitems']);
    //                 }
    //             }
    //             $item['depth_index'] = '2';
    //             $part_items['items'][] = Timber::compile($args['items_tpl'], $item);
    //         }
    //         $part_items['alignement'] = $args['alignement'];
    //         $part_items['no_padding'] = $args['no_padding'];

    //         $return = Timber::compile($args['grid'], $part_items);
    //     }

    //     return $return;
    // }
}
