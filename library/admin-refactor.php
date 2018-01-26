<?php

/**
 * We remove the pageparentdiv box to create our own
 *
 * @return null
 */
function basetheme_remove_pageparentdiv() {
    remove_meta_box( 'pageparentdiv' , 'page' , 'side' );
}
add_action( 'admin_menu' , 'basetheme_remove_pageparentdiv' );

// /**
//  * Add the pageparentdiv box back in at the top of the sidebar
//  *
//  * @param  string $post_type
//  * @return null
//  */
// function basetheme_add_pageparentdiv( $post_type ) {
//     if ( in_array( $post_type, array( 'post', 'page' ) ) ) {
//         add_meta_box(
//             'pageparentdiv',
//             'Mise en page - Templates',
//             'page_attributes_meta_box',
//              null,
//             'side',
//             'high'
//         );
//     }
// }
// add_action( 'add_meta_boxes', 'basetheme_add_pageparentdiv' );

/**
 * We remove the content text editor cause we'll use ACF to create pages
 *
 * @return null
 */
function basetheme_remove_pages_editor(){
    remove_post_type_support( 'page', 'editor' );
}
add_action( 'init', 'basetheme_remove_pages_editor' );

/**
 * We remove some admin menu entries for non admin users
 * @return null
 */
function basetheme_remove_menus(){
    global $submenu;

    $user = wp_get_current_user();
    if(!in_array('administrator', $user->roles)){
        remove_menu_page('plugins.php'); // Plugins
        remove_menu_page('tools.php'); // Tools
        remove_menu_page('edit.php'); // Posts
        remove_menu_page('options-general.php'); // Settings
        remove_submenu_page('themes.php', 'widgets.php'); // Theme widgets
        remove_menu_page('edit.php?post_type=acf-field-group'); // Advanced Custom Fields
    }
}
add_action('admin_menu', 'basetheme_remove_menus');
