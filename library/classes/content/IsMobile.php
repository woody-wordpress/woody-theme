<?php

/**
 * Is Mobile
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.46.3
 */

namespace Woody\WoodyTheme\library\classes\content;

class IsMobile
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
        $headers = getallheaders();
        foreach ($headers as $key => $val) {
            unset($headers[$key]);
            $headers[strtolower($key)] = $val;
        }

        if (!empty($headers['x-ua-device'])) {
            if ($headers['x-ua-device'] == 'mobile') {
                return true;
            } else {
                return false;
            }
        }

        return $is_mobile;
    }
}
