<?php
/**
 * Activation of required plugins
 *
 * @link https://codex.wordpress.org/Function_Reference/activate_plugin
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_Plugins_Activation
{
    public $activate_plugins;
    public $deactivate_plugins;
    public $dev_plugins;

    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_action('woody_update', array($this, 'activatePlugins'));
    }

    public function activatePlugins()
    {
        $this->activate_plugins = [
            'advanced-custom-fields-pro/acf.php',
            'timber-library/timber.php',
            'bea-media-analytics/bea-media-analytics.php',
            'bea-sanitize-filename/bea-sanitize-filename.php',
            'minify-html-markup/minify-html.php',
            'wp-deferred-javascripts/wp-deferred-javascripts.php',
            'acf-content-analysis-for-yoast-seo/yoast-acf-analysis.php',
            'advanced-cron-manager/advanced-cron-manager.php',
            'acf-repeater-flexible-content-collapser/acf-repeater-flexible-content-collapser.php',
            'redirection/redirection.php',
            'permalink-manager/permalink-manager.php',
            'wordpress-seo/wp-seo.php',
            'yoimages/yoimages.php',
            'enhanced-media-library/enhanced-media-library.php',
            'wp-nested-pages/nestedpages.php',
            'members/members.php',
            'woody-plugin/woody.php',
            'single-sign-on-client/wposso.php',
            'regenerate-thumbnails/regenerate-thumbnails.php',
            'ssl-insecure-content-fixer/ssl-insecure-content-fixer.php',
            'wp-optimize/wp-optimize.php',
            'wp-super-cache/wp-cache.php',
        ];

        $this->dev_plugins = [
            'debug-bar/debug-bar.php',
            'query-monitor/query-monitor.php',
            'wp-php-console/wp-php-console.php',
        ];

        $this->deactivate_plugins = [
            'debug-bar-timber/debug-bar-timber.php',
            'kint-debugger/kint-debugger.php',
            'fakerpress/fakerpress.php',
            'rocket-lazy-load/rocket-lazy-load.php',
            'media-file-renamer/media-file-renamer.php',
            'acf-relationship-create-pro/acf-relationship-create-pro.php',
        ];

        // Enable debug plugins on DEV
        if (WP_ENV == 'dev') {
            $this->activate_plugins = array_merge($this->activate_plugins, $this->dev_plugins);
            $this->dev_plugins = [];
        }

        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        foreach ($this->activate_plugins as $plugin) {
            if (!is_plugin_active($plugin)) {
                $result = activate_plugin($plugin);

                if (!is_wp_error($result)) {
                    add_action('admin_notices', function () use ($plugin) {
                        echo '<div class="notice notice-success"><p>' . sprintf('<strong>%s</strong> plugin is required & auto-enabled by the current theme.', $plugin) . '</p></div>';
                    });
                } else {
                    add_action('admin_notices', function () use ($plugin) {
                        echo '<div class="notice notice-error"><p>' . sprintf('<strong>%s</strong> plugin can\'t be auto-enabled by the current theme.', $plugin) . '</p></div>';
                    });
                }
            }
        }

        deactivate_plugins($this->dev_plugins);
        deactivate_plugins($this->deactivate_plugins);
    }
}
