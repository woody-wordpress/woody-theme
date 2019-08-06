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
        add_action('wp_ajax_woody_get_available_link_page', [$this, 'getAvailablePages']);
        add_action('save_post', [$this, 'setLinkBetweenPages'], 100, 1);
    }

    /**
     * Get all pages that could match the current.
     * If current is a preparation version, it returns on the spot pages and vice versa.
     *
     * AJAX Call
     * @return return array containing post IDs and title to create options in the select field
     */
    public function getAvailablePages()
    {
        $return = [];
        $value = filter_input(INPUT_POST, 'params', FILTER_SANITIZE_STRING);
        $post_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $selected = !empty(get_post_meta($post_id, 'linked_alternative_page')) ? get_post_meta($post_id, 'linked_alternative_page')[0] : '';

        if (!empty($selected)) {
            // Add and set to selected the already chosen value
            $return[] = [
                'id' => intval($selected),
                'title' => get_the_title($selected),
                'selected' => true
            ];
        }

        if (!empty($value)) {
            $args = array(
                'post_status' => array(
                    'draft',
                    'publish'
                ),
                'posts_per_page' => -1,
                'post_type' => 'page',
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'relation' => 'OR',
                        array(
                            'key' => 'choice_prepare_on_the_spot',
                            'value' => $value,
                            'compare' => '!='
                        ),
                        array(
                            'key' => 'choice_prepare_on_the_spot',
                            'compare' => 'NOT EXISTS',
                            'value' => ''
                        ),
                    ),
                    array(
                        'key' => 'linked_alternative_page',
                        'compare' => 'NOT EXISTS',
                        'value' => ''
                    )
                ),
                'post__not_in' => array(
                    $post_id
                )
            );

            $query_result = new \WP_Query($args);

            if (!empty($query_result->posts)) {
                foreach ($query_result->posts as $post) {
                    $title = !empty($post->post_title) ? $post->post_title : 'Sans titre';

                    $return[] = [
                        'id'=> $post->ID,
                        'title'=> $title,
                        'selected'=> false
                    ];
                }
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

    public function setLinkBetweenPages($post_id)
    {
        // TODO: if linked post has been changed, should remove link to the older page that was linked to it

        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }

        $page_version = !empty(get_post_meta($post_id, 'choice_prepare_on_the_spot')) ? get_post_meta($post_id, 'choice_prepare_on_the_spot')[0] : '' ;
        $linked_post = !empty(get_post_meta($post_id, 'linked_alternative_page')) ? get_post_meta($post_id, 'linked_alternative_page')[0] : '' ;

        if( !empty( $page_version ) && !empty( $linked_post )) {
            // Set other page meta only if linked post is of opposite type of current post
            $opposite_version = !empty(get_post_meta($linked_post, 'choice_prepare_on_the_spot')) ? get_post_meta($linked_post, 'choice_prepare_on_the_spot')[0] : '' ;

            if( $opposite_version == $page_version ){
                update_post_meta($post_id, 'linked_alternative_page', '');
            }else {
                $opposite = $page_version == "spot" ? "prepare" : "spot";
                update_post_meta($linked_post, 'choice_prepare_on_the_spot', $opposite);
                update_post_meta($linked_post, 'linked_alternative_page', $post_id);
                update_post_meta($linked_post, '_linked_alternative_page', 'field_5d47d332df765');

            }
        }
    }
}
