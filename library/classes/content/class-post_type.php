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
            'description'         => 'Création d\'urls courtes / playlists préfiltrées',
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

        $woody_claims = array(
            'label'               => 'Blocs de publicité',
            'description'         => 'Contenus pub à afficher sur des pages',
            'labels'              => array(
                'name'                => 'Blocs de publicité',
                'singular_name'       => 'Bloc de publicité',
                'menu_name'           => 'Publicités',
                'all_items'           => 'Tous les blocs de publicité',
                'view_item'           => 'Voir les blocs de publicité',
                'add_new_item'        => 'Ajouter bloc de publicité',
                'add_new'             => 'Ajouter un bloc de publicité',
                'edit_item'           => 'Editer le bloc de publicité',
                'update_item'         => 'Modifier le bloc de publicité',
                'search_items'        => 'Rechercher un bloc de publicité',
                'not_found'           => 'Non trouvé',
                'not_found_in_trash'  => 'Non trouvé dans la corbeille',
            ),
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'supports'            => array('title', 'custom-fields'),
            'show_in_menu'        => true,
            'menu_icon'           => 'dashicons-admin-comments',
            'menu_position'       => 30,
            'show_in_nav_menus'   => false
        );

        register_post_type('short_link', $short_link);
        register_post_type('woody_claims', $woody_claims);
    }


}
