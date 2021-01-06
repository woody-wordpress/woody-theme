<?php

/**
 * Profiles
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.23.0
 */

class WoodyTheme_Profiles
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_action('init', array($this, 'registerPostType'), 10);
        add_action('init', array($this, 'registerTaxonomies'), 11);
        add_action('woody_theme_update', [$this, 'woodyThemeUpdate']);
    }

    public function registerPostType()
    {
        $profile = array(
            'label'               => 'Profil',
            'description'         => 'Profils à lier avec dans les pages ou les témoignages',
            'labels'              => array(
                'name'                => 'Profil',
                'singular_name'       => 'Profil',
                'menu_name'           => 'Profils',
                'all_items'           => 'Tous les profils',
                'view_item'           => 'Voir les profils',
                'add_new_item'        => 'Ajouter un profil',
                'add_new'             => 'Ajouter',
                'edit_item'           => 'Editer le profil',
                'update_item'         => 'Modifier le profil',
                'search_items'        => 'Rechercher un profil',
                'not_found'           => 'Non trouvé',
                'not_found_in_trash'  => 'Non trouvé dans la corbeille',
            ),
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'supports'            => array('title', 'custom-fields'),
            'show_in_menu'        => true,
            'menu_icon'           => 'dashicons-businessperson',
            'menu_position'       => 30,
            'show_in_nav_menus'   => false
        );

        register_post_type('profile', $profile);
    }

    public function registerTaxonomies()
    {
        register_taxonomy(
            'expression_category',
            'profile',
            array(
                'label' => 'Catégorie d\'expression',
                'labels' => [
                    'name' => 'Catégorie d\'expression',
                    'singular_name' => 'Catégorie d\'expression',
                    'menu_name' => 'Catégorie d\'expression',
                    'all_items' => 'Toutes les catégories d\'expression',
                    'edit_item' => 'Modifier les catégories d\'expression',
                    'view_item' => 'Voir les catégories d\'expression',
                    'update_item' => 'Mettre à jour les catégories d\'expression',
                    'new_item_name' => 'Nouvelle catégorie d\'expression',
                    'search_items' => 'Rechercher parmi les catégories d\'expression',
                    'popular_items' => 'Catégories d\'expression les plus utilisés'
                ],
                'hierarchical' => false,
                'show_ui' => true,
                'show_in_menu' => true,
                'meta_box_cb' => false,
                'capabilities' => [
                    'manage_terms' => 'Configurer les catégories d\'expression',
                    'edit_terms' => 'Editer les catégories d\'expression',
                    'delete_terms' => 'Supprimer les catégories d\'expression',
                    'assign_terms' => 'Assigner les catégories d\'expression'
                ]
            )
        );

        register_taxonomy(
            'profile_category',
            'profile',
            array(
                'label' => 'Catégorie de profil',
                'labels' => [
                    'name' => 'Catégorie de profil',
                    'singular_name' => 'Catégorie de profil',
                    'menu_name' => 'Catégorie de profil',
                    'all_items' => 'Toutes les catégories de profil',
                    'edit_item' => 'Modifier les catégories de profil',
                    'view_item' => 'Voir les catégories de profil',
                    'update_item' => 'Mettre à jour les catégories de profil',
                    'new_item_name' => 'Nouvelle catégorie de profil',
                    'search_items' => 'Rechercher parmi les catégories de profil',
                    'popular_items' => 'Catégories de profil les plus utilisés'
                ],
                'hierarchical' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'capabilities' => [
                    'manage_terms' => 'Configurer les catégories de profil',
                    'edit_terms' => 'Editer les catégories de profil',
                    'delete_terms' => 'Supprimer les catégories de profil',
                    'assign_terms' => 'Assigner les catégories de profil'
                ]
            )
        );
    }

    /**
     * Ajout des profils aux posts types traduisibles
     */
    public function woodyThemeUpdate()
    {
        $pll_option = get_option('polylang');
        if (!in_array('profile', $pll_option['post_types'])) {
            $pll_option['post_types'][] = 'profile';
            update_option('polylang', $pll_option);
        }
    }
}
