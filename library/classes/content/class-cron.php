<?php
/**
 * Cron
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_Cron
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        // Cron force Disable HTTP
        add_action('init', function () {
            if (defined('DOING_CRON') && DOING_CRON && php_sapi_name() != 'cli') {
                print "No way !!!";
                die();
            }
        });

        add_filter('cron_schedules', [$this, 'cronSchedules']);
    }

    public function cronSchedules($schedules)
    {
        // Adds once weekly to the existing schedules.
        $schedules['weekly'] = [
            'interval' => 604800,
            'display' => __('Once Weekly')
        ];

        // Adds once monthly to the existing schedules.
        $schedules['monthly'] = [
            'interval' => 2592000,
            'display' => __('Once Monthly')
        ];

        $schedules['always'] = [
            'interval' => 1,
            'display' => __('Always')
        ];

        $schedules['twicehourly'] = [
            'interval' => 1800,
            'display' => __('Twice Hourly')
        ];

        return $schedules;
    }
}
