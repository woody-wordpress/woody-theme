<?php
/**
 * Taxonomy
 *
 * @link https://www.advancedcustomfields.com/resources/acf-settings
 * @package HawwwaiTheme
 * @since HawwwaiTheme 1.0.0
 */

class HawwwaiTheme_Post_Type
{
    public function __construct()
    {
        $this->register_hooks();
    }

    protected function register_hooks()
    {
        //if (!empty($plugin_hawwwai_kernel)) {
        add_action('init', array($this, 'register_tourism_sheet_post_type'), 0);
        //}
    }

    public function register_tourism_sheet_post_type()
    {
        // On rentre les différentes dénominations de notre custom post type qui seront affichées dans l'administration
        $labels = array(
            // Le nom au pluriel
            'name'                => 'Fiche SIT',
            // Le nom au singulier
            'singular_name'       => 'Fiche SIT',
            // Le libellé affiché dans le menu
            'menu_name'           => 'Fiches SIT',
            // Les différents libellés de l'administration
            'all_items'           => 'Toutes les fiches SIT',
            'view_item'           => 'Voir les fiches SIT',
            'add_new_item'        => 'Ajouter une fiche SIT',
            'add_new'             => 'Ajouter',
            'edit_item'           => 'Editer la fiche SIT',
            'update_item'         => 'Modifier la fiche SIT',
            'search_items'        => 'Rechercher une fiche SIT',
            'not_found'           => 'Non trouvée',
            'not_found_in_trash'  => 'Non trouvée dans la corbeille',
        );

        // On peut définir ici d'autres options pour notre custom post type
        $args = array(
        'label'               => 'Fiches SITs',
        'description'         => 'Imports des fiches depuis SIT source',
        'labels'              => $labels,
        // On définit les options disponibles dans l'éditeur de notre custom post type ( un titre, un auteur...)
        'supports'            => array( 'title', 'custom-fields'),
        /*
        * Différentes options supplémentaires
        */
        'hierarchical'        => false,
        'public'              => true,
        'has_archive'         => false,
        'rewrite'			  => array( 'slug' => 'touristic_sheet'),
        'show_in_menu'        => true,
        'show_in_nav_menus'   => false

    );

        // On enregistre notre custom post type qu'on nomme ici "serietv" et ses arguments
        register_post_type('touristic_sheet', $args);
    }
}

// Execute Class
new HawwwaiTheme_Post_Type();
