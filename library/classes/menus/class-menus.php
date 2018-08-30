<?php
/**
 * Taxonomy
 *
 * @link https://www.advancedcustomfields.com/resources/acf-settings
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_Menus
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_theme_support('menus');
    }

    public static function setMainMenu()
    {
        $args = [
            'sort_order' => 'asc',
            'hierarchical' => 1,
            'parent' => 0,
            'post_type' => 'page',
            'post_status' => 'publish'
        ];

        $pages_depth1 = get_pages($args);

        $menu_links = [];
        foreach ($pages_depth1 as $key => $page) {
            // Exclude frontpage from menu links
            if (get_permalink($page->ID) === get_home_url() . '/') {
                continue;
            }
            $menu_links[$key]['title'] = $page->post_title;
            $menu_links[$key]['url'] = get_permalink($page->ID);
        }

        return $menu_links;
    }
}
