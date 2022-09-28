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
    }

    public function woodySeoTransformPattern($string)
    {
        $tools = new WoodyTheme_WoodyProcessTools();
        $string = $tools->replacePattern($string, get_the_ID());
        return $string;
    }

    public function generateMenu()
    {
        acf_add_options_page([
            'page_title' => 'Paramètres Woody SEO',
            'menu_title' => 'Woody SEO',
            'menu_slug' => 'woodyseo_settings',
            'capability'    => 'edit_pages',
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
            foreach ($languages as $key => $lang) {
                $new_field = $woody_custom_meta_field;
                $new_field['key'] .= '_' . $lang;
                $new_field['label'] .= ' (uniquement sur ' . strtoupper($lang) . ')';
                $new_field['name'] .= '_' . $lang;
                $new_field['_name'] .= '_' . $lang;
                $new_field['value'] = get_field($new_field['name']);

                acf_add_local_field($new_field);
            }
        }
    }
}
