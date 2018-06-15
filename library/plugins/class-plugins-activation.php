<?php
/**
 * Activation of required plugins
 *
 * @link https://codex.wordpress.org/Function_Reference/activate_plugin
 * @package HawwwaiTheme
 * @since HawwwaiTheme 1.0.0
 */

class HawwwaiTheme_Plugins_Activation
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
            'wordpress-seo/wp-seo.php',
            'acf-content-analysis-for-yoast-seo/yoast-acf-analysis.php',
            'advanced-custom-fields-font-awesome/acf-font-awesome.php',
            'regenerate-thumbnails/regenerate-thumbnails.php',
            'yoimages/yoimages.php',
            'bea-media-analytics/bea-media-analytics.php',
            'bea-sanitize-filename/bea-sanitize-filename.php',
            'rocket-lazy-load/rocket-lazy-load.php',
            'enhanced-media-library/enhanced-media-library.php',
            'minify-html-markup/minify-html.php',
            'wp-deferred-javascripts/wp-deferred-javascripts.php',
            'advanced-cron-manager/advanced-cron-manager.php',
            'acf-repeater-flexible-content-collapser/acf-repeater-flexible-content-collapser.php'
        ];

        $this->dev_plugins = [
            'debug-bar/debug-bar.php',
            'debug-bar-timber/debug-bar-timber.php',
            'kint-debugger/kint-debugger.php',
            'wp-php-console/wp-php-console.php',
        ];

        $this->deactivate_plugins = [

        ];

        // Enable debug plugins on DEV
        if (WP_ENV == 'dev') {
            $this->activate_plugins = array_merge($this->activate_plugins, $this->dev_plugins);
            $this->dev_plugins = [];
        }

        $this->register_hooks();
    }

    protected function register_hooks()
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

// Execute Class
new HawwwaiTheme_Plugins_Activation();
