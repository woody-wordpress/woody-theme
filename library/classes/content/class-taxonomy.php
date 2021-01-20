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
        $this->registerTaxonomy();
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_action('wp_ajax_get_models_for_type', [$this, 'getModelsForTerm']);
        add_action('wp_ajax_get_current_place', [$this, 'getCurrentPlace']);
        add_action('woody_theme_update', [$this, 'woodyThemeUpdate']);
    }

    public function woodyThemeUpdate()
    {
        // On inclut les termes génériques à la taxo
        wp_insert_term('Page d\'accueil', 'page_type', array('slug' => 'front_page'));
        wp_insert_term('Page de contenu', 'page_type', array('slug' => 'basic_page'));
        wp_insert_term('Page miroir', 'page_type', array('slug' => 'mirror_page'));
        wp_insert_term('Séjour', 'page_type', array('slug' => 'trip'));
    }

    public function registerTaxonomy()
    {
        $user = wp_get_current_user();
        if (in_array('administrator', $user->roles)) {
            $is_administrator = true;
        } else {
            $is_administrator = false;
        }

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
                'hierarchical' => true,
                'show_ui' => $is_administrator,
                'show_in_menu' => $is_administrator,
                'capabilities' => [
                    'manage_terms' => 'Configurer les types de publications',
                    'edit_terms' => 'Editer les types de publications',
                    'delete_terms' => 'Supprimer les types de publications',
                    'assign_terms' => 'edit_posts'
                ]
            )
        );

        // On créé la taxonomie "Thématiques"
        register_taxonomy(
            'themes',
            ['page', 'attachment', 'woody_topic'],
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
                'show_in_menu' => true,
                'capabilities' => [
                    'manage_terms' => 'Configurer les thématiques',
                    'edit_terms' => 'Editer les thématiques',
                    'delete_terms' => 'Supprimer les thématiques',
                    'assign_terms' => 'Assigner les thématiques'
                ]
            )
        );

        // On créé la taxonomie "Lieux"
        register_taxonomy(
            'places',
            ['page', 'attachment', 'woody_topic'],
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
                'show_in_menu' => true,
                'capabilities' => [
                    'manage_terms' => 'Configurer les lieux',
                    'edit_terms' => 'Editer les lieux',
                    'delete_terms' => 'Supprimer les lieux',
                    'assign_terms' => 'Assigner les lieux'
                ]
            )
        );

        // On créé la taxonomie "Saisons"
        register_taxonomy(
            'seasons',
            ['page', 'attachment', 'woody_topic'],
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
                'show_in_menu' => true,
                'capabilities' => [
                    'manage_terms' => 'Configurer les saisons',
                    'edit_terms' => 'Editer les saisons',
                    'delete_terms' => 'Supprimer les saisons',
                    'assign_terms' => 'Assigner les saisons'
                ]
            )
        );



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
                'show_in_menu' => false,
            )
        );

        // On créé la taxonomie "Catégories de média"
        register_taxonomy(
            'attachment_categories',
            'attachment',
            array(
                'label' => 'Catégories de média',
                'labels' => [
                    'name' => 'Catégories de média',
                    'singular_name' => 'Catégorie du média',
                    'menu_name' => 'Catégories de média',
                    'all_items' => 'Toutes les catégories de média',
                    'edit_item' => 'Modifier les catégories de média',
                    'view_item' => 'Voir les catégories de média',
                    'update_item' => 'Mettre à jour les catégories de média',
                    'add_new_item' => 'Ajouter une catégorie de média',
                    'new_item_name' => 'Nouvelle catégorie de média',
                    'search_items' => 'Rechercher parmi les catégories de média',
                    'popular_items' => 'Catégories de média les plus utilisés'
                ],
                'hierarchical' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'capabilities' => [
                    'manage_terms' => 'Configurer les catégories de médias',
                    'edit_terms' => 'Editer les catégories de médias',
                    'delete_terms' => 'Supprimer les catégories de médias',
                    'assign_terms' => 'Assigner les catégories de médias'
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
                'show_in_menu' => true,
                'capabilities' => [
                    'manage_terms' => 'Configurer les hashtags',
                    'edit_terms' => 'Editer les hashtags',
                    'delete_terms' => 'Supprimer les hashtags',
                    'assign_terms' => 'Assigner les hashtags'
                ]
            )
        );
    }

    public function getModelsForTerm()
    {
        $posts = [
            'posts' => [],
            'term' => []
        ];

        $term_id = filter_input(INPUT_POST, 'term_id', FILTER_VALIDATE_INT);
        if (!empty($term_id)) {
            $term = get_term($term_id);
            $args = [
                'post_type' => 'woody_model',
                'post_status' => array(
                    'publish',
                    'draft'
                ),
                'posts_per_page' => -1,
                'meta_key' => 'model_linked_post_type',
                'meta_value' => $term_id,
                'meta_compare' => '='
            ];

            $query_result = new WP_Query($args);

            if (!empty($query_result->found_posts)) {
                foreach ($query_result->posts as $post) {
                    $posts['posts'][] = [
                        'ID' => $post->ID,
                        'link' => apply_filters('woody_get_permalink', $post->ID),
                        'title' => $post->post_title
                    ];
                }
                $posts['term'] = $term->name;
            }
        }

        wp_send_json($posts);
        exit;
    }

    /**
     * Get current place associated to post
     */
    public function getCurrentPlace()
    {
        $place = [];
        $post_id = filter_input(INPUT_POST, 'post_id', FILTER_SANITIZE_NUMBER_INT);
        $terms = wp_get_post_terms($post_id, 'places');

        if (!is_wp_error($terms)) {
            foreach ($terms as $term) {
                if (!empty($term->name)) {
                    $place[] = $term->name;
                    wp_send_json($place);
                    exit;
                }
            }
        } else {
            wp_send_json($place);
            exit;
        }
    }
}
