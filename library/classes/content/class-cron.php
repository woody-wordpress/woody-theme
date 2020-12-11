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
        add_action('woody_theme_update', [$this, 'woodyThemeUpdate']);

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

        $schedules['everyquarter'] = [
            'interval' => 900,
            'display' => __('Every Quarter of an Hour')
        ];

        return $schedules;
    }

    public function woodyThemeUpdate()
    {
        // ErrorException Warning: Invalid argument supplied for foreach() on web/wp/wp-cron.php at line 122
        // Sometimes the cron contained "false" instead of an event

        $crons = _get_cron_array();
        $need_update = false;
        if (is_array($crons)) {
            foreach ($crons as $timestamp => $cronhooks) {
                if (!is_array($cronhooks)) {
                    unset($crons[$timestamp]);
                    $need_update = true;
                }
            }
        }

        if ($need_update) {
            _set_cron_array($crons);
        }
    }
}
