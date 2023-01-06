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
        add_action('woody_theme_update', [$this, 'woodyThemeUpdate'], 1);
    }

    public function woodyThemeUpdate()
    {
        $this->deactivate_plugins = [
            'wp-php-console/wp-php-console.php',
            'vcaching/vcaching.php',
            'contact-form-7/wp-contact-form-7.php',
            'contact-form-7-mailchimp-extension/chimpmatic-lite.php',
        ];

        $this->activate_plugins = [
            'advanced-cron-manager/advanced-cron-manager.php',
            'advanced-custom-fields-pro/acf.php',
            'disable-embeds/disable-embeds.php',
            'duplicate-post/duplicate-post.php',
            'enhanced-media-library/enhanced-media-library.php',
            'members/members.php',
            'polylang-pro/polylang.php',
            'publish-view/publish-view.php',
            'redirection/redirection.php',
            'ssl-insecure-content-fixer/ssl-insecure-content-fixer.php',
            'woody-acf-sync/woody-acf-sync.php',
            'woody-crop/woody-crop.php',
            'woody-plugin/woody.php',
            'woody-sso/woody-sso.php',
            'wp-deferred-javascripts/wp-deferred-javascripts.php',
            'better-search-replace/better-search-replace.php',
            'enable-media-replace/enable-media-replace.php'
        ];

        switch (WP_ENV) {
            case 'dev':

            case 'preprod':
                // Enable
                $this->activate_plugins[] = 'query-monitor/query-monitor.php';
                break;

            case 'prod':
                if (WOODY_ACCESS_STAGING) {
                    // Enable
                    $this->activate_plugins[] = 'query-monitor/query-monitor.php';
                } else {
                    // Disable
                    $this->deactivate_plugins[] = 'query-monitor/query-monitor.php';
                }

                break;
        }

        // Override plugins activations
        $this->activate_plugins = apply_filters('woody_activate_plugins', $this->activate_plugins);
        $this->deactivate_plugins = apply_filters('woody_deactivate_plugins', $this->deactivate_plugins);

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
                        echo '<div class="notice notice-error"><p>' . sprintf("<strong>%s</strong> plugin can't be auto-enabled by the current theme.", $plugin) . '</p></div>';
                    });
                }
            }
        }

        deactivate_plugins($this->deactivate_plugins);
    }
}
