<?php

/**
 * Testimonials
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.23.0
 */

class WoodyTheme_Testimonials
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_action('init', array($this, 'registerPostType'));
        add_action('woody_subtheme_update', [$this, 'updatePllOption']);
    }

    public function registerPostType()
    {
        $testimony = array(
            'label'               => 'Témoignage',
            'description'         => 'Témoignages, utilisables dans les pages de contenu',
            'labels'              => array(
                'name'                => 'Témoignage',
                'singular_name'       => 'Témoignage',
                'menu_name'           => 'Témoignages',
                'all_items'           => 'Tous les témoignages',
                'view_item'           => 'Voir les témoignages',
                'add_new_item'        => 'Ajouter un témoignage',
                'add_new'             => 'Ajouter',
                'edit_item'           => 'Editer le témoignage',
                'update_item'         => 'Modifier le témoignage',
                'search_items'        => 'Rechercher un témoignage',
                'not_found'           => 'Non trouvé',
                'not_found_in_trash'  => 'Non trouvé dans la corbeille',
            ),
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'supports'            => array('title', 'custom-fields'),
            'show_in_menu'        => true,
            'menu_icon'           => 'dashicons-testimonial',
            'menu_position'       => 31,
            'show_in_nav_menus'   => false
        );

        register_post_type('testimony', $testimony);
    }

    /**
     * Ajout des témoignages aux posts types traduisibles
     */
    public function updatePllOption()
    {
        $pll_option = get_option('polylang');
        $pll_option['post_types'][] = 'testimony';

        $pll_option = update_option('polylang', $pll_option);

        return $pll_option;
    }
}
