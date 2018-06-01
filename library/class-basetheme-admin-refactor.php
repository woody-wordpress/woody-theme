<?php

class Basetheme_adminRefactor {

    public function execute() {
        $this->register_hooks();
    }

    protected function register_hooks() {
      add_filter('wpseo_metabox_prio', array($this, 'basetheme_yoast_move_meta_box_bottom'));
      add_action( 'init', array($this, 'basetheme_remove_pages_editor'));
      add_action('admin_menu', array($this, 'basetheme_remove_menus'));
      add_action('admin_enqueue_scripts', array($this, 'basetheme_admin_style'));

    }

    /**
     * Benoit Bouchaud
     * On vire l'éditeur de texte basique de WP, inutile avec ACF
     */
    function basetheme_remove_pages_editor(){
        remove_post_type_support( 'page', 'editor' );
    }

    /**
     * Benoit Bouchaud
     * On masque certaines entrées de menu pour les non administrateurs
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

    /**
     * Benoit Bouchaud
     * On déplace la metabox Yoast en bas de page
     */
    public function basetheme_yoast_move_meta_box_bottom() {
      return 'low';
    }

    /**
     * Benoit Bouchaud
     * On ajoute admin.css aux styles du backoffice
     */
    public function basetheme_admin_style() {
      wp_enqueue_style('admin-styles', get_template_directory_uri().'/admin.css');
    }

}
