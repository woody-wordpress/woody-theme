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
        if (is_user_logged_in()) {
            add_action('init', [$this, 'removePagesEditor']);
            add_action('admin_menu', [$this, 'removeAdminMenu']);
            add_action('admin_menu', [$this, 'customMenusPage']);
            add_action('admin_menu', [$this, 'woodySettingsPage']);
            add_filter('admin_body_class', [$this, 'adminBodyClass']);

            add_action('admin_footer_text', [$this, 'addThemeSettings']);

            add_action('wp_before_admin_bar_render', [$this, 'customAdminBarMenu']);
            add_action('wp_dashboard_setup', [$this, 'removeDashboardWidgets']);
            add_filter('tiny_mce_before_init', [$this, 'tiny_mce_remove_unused_formats']);

            add_filter('get_user_option_meta-box-order_page', [$this, 'sideMetaboxOrder']);
            add_action('admin_menu', [$this, 'removeAttributeMetaBox']);

            add_filter('gettext_with_context', [$this, 'wpTranslationsOverrides'], 10, 4);

            $user = wp_get_current_user();
            if (!in_array('administrator', $user->roles)) {
                add_action('admin_head', [$this, 'removeScreenOptions']);
                add_filter('screen_options_show_screen', '__return_false');
                add_filter('update_user_metadata', [$this, 'updateUserMetadata'], 10, 5);
            }

            add_action('pre_get_posts', [$this, 'custom_pre_get_posts']);

            // add_action('admin_bar_menu', [$this, 'cleanupAdminBarMenu'], 99);

            add_filter('page_row_actions', [$this, 'removeRowActions'], 11, 1);
            add_filter('bulk_actions-edit-short_link', [$this, 'removeBulkActions'], 11, 1);
        }
    }

    public function removeAttributeMetaBox()
    {
        remove_meta_box('pageparentdiv', 'page', 'side');
    }

    public function adminBodyClass($admin_body_classes)
    {
        // Added ENV to body classes
        $admin_body_classes .= ' ' . WP_ENV;

        // Added User roles to body classes
        $user = wp_get_current_user(); // getting & setting the current user
        $roles = (array) $user->roles; // obtaining the role
        foreach ($roles as $role) {
            $admin_body_classes .= ' role-' . $role;
        }

        return $admin_body_classes;
    }

    public function sideMetaboxOrder($order)
    {
        add_meta_box('pageparentdiv', __('Déplacer la page'), 'page_attributes_meta_box', 'page', 'side');
        $box_order = array(
            'side' => join(
                ",",
                array(
                    'submitdiv',
                    'woody-unpublisher',
                    'ml_box',
                )
            ),
        );
        return $box_order;
    }

    /**
     * Benoit Bouchaud
     * On vire l'éditeur de texte basique de WP, inutile avec ACF
     */
    public function removePagesEditor()
    {
        remove_post_type_support('page', 'editor');
        remove_post_type_support('page', 'comments');
        remove_post_type_support('page', 'thumbnail');
        remove_post_type_support('page', 'excerpt');
        remove_post_type_support('page', 'trackbacks');
        remove_post_type_support('page', 'post-formats');

        remove_post_type_support('short_link', 'editor');
        remove_post_type_support('short_link', 'comments');
        remove_post_type_support('short_link', 'thumbnail');
        remove_post_type_support('short_link', 'excerpt');
        remove_post_type_support('short_link', 'trackbacks');
        remove_post_type_support('short_link', 'post-formats');
    }

    /**
     * Benoit Bouchaud
     * On masque certaines entrées de menu dans la barre d'administration
     */
    public function customAdminBarMenu()
    {
        global $wp_admin_bar;
        $wp_admin_bar->remove_menu('wp-logo');
        $wp_admin_bar->remove_menu('customize');
        $wp_admin_bar->remove_menu('comments');
        $wp_admin_bar->remove_node('new-post');
        $wp_admin_bar->remove_node('new-touristic_sheet');

        $post_type = get_post_type(get_the_ID());
        if ($post_type == 'touristic_sheet') {
            $wp_admin_bar->remove_node('edit');
        }

        // Modification du lien de l'entrée "Créer"
        $new_content_node = $wp_admin_bar->get_node('new-content');
        if (!empty($new_content_node)) {
            $new_content_node->href = home_url() . 'wp/wp-admin/post-new.php?post_type=page';
            $wp_admin_bar->remove_menu('new-content');
            $wp_admin_bar->add_menu($new_content_node);
        }
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
            remove_menu_page('profile.php'); // Profil
            remove_menu_page('edit.php?post_type=touristic_sheet'); // Fiches SIT
        }

        if (!in_array('administrator', $user->roles) && !in_array('editor', $user->roles)) {
            remove_menu_page('tools.php'); // Outils
        }

        remove_menu_page('edit.php'); // Articles
        remove_menu_page('edit-comments.php'); // Commentaires

        remove_menu_page('tools.php?page=export_personal_data'); // Exporter les données
        remove_menu_page('tools.php?page=remove_personal_data'); // Effacer les données

        // Personnaliser
        global $submenu;
        if (isset($submenu['themes.php'])) {
            foreach ($submenu['themes.php'] as $index => $menu_item) {
                if (in_array('customize', $menu_item)) {
                    unset($submenu['themes.php'][$index]);
                }
            }
        }
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
     * customMenusPage
     *
     * @deprecated since version 1.28.35
     * @see WoodyTheme_Admin_Menus
     *
     */
    public function customMenusPage()
    {
        if (function_exists('acf_add_options_page') && function_exists('acf_add_options_sub_page')) {
            $lang = pll_current_language();

            // Page principale
            acf_add_options_page(array(
                'page_title'    => 'Personnalisation des menus',
                'menu_title'    => 'Menus',
                'menu_slug'     => 'custom-menus',
                'capability'    => 'edit_pages',
                'icon_url'      => 'dashicons-menu',
                'position'      => 30,
                'redirect'      => true,
            ));

            // Permet d'être compatible avec tout les sites (anciens et nouveaux)
            $legacyMenus = class_exists('SubWoodyTheme_Admin') && method_exists('SubWoodyTheme_Admin', 'addMenuMainPages') ? false : true;

            if ($legacyMenus && $lang == PLL_DEFAULT_LANG && !defined('WOODY_GENERATE_MENU') && !defined('WOODY_MENUS_V2')) {
                // Première sous-page
                acf_add_options_sub_page(array(
                    'page_title'    => 'Menu principal',
                    'menu_title'    => 'Menu principal',
                    'parent_slug'   => 'custom-menus',
                    'capability'    => 'edit_pages',
                ));
            }
        }
    }

    public function woodySettingsPage()
    {
        if (function_exists('acf_add_options_page')) {
            $lang = (function_exists('pll_current_language')) ? pll_current_language() : PLL_DEFAULT_LANG;

            if ($lang == PLL_DEFAULT_LANG) {
                // Page principale
                acf_add_options_page(array(
                    'page_title'    => 'Paramètres',
                    'menu_title'    => 'Paramètres',
                    'menu_slug'     => 'woody-settings',
                    'capability'    => 'edit_pages',
                    'icon_url'      => 'dashicons-admin-generic',
                    'position'      => 40,
                ));
            }
        }
    }

    /**
     * Benoit Bouchaud
     * On retire les tabs "Aide" pour les "non admin"
     */
    public function removeScreenOptions()
    {
        $screen = get_current_screen();
        $screen->remove_help_tabs();
    }

    /**
    * Léo POIROUX
    * Un "non admin" n'a pas le doit de sauvegarder un metabox hidden
    */
    public function updateUserMetadata($check, $object_id, $meta_key, $meta_value, $prev_value)
    {
        if (strpos($meta_key, 'metaboxhidden_') !== false) {
            return false;
        }

        return $check;
    }

    /**
     * Benoit Bouchaud
     * On retire les boxes inutiles du dashboard
     */
    public function removeDashboardWidgets()
    {
        // global $wp_meta_boxes;
        remove_action('welcome_panel', 'wp_welcome_panel');
        remove_meta_box('dashboard_right_now', 'dashboard', 'normal');
        remove_meta_box('dashboard_activity', 'dashboard', 'normal');
        remove_meta_box('dashboard_primary', 'dashboard', 'side');
        remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
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

    /**
     * Benoit Bouchaud
     * On surcharge la traduction ACF "Texte" pour l'onglet code de l'éditeur WYSIWYG
     */
    public function wpTranslationsOverrides($translated, $original, $context, $domain)
    {
        if ($domain == 'acf' && $context == 'Name for the Text editor tab (formerly HTML)') {
            $translated = 'Code HTML';
        }
        return $translated;
    }

    /**
     * Franck Delaunay
     * On retire des options selon le post_type
     */
    public function removeRowActions($actions)
    {
        if (get_post_type() == "short_link") {
            unset($actions['clone']);
            unset($actions['edit_as_new_draft']);
        }

        return $actions;
    }

    /**
     * Franck Delaunay
     * On retire des actions de masse du sélecteur
     */
    public function removeBulkActions($actions)
    {
        unset($actions['duplicate_post_clone']);

        return $actions;
    }

    /**
     * Ajoute la version du thème et l'URL distante d'un sous-thème dans le DOM
     *
     * @param string $data
     *
     * @return string $data
     */
    public function addThemeSettings($data) {

        $theme_version = wp_get_theme(get_template())->get('Version');

        $data .= '<div id="woody-theme-settings-footer" data-version=' . $theme_version . ' data-theme-dist-url=' . WP_DIST_URL . '></div>';

        return $data;
    }
}