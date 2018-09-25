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
        add_action('after_setup_theme', array($this, 'activatePlugins'), 2);
    }

    public function activatePlugins()
    {
        $this->activate_plugins = [
            'advanced-custom-fields-pro/acf.php',
            'timber-library/timber.php',
            'bea-sanitize-filename/bea-sanitize-filename.php',
            'wp-deferred-javascripts/wp-deferred-javascripts.php',
            'advanced-cron-manager/advanced-cron-manager.php',
            'redirection/redirection.php',
            'permalink-manager-pro/permalink-manager.php',
            'wordpress-seo/wp-seo.php',
            'yoimages/yoimages.php',
            'enhanced-media-library/enhanced-media-library.php',
            'wp-nested-pages/nestedpages.php',
            'members/members.php',
            'woody-plugin/woody.php',
            'single-sign-on-client/wposso.php',
            'ssl-insecure-content-fixer/ssl-insecure-content-fixer.php',
            'wp-optimize/wp-optimize.php',
            'mce-table-buttons/mce_table_buttons.php',
            'acf-content-analysis-for-yoast-seo/yoast-acf-analysis.php',
        ];

        $this->deactivate_plugins = [
            'minify-html-markup/minify-html.php',
            'bea-media-analytics/bea-media-analytics.php',
            'regenerate-thumbnails/regenerate-thumbnails.php',
            'acf-repeater-flexible-content-collapser/acf-repeater-flexible-content-collapser.php',
            'debug-bar/debug-bar.php',
            'debug-bar-timber/debug-bar-timber.php',
            'kint-debugger/kint-debugger.php',
            'fakerpress/fakerpress.php',
            'rocket-lazy-load/rocket-lazy-load.php',
            'media-file-renamer/media-file-renamer.php',
            'acf-relationship-create-pro/acf-relationship-create-pro.php',
        ];

        if (SAVEQUERIES == true) {
            $this->activate_plugins[] = 'query-monitor/query-monitor.php';
            $this->activate_plugins[] = 'wp-php-console/wp-php-console.php';
        } else {
            $this->deactivate_plugins[] = 'query-monitor/query-monitor.php';
            $this->deactivate_plugins[] = 'wp-php-console/wp-php-console.php';
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

        deactivate_plugins($this->deactivate_plugins);
    }
}
