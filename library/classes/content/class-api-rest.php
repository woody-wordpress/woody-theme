<?php

/**
 * API Rest
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.25.2
 */

class WoodyTheme_Api_Rest
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_action('rest_api_init', function () {
            register_rest_route('woody', 'page/preview', array(
                'methods' => 'GET',
                'callback' => [$this, 'getPagePreviewApiRest']
            ));
        });
        add_action('save_post', [$this, 'savePost'], 10, 3);
    }

    public function getPagePreviewApiRest() {

        # /wp-json/woody/page/preview?post=${id}
        $post_id = filter_input(INPUT_GET, 'post', FILTER_VALIDATE_INT);
        header('xkey: ' . WP_SITE_KEY . '_page_preview', false);

        if(!empty($post_id)) {
            $post = get_post($post_id);

            return getPagePreview($wrapper, $post);
        }
    }

    public function savePost($post_ID, $post, $update)
    {
        if (!empty($post)) {
            do_action('woody_flush_varnish', 'page_preview');
        }
    }
}