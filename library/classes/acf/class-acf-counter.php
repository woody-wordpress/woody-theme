<?php
/**
 * ACF Counter
 *
 * @link https://www.advancedcustomfields.com/resources/acf-settings
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_ACF_Counter
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_action('wp_ajax_woody_autofocus_count', [$this, 'autoFocusCount']);
    }

    public function autoFocusCount()
    {
        $return = null;
        $params = $_POST['params'];
        if (is_array($params) && !empty($params['current_post'])) {
            $cache_key = 'woody_afc_' . md5(serialize($params));
            if (false === ($return = wp_cache_get($cache_key, 'woody'))) {
                $tax_query = [
                    'relation' => 'AND'
                ];

                // Création du paramètre tax_query pour la wp_query
                // Référence : https://codex.wordpress.org/Class_Reference/WP_Query
                if (!empty($params['focused_content_type'])) {
                    $tax_query = [
                        'page_type' => [
                            'taxonomy' => 'page_type',
                            'terms' => $params['focused_content_type'],
                            'field' => 'term_id',
                            'operator' => 'IN'
                        ],
                    ];
                }

                // Si des termes ont été choisi pour filtrer les résultats
                // on créé tableau custom_tax à passer au paramètre tax_query
                $custom_tax = [];
                if (!empty($params['focused_taxonomy_terms'])) {

                // On récupère la relation choisie (ET/OU) entre les termes
                    // et on génère un tableau de term_id pour chaque taxonomie
                    $focused_taxonomy_terms_andor = (empty($params['focused_taxonomy_terms_andor'])) ? 'OR' : current($params['focused_taxonomy_terms_andor']);
                    if ($focused_taxonomy_terms_andor == 'OR') {
                        $tax_query['custom_tax']['relation'] = 'OR';
                        // Get terms
                        foreach ($params['focused_taxonomy_terms'] as $focused_term_id) {
                            $focused_term = get_term($focused_term_id);
                            $custom_tax[$focused_term->taxonomy][] = $focused_term_id;
                        }

                        foreach ($custom_tax as $taxo => $terms) {
                            $tax_query['custom_tax'][] = array(
                                'taxonomy' => $taxo,
                                'terms' => $terms,
                                'field' => 'term_id',
                                'operator' => 'IN'
                            );
                        }
                    } else {
                        foreach ($params['focused_taxonomy_terms'] as $focused_term_id) {
                            $focused_term = get_term($focused_term_id);
                            $tax_query['custom_tax_'.$focused_term_id] = array(
                                'taxonomy' => $focused_term->taxonomy,
                                'terms' => $focused_term_id,
                                'field' => 'term_id',
                                'operator' => 'IN'
                            );
                        }
                    }
                }

                // On créé la wp_query en fonction des choix faits dans le backoffice
                // NB : si aucun choix n'a été fait, on remonte automatiquement tous les contenus de type page
                $the_query = [
                    'post_type' => (empty($params['focused_post_type'])) ? 'page' : $params['focused_post_type'],
                    'tax_query' => $tax_query,
                    'post_status' => 'publish',
                    'post__not_in' => array($params['current_post']),
                    'posts_per_page' => (empty($params['focused_count'])) ? 16 : (int) current($params['focused_count'])
                ];

                if (!empty($params['focused_hierarchy'])) {
                    if (current($params['focused_hierarchy']) == 'child_of') {
                        $the_query['post_parent'] = $params['current_post'];
                    } elseif (current($params['focused_hierarchy']) == 'brother_of') {
                        // Si Hiérarchie = Enfants directs de la page
                        // On passe le post ID dans le paramètre post_parent de la query
                        $post_parent = wp_get_post_parent_id($params['current_post']);
                        $the_query['post_parent'] = $post_parent;
                    }
                }

                // It wasn't there, so regenerate the data and save the cache
                $focused_posts = new \WP_Query($the_query);
                $return = $focused_posts->post_count;
                wp_cache_set($cache_key, $return, 'woody', 2*60);
            }
        }

        $this->JsonResponse($return);
    }

    private function JsonResponse($response)
    {
        if (!is_null($response)) {
            wp_send_json($response);
        } else {
            header("HTTP/1.0 400 Bad Request");
            die();
        }
    }
}
