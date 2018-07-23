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
        $this->register_hooks();
    }

    protected function register_hooks()
    {
        add_theme_support('menus');
    }
}

// Execute Class
new WoodyTheme_Menus();
