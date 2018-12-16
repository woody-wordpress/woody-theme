<?php
/**
 * Commands
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_Commands
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        \WP_CLI::add_command('woody', [$this, 'flush']);
    }

    public function flush($args)
    {
        do_action('woody_subtheme_update');
        \WP_CLI::success('woody_subtheme_update');

        do_action('woody_theme_update');
        \WP_CLI::success('woody_theme_update');

        // Clear the cache to prevent an update_option() from saving a stale db_version to the cache
        wp_cache_flush();
        \WP_CLI::success('wp_cache_flush');

        // (Not all cache back ends listen to 'flush')
        wp_cache_delete('alloptions', 'options');
        \WP_CLI::success('wp_cache_delete alloptions');
    }
}
