<?php

/**
** This is our main taxonomy needed to sort types of pages.
** For this reason, we've set show_ui to false, then you can't access to this taxonomy by the backoffice.
** To add a new term in this taxonomy, please add in the wp_captain.yml : term create page_type "TERM NAME" --slug=TERM_SLUG
**/

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
