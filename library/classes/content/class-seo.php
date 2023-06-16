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
        add_action('acf/save_post', [$this, 'saveTranslation']);
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


            $woody_main_data_field = get_field_object('field_648c24a4dddad');

            foreach ($languages as $lang) {
                $new_field = $woody_main_data_field;
                $new_field['key'] .= '_' . $lang;
                $new_field['label'] .= ' ' . strtoupper($lang);
                $new_field['name'] .= '_' . $lang;
                $new_field['_name'] .= '_' . $lang;
                $new_field['value'] = get_field($new_field['name']);
                $new_field['parent'] = 'group_5e16e3b48d2d6';

                acf_add_local_field($new_field);
            }
        }
    }


    public function saveTranslation($post)
    {
        $test = [];
        if($post == 'options') {
            error_log('test de passage', 3, '/tmp/sleepy');
            if(get_current_screen()->id == 'toplevel_page_woodyseo_settings') {
                $languages = pll_the_languages(array('raw'=>1));

                //TEST
                $test['Liste langue'] = $languages;

                $blog_name = get_field_object('field_64887499e6961');
                $blog_description = get_field_object('field_64887519e6962');
                $touristic_sheet = get_field_object('field_64887531e6963');
                $base_title = get_bloginfo('name');
                $base_description = get_bloginfo('description');
                foreach ($languages as $key->$lang) {

                    $blog_name_lang = $blog_name['name']. '_' . $lang['slug'];
                    $blog_description_lang = $blog_description['name']. '_' . $lang['slug'];
                    $touristic_sheet_lang = $touristic_sheet['name']. '_' . $lang['slug'];
                    $args = array(
                        'post_type' => 'polylang_mo',
                        'post_status' => array('private'),
                        'name' => 'polylang_mo_'.$lang['id']
                    );

                    $query = new \WP_Query($args);

                    //TEST
                    $test['Liste posts'][$lang['slug']] = $query->posts;
                    $test['Liste args'][$lang['slug']] = $args;

                    $post = $query->have_posts();
                    error_log(var_export('toto', true), 3, '/tmp/sleepy20');
                    error_log(var_export($lang, true), 3, '/tmp/sleepy20');
                    $new_sheet = true;
                    $new_title = true;
                    $new_description = true;
                    $string_translations = maybe_unserialize(get_post_meta($post->ID, '', true)['_pll_strings_translations'][0]);

                    // TEST
                    $test[$lang['slug']]['lang'] = [get_field($blog_name_lang, 'options'),get_field($blog_description_lang, 'options'),get_field($touristic_sheet_lang, 'options')];
                    $test[$lang['slug']]['base'] = [$base_description,$base_title,'touristic_sheet'];
                    $test[] = 'test de passage';

                    foreach($string_translations as &$string_translation) {

                        if(in_array('touristic_sheet', $string_translation)) {
                            $string_translation[1] = get_field($touristic_sheet_lang, 'options');
                            $new_sheet = false;
                        }
                        if(in_array($base_title, $string_translation)) {
                            $string_translation[1] = get_field($blog_name_lang, 'options');
                            $new_title = false;
                        }
                        if(in_array($base_description, $string_translation)) {
                            $string_translation[1] = get_field($blog_description_lang, 'options');
                            $new_description = false;
                        }
                    }
                    if($new_sheet) {
                        error_log('test de passage sheet', 3, '/tmp/sleepy11');
                        $string_translations[] = ['touristic_sheet',get_field($touristic_sheet_lang, 'options')];
                    }
                    if($new_title) {
                        error_log('test de passage title', 3, '/tmp/sleepy11');
                        $string_translations[] = [$base_title,get_field($blog_name_lang, 'options')];
                    }
                    if($new_description) {
                        error_log('test de passage description', 3, '/tmp/sleepy11');
                        $string_translations[] = [$base_description,get_field($blog_description_lang, 'options')];
                    }

                    //TEST
                    $test['post ID'][$lang->slug] = $post->ID;

                    update_post_meta($post->ID, '_pll_strings_translations', $string_translations);
                }
                error_log(print_r($test, true), 3, '/tmp/sleepy19');
            }
        }
    }

}
