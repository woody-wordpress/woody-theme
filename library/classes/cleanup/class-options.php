<?php
/**
 * Admin Theme Cleanup
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_Cleanup_OptionsTable
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_action('woody_cleanup_options_table', [$this, 'cleanupOptionsTable']);
        add_action('wp', [$this, 'scheduleOptionsTableCleanup']);
    }

    /**
     * Loop through option strings that might exist in the "option_name" column
     * and delete the row if there is a match.
     */
    public function cleanupOptionsTable()
    {
        global $wpdb;

        $options = [
            '%_cache_validator', // Plugin: Yoast SEO
        ];

        foreach ($options as $option) {
            $query = "DELETE FROM $wpdb->options WHERE option_name LIKE %s";
            $wpdb->query($wpdb->prepare($query, $option));
        }
    }


    /**
     * Schedule options table cleanup daily.
     */
    public function scheduleOptionsTableCleanup()
    {
        if (! wp_next_scheduled('woody_cleanup_options_table')) {
            wp_schedule_event(time(), 'daily', 'woody_cleanup_options_table');
        }
    }
}
