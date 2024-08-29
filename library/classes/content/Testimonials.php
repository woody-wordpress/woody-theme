<?php

/**
 * Testimonials
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.23.0
 */

namespace Woody\WoodyTheme\library\classes\content;

class Testimonials
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_action('init', array($this, 'registerPostType'));
        add_filter('woody_polylang_update_options', [$this, 'woodyPolylangUpdateOptions']);
    }

    //ANCHOR - Legacy content type => ne peut être supprimé car utilisé sur certains sites
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
            'show_ui'             => false,
            'supports'            => array('title', 'custom-fields'),
            'show_in_menu'        => false,
            'menu_icon'           => 'dashicons-testimonial',
            'menu_position'       => 31,
            'show_in_nav_menus'   => false
        );

        register_post_type('testimony', $testimony);
    }

    /**
     * Ajout des témoignages aux posts types traduisibles
     */
    public function woodyPolylangUpdateOptions($polylang)
    {
        if (!in_array('testimony', $polylang['post_types'])) {
            $polylang['post_types'][] = 'testimony';
        }

        return $polylang;
    }
}
