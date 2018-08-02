<?php
/**
 * Taxonomy
 *
 * @link https://www.advancedcustomfields.com/resources/acf-settings
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_Taxonomy
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_action('init', array($this, 'registerContentTypeTaxonomy'), 0);
    }

    public function registerContentTypeTaxonomy()
    {
        // On créé la taxonomie "Type de publication"
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
                'show_ui' => true //TODO passer à false quand les types de publications seront définitifs
            )
        );

        // On inclut les termes génériques à la taxo
        wp_insert_term('Actu', 'page_type', array('slug' => 'article'));
        wp_insert_term('Expérience', 'page_type', array('slug' => 'experience'));
        wp_insert_term('Contenu', 'page_type', array('slug' => 'basic_page'));
        wp_insert_term('Page d\'atterrissage', 'page_type', array('slug' => 'landing_page'));
        wp_insert_term('Personne', 'page_type', array('slug' => 'member'));
    }
}
