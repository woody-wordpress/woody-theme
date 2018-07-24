<?php
/**
 * Admin Theme Cleanup
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_Cleanup_Admin
{
    public function __construct()
    {
        $this->register_hooks();
    }

    protected function register_hooks()
    {
        add_filter('wpseo_metabox_prio', array($this, 'yoast_move_meta_box_bottom'));
        add_action('init', array($this, 'remove_pages_editor'));
        add_action('admin_menu', array($this, 'remove_comments_meta_box'));
        add_action('admin_menu', array($this, 'remove_admin_menus'));
        add_action('admin_menu', array($this, 'move_appearance_menusItem'));


        if (is_admin()) {
            add_action('pre_get_posts', array($this, 'custom_pre_get_posts'));
        }
    }

    /**
     * Benoit Bouchaud
     * On vire l'éditeur de texte basique de WP, inutile avec ACF
     */
    public function remove_pages_editor()
    {
        remove_post_type_support('page', 'editor');
    }

    /**
     * Benoit Bouchaud
     * On masque certaines entrées de menu pour les non administrateurs
     */
    public function remove_admin_menus()
    {
        global $submenu;

        $user = wp_get_current_user();
        if (!in_array('administrator', $user->roles)) {
            remove_menu_page('plugins.php'); // Plugins
            remove_menu_page('tools.php'); // Tools
            remove_menu_page('edit.php'); // Posts
            remove_menu_page('options-general.php'); // Settings
            remove_submenu_page('themes.php', 'widgets.php'); // Theme widgets
            remove_menu_page('edit.php?post_type=acf-field-group'); // Advanced Custom Fields
        }
        remove_menu_page('edit.php'); // Articles
        remove_menu_page('edit-comments.php'); // Commentaires
    }

    /**
     * Benoit Bouchaud
     * On déplace la metabox Yoast en bas de page
     */
    public function yoast_move_meta_box_bottom()
    {
        return 'low';
    }

    /**
     * Benoit Bouchaud
     * On retire la metabox pour les commentaires
     */
    public function remove_comments_meta_box()
    {
        remove_meta_box('commentsdiv', 'page', 'normal');
    }

    /**
     * Disable Posts' meta from being preloaded
     * This fixes memory problems in the WordPress Admin
     */
    public function custom_pre_get_posts(WP_Query $wp_query)
    {
        if (in_array($wp_query->get('post_type'), array('page'))) {
            $wp_query->set('update_post_meta_cache', false);
        }
    }

    /**
     * Benoit Bouchaud
     * On déplace le menu "Menus" pour le mettre à la racine du menu d'admin
     */
    public function move_appearance_menusItem()
    {
        // On retire le sous-menu Menus dans Apparence
        remove_submenu_page('themes.php', 'nav-menus.php');

        // On créé un nouvel item de menu à la racine du menu d'admin
        add_menu_page('Menus', 'Menus', 'edit_theme_options', 'nav-menus.php', none, 'dashicons-list-view', 68);

        // La création d'un nouveau menu envoie automatiquemenrt sur /admin.php :/
        // Donc, si l'url == /admin.php?page=nav-menus.php => on redirige vers /nav-menus.php
        global $pagenow;
        if ($pagenow == 'admin.php' && isset($_GET['page']) && $_GET['page'] == 'nav-menus.php') {
            wp_redirect(admin_url('/nav-menus.php'), 301);
        }
    }
}

// Execute Class
new WoodyTheme_Cleanup_Admin();
