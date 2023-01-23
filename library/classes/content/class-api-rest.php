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
    }

    public function getPagePreviewApiRest() {

        # /wp-json/woody/page/preview?post=${id}
        $post_id = filter_input(INPUT_GET, 'post', FILTER_VALIDATE_INT);

        if(!empty($post_id)) {
            header('xkey: ' . WP_SITE_KEY . '_' . $post_id, false);
            $post = get_post($post_id);

            $wrapper = [
                'display_img' => true,
                'display_elements' => [
                    'icon',
                    'pretitle',
                    'subtitle',
                    'description'
                ],
                'display_button' => true
            ];

            if ($post->post_status == 'publish') {
                $post_preview = [];

                switch ($post->post_type) {
                    case 'touristic_sheet':
                        $post_preview = getTouristicSheetPreview($wrapper, $post);
                        break;
                    default:
                        $post_preview = getPagePreview($wrapper, $post);
                        break;
                }
            }

            return $post_preview;
        }
    }
}