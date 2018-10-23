<?php

// Plugins
new WoodyTheme_Plugins_Activation();
new WoodyTheme_Plugins_Options();
new WoodyTheme_Plugins_Order();

// PHP Console
if (!class_exists('PC', false) && class_exists('PhpConsole', false) && WP_ENV == 'dev') {
    PhpConsole\Helper::register();
}

// ACF
if (WP_ENV == 'dev') {
    new WoodyTheme_ACF_Save_Manager();
}
new WoodyTheme_ACF();
new WoodyTheme_ACF_Counter();

// Cleanup
new WoodyTheme_Cleanup_Minify();
new WoodyTheme_Cleanup_Admin();
new WoodyTheme_Cleanup_Front();
new WoodyTheme_Cleanup_Permalink();

// Dashboard
new WoodyTheme_Dashboard();

// Assets
new WoodyTheme_Enqueue_Assets();

// Content
new WoodyTheme_Cron();
new WoodyTheme_Post_Type();
new WoodyTheme_Taxonomy();
new WoodyTheme_Images();
new WoodyTheme_Tinymce();
// new WoodyTheme_Videos();
// new WoodyTheme_Yoast();

// Menu
new WoodyTheme_Menus();

// Roles
new WoodyTheme_Roles();

// Execute hook_update like Drupal if theme version change
add_action('init', 'woodyThemeCheckThemeVersion', 1);
function woodyThemeCheckThemeVersion()
{
    $current_theme_version = wp_get_theme(get_template())->get('Version');
    $old_theme_version = get_option('woody_theme_version');
    if ($old_theme_version !== $current_theme_version) {
        // Call all hooks
        do_action('woody_theme_update');

        // update not to run twice
        update_option('woody_theme_version', $current_theme_version, true);
    }

    $current_subtheme_version = wp_get_theme()->get('Version');
    $old_subtheme_version = get_option('woody_subtheme_version');
    if ($old_subtheme_version !== $current_subtheme_version) {
        // Call all hooks
        do_action('woody_subtheme_update');

        // update not to run twice
        update_option('woody_subtheme_version', $current_subtheme_version, true);
    }
}
