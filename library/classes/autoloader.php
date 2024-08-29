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


// Commands
new Woody\WoodyTheme\library\classes\content\commands\Commands();

// Query
new Woody\WoodyTheme\library\classes\content\query\Query();

// Plugins
new Woody\WoodyTheme\library\classes\content\plugins\Activation();
new Woody\WoodyTheme\library\classes\content\plugins\Options();
new Woody\WoodyTheme\library\classes\content\plugins\Order();

// ACF
if (WP_ENV == 'dev') {
    new Woody\WoodyTheme\library\classes\content\acf\SaveManager();
}

new Woody\WoodyTheme\library\classes\content\acf\Main();
new Woody\WoodyTheme\library\classes\content\acf\PrivateGroups();
new Woody\WoodyTheme\library\classes\content\acf\Counter();
new Woody\WoodyTheme\library\classes\content\acf\ShortLink();

// Cleanup
new Woody\WoodyTheme\library\classes\content\cleanup\Admin();
new Woody\WoodyTheme\library\classes\content\cleanup\Front();
new Woody\WoodyTheme\library\classes\content\cleanup\Minify();
// new Woody\WoodyTheme\library\classes\content\cleanup\OptionsTable(); //Disable
// new Woody\WoodyTheme\library\classes\content\cleanup\OptimizeBDD(); // Disable
new Woody\WoodyTheme\library\classes\content\cleanup\Schedule();
new Woody\WoodyTheme\library\classes\content\cleanup\DataBases();

// Dashboard
new Woody\WoodyTheme\library\classes\content\dashboard\Dashboard();

// Assets
new Woody\WoodyTheme\library\classes\assets\Enqueue();

// Langs
new Woody\WoodyTheme\library\classes\content\Polylang();

// Content
new Woody\WoodyTheme\library\classes\content\ApiRest();
new Woody\WoodyTheme\library\classes\content\Cron();
new Woody\WoodyTheme\library\classes\content\IsMobile();
new Woody\WoodyTheme\library\classes\content\Links();
new Woody\WoodyTheme\library\classes\content\Page();
new Woody\WoodyTheme\library\classes\content\Permalink();
new Woody\WoodyTheme\library\classes\content\PostType();
new Woody\WoodyTheme\library\classes\content\Profiles();
new Woody\WoodyTheme\library\classes\content\Robots();
new Woody\WoodyTheme\library\classes\content\Seo();
new Woody\WoodyTheme\library\classes\content\Taxonomy();
new Woody\WoodyTheme\library\classes\content\TinyMCE();
new Woody\WoodyTheme\library\classes\content\Unpublisher();
new Woody\WoodyTheme\library\classes\content\Shuffle();
new Woody\WoodyTheme\library\classes\content\Videos();
new Woody\WoodyTheme\library\classes\content\Testimonials();
new Woody\WoodyTheme\library\classes\content\Shortcodes();

// Timber
new Woody\WoodyTheme\library\classes\timber\Filters();

// Menu
if (!defined('WOODY_MENUS_V2')) {
    new Woody\WoodyTheme\library\classes\menus\Menus();
    if (defined('WOODY_GENERATE_MENU')) {
        new Woody\WoodyTheme\library\classes\menus\Admin();
    }
}

// Roles
new Woody\WoodyTheme\library\classes\roles\Roles();
new Woody\WoodyTheme\library\classes\roles\UsersRestrictions();

// Inclusions
new Woody\WoodyTheme\library\classes\inclusions\Inclusions();

// Mailer
new Woody\WoodyTheme\library\classes\mailer\Mailer();

// Data Processing
new WoodyProcess\Compilers\WoodyTheme_WoodyCompilers();
new WoodyProcess\Getters\WoodyTheme_WoodyGetters();
new WoodyProcess\Process\WoodyTheme_WoodyProcess();
new WoodyProcess\Tools\WoodyTheme_WoodyProcessTools();
