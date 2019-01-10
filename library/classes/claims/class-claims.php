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
        $url = $request->get_body();
        if (!empty($url)) {
            $post_ID = url_to_postid($url);
            $ancestors = getPostAncestors($post_ID, false);
        }

        if (!is_numeric($post_ID)) {
            return;
        }

        $results = get_transient('woody_claims');

        if (empty($results)) {
            $query_args = [
                'post_type' => 'woody_claims',
                'post_status' => 'publish',
                'orderby' => 'rand',
                'meta_key' => 'claim_linked_pages_$_claim_linked_post_ID',
                'meta_value'	=> $post_ID,
                'meta_compare' => '='
            ];

            $results = new WP_Query($query_args);
            set_transient('woody_claims', $results);
        }

        if (!empty($results->posts)) {
            $woody_components = getWoodyTwigPaths();
            foreach ($results->posts as $post) {
                $template = get_field('claim_woody_tpl', $post->ID);
                $data = get_field('claim_background_parameters', $post->ID);
                $data['items'] = get_field('claim_slides', $post->ID);
                if (empty($template || empty($data))) {
                    continue;
                }
                $linked_pages = get_field('claim_linked_pages', $post->ID);
                if (empty($linked_pages)) {
                    return;
                }
                foreach ($linked_pages as $linked_page) {
                    if (is_array($ancestors)) {
                        if ($linked_page['claim_linked_page_hierarchy'] && in_array($linked_page['claim_linked_post_ID'], $ancestors)) {
                            $return[] = Timber::compile($woody_components[$template], $data);
                        }
                    } elseif ($ancestors === $post_ID) {
                        $return[] = Timber::compile($woody_components[$template], $data);
                    } else {
                        return;
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
