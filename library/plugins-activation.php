<?php
/**
 * Activate required plugins
 */

$plugins = [
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
];

$debug_plugins = [
    'debug-bar/debug-bar.php',
    'debug-bar-timber/debug-bar-timber.php',
    'kint-debugger/kint-debugger.php',
    'wp-php-console/wp-php-console.php',
];

if (WP_ENV == 'dev') {
    $plugins = array_merge($plugins, $debug_plugins);
    $debug_plugins = [];
}

/**
 * ---------------------------------
 */

include_once ( ABSPATH . 'wp-admin/includes/plugin.php' );

foreach ($plugins as $plugin) {
    if (!is_plugin_active( $plugin )) {
        $result = activate_plugin( $plugin );

        if ( !is_wp_error( $result ) ) {
            add_action( 'admin_notices', function() use ($plugin) {
                echo '<div class="notice notice-success"><p>' . sprintf('<strong>%s</strong> plugin is required & auto-enabled by the current theme.', $plugin) . '</p></div>';
            } );
        } else {
            add_action( 'admin_notices', function() use ($plugin) {
                echo '<div class="notice notice-error"><p>' . sprintf('<strong>%s</strong> plugin can\'t be auto-enabled by the current theme.', $plugin) . '</p></div>';
            } );
        }
    }
}

deactivate_plugins($debug_plugins);
