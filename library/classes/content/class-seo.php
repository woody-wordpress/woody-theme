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
        if($post == 'options') {
            if(get_current_screen()->id == 'toplevel_page_woodyseo_settings') {
                $langs = pll_the_languages(['raw' => true]);

                if(!empty($langs)) {
                    foreach ($langs as $lang) {
                        $blogname = get_field(sprintf('blog_data_%s_blog_name', $lang['slug']), 'options');
                        $blogdescription = get_field(sprintf('blog_data_%s_blog_description', $lang['slug']), 'options');
                        $touristic_sheet_slug = get_field(sprintf('blog_data_%s_touristic_sheet', $lang['slug']), 'options');

                        if($lang['slug'] === PLL_DEFAULT_LANG) {
                            $this->updateBlogData(['blogname' => $blogname, 'blogdescription' => $blogdescription]);
                        }

                        $this->updateTransaltionPost($lang, ['blogname' => $blogname, 'blogdescription' => $blogdescription, 'touristic_sheet_slug' => $touristic_sheet_slug]);
                    }
                }
            }
        }
    }

    private function updateBlogData($blogdata)
    {
        if(!empty($blogdata['blogname'])) {
            update_option('blogname', $blogdata['blogname']);
        }

        if(!empty($blogdata['blogdescription'])) {
            update_option('blogdescription', $blogdata['blogdescription']);
        }
    }

    private function updateTransaltionPost($lang, $blogdata)
    {
        $args = [
            'post_type' => 'polylang_mo',
            'post_status' => ['private'],
            'name' => sprintf('polylang_mo_%s', $lang['id']),
            'fields' => 'ids'
        ];

        $query = new \WP_Query($args);
        $mo_post = (is_array($query->posts) && !empty($query->posts)) ? current($query->posts) : null;
        $translations = maybe_unserialize(get_post_meta($mo_post, '_pll_strings_translations', true));

        if(!empty($translations)) {

            $blogname = get_option('blogname');
            $blogdescription = get_option('blogdescription');
            $touristic_sheet_slug_exists = false;
            $blogname_exists = false;
            $blogdescription_exists = false;

            foreach ($translations as $translation_key => $translation) {
                if($translation[0] === 'touristic_sheet') {
                    $touristic_sheet_slug_exists = true;
                    $translations[$translation_key][1] = $blogdata['touristic_sheet_slug'];
                }

                if($lang['slug'] !== PLL_DEFAULT_LANG) {
                    if($translation[0] == $blogname) {
                        $blogname_exists = true;
                        $translations[$translation_key][1] = $blogdata['blogname'];
                    }
                    if($translation[0] == $blogdescription) {
                        $blogdescription_exists = true;
                        $translations[$translation_key][1] = $blogdata['blogdescription'];
                    }
                }
            }

            if(!$touristic_sheet_slug_exists) {
                $translations[] = ['touristic_sheet', $blogdata['touristic_sheet_slug']];
            }

            if(!$blogname_exists) {
                $translations[] = [$blogname, $blogdata['blogname']];
            }

            if(!$blogdescription_exists) {
                $translations[] = [$blogdescription, $blogdata['blogdescription']];
            }
        }

        update_post_meta($mo_post, '_pll_strings_translations', $translations);
        clean_post_cache($mo_post);
    }
}
