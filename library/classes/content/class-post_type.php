<?php
/**
 * Taxonomy
 *
 * @link https://www.advancedcustomfields.com/resources/acf-settings
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_Post_Type
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_action('init', array($this, 'registerPostType'));
    }

    public function registerPostType()
    {
    }
}
