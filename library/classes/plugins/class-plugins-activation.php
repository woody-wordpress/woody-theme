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
        $this->activate_plugins = [
            'advanced-custom-fields-pro/acf.php',
            'acf-relationship-create-pro/acf-relationship-create-pro.php',
            'timber-library/timber.php',
            'advanced-custom-fields-font-awesome/acf-font-awesome.php',
            'bea-media-analytics/bea-media-analytics.php',
            'bea-sanitize-filename/bea-sanitize-filename.php',
            'minify-html-markup/minify-html.php',
            'wp-deferred-javascripts/wp-deferred-javascripts.php',
            'advanced-cron-manager/advanced-cron-manager.php',
            'acf-repeater-flexible-content-collapser/acf-repeater-flexible-content-collapser.php',
            'redirection/redirection.php',
            'permalink-manager/permalink-manager.php',
            'wordpress-seo/wp-seo.php',
            'yoimages/yoimages.php',
            'enhanced-media-library/enhanced-media-library.php',
            'wp-nested-pages/nestedpages.php',
            'members/members.php'
        ];

        $this->dev_plugins = [
            'debug-bar/debug-bar.php',
            'debug-bar-timber/debug-bar-timber.php',
            'kint-debugger/kint-debugger.php',
            'wp-php-console/wp-php-console.php',
            'fakerpress/fakerpress.php',
        ];

        $this->deactivate_plugins = [
            'rocket-lazy-load/rocket-lazy-load.php',
            'acf-content-analysis-for-yoast-seo/yoast-acf-analysis.php',
            'media-file-renamer/media-file-renamer.php',
            'regenerate-thumbnails/regenerate-thumbnails.php',
        ];

        // Enable debug plugins on DEV
        if (WP_ENV == 'dev') {
            $this->activate_plugins = array_merge($this->activate_plugins, $this->dev_plugins);
            $this->dev_plugins = [];
        }

        $this->registerHooks();
    }

    protected function registerHooks()
    {
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
