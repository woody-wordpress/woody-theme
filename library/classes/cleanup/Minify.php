<?php
/**
 * Front Theme Cleanup
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

namespace Woody\WoodyTheme\library\classes\cleanup;

class Minify
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        $minify_html_active = get_option('minify_html_active');
        if (!(defined('WP_CLI')) && $minify_html_active != 'no' && (!defined('DOING_AJAX') || !DOING_AJAX) && (!defined('DOING_CRON') || !DOING_CRON)) {
            add_action('init', [$this, 'minifyHtml'], 1);
        }
    }

    public function minifyHtml()
    {
        ob_start([$this, 'minifyHtmlOutput']);
    }

    public function minifyHtmlOutput($buffer)
    {
        // Supprime les commentaires HTML
        $buffer = preg_replace('#<!--(.|\s)*?-->#', '', $buffer);

        // Supprime les espaces entre les balises html
        //$buffer = trim(preg_replace('/>\s+</', '><', $buffer));
        return $buffer;
    }
}
