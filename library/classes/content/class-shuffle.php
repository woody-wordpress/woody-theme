<?php

/**
 * CDN
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.25.2
 */

use WoodyProcess\Compilers\WoodyTheme_WoodyCompilers;

class WoodyTheme_Shuffle
{
    public function __construct()
    {
        $this->compilers = new WoodyTheme_WoodyCompilers;
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_action('rest_api_init', function () {
            register_rest_route('woody', 'shuffle/focus', array(
                'methods' => 'GET',
                'callback' => [$this, 'getFocusParams'],
            ));
        });
    }

    public function getFocusParams()
    {
        $return = [];
        $section_index = filter_input(INPUT_GET, 'section_index');
        $block_index = filter_input(INPUT_GET, 'block_index');
        $post_id = filter_input(INPUT_GET, 'post_id', FILTER_VALIDATE_INT);
        $length = filter_input(INPUT_GET, 'length', FILTER_VALIDATE_INT);

        if (isset($section_index) && isset($block_index) && !empty($post_id)) {
            $section = get_field('section', $post_id);
            if (!empty($section[$section_index]) && !empty($section[$section_index]['section_content'][$block_index])) {
                $wrapper = $section[$section_index]['section_content'][$block_index];
                if (!empty($wrapper)) {
                    $current_post = get_post($post_id);
                    if (!empty($length)) {
                        $wrapper['focused_count'] = $length;
                    }

                    $twig_paths = getWoodyTwigPaths();

                    $return = $this->compilers->formatFocusesData($wrapper, $current_post, $twig_paths);
                }
            }
        }

        return $return;
    }
}
