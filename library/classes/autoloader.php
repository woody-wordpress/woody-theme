<?php

// Commands
new WoodyTheme_Commands();

// Plugins
new WoodyTheme_Plugins_Activation();
new WoodyTheme_Plugins_Options();
new WoodyTheme_Plugins_Order();

// ACF
if (WP_ENV == 'dev') {
    new WoodyTheme_ACF_Save_Manager();
}
new WoodyTheme_ACF();
new WoodyTheme_ACF_PrivateGroups();
new WoodyTheme_ACF_Counter();
new WoodyTheme_ACF_ShorLink();

// Cleanup
new WoodyTheme_Cleanup_Minify();
new WoodyTheme_Cleanup_Admin();
new WoodyTheme_Cleanup_Front();
new WoodyTheme_Cleanup_OptionsTable();

// Dashboard
new WoodyTheme_Dashboard();

// Langs
new WoodyTheme_Polylang();

// Assets
new WoodyTheme_Enqueue_Assets();

// Content
new WoodyTheme_Cron();
new WoodyTheme_Post_Type();
new WoodyTheme_Taxonomy();
new WoodyTheme_Images();
new WoodyTheme_Tinymce();
new WoodyTheme_Links();
new WoodyTheme_SiteMap();
new WoodyTheme_Robots();
new WoodyTheme_Yoast();
new WoodyTheme_Varnish();
// new WoodyTheme_Videos();

// Claims
new WoodyTheme_Claims();

// Timber
new WoodyTheme_Twig_Filters();

// Menu
new WoodyTheme_Menus();

// Shortcodes
new WoodyTheme_Shortcodes();

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
