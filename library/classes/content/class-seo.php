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
        add_filter('woody_seo_edit_meta_string', [$this, 'woodySeoTransformPattern'], 10, 1);
    }

    public function woodySeoTransformPattern($string)
    {
        $tools = new WoodyTheme_WoodyProcessTools;
        $string = $tools->replacePattern($string, Timber::get_post());
        return $string;
    }
}
