<?php

/**
 * Robots
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
        add_action('init', array($this, 'registerPostType'));
        add_action('init', array($this, 'registerTaxonomies'));
        add_action('woody_theme_update', [$this, 'updatePllOption']);
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
                'capabilities' => [
                    'manage_terms' => 'Configurer les catégories d\'expression',
                    'edit_terms' => 'Editer les catégories d\'expression',
                    'delete_terms' => 'Supprimer les catégories d\'expression',
                    'assign_terms' => 'Assigner les catégories d\'expression'
                ]
            )
        );
    }

    /**
     * Ajout des profils aux posts types traduisibles
     */
    public function updatePllOption()
    {
        $pll_option = get_option('polylang');
        $pll_option['post_types'][] = 'profile';

        $pll_option = update_option('polylang', $pll_option);

        return $pll_option;
    }
}
