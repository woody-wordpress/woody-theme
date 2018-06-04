<?php
/**
 * Taxonomy
 *
 * @link https://www.advancedcustomfields.com/resources/acf-settings
 * @package HawwwaiTheme
 * @since HawwwaiTheme 1.0.0
 */

class HawwwaiTheme_Taxonomy
{
    public function __construct()
    {
        $this->register_hooks();
    }

    protected function register_hooks()
    {
        register_taxonomy(
            'page_type',
            'page',
            array(
                'label' => 'Type de publication',
                'labels' => array(
                    'name' => 'Types de publications',
                    'singular_name' => 'Type de publication',
                    'menu_name' => 'Type de publication',
                    'all_items' => 'Tous les types de publications',
                    'edit_item' => 'Modifier les types de publications',
                    'view_item' => 'Voir les types de publications',
                    'update_item' => 'Mettre à jour les types de publications',
                    'add_new_item' => 'Ajouter un type de publication',
                    'new_item_name' => 'Nouveau type de publication',
                    'search_items' => 'Rechercher parmi types de publications',
                    'popular_items' => 'Types de publications les plus utilisées'
                ),
                'hierarchical' => false,
                'show_ui' => false,
            )
        );
    }
}

// Execute Class
$HawwwaiTheme_Taxonomy = new HawwwaiTheme_Taxonomy();
