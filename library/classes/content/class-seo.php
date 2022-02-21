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

        if (defined('WP_CLI') && WP_CLI) {
            $this->registerCommands();
        }
    }

    protected function registerHooks()
    {
        add_filter('woody_seo_transform_pattern', [$this, 'woodySeoTransformPattern'], 10, 1);
        add_action('admin_menu', [$this, 'generateMenu'], 10);
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
}
