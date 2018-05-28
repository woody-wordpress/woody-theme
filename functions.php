<?php
/**
 * Author: Ole Fredrik Lie
 * URL: http://olefredrik.com
 *
 * FoundationPress functions and definitions
 *
 * Set up the theme and provides some helper functions, which are used in the
 * theme as custom template tags. Others are attached to action and filter
 * hooks in WordPress to change core functionality.
 *
 * @link https://codex.wordpress.org/Theme_Development
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

/** Plugins Activation **/
require_once( 'library/plugins-activation.php' );

/** Plugins Options **/
require_once( 'library/plugins-options.php' );

/** Various clean up functions */
require_once( 'library/cleanup.php' );

/** Required for Foundation to work properly */
require_once( 'library/foundation.php' );

/** Format comments */
require_once( 'library/class-basetheme-comments.php' );

/** Register all navigation menus */
require_once( 'library/navigation.php' );

/** Add menu walkers for top-bar and off-canvas */
require_once( 'library/class-basetheme-top-bar-walker.php' );
require_once( 'library/class-basetheme-mobile-walker.php' );

/** Create widget areas in sidebar and footer */
require_once( 'library/widget-areas.php' );

/** Return entry meta information for posts */
require_once( 'library/entry-meta.php' );

/** Enqueue scripts */
require_once( 'library/enqueue-scripts.php' );

/** Add theme support */
require_once( 'library/theme-support.php' );

/** Add Nav Options to Customer */
require_once( 'library/custom-nav.php' );

/** Change WP's sticky post class */
require_once( 'library/sticky-posts.php' );

/** Configure responsive image sizes */
require_once( 'library/responsive-images.php' );

/** Create taxonomies **/
require_once( 'library/taxonomies.php' );

/** Sync ACF fields **/
require_once( 'library/acf_sync.php' );


/** If your site requires protocol relative url's for theme assets, uncomment the line below */
// require_once( 'library/class-basetheme-protocol-relative-theme-assets.php' );

/**
** Get Timber parameters file
**/
if (class_exists('TimberSite')) {
    require_once( 'library/class-basetheme-timber.php' );
    $basetheme_timber = new Basetheme_Timber();
    $basetheme_timber->execute();
}

/**
** Improve ACF
**/
include get_template_directory().'/library/class-basetheme-acf.php';
$basetheme_acf = new Basetheme_ACF();
$basetheme_acf->execute();

/**
** Improve Menus
**/
include get_template_directory().'/library/class-basetheme-menus.php';
$basetheme_acf = new Basetheme_menu();
$basetheme_acf->execute();

/**
** A better backoffice for easier work
**/
include get_template_directory().'/library/class-basetheme-admin-refactor.php';
$basetheme_adminRef = new Basetheme_adminRefactor();
$basetheme_adminRef->execute();

/**
 * Disable Posts' meta from being preloaded
 * This fixes memory problems in the WordPress Admin
 */
function jb_pre_get_posts( WP_Query $wp_query ) {
	if (in_array( $wp_query->get('post_type'), array('page'))) {
		$wp_query->set( 'update_post_meta_cache', false );
	}
}

// Only do this for admin
if ( is_admin() ) {
	add_action( 'pre_get_posts', 'jb_pre_get_posts' );
}
