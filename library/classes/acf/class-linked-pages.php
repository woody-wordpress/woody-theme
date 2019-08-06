<?php
/**
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
    }

    public function getAvailablePages()
    {

        $return = [];
        $value = filter_input(INPUT_POST, 'params', FILTER_SANITIZE_STRING);
        $post_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $selected = !empty(get_post_meta($post_id, 'linked_alternative_page')) ? get_post_meta($post_id, 'linked_alternative_page')[0] : '';


        $return[] = [
            'id' => intval($selected),
            'title' => get_the_title($selected),
            'selected' => true
        ];

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
}
