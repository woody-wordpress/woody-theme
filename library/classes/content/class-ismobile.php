<?php

/**
 * Profiles
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.23.0
 */

class WoodyTheme_IsMobile
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_filter('wp_is_mobile', [$this, 'wp_is_mobile'], 10);
    }

    public function wp_is_mobile($is_mobile)
    {
        if (!empty($_SERVER['X-UA-Device'])) {
            if ($_SERVER['X-UA-Device'] == 'mobile') {
                return true;
            } else {
                return false;
            }
        }

        return $is_mobile;
    }
}
