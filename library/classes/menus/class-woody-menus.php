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

        foreach ($return as $key => $value) {
            $submenus = self::getSubmenus($value['the_id']);
        }

        return $return;
    }

    public static function getSubmenus($post_id)
    {
        $return = [];
        $fields = get_fields('options');
        foreach ($fields as $key => $field) {
        }
        // rcd($fields);
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
                'the_url' => get_permalink($post->ID),
            ];

            $return[$key]['the_fields']['title'] = (!empty(get_field('in_menu_title', $post->ID))) ? get_field('in_menu_title', $post->ID) : $post->post_title;
            $return[$key]['the_fields']['icon'] = (!empty(get_field('in_menu_woody_icon', $post->ID))) ? get_field('in_menu_woody_icon', $post->ID) : '';
            $return[$key]['the_fields']['pretitle'] = (!empty(get_field('in_menu_pretitle', $post->ID))) ? get_field('in_menu_pretitle', $post->ID) : get_field('field_5b87f20257a1d', $post->ID);
            $return[$key]['the_fields']['subtitle'] = (!empty(get_field('in_menu_subtitle', $post->ID))) ? get_field('in_menu_subtitle', $post->ID) : get_field('field_5b87f23b57a1e', $post->ID);
            $return[$key]['img'] = (!empty(get_field('in_menu_img', $post->ID))) ? get_field('in_menu_img', $post->ID) : get_field('field_5b0e5ddfd4b1b', $post->ID);
        }

        return $return;
    }
}
