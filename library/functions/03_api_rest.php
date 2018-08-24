<?php

function countAutofocusEl(\WP_REST_Request $request)
{
    $params = $request->get_params();

    // Création du paramètre tax_query pour la wp_query
    // Référence : https://codex.wordpress.org/Class_Reference/WP_Query
    $tax_query = [
            'relation' => 'AND',
            'page_type' => array(
            'taxonomy' => 'page_type',
            'terms' => $params['focused_content_type'],
            'field' => 'taxonomy_term_id',
            'operator' => 'IN'
        ),
    ];

    // Si des termes ont été choisi pour filtrer les résultats
    // on créé tableau custom_tax à passer au paramètre tax_query
    $custom_tax = [];
    if (!empty($params['focused_taxonomy_terms'])) {

        // On récupère la relation choisie (ET/OU) entre les termes
        // et on génère un tableau de term_id pour chaque taxonomie
        $tax_query['custom_tax']['relation'] = (!empty($params['focused_taxonomy_terms_andor'])) ? $params['focused_taxonomy_terms_andor'] : 'OR';
        foreach ($params['focused_taxonomy_terms'] as $focused_term_key => $focused_term) {
            $term = get_term($focused_term);
            $custom_tax[$term->taxonomy][] = $focused_term;
        }
        foreach ($custom_tax as $taxo => $terms) {
            $tax_query['custom_tax'][] = array(
                'taxonomy' => $taxo,
                'terms' => $terms,
                'field' => 'taxonomy_term_id',
                'operator' => 'IN'
            );
        }
    }

    // On créé la wp_query en fonction des choix faits dans le backoffice
    // NB : si aucun choix n'a été fait, on remonte automatiquement tous les contenus de type page
    $the_query = [
        'post_type' => (!empty($params['focused_post_type'])) ? $params['focused_post_type'] : 'page',
        'tax_query' => $tax_query,
        'nopaging' => true,
        'posts_per_page' => (!empty($params['focused_count'])) ? $params['focused_count'] : -1
    ];

    // Si Hiérarchie = Enfants directs de la page
    // On passe le post ID dans le paramètre post_parent de la query
    $post_parent = wp_get_post_parent_id($params['current_post']);

    if ($params['focused_hierarchy'][0] == 'child_of') {
        $the_query['post_parent'] = $params['current_post'];
    } elseif ($params['focused_hierarchy'][0] == 'brother_of') {
        $the_query['post_parent'] = $post_parent;
    }

    // On créé la wp_query avec les paramètres définis
    $focused_posts = new WP_Query($the_query);
    $focused_posts_count = $focused_posts->post_count;

    return $focused_posts_count;
}

add_action('rest_api_init', function () {
    register_rest_route('woody', 'autofocus-count', array(
    'methods' => 'POST',
    'callback' => 'countAutofocusEl',
  ));
});
