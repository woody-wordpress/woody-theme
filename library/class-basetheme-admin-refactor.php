<?php

class Basetheme_adminRefactor {

    public function execute() {
        $this->register_hooks();
    }

    protected function register_hooks() {
      add_filter('wpseo_metabox_prio', array($this, 'basetheme_yoast_move_meta_box_bottom'));
      add_action( 'admin_menu' , array($this,'basetheme_remove_pageparentdiv'));
      add_action( 'init', array($this, 'basetheme_remove_pages_editor'));
      add_action('admin_menu', array($this, 'basetheme_remove_menus'));
    }

    /**
     * We remove the pageparentdiv box to create our own
     *
     * @return null
     */
    function basetheme_remove_pageparentdiv() {
        remove_meta_box( 'pageparentdiv' , 'page' , 'side' );
    }

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

    public function basetheme_yoast_move_meta_box_bottom() {
      return 'low';
    }

}
