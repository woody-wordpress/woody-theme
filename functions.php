<?php

/**
 * WoodyTheme functions and definitions
 *
 * Set up the theme and provides some helper functions, which are used in the
 * theme as custom template tags. Others are attached to action and filter
 * hooks in WordPress to change core functionality.
 *
 * @link https://codex.wordpress.org/Theme_Development
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

use Symfony\Component\Finder\Finder;

// Theme Support
add_theme_support('html5');

// Added Global Group Woody Cache
if (function_exists('wp_cache_add_global_groups')) {
    wp_cache_add_global_groups('woody');
}

// Globals
define('WOODY_THEME_DIR', __DIR__);
define('WOODY_SUBTHEME_DIR', get_stylesheet_directory());

// Load functions
$finder = new Finder();
$finder->files()->in(WOODY_THEME_DIR . '/library/functions')->name('*.php')->sortByName();
foreach ($finder as $file) {
    require_once($file->getPathname());
}

// Load classes
$finder = new Finder();
$finder->files()->in(WOODY_THEME_DIR . '/library/classes/*')->name('*.php')->notName('autoloader.php');
foreach ($finder as $file) {
    require_once($file->getPathname());
}
require_once(WOODY_THEME_DIR . '/library/classes/autoloader.php');
