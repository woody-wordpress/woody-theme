<?php

/**
 * Woody SEO
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

use WoodyProcess\Tools\WoodyTheme_WoodyProcessTools;

class WoodyTheme_Seo
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_filter('woody_seo_transform_pattern', [$this, 'woodySeoTransformPattern'], 10, 1);
        add_action('admin_menu', [$this, 'generateMenu'], 10);
        add_action('acf/init', [$this, 'acfAddFields']);
        add_action('members_register_caps', [$this, 'membersRegisterCaps']);
        add_action('acf/save_post', [$this, 'test']);
    }

    public function woodySeoTransformPattern($string)
    {
        $tools = new WoodyTheme_WoodyProcessTools();
        return $tools->replacePattern($string, get_the_ID());
    }

    public function membersRegisterCaps()
    {
        members_register_cap('woody_seo', array(
            'label' => _x('Woody SEO', '', 'woody'),
            'group' => 'woody',
        ));
    }

    public function generateMenu()
    {
        acf_add_options_page([
            'page_title' => 'Paramètres Woody SEO',
            'menu_title' => 'Woody SEO',
            'menu_slug' => 'woodyseo_settings',
            'capability'    => 'woody_seo',
            'icon_url'      => 'dashicons-awards',
            'position'      => 50
        ]);
    }

    public function acfAddFields()
    {
        global $pagenow;
        if ($pagenow == 'admin.php' && !empty($_GET['page']) && $_GET['page'] == 'woodyseo_settings') {
            $woody_custom_meta_field = get_field_object('field_5e16e3c18c1c3');

            $languages = pll_languages_list();
            foreach ($languages as $lang) {
                $new_field = $woody_custom_meta_field;
                $new_field['key'] .= '_' . $lang;
                $new_field['label'] .= ' (uniquement sur ' . strtoupper($lang) . ')';
                $new_field['name'] .= '_' . $lang;
                $new_field['_name'] .= '_' . $lang;
                $new_field['value'] = get_field($new_field['name']);

                acf_add_local_field($new_field);
            }


            $woody_custom_meta_field = get_field_object('field_64887499e6961');

            foreach ($languages as $lang) {
                $new_field = $woody_custom_meta_field;
                $new_field['key'] .= '_' . $lang;
                $new_field['label'] .= ' (uniquement sur ' . strtoupper($lang) . ')';
                $new_field['name'] .= '_' . $lang;
                $new_field['_name'] .= '_' . $lang;
                $new_field['value'] = get_field($new_field['name']);
                $new_field['parent'] = 'group_5e16e3b48d2d6';

                acf_add_local_field($new_field);
            }

            $woody_custom_meta_field = get_field_object('field_64887519e6962');

            foreach ($languages as $lang) {
                $new_field = $woody_custom_meta_field;
                $new_field['key'] .= '_' . $lang;
                $new_field['label'] .= ' (uniquement sur ' . strtoupper($lang) . ')';
                $new_field['name'] .= '_' . $lang;
                $new_field['_name'] .= '_' . $lang;
                $new_field['value'] = get_field($new_field['name']);
                $new_field['parent'] = 'group_5e16e3b48d2d6';

                acf_add_local_field($new_field);
            }

            $woody_custom_meta_field = get_field_object('field_64887531e6963');

            foreach ($languages as $lang) {
                $new_field = $woody_custom_meta_field;
                $new_field['key'] .= '_' . $lang;
                $new_field['label'] .= ' (uniquement sur ' . strtoupper($lang) . ')';
                $new_field['name'] .= '_' . $lang;
                $new_field['_name'] .= '_' . $lang;
                $new_field['value'] = get_field($new_field['name']);
                $new_field['parent'] = 'group_5e16e3b48d2d6';

                acf_add_local_field($new_field);
            }
        }
    }


    public function test($post)
    {

        $args = array(
            'post_type' => 'polylang_mo',
            'post_status' => array('private'),
        );
        if($post == 'options') {
            if(get_current_screen()->id == 'toplevel_page_woodyseo_settings') {
                $languages = pll_languages_list();
                $blog_name = get_field_object('field_64887499e6961');
                $blog_description = get_field_object('field_64887519e6962');
                $touristic_sheet = get_field_object('field_64887531e6963');
                foreach ($languages as $lang) {
                    $blog_name['name'] .= '_' . $lang;
                    $blog_description['name'] .= '_' . $lang;
                    $touristic_sheet['name'] .= '_' . $lang;
                }
            }
        }
        error_log(print_r(get_current_screen(),true),3,'/tmp/sleep1');
        error_log(print_r(get_fields($post->ID),true),3,'/tmp/sleep');
        // $query = new \WP_Query($args);
        // $title = get_bloginfo('name');
        // $description = get_bloginfo('description');
        // $test4 = [];
        // foreach($query->posts as $post) {
        //     $test3 = maybe_unserialize(get_post_meta($post->ID,'',true)['_pll_strings_translations'][0]);
        //     foreach($test3 as &$field) {
        //         if( in_array('touristic_sheet',$field)){
        //             $field[1] = get_field();
        //             $new_sheet = false;
        //         }
        //         if( in_array($title,$field)){
        //             $field[1] = get_field();
        //             $new_title = false;
        //         }
        //         if( in_array($description,$field)){
        //             $field[1] = get_field();
        //             $new_description = false;
        //         }
        //     }
        //     if($new_sheet) {
        //         $test3[] = ['touristic_sheet',get_field()];
        //     }
        //     if($new_title) {
        //         $test3[] = [$title,get_field()];
        //     }
        //     if($new_description) {
        //         $test3[] = [$description,get_field()];
        //     }
        //     update_post_meta($post->ID,'_pll_strings_translations',$test3);
        //     $test4[] = maybe_unserialize(get_post_meta($post->ID,'',true)['_pll_strings_translations'][0]);
        // }
    }

}
