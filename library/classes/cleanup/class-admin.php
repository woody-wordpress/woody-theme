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
        add_filter('wpseo_metabox_prio', [$this, 'yoastMoveMetaBoxBottom']);
        add_action('init', [$this, 'removePagesEditor']);
        add_action('init', [$this, 'removeTaxonomies']);
        add_action('admin_menu', [$this, 'removeCommentsMetaBox']);
        add_action('admin_menu', [$this, 'removeAdminMenu']);
        add_action('admin_menu', [$this, 'customMenusPage']);
        add_action('wp_before_admin_bar_render', [$this, 'customAdminBarMenu']);
        add_action('wp_dashboard_setup', [$this, 'removeDashboardWidgets']);
        add_filter('tiny_mce_before_init', [$this, 'tiny_mce_remove_unused_formats']);

        $user = wp_get_current_user();
        if (!in_array('administrator', $user->roles)) {
            add_action('admin_head', [$this, 'removeScreenOptions']);
            add_filter('screen_options_show_screen', '__return_false');
        }

        if (is_admin()) {
            add_action('pre_get_posts', [$this, 'custom_pre_get_posts']);
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
     * Léo POIROUX
     * On vire les taxos catégories/étiquettes des articles
     */
    public function removeTaxonomies()
    {
        global $wp_taxonomies;
        $taxonomies = array( 'post_tag' );
        foreach ($taxonomies as $taxonomy) {
            if (taxonomy_exists($taxonomy)) {
                unset($wp_taxonomies[$taxonomy]);
            }
        }
    }

    /**
     * Benoit Bouchaud
     * On masque certaines entrées de menu pour les non administrateurs
     */
    public function customAdminBarMenu()
    {
        global $wp_admin_bar;
        $wp_admin_bar->remove_menu('wp-logo');
        $wp_admin_bar->remove_menu('customize');
        $wp_admin_bar->remove_menu('comments');
    }

    /**
     * Benoit Bouchaud
     * On masque certaines entrées de menu pour les non administrateurs
     */
    public function removeAdminMenu()
    {
        $user = wp_get_current_user();
        if (!in_array('administrator', $user->roles)) {
            remove_menu_page('themes.php'); // Apparence
            remove_menu_page('tools.php'); // Outils
            remove_menu_page('profile.php'); // Profil
            remove_menu_page('edit.php?post_type=touristic_sheet'); // Fiches SIT
        }
        remove_menu_page('edit.php'); // Articles
        remove_menu_page('edit-comments.php'); // Commentaires

        // Personnaliser
        global $submenu;
        if (isset($submenu[ 'themes.php' ])) {
            foreach ($submenu[ 'themes.php' ] as $index => $menu_item) {
                if (in_array('customize', $menu_item)) {
                    unset($submenu[ 'themes.php' ][ $index ]);
                }
            }
        }
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

    public function customMenusPage()
    {
        if (function_exists('acf_add_options_page')) {
            // Page principale
            acf_add_options_page(array(
                'page_title'    => 'Personnalisation des menus',
                'menu_title'    => 'Menus',
                'menu_slug'     => 'custom-menus',
                'capability'    => 'edit_pages',
                'icon_url'      => 'dashicons-menu',
                'position'      => 30,
                'redirect'      => true
            ));

            // Première sous-page
            acf_add_options_sub_page(array(
                'page_title'    => 'Menu principal',
                'menu_title'    => 'Menu principal',
                'parent_slug'   => 'custom-menus',
            ));
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
        // global $wp_meta_boxes;
        // \PC::debug($wp_meta_boxes);
        remove_action('welcome_panel', 'wp_welcome_panel');
        remove_meta_box('dashboard_right_now', 'dashboard', 'normal');
        remove_meta_box('dashboard_activity', 'dashboard', 'normal');
        remove_meta_box('dashboard_primary', 'dashboard', 'side');
        remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
        remove_meta_box('wpseo-dashboard-overview', 'dashboard', 'side');
    }

    /**
     * Benoit Bouchaud
     * On retire le Heading 1=h2, Heading 6=h6, Adress=adress, Pre=pre disponibles dans l'éditeur de texte
     */
    public function tiny_mce_remove_unused_formats($init)
    {
        $init['block_formats'] = 'Paragraph=p;Heading 2=h2;Heading 3=h3;Heading 4=h4;Heading 5=h5;';
        return $init;
    }
}
