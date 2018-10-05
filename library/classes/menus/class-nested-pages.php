<?php
/**
 * NestedPages
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_NestedPages
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_action('nestedpages_post_order_updated', [$this, 'flushPostCache'], 1, 3);
    }

    public static function flushPostCache($post_id, $parent, $key)
    {
        // Flush Object when move post with NestedPage plugin
        clean_post_cache($post_id);

        // Save Permalinks
        // $post = get_post($post_id);
        // $chunk = [[
        //     'ID' => $post_id,
        //     'post_name' => $post->post_name,
        //     'post_title' => $post->post_title,
        // ]];
        // $mode = 'custom_uris';
        // Permalink_Manager_URI_Functions_Post::regenerate_all_permalinks($chunk, $mode);
    }
}
