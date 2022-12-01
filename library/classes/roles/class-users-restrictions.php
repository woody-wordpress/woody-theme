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
        add_action('admin_menu', [$this, 'generateMenu'],10);
        add_action('init', [$this, 'checkRules'],10);
    }

    public function generateMenu()
    {
        acf_add_options_page([
            'page_title' => 'Paramètres restrictions d\'accès',
            'menu_title' => 'Restriction d\'accès',
            'menu_slug' => 'woodyusers_restrictions_settings',
            'capability'    => 'edit_pages',
            'icon_url'      => 'dashicons-admin-users',
            'position'      => 99
        ]);
    }

    public function checkRules()
    {
        global $pagenow;
        $post_id = $_GET['post'];
        if($pagenow == 'post.php' && !empty($post_id)) {
            $verif_page_type = [];
            $verif_hierarchy = [];
            $restrictions_list = get_field('users_restrictions','options');
            foreach($restrictions_list as $restriction) {
                if(in_array(get_current_user_id(),$restriction['users'])) {
                    switch($restriction['restriction_type_choice']) {
                        case 'hierarchy' :
                            $verif_hierarchy[] = $this->checkPage($restriction['authorize_page'],$post_id);
                            break;
                        case 'page_type' :
                            $verif_page_type[] = $this->checkType($restriction['authorize_publication'],$post_id);
                            break;
                    }
                }
            }
            if((!in_array(true,$verif_hierarchy) && !empty($verif_hierarchy))  || (!in_array(true,$verif_page_type) && !empty($verif_page_type))){
                wp_die('Vous ne possédez pas l\'autorisation pour accéder à cette page');
            }
        }

    }

    public function checkPage($restriction,$post_id) {
        if(get_permalink($post_id) == $restriction) {
            return true;
        }
        foreach(get_post_ancestors($post_id) as $parent) {
            if(get_permalink($parent) == $restriction) {
                return true;
            }
        }
        return false;
    }

    public function checkType($restriction,$post_id) {
        $content_type = get_field('content_type',$post_id);
        return !empty($content_type) ? $content_type->term_id == $restriction : new \WP_Error();
    }
}
