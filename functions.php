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

$finder = new Finder();
$finder->files()->in(__DIR__ . '/library')->name('*.php');

foreach ($finder as $file) {
    require_once(__DIR__ . '/library/' . $file->getRelativePathname());
}

// Change Timber locations
Timber::$locations = array('views', Woody::getTemplatesDirname());
