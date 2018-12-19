<?php
/**
 * Front Theme Cleanup
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

use voku\helper\HtmlMin;

class WoodyTheme_Cleanup_Minify
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        $minify_html_active = get_option('minify_html_active');
        $minify_html_active = 'no';
        if (!(defined('WP_CLI') && WP_CLI) && $minify_html_active != 'no' && (!defined('DOING_AJAX') || !DOING_AJAX)) {
            add_action('init', [$this, 'minifyHtml'], 1);
        }
    }

    public function minifyHtml()
    {
        ob_start([$this, 'minifyHtmlOutput']);
    }

    public function minifyHtmlOutput($buffer)
    {
        $buffer = preg_replace('/<!--(.|\s)*?-->/', '', $buffer);
        $buffer = trim(preg_replace('/>\s+</', '><', $buffer));
        return $buffer;
    }
}
