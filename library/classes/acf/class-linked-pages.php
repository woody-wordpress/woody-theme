<?php
/**
 * @author Jérémy Legendre
 * @copyright Raccourci Agency 2019
 *
 * ACF LinkedPages
 *
 * @link https://www.advancedcustomfields.com/resources/acf-settings
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_ACF_LinkedPages
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_action('init', [$this, 'registerTaxonomy']);
        add_action('woody_theme_update', [$this, 'setPostTaxonomyTerm']);
        add_action('acf/load_field/key=field_5d7f57f2b21f7', [$this, 'getAvailablePages'], 10, 1);
        add_action('post_updated', [$this, 'removeLinkBetweenPages'], 10, 3);
        add_action('save_post', [$this, 'setLinkBetweenPages'], 100, 1);

        add_action('wp_ajax_set_post_term', [$this, 'setPostTerms']);
        add_action('wp_ajax_redirect_prepare_onspot', [$this, 'redirectToLinkedPage']);
        add_action('wp_ajax_get_opposite', [$this, 'getOpposite']);
        add_action('wp_ajax_get_destination_coord', [$this, 'getDestinationCoordinates']);
    }

    public function registerTaxonomy()
    {
        register_taxonomy(
            'prepare_onspot',
            'page',
            array(
                'label' => 'Type préparation / sur place',
                'labels' => [],
                'hierarchical' => false,
                'show_ui' => false,
                'show_in_menu' => false
            )
        );

        wp_insert_term('prepare', 'prepare_onspot', array('slug' => 'prepare'));
        wp_insert_term('spot', 'prepare_onspot', array('slug' => 'prepare'));
    }

    /**
     * Set all pages default value on woody theme update
     */
    public function setPostTaxonomyTerm()
    {
        $args = array(
            'post_type' => 'page',
            'post_status' => array(
                'publish',
                'draft'
            ),
            'posts_per_page' => -1
        );

        $result = new \WP_Query($args);

        foreach ($result->posts as $post) {
            $type = get_field('field_5d47d14bdf764', $post->ID) ? 'prepare' : 'spot' ;
            $term = get_term_by('slug', $type, 'prepare_onspot');
            wp_set_post_terms($post->ID, $term->slug, 'prepare_onspot');
        }
    }

    /**
     * Set taxonomy term for wp query
     * @param field
     * @return field with tax value updated
     */
    public function getAvailablePages($field)
    {
        $post_id = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
        $type = get_field('field_5d47d14bdf764', $post_id) ? 'spot' : 'prepare' ;
        $field['taxonomy'] = ["prepare_onspot:".$type];

        return $field;
    }

    /**
     * Remove link between two pages
     * @param post_id
     * @param post_after
     * @param post_before post before save post
     */
    public function removeLinkBetweenPages($post_id, $post_after, $post_before)
    {
        $old_linked_post = get_field('field_5d7f57f2b21f7', $post_before);
        if ($old_linked_post) {
            update_field('field_5d7f57f2b21f7', '', $old_linked_post->ID);
        }
    }

    /**
     * Update Link between pages
     * @param post_id
     */
    public function setLinkBetweenPages($post_id)
    {
        $type = get_field('field_5d47d14bdf764', $post_id) ;
        $type_value = $type ? 'prepare' : 'spot';
        $term = get_term_by('slug', $type_value, 'prepare_onspot');
        wp_set_post_terms($post_id, $term->slug, 'prepare_onspot');

        if (!wp_is_post_revision($post_id)) {
            // set linked page opposite (if current is preparation post, other must be on spot page )
            $opposite = $type === false ? true : false;

            $linked_post = get_field('field_5d7f57f2b21f7', $post_id);
            if ($linked_post) {
                // Page that we  want to connect with current could already be related.
                // Remove the link with this other page
                $linked_post_related = get_field('field_5d7f57f2b21f7', $linked_post->ID) ;
                if (!empty($linked_post_related)) {
                    update_field('field_5d7f57f2b21f7', '', $linked_post_related->ID);
                }

                update_field('field_5d47d14bdf764', $opposite, $linked_post->ID);
                update_field('field_5d7f57f2b21f7', get_post($post_id), $linked_post->ID);

                $type_value = $opposite ? 'prepare' : 'spot';
                $term = get_term_by('slug', $type_value, 'prepare_onspot');
                wp_set_post_terms($linked_post->ID, $term->slug, 'prepare_onspot');
            }
        }
    }

    /**
     * Update post taxonomy (prepare OR spot)
     * AJAX Call on switcher change (Back Office)
     * Usefull for load_field field_5d7f57f2b21f7 ( retreive posts based on current value of prepare/onspot switcher)
     */
    public function setPostTerms()
    {
        $data = filter_input(INPUT_POST, 'params');
        $post_id = filter_input(INPUT_POST, 'post_id');

        $value = $data == 'prepare' ? true : false ;
        $update = update_field('field_5d47d14bdf764', $value, $post_id);

        wp_send_json($update);
    }

    /**
     * Return url of the linked page if she exists on click on switcher
     * AJAX call on swicther change (Front-End)
     */
    public function redirectToLinkedPage()
    {
        $switcher = filter_input(INPUT_POST, 'params', FILTER_VALIDATE_BOOLEAN);
        $post_id = filter_input(INPUT_POST, 'post_id');
        $field = get_field('field_5d47d14bdf764', $post_id);

        if ($field !== $switcher) {
            $linked_post = get_field('field_5d7f57f2b21f7', $post_id);
            $permalink = get_permalink($linked_post);
            wp_send_json($permalink);
        }
        exit;
    }

    /**
     * Return if page have an opposite page
     * AJAX Call
     */
    public function getOpposite()
    {
        $return = false;

        $post_id = filter_input(INPUT_POST, 'post_id');
        $linked_post = get_field('field_5d7f57f2b21f7', $post_id);
        $return = !empty($linked_post) ? true : $return ;

        wp_send_json($return);
        exit;
    }

    /**
     * Get website destination coordinates (latitude and longitude)
     * AJAX Call on first visit of website, to see if user is near destination
     */
    public function getDestinationCoordinates()
    {
        $coord = [];
        // TODO: get woody global vars...
        // $coord['lat'] = WOODY_DESTINATION_LATITUDE;
        // $coord['lon'] = WOODY_DESTINATION_LONGITUDE;

        $coord['lat'] = 51.507351;
        $coord['lon'] = -0.127758;

        wp_send_json($coord);
    }
}
