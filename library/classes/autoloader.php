<?php

// Define PLL_DEFAULT_LANG
define('PLL_DEFAULT_LANG', function_exists('pll_default_language') ? pll_default_language() : 'fr');

// Define PLL_DEFAULT_LOCALE
if (function_exists('pll_languages_list')) {
    $languages = pll_languages_list(['fields' => '']);
    $default_locale = 'fr_FR';
    foreach ($languages as $language) {
        if ($language->slug == PLL_DEFAULT_LANG) {
            $default_locale = $language->locale;
            break;
        }
    }
    define('PLL_DEFAULT_LOCALE', $default_locale);
}


// Commands & Helpers
new WoodyTheme_Commands();
new WoodyTheme_cleanDataBases();

// Query
new WoodyTheme_Query();

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
new WoodyTheme_ACF_ShortLink();

// Cleanup
new WoodyTheme_Cleanup_Admin();
new WoodyTheme_Cleanup_Front();
new WoodyTheme_Cleanup_Minify();
new WoodyTheme_Cleanup_OptionsTable(); //Disable
new WoodyTheme_Cleanup_OptimizeBDD(); // Disable
new WoodyTheme_Cleanup_Schedule();

// Dashboard
new WoodyTheme_Dashboard();

// Assets
new WoodyTheme_Enqueue_Assets();

// Langs
new WoodyTheme_Polylang();

// Clouflare CDN
new WoodyTheme_CDN();

// Content
new WoodyTheme_Api_Rest();
new WoodyTheme_Cron();
new WoodyTheme_IsMobile();
new WoodyTheme_Links();
new WoodyTheme_Page();
new WoodyTheme_Permalink();
new WoodyTheme_Post_Type();
new WoodyTheme_Profiles();
new WoodyTheme_PWA();
new WoodyTheme_Robots();
new WoodyTheme_Seo();
new WoodyTheme_Taxonomy();
new WoodyTheme_Tinymce();
new WoodyTheme_Unpublisher(); //Disable
new WoodyTheme_Shuffle();
new WoodyTheme_Videos();
new WoodyTheme_Testimonials();

// Timber
new WoodyTheme_Timber_Filters();

// Menu
if (!defined('WOODY_MENUS_V2')) {
    new WoodyTheme_Menus();
    if (defined('WOODY_GENERATE_MENU')) {
        new Woody\Menus\Admin_Menus();
    }
}

// Shortcodes
new WoodyTheme_Shortcodes();

// Roles
new WoodyTheme_Roles();

// Inclusions
new WoodyTheme_Inclusions();

// Mailer
new WoodyTheme_Mailer();

// Data Processing
new WoodyProcess\Compilers\WoodyTheme_WoodyCompilers();
new WoodyProcess\Getters\WoodyTheme_WoodyGetters();
new WoodyProcess\Process\WoodyTheme_WoodyProcess();
new WoodyProcess\Tools\WoodyTheme_WoodyProcessTools();
