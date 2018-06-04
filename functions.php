<?php
/**
 * HawwwaiTheme functions and definitions
 *
 * Set up the theme and provides some helper functions, which are used in the
 * theme as custom template tags. Others are attached to action and filter
 * hooks in WordPress to change core functionality.
 *
 * @link https://codex.wordpress.org/Theme_Development
 * @package HawwwaiTheme
 * @since HawwwaiTheme 1.0.0
 */

use Symfony\Component\Finder\Finder;

$finder = new Finder();
$finder->files()->in(__DIR__ . '/library')->name('*.php');

foreach ($finder as $file) {
    require_once(__DIR__ . '/library/' . $file->getRelativePathname() );
}

/** Plugins Activation */
// require_once( 'library/plugins/class-plugins-activation.php' );

// /** Plugins Activation */
// require_once( 'library/plugins-activation.php' );

// /** Plugins Options */
// require_once( 'library/plugins-options.php' );

// /** Supprime des éléments tels que les feed links dans le header, des liens dievers, etc ... */
// require_once( 'library/cleanup.php' );

// /** Enqueue scripts */
// require_once( 'library/enqueue-scripts.php' );

// /** Styles d'images personnalisés */
// require_once( 'library/responsive-images.php' );

// /** Taxonomies globales à tous nos sites */
// require_once( 'library/taxonomies.php' );

// /** Sync ACF fields **/
// require_once( 'library/acf_sync.php' );

// /**
// ** Get Timber parameters file
// **/
// if (class_exists('TimberSite')) {
//     require_once( 'library/class-basetheme-timber.php' );
//     $basetheme_timber = new Basetheme_Timber();
//     $basetheme_timber->execute();
// }

// /**
// ** Improve ACF
// **/
// include get_template_directory().'/library/class-basetheme-acf.php';
// $basetheme_acf = new Basetheme_ACF();
// $basetheme_acf->execute();

// /**
// ** Improve Menus
// ** Permet la synchronisation entre la hierarchie des pages et le menu
// ** Attention => id du menu en dur
// **/
// include get_template_directory().'/library/class-basetheme-menus.php';
// $basetheme_acf = new Basetheme_menu();
// $basetheme_acf->execute();

// /**
// ** A better backoffice for easier work
// **/
// include get_template_directory().'/library/class-basetheme-admin-refactor.php';
// $basetheme_adminRef = new Basetheme_adminRefactor();
// $basetheme_adminRef->execute();

// /**
//  * Disable Posts' meta from being preloaded
//  * This fixes memory problems in the WordPress Admin
//  */
// function jb_pre_get_posts( WP_Query $wp_query ) {
// 	if (in_array( $wp_query->get('post_type'), array('page'))) {
// 		$wp_query->set( 'update_post_meta_cache', false );
// 	}
// }

// // Only do this for admin
// if ( is_admin() ) {
// 	add_action( 'pre_get_posts', 'jb_pre_get_posts' );
// }
