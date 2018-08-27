<?php

// Plugins
new WoodyTheme_Plugins_Activation();
new WoodyTheme_Plugins_Options();

// PHP Console
if (!class_exists('PC', false) && WP_ENV == 'dev') {
    PhpConsole\Helper::register();
}

// ACF
new WoodyTheme_ACF();

// Cleanup
new WoodyTheme_Cleanup_Admin();
new WoodyTheme_Cleanup_Front();

// Assets
new WoodyTheme_Enqueue_Assets();

// Content
new WoodyTheme_Cron();
new WoodyTheme_Post_Type();
new WoodyTheme_Taxonomy();
new WoodyTheme_Images();
// new WoodyTheme_Videos();

// Menu
new WoodyTheme_Menus();
