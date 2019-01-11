<?php
/**
 * Claims
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_Claims
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_action('init', [$this, 'registerClaims']);
        add_action('save_post', [$this, 'resetWoodyClaimsTransient'], 10, 3);
        add_action('rest_api_init', function () {
            register_rest_route('woody', 'claims-blocks', array(
                'methods' => 'POST',
                'callback' => [$this, 'addClaimsBlocks']
            ));
        });
    }

    public function registerClaims()
    {
        $woody_claims = array(
            'label'               => 'Blocs de publicité',
            'description'         => 'Contenus pub à afficher sur des pages',
            'labels'              => array(
                'name'                => 'Blocs de publicité',
                'singular_name'       => 'Bloc de publicité',
                'menu_name'           => 'Publicités',
                'all_items'           => 'Tous les blocs de publicité',
                'view_item'           => 'Voir les blocs de publicité',
                'add_new_item'        => 'Ajouter bloc de publicité',
                'add_new'             => 'Ajouter un bloc de publicité',
                'edit_item'           => 'Editer le bloc de publicité',
                'update_item'         => 'Modifier le bloc de publicité',
                'search_items'        => 'Rechercher un bloc de publicité',
                'not_found'           => 'Non trouvé',
                'not_found_in_trash'  => 'Non trouvé dans la corbeille',
            ),
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'supports'            => array('title', 'custom-fields'),
            'show_in_menu'        => true,
            'menu_icon'           => 'dashicons-admin-comments',
            'menu_position'       => 30,
            'show_in_nav_menus'   => false
        );

        if (WP_SITE_KEY == 'superot' || WP_SITE_KEY == 'sarlat') {
            register_post_type('woody_claims', $woody_claims);
        }
    }

    public function addClaimsBlocks(\WP_REST_Request $request)
    {
        $return = [];
        $post_ID = '';
        // L'url courante passée en data dans l'appel ajax
        $url = $request->get_body();

        // Pas d'url, pas de retour !
        if (empty($url)) {
            return false;
        }

        // On récupère l'ID de la page courante + ID des tous ses parents
        $post_ID = url_to_postid($url);
        $ancestors = getPostAncestors($post_ID);

        // Si le post ID n'es pas numérique => return
        if (!is_numeric($post_ID)) {
            return;
        }

        // On récupère les résulats dans un transient. Si transient inexistant on le créé
        $results = get_transient('woody_claims');
        if (empty($results)) {
            // On récupère tous les blocs de pub publiés
            $query_args = [
                'post_type' => 'woody_claims',
                'post_status' => 'publish',
                'orderby' => 'rand'
            ];

            $results = new WP_Query($query_args);
            set_transient('woody_claims', $results);
        }

        if (!empty($results->posts)) {
            $woody_components = getWoodyTwigPaths();
            foreach ($results->posts as $post) {
                // On récupère la valeur des champs du bloc pub
                $template = get_field('claim_woody_tpl', $post->ID);
                $data = get_field('claim_background_parameters', $post->ID);
                $data['block_ID'] =  $post->ID;
                $data['items'] = get_field('claim_slides', $post->ID);
                $linked_pages = get_field('claim_linked_pages', $post->ID);

                // S'il n'y a pas de pub dans le bloc, on passe au suivant
                if (empty($template || empty($data))) {
                    continue;
                }

                foreach ($linked_pages as $linked_page) {
                    // Si le bloc n'est lié à aucune page, on passe au suivant
                    if (empty($linked_page['claim_linked_post_id'])) {
                        continue;
                    }


                    if (($linked_page['claim_linked_post_id'] === $post_ID)) {
                        // Si un bloc de pub est lié à la page courante => on ajoute l'html du bloc au return
                        $return[] = Timber::compile($woody_components[$template], $data);
                    } elseif (!empty($ancestors)) {  // Sinon, la page courante a des parents
                        // Si un bloc de pub est lié à un des parents avec "Afficher sur tous ses enfants" actif => on ajoute l'html du bloc au return
                        if ($linked_page['claim_linked_page_hierarchy'] && in_array($linked_page['claim_linked_post_id'], $ancestors)) {
                            $return[] = Timber::compile($woody_components[$template], $data);
                        }
                    } else { // Sinon, on passe au lien suivant
                        continue;
                    }
                }
            }
        }

        return $return;
    }

    public function resetWoodyClaimsTransient($post_ID, $post, $update)
    {
        if ($post->post_type === 'woody_claims') {
            delete_transient('woody_claims');
        }
    }
}
