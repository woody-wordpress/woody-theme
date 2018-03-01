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


/** If your site requires protocol relative url's for theme assets, uncomment the line below */
// require_once( 'library/class-basetheme-protocol-relative-theme-assets.php' );

/**
** Get Timber parameters file
**/
require_once( 'library/class-basetheme-timber.php' );
$basetheme_timber = new Basetheme_Timber();
$basetheme_timber->execute();

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

// ************************************************* //
// TESTING HAWWWAI FUNCTIONS
// ************************************************* //

/**
 * Creating options pages for Hawwwai settings
**/
if( function_exists('acf_add_options_page') ) {

	acf_add_options_page(array(
		'page_title' 	=> 'Paramètres du plugin',
		'menu_title'	=> 'Hawwwai',
		'menu_slug' 	=> 'hawwwai',
		'capability'	=> 'edit_posts',
        'icon_url'      => 'dashicons-palmtree',
		'redirect'		=> false
	));

    acf_add_options_sub_page(array(
        'page_title' 	=> 'Ajouter un bloc hawwwai',
		'menu_title'	=> 'Ajouter un bloc hawwwai',
		'parent_slug' 	=> 'hawwwai',
    ));
}

/**
 * Create custom post type => hawwwai_block
**/
function create_hawwwai_block() {
    $args = array(
      'labels' => array(
        'name' => 'Blocs Hawwwai',
        'singular_name' => 'Bloc Hawwwai',
        'menu_name' => 'Blocs Hawwwai',
        'add_new' => 'Ajouter',
        'add_new_item' => 'Ajouter nouveau un bloc Hawwwai',
        'edit_item' => 'Modifier le bloc Hawwwai',
        'new_item' => 'Nouveau bloc Hawwwai',
        'view_item' => 'Voir le bloc',
        'view_items' => 'Voir les blocs',
        'search_items' => 'Chercher des blocs',
        'not_found' => 'Aucun bloc trouvé',
        'not_found_in_trash' => 'Aucun bloc trouvé dans la poubelle',
        'all_items' => 'Tous les blocs Hawwwai',
        'attributes' => 'Attributs du bloc'
      ),
      'public' => true,
      'show_in_menu' => false,
      'has_archive' => false,
      'supports' => array('title','thumbnail')
  );
  register_post_type( 'hawwwai_block', $args);
}
add_action( 'init', 'create_hawwwai_block' );

/**
 * Redirect option page "Ajouter un bloc Hawwwai" to /post-new.php?post_type=hawwwai_block
**/
function redirect_to_hawwwai_block_edit(){
    global $pagenow;
    if($pagenow == 'admin.php' && isset($_GET['page']) && $_GET['page'] == 'acf-options-ajouter-un-bloc-hawwwai'){
        d($_GET['page']);
        wp_redirect(admin_url('/post-new.php?post_type=hawwwai_block', 'http'), 301);
    }
}
add_action('admin_init', 'redirect_to_hawwwai_block_edit');


/**
 * Add new taxonomy to organize Hawwwai blocks
**/
register_taxonomy(
    'block_type',
    'hawwwai_block',
    array(
        'label' => 'Type de bloc',
        'labels' => array(
            'name' => 'Types de blocs',
            'singular_name' => 'Type de bloc',
            'menu_name' => 'Type de bloc',
            'all_items' => 'Tous les types de blocs',
            'edit_item' => 'Modifier les types de blocs',
            'view_item' => 'Voir les types de blocs',
            'update_item' => 'Mettre à jour les types de blocs',
            'add_new_item' => 'Ajouter un type de bloc',
            'new_item_name' => 'Nouveau type de bloc',
            'search_items' => 'Rechercher parmi types de blocs',
            'popular_items' => 'Types de blocs les plus utilisées'
        ),
        'hierarchical' => false,
        'show_ui' => true,
    )
);
