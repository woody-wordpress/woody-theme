<?php
/**
 * Admin Theme Cleanup
 *
 * @package HawwwaiTheme
 * @since HawwwaiTheme 1.0.0
 */

class HawwwaiTheme_Cleanup_Admin {

    public function __construct() {
        $this->register_hooks();
    }

    protected function register_hooks() {
      add_filter('wpseo_metabox_prio', array($this, 'yoast_move_meta_box_bottom'));
      add_action('init', array($this, 'remove_pages_editor'));
      add_action('admin_menu', array($this, 'remove_menus'));
      add_action('admin_enqueue_scripts', array($this, 'admin_style'));
    }

    /**
     * Benoit Bouchaud
     * On vire l'éditeur de texte basique de WP, inutile avec ACF
     */
    public function remove_pages_editor(){
        remove_post_type_support( 'page', 'editor' );
    }

    /**
     * Benoit Bouchaud
     * On masque certaines entrées de menu pour les non administrateurs
     */
    public function remove_menus(){
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
    public function yoast_move_meta_box_bottom() {
        return 'low';
    }

    /**
     * Benoit Bouchaud
     * On ajoute admin.css aux styles du backoffice
     */
    public function admin_style() {
        wp_enqueue_style('admin-styles', get_template_directory_uri() . '/admin.css');
    }

}

// Execute Class
$HawwwaiTheme_Cleanup_Admin = new HawwwaiTheme_Cleanup_Admin();
