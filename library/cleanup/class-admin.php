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
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_filter('wpseo_metabox_prio', array($this, 'yoastMoveMetaBoxBottom'));
        add_action('init', array($this, 'removePagesEditor'));
        add_action('admin_menu', array($this, 'removeCommentsMetaBox'));
        add_action('admin_menu', array($this, 'removeNavMenusItem'));
        add_action('admin_menu', array($this, 'moveAppearanceMenusItem'));
        add_action('wp_dashboard_setup', array($this, 'removeDashboardWidgets'));


        global $submenu;
        $user = wp_get_current_user();
        if (!in_array('administrator', $user->roles)) {
            add_action('admin_head', array($this, 'removeScreenOptions'));
            add_filter('screen_options_show_screen', '__return_false');
        }


        if (is_admin()) {
            add_action('pre_get_posts', array($this, 'custom_pre_get_posts'));
        }
    }

    /**
     * Benoit Bouchaud
     * On vire l'éditeur de texte basique de WP, inutile avec ACF
     */
    public function removePagesEditor()
    {
        remove_post_type_support('page', 'editor');
    }

    /**
     * Benoit Bouchaud
     * On masque certaines entrées de menu pour les non administrateurs
     */
    public function removeAdminMenus()
    {
        global $submenu;
        $user = wp_get_current_user();
        if (!in_array('administrator', $user->roles)) {
            remove_menu_page('themes.php'); // Apparence
        }
        remove_menu_page('edit.php'); // Articles
        remove_menu_page('edit-comments.php'); // Commentaires
    }

    /**
     * Benoit Bouchaud
     * On déplace la metabox Yoast en bas de page
     */
    public function yoastMoveMetaBoxBottom()
    {
        return 'low';
    }

    /**
     * Benoit Bouchaud
     * On retire la metabox pour les commentaires
     */
    public function removeCommentsMetaBox()
    {
        remove_meta_box('commentsdiv', 'page', 'normal');
    }

    /**
     * Source https://junaidbhura.com/wordpress-admin-fix-fatal-error-allowed-memory-size-error/
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
    public function removeNavMenusItem()
    {
        // On retire le sous-menu Menus dans Apparence
        remove_submenu_page('themes.php', 'nav-menus.php');

        // On créé un nouvel item de menu à la racine du menu d'admin
        add_menu_page('Menus', 'Menus', 'edit_pages', 'nav-menus.php', '', 'dashicons-menu', 31);

        // La création d'un nouveau menu envoie automatiquemenrt sur /admin.php :/
        // Donc, si l'url == /admin.php?page=nav-menus.php => on redirige vers /nav-menus.php
        global $pagenow;
        if ($pagenow == 'admin.php' && isset($_GET['page']) && $_GET['page'] == 'nav-menus.php') {
            wp_redirect(admin_url('/nav-menus.php'), 301);
        }
    }

    /**
     * Benoit Bouchaud
     * On retire les tabs "Options de l'écran" et "Aide" pour les non admin
     */
    public function removeScreenOptions()
    {
        $screen = get_current_screen();
        $screen->remove_help_tabs();
    }

    /**
     * Benoit Bouchaud
     * On retire les boxes inutiles du dashboard
     */
    public function removeDashboardWidgets()
    {
        remove_meta_box('dashboard_activity', 'dashboard', 'normal');
        remove_meta_box('dashboard_primary', 'dashboard', 'side');
    }
}

// Execute Class
new WoodyTheme_Cleanup_Admin();
