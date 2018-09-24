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
    }
}
