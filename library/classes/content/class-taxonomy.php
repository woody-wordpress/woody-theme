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
                'labels' => [
                    'name' => 'Types de publications',
                    'singular_name' => 'Type de publication',
                    'menu_name' => 'Type de publication',
                    'all_items' => 'Tous les types de publications',
                    'edit_item' => 'Modifier les types de publications',
                    'view_item' => 'Voir les types de publications',
                    'update_item' => 'Mettre à jour les types de publications',
                    'add_new_item' => 'Ajouter un type de publication',
                    'new_item_name' => 'Nouveau type de publication',
                    'search_items' => 'Rechercher parmi les types de publications',
                    'popular_items' => 'Types de publications les plus utilisées'
                ],
                'hierarchical' => false,
                'show_ui' => false,
                'capabilities' => [
                    'manage_terms' => false,
                    'edit_terms' => false,
                    'delete_terms' => false,
                    'assign_terms' => true
                ]
            )
        );

        // On inclut les termes génériques à la taxo
        wp_insert_term('Actu', 'page_type', array('slug' => 'article'));
        wp_insert_term('Expérience', 'page_type', array('slug' => 'experience'));
        wp_insert_term('Contenu', 'page_type', array('slug' => 'basic_page'));
        wp_insert_term('Page d\'atterrissage', 'page_type', array('slug' => 'landing_page'));
        wp_insert_term('Personne', 'page_type', array('slug' => 'member'));
        wp_insert_term('Séjour', 'page_type', array('slug' => 'trip'));

        // On créé la taxonomie "Thématiques"
        register_taxonomy(
            'themes',
            ['page', 'attachment'],
            array(
                'label' => 'Thématiques',
                'labels' => [
                    'name' => 'Thématiques',
                    'singular_name' => 'Thématique',
                    'menu_name' => 'Thématiques',
                    'all_items' => 'Toutes les thématiques',
                    'edit_item' => 'Modifier les thématiques',
                    'view_item' => 'Voir les thématiques',
                    'update_item' => 'Mettre à jour les thématiques',
                    'add_new_item' => 'Ajouter une thématique',
                    'new_item_name' => 'Nouvelle thématique',
                    'search_items' => 'Rechercher parmi les thématiques',
                    'popular_items' => 'Thématiques les plus utilisées'
                ],
                'hierarchical' => true,
                'show_ui' => true,
                'capabilities' => [
                    'manage_terms' => true,
                    'edit_terms' => true,
                    'delete_terms' => true,
                    'assign_terms' => true
                ]
            )
        );

        // On créé la taxonomie "Lieux"
        register_taxonomy(
            'places',
            ['page', 'attachment'],
            array(
                'label' => 'Lieux',
                'labels' => [
                    'name' => 'Lieux',
                    'singular_name' => 'Lieu',
                    'menu_name' => 'Lieux',
                    'all_items' => 'Tous les lieux',
                    'edit_item' => 'Modifier les lieux',
                    'view_item' => 'Voir les lieux',
                    'update_item' => 'Mettre à jour les lieux',
                    'add_new_item' => 'Ajouter un lieu',
                    'new_item_name' => 'Nouveau lieu',
                    'search_items' => 'Rechercher parmi les lieux',
                    'popular_items' => 'Lieux les plus utilisés'
                ],
                'hierarchical' => true,
                'show_ui' => true,
                'capabilities' => [
                    'manage_terms' => true,
                    'edit_terms' => true,
                    'delete_terms' => true,
                    'assign_terms' => true
                ]
            )
        );

        // On créé la taxonomie "Saisons"
        register_taxonomy(
            'seasons',
            ['page', 'attachment'],
            array(
                'label' => 'Saisons',
                'labels' => [
                    'name' => 'Saisons',
                    'singular_name' => 'Saison',
                    'menu_name' => 'Saisons',
                    'all_items' => 'Toutes les saisons',
                    'edit_item' => 'Modifier les saisons',
                    'view_item' => 'Voir les saisons',
                    'update_item' => 'Mettre à jour les saisons',
                    'add_new_item' => 'Ajouter une saison',
                    'new_item_name' => 'Nouvelle saison',
                    'search_items' => 'Rechercher parmi les saisons',
                    'popular_items' => 'Saisons les plus utilisées'
                ],
                'hierarchical' => true,
                'show_ui' => true,
                'show_in_menu' => false,
                'capabilities' => [
                    'manage_terms' => false,
                    'edit_terms' => false,
                    'delete_terms' => false,
                    'assign_terms' => true
                ]
            )
        );

        // On inclut les termes génériques à la taxo
        wp_insert_term('Été', 'seasons', array('slug' => 'summer'));
        wp_insert_term('Printemps', 'seasons', array('slug' => 'spring'));
        wp_insert_term('Automne', 'seasons', array('slug' => 'autumn'));
        wp_insert_term('Hiver', 'seasons', array('slug' => 'winter'));

        // On créé la taxonomie "Types de média"
        register_taxonomy(
            'attachment_types',
            'attachment',
            array(
                'label' => 'Types de média',
                'labels' => [
                    'name' => 'Types de média',
                    'singular_name' => 'Type de média',
                    'menu_name' => 'Types de média',
                    'all_items' => 'Toutes les types de média',
                    'edit_item' => 'Modifier les types de média',
                    'view_item' => 'Voir les types de média',
                    'update_item' => 'Mettre à jour les types de média',
                    'add_new_item' => 'Ajouter une type de média',
                    'new_item_name' => 'Nouveau type de média',
                    'search_items' => 'Rechercher parmi les types de média',
                    'popular_items' => 'Types de média les plus utilisés'
                ],
                'hierarchical' => false,
                'show_ui' => false,
                'capabilities' => [
                    'manage_terms' => false,
                    'edit_terms' => false,
                    'delete_terms' => false,
                    'assign_terms' => true
                ]
            )
        );

        // On créé la taxonomie "Hashtags"
        register_taxonomy(
            'attachment_hashtags',
            'attachment',
            array(
                'label' => 'Hashtags',
                'labels' => [
                    'name' => 'Hashtags',
                    'singular_name' => 'Hashtag',
                    'menu_name' => 'Hashtags',
                    'all_items' => 'Toutes les hashtags',
                    'edit_item' => 'Modifier les hashtags',
                    'view_item' => 'Voir les hashtags',
                    'update_item' => 'Mettre à jour les hashtags',
                    'new_item_name' => 'Nouveau hashtag',
                    'search_items' => 'Rechercher parmi les hashtags',
                    'popular_items' => 'Hashtags les plus utilisés'
                ],
                'hierarchical' => false,
                'show_ui' => true,
                'capabilities' => [
                    'manage_terms' => true,
                    'edit_terms' => true,
                    'delete_terms' => true,
                    'assign_terms' => true
                ]
            )
        );
    }
}
