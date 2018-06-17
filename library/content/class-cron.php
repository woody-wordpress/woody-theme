<?php
/**
 * Cron
 *
 * @package HawwwaiTheme
 * @since HawwwaiTheme 1.0.0
 */

class HawwwaiTheme_Cron
{
    public function __construct()
    {
        $this->register_hooks();
    }

    protected function register_hooks()
    {
        // Cron force Disable HTTP
        add_action('init', function () {
            if (defined('DOING_CRON') && DOING_CRON && php_sapi_name() != 'cli') {
                print "No way !!!";
                die();
            }
        });
    }
}

// Execute Class
new HawwwaiTheme_Cron();
