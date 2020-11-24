<?php

/**
 * CDN
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.25.2
 */

use WoodyProcess\Compilers\WoodyTheme_WoodyCompilers;
use WoodyProcess\Tools\WoodyTheme_WoodyProcessTools;

class WoodyTheme_Shuffle
{
    public function __construct()
    {
        $this->compilers = new WoodyTheme_WoodyCompilers;
        $this->tools = new WoodyTheme_WoodyProcessTools;

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

                // get_field returns null foreach taxonomy terms, so we define them with get_post_meta
                $wrapper['focused_taxonomy_terms'] = [];
                $focused_terms = get_post_meta($post_id, 'section_'. $section_index .'_section_content_'. $block_index .'_focused_taxonomy_terms');
                if (!empty($focused_terms) && !empty($focused_terms[0])) {
                    foreach ($focused_terms[0] as $term_id) {
                        $wrapper['focused_taxonomy_terms'][] = $term_id;
                    }
                }

                if (!empty($wrapper)) {
                    $current_post = get_post($post_id);
                    if (!empty($length)) {
                        $wrapper['focused_count'] = $length;
                    }

                    $twig_paths = getWoodyTwigPaths();

                    if (!empty($wrapper['visual_effects']['transform'])) {
                        $wrapper['visual_effects'] = $this->tools->formatVisualEffectData($wrapper['visual_effects']);
                    }

                    $return = $this->compilers->formatFocusesData($wrapper, $current_post, $twig_paths);
                }
            }
        }

        return $return;
    }
}
