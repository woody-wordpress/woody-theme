<?php
/**
 * Admin Theme Cleanup
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

namespace Woody\WoodyTheme\library\classes\cleanup;

class Schedule
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_action('woody_theme_update', [$this, 'scheduleCleanup']);
    }

    public function scheduleCleanup()
    {
        if (wp_next_scheduled('wpseo_onpage_fetch')) {
            wp_clear_scheduled_hook('wpseo_onpage_fetch');
            output_success(sprintf('- Schedule %s', 'wpseo_onpage_fetch'));
        }
    }
}
