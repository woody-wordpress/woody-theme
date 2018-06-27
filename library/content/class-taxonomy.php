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
        add_action('init', array($this, 'register_content_type_taxonomy'), 0);
    }

    public function register_content_type_taxonomy()
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
                'show_ui' => false,
            )
        );

        // On inclut les termes génériques à la taxo
        wp_insert_term('Actualite', 'page_type', array('slug' => 'article'));
        wp_insert_term('Article de blog', 'page_type', array('slug' => 'blog_article'));
        wp_insert_term('Experience', 'page_type', array('slug' => 'experience'));
        wp_insert_term('Page de contenu', 'page_type', array('slug' => 'basic_page'));
        wp_insert_term('Personne', 'page_type', array('slug' => 'member'));

        // Si le plugin Hawwwai est activé
        // TODO : uncomment the condition when the hawwwai plugin will run
        // if(is_plugin_active('hawwwai')){
        wp_insert_term('Playlist tourisme', 'page_type', array('slug' => 'playlist_tourism'));
        // }
    }
}

// Execute Class
new HawwwaiTheme_Taxonomy();
