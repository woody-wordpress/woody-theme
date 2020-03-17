<?php

/**
 * Cookies
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_Cookies
{
    public function __construct()
    {
        $this->registerHooks();
    }

    private function registerHooks()
    {
        add_action('wp_ajax_get_cookie_options', [$this, 'getCookieOptions']);
        add_action('wp_ajax_nopriv_get_cookie_options', [$this, 'getCookieOptions']);
    }

    public function getCookieOptions()
    {
        $return = [];

        $max = !empty(get_option("options_cookie_activate")) ? get_option("options_cookie_activate") : 0 ;
        for ($i = 0 ; $i < $max ; $i++ ) {
            $return[$i]['label'] = !empty(get_option('options_cookie_activate_'.$i.'_label')) ? get_option('options_cookie_activate_'.$i.'_label') : '';
            $return[$i]['description'] = !empty(get_option('options_cookie_activate_'.$i.'_description')) ? get_option('options_cookie_activate_'.$i.'_description') : '';
        }

        if (!is_null($return)) {
            wp_send_json($return);
        } else {
            header("HTTP/1.0 400 Bad Request");
            die();
        }
        exit;
    }
}
