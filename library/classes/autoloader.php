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
//TODO: A supprimer après le merge de la feature RemovingYoast
new WoodyTheme_Cleanup_OptionsTable();

// Dashboard
new WoodyTheme_Dashboard();

// Assets
new WoodyTheme_Enqueue_Assets();

// Langs
new WoodyTheme_Polylang();

// Content
new WoodyTheme_Cookies();
new WoodyTheme_Permalink();
new WoodyTheme_Cron();
new WoodyTheme_Post_Type();
new WoodyTheme_Taxonomy();
new WoodyTheme_Images();
new WoodyTheme_Tinymce();
new WoodyTheme_Links();
new WoodyTheme_SiteMap();
new WoodyTheme_Robots();
new WoodyTheme_Seo();
new WoodyTheme_Varnish();
new WoodyTheme_Unpublisher();
// new WoodyTheme_Videos();

// Timber
new WoodyTheme_Timber_Filters();

// Menu
new WoodyTheme_Menus();

// Shortcodes
new WoodyTheme_Shortcodes();

// Roles
new WoodyTheme_Roles();

// Inclusions
new WoodyTheme_Inclusions();

// Data Processing
new WoodyProcess\Tools\WoodyTheme_WoodyProcessTools();
new WoodyProcess\Getters\WoodyTheme_WoodyGetters();
new WoodyProcess\Compilers\WoodyTheme_WoodyCompilers();
new WoodyProcess\Process\WoodyTheme_WoodyProcess();
