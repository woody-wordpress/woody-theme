<?php
/**
 * Taxonomy
 *
 * @link https://www.advancedcustomfields.com/resources/acf-settings
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_Post_Type
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_action('init', array($this, 'registerPostType'));
    }

    public function registerPostType()
    {
        $short_link = array(
            'label'               => 'Liens rapides',
            'description'         => "Création d'urls courtes / playlists préfiltrées",
            'labels'              => array(
                'name'                => 'Lien rapide',
                'singular_name'       => 'Lien rapide',
                'menu_name'           => 'Liens rapides',
                'all_items'           => 'Tous les liens rapides',
                'view_item'           => 'Voir les liens rapides',
                'add_new_item'        => 'Ajouter un lien rapide',
                'add_new'             => 'Ajouter',
                'edit_item'           => 'Editer le lien rapide',
                'update_item'         => 'Modifier le lien rapide',
                'search_items'        => 'Rechercher un lien rapide',
                'not_found'           => 'Non trouvé',
                'not_found_in_trash'  => 'Non trouvé dans la corbeille',
            ),
            'hierarchical'        => true,
            'public'              => true,
            'show_ui'             => true,
            'supports'            => array('title', 'custom-fields', 'page-attributes'),
            'show_in_menu'        => true,
            'menu_icon'           => 'dashicons-admin-links',
            'menu_position'       => 20,
            'show_in_nav_menus'   => false
        );

        register_post_type('short_link', $short_link);
    }
}
