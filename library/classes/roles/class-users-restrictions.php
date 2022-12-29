<?php

/**
 * Woody Users Restrictions
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

use WoodyProcess\Tools\WoodyTheme_WoodyProcessTools;

class WoodyTheme_Users_Restrictions
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_action('admin_menu', [$this, 'generateMenu'], 10);
        add_action('init', [$this, 'checkRules'], 10);
        add_filter('acf/load_field/name=users', [$this, 'filterAdmin']);
    }

    public function generateMenu()
    {
        acf_add_options_page([
            'page_title' => "Paramètres restrictions d'accès",
            'menu_title' => "Restriction d'accès",
            'menu_slug' => 'woodyusers_restrictions_settings',
            'capability'    => 'edit_pages',
            'icon_url'      => 'dashicons-admin-users',
            'position'      => 99
        ]);
    }

    public function checkRules()
    {
        global $pagenow;
        global $post;

        // Si on est en train d'éditer un post de type page, on vérifie si une restriction d'accès s'applique
        if ($pagenow == 'post.php') {
            $post_id = $_GET['post'];
            if (!empty($post_id)) {
                $post_type = get_post_type($post_id);
                if ($post_type == 'page') {
                    $restrictions_list = get_field('users_restrictions', 'options');
                    if (!empty($restrictions_list)) {
                        $verif_page_type = [];
                        $verif_hierarchy = [];
                        foreach ($restrictions_list as $restriction) {
                            if (in_array(get_current_user_id(), $restriction['users'])) {
                                if ($restriction['restriction_type_choice'] == 'hierarchy') {
                                    $verif_hierarchy[] = $this->checkPage($restriction['granted_post_id'], $post_id);
                                } elseif ($restriction['restriction_type_choice'] == 'page_type') {
                                    $verif_page_type[] = $this->checkType($restriction['granted_page_type_id'], $post_id);
                                }
                            }
                        }

                        if ((!in_array(true, $verif_hierarchy) && !empty($verif_hierarchy))  || (!in_array(true, $verif_page_type) && !empty($verif_page_type))) {
                            wp_die("Désolé, vous ne possédez pas l'autorisation pour accéder à cette page");
                        }
                    }
                }
            }
        }
    }

    public function checkPage($granted_id, $post_id)
    {
        // On donne l'accès aux pages autorisées et à leur traductions
        if (function_exists('pll_get_post')) {
            $granted_id = pll_get_post($granted_id);
        }

        if ($post_id == $granted_id) {
            return true;
        }

        return in_array($granted_id, get_post_ancestors($post_id));
    }

    public function checkType($granted_page_type_id, $post_id)
    {
        $content_type = get_field('content_type', $post_id);
        return empty($content_type) ? new \WP_Error() : $content_type->term_id == $granted_page_type_id;
    }

    public function filterAdmin($field){
        global $wp_roles;
        $roles = $wp_roles->role_names;
        unset($roles['administrator']);
        $field['role'] = array_keys($roles);
        return $field;
    }
}
