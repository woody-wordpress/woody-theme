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

    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_action('woody_theme_update', [$this, 'activatePlugins'], 1);
    }

    public function activatePlugins()
    {
        $this->deactivate_plugins = [];
        $this->activate_plugins = [
            'advanced-custom-fields-pro/acf.php',
            'timber-library/timber.php',
            'bea-sanitize-filename/bea-sanitize-filename.php',
            'wp-deferred-javascripts/wp-deferred-javascripts.php',
            'advanced-cron-manager/advanced-cron-manager.php',
            'redirection/redirection.php',
            // 'wordpress-seo/wp-seo.php',
            'enhanced-media-library/enhanced-media-library.php',
            'members/members.php',
            'ssl-insecure-content-fixer/ssl-insecure-content-fixer.php',
            'mce-table-buttons/mce_table_buttons.php',
            // 'acf-content-analysis-for-yoast-seo/yoast-acf-analysis.php',
            'duplicate-post/duplicate-post.php',
            'polylang-pro/polylang.php',
            'publish-view/publish-view.php',
            'woody-plugin/woody.php',
            'woody-acf-sync/woody-acf-sync.php',
            'woody-crop/woody-crop.php',
            'woody-sso/woody-sso.php',
        ];

        switch (WP_ENV) {
            case 'dev':
                // Enable
                $this->activate_plugins[] = 'query-monitor/query-monitor.php';
                $this->activate_plugins[] = 'wp-php-console/wp-php-console.php';

                // Disable
                $this->deactivate_plugins[] = 'vcaching/vcaching.php';
                break;

            case 'preprod':
                // Enable
                $this->activate_plugins[] = 'vcaching/vcaching.php';
                $this->activate_plugins[] = 'query-monitor/query-monitor.php';

                // Disable
                $this->deactivate_plugins[] = 'wp-php-console/wp-php-console.php';
                break;

            case 'prod':
                // Enable
                $this->activate_plugins[] = 'vcaching/vcaching.php';

                // Disable
                $this->deactivate_plugins[] = 'query-monitor/query-monitor.php';
                $this->deactivate_plugins[] = 'wp-php-console/wp-php-console.php';
                break;
        }

        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        foreach ($this->activate_plugins as $plugin) {
            if (!is_plugin_active($plugin) && file_exists(WP_PLUGINS_DIR . '/' . $plugin)) {
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
