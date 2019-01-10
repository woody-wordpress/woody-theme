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
        add_action('init', array($this, 'registerClaims'), 10);
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
        $data = [];
        $template = '';
        $post_id = '';
        $url = $request->get_body();
        if (!empty($url)) {
            $post_id = url_to_postid($url);
        }

        if (!is_numeric($post_id)) {
            return;
        }

        // $post_id = url_to_postid($url);
        add_filter('posts_where', [$this, 'postsWhereClaimLinkedPostId']);
        // WP Query to get every claims linked to the page
        $query_args = [
            'post_type' => 'woody_claims',
            'post_status' => 'publish',
            'orderby' => 'rand',
            'meta_key' => 'claim_linked_pages_$_claim_linked_post_id',
            'meta_value'	=> $post_id,
            'meta_compare' => '='
        ];

        $results = new WP_Query($query_args);
        if (empty($results->post_count)) {
            return;
        }


        $template = get_field('claim_woody_tpl', $results->post->ID);
        $data = get_field('claim_background_parameters', $results->post->ID);
        $data['items'] = get_field('claim_slides', $results->post->ID);
        $woody_components = getWoodyTwigPaths();
        $return = Timber::compile($woody_components[$template], $data);

        return $return;
    }

    public function postsWhereClaimLinkedPostId($where)
    {
        $where = str_replace("meta_key = 'claim_linked_pages_$", "meta_key LIKE 'claim_linked_pages_%", $where);

        return $where;
    }
}
