<?php
/**
 * Default options of plugins
 *
 * @link https://codex.wordpress.org/Function_Reference/update_option
 * @package HawwwaiTheme
 * @since HawwwaiTheme 1.0.0
 */

class HawwwaiTheme_Plugins_Options
{
    public function __construct()
    {
        $this->register_hooks();
    }

    protected function register_hooks()
    {
        update_option('timezone_string', '', '', 'yes'); // Mettre vide si le serveur est déjà configuré sur la bonne timezone Europe/Paris
        update_option('date_format', 'j F Y', '', 'yes');
        update_option('time_format', 'G\hi', '', 'yes');
        update_option('acf_pro_license', 'b3JkZXJfaWQ9MTIyNTQwfHR5cGU9ZGV2ZWxvcGVyfGRhdGU9MjAxOC0wMS0xNSAwOTozMToyMw==', '', 'yes');
        update_option('wp_php_console', array('password' => 'root', 'register' => true, 'short' => true, 'stack' => true), '', 'yes');
        update_option('rocket_lazyload_options', array('images' => true, 'iframes' => true, 'youtube' => true), '', 'yes');
        update_option('minify_html_active', (WP_ENV == 'dev') ? 'no' : 'yes', '', 'yes');
        update_option('minify_javascript', (WP_ENV == 'dev') ? 'no' : 'yes', '', 'yes');
        update_option('minify_html_comments', (WP_ENV == 'dev') ? 'no' : 'yes', '', 'yes');
        update_option('minify_html_xhtml', 'yes', '', 'yes');
        update_option('minify_html_relative', 'yes', '', 'yes');
        update_option('minify_html_scheme', 'no', '', 'yes');
        update_option('minify_html_utf8', 'no', '', 'yes');
    }
}

// Execute Class
$HawwwaiTheme_Plugins_Options = new HawwwaiTheme_Plugins_Options();
