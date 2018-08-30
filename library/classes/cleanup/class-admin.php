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
        add_action('admin_menu', [$this, 'removeNavMenuItem']);
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

    /**
     * Benoit Bouchaud
     * On déplace le menu "Menus" pour le mettre à la racine du menu d'admin
     */
    public function removeNavMenuItem()
    {
        // On retire le sous-menu Menus dans Apparence
        remove_submenu_page('themes.php', 'nav-menus.php');

        // On créé un nouvel item de menu à la racine du menu d'admin
        // add_menu_page('Menus', 'Menus', 'edit_pages', 'nav-menus.php', '', 'dashicons-menu', 31);

        // La création d'un nouveau menu envoie automatiquemenrt sur /admin.php :/
        // Donc, si l'url == /admin.php?page=nav-menus.php => on redirige vers /nav-menus.php
        // global $pagenow;
        // if ($pagenow == 'admin.php' && isset($_GET['page']) && $_GET['page'] == 'nav-menus.php') {
        //     wp_redirect(admin_url('/nav-menus.php'), 301);
        // }
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
        remove_meta_box('dashboard_right_now', 'dashboard', 'normal');
        remove_meta_box('dashboard_activity', 'dashboard', 'normal');
        remove_meta_box('dashboard_primary', 'dashboard', 'side');
        remove_meta_box('dashboard_quick_press', 'dashboard', 'side');

        wp_add_dashboard_widget(
            'raccourci-family', // Widget slug.
            'Raccourci Family', // Title.
            [$this, 'raccourciFamilyWidget'] // Display function.
        );
    }

    public function raccourciFamilyWidget()
    {
        echo '<table border="0" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;"><tr style=""><td height="28" style="line-height:28px;">&nbsp;</td></tr><tr><td style=""><table border="0" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:separate;background-color:#ffffff;font-family:Helvetica, Arial, sans-serif;margin:0px auto;"><tr style="padding-bottom: 8px;"><td style=""><img class="img" src="https://scontent-cdg2-1.xx.fbcdn.net/v/t1.0-0/c0.0.567.296/p526x296/36305656_10215635967506764_1276999918627586048_o.jpg?_nc_cat=0&amp;oh=85ecdbd5225d67c913341c8ae3b21aae&amp;oe=5BF38F19" width="280" height="146" alt="" /></td></tr><tr><td style="font-size:14px;font-weight:bold;padding:8px 8px 0px 8px;text-align:center;">Raccourci Family</td></tr><tr><td style="color:#90949c;font-size:12px;font-weight:normal;text-align:center;">Groupe Facebook · 287 membres</td></tr><tr><td style="padding:8px 12px 12px 12px;"><table border="0" cellspacing="0" cellpadding="0" style="border-collapse:collapse;width:100%;"><tr><td style="background-color:#4267b2;border-radius:3px;text-align:center;"><a style="color:#3b5998;text-decoration:none;cursor:pointer;width:100%;" href="https://www.facebook.com/plugins/group/join/popup/?group_id=355097798174987&amp;source=email_campaign_plugin" target="_blank" rel="noopener"><table border="0" cellspacing="0" cellpadding="3" align="center" style="border-collapse:collapse;"><tr><td style="border-bottom:3px solid #4267b2;border-top:3px solid #4267b2;"><img width="16" src="https://facebook.com/images/groups/plugin/email/app_fb_32_fig_white.png" /></td><td style="border-bottom:3px solid #4267b2;border-top:3px solid #4267b2;color:#FFF;font-family:Helvetica, Arial, sans-serif;font-size:12px;font-weight:bold;">Rejoindre ce groupe</td></tr></table></a></td></tr></table></td></tr><tr><td style="border-top:1px solid #dddfe2;font-size:12px;padding:8px 12px;">Un lieu d&#039;échange de partage et de bonnes idées entre tous les membres de la Raccourci Family !</td></tr></table></td></tr><tr style=""><td height="14" style="line-height:14px;">&nbsp;</td></tr></table>';
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
