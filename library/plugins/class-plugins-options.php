<?php
/**
 * Default options of plugins
 *
 * @link https://codex.wordpress.org/Function_Reference/update_option
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_Plugins_Options
{
    public function __construct()
    {
        $this->register_hooks();
    }

    protected function register_hooks()
    {
        // Plugins Settings
        update_option('timezone_string', '', '', 'yes'); // Mettre vide si le serveur est déjà configuré sur la bonne timezone Europe/Paris
        update_option('date_format', 'j F Y', '', 'yes');
        update_option('time_format', 'G\hi', '', 'yes');
        update_option('acf_pro_license', 'b3JkZXJfaWQ9MTIyNTQwfHR5cGU9ZGV2ZWxvcGVyfGRhdGU9MjAxOC0wMS0xNSAwOTozMToyMw==', '', 'yes');
        update_option('wp_php_console', array('password' => 'root', 'register' => true, 'short' => true, 'stack' => true), '', 'yes');
        update_option('rocket_lazyload_options', array('images' => true, 'iframes' => true, 'youtube' => true), '', 'yes');
        update_option('minify_html_active', (WP_ENV == 'dev') ? 'no' : 'yes', '', 'yes');
        update_option('minify_javascript', 'yes', '', 'yes');
        update_option('minify_html_comments', (WP_ENV == 'dev') ? 'no' : 'yes', '', 'yes');
        update_option('minify_html_xhtml', 'yes', '', 'yes');
        update_option('minify_html_relative', 'yes', '', 'yes');
        update_option('minify_html_scheme', 'no', '', 'yes');
        update_option('minify_html_utf8', 'no', '', 'yes');
        update_option('upload_path', WP_CONTENT_DIR . '/uploads/' . WP_SITE_KEY, '', 'yes');
        update_option('upload_url_path', WP_CONTENT_URL . '/uploads/' . WP_SITE_KEY, '', 'yes');
        update_option('acm_server_settings', array('server_enable' => true), '', 'yes');
        update_option('permalink_structure', '/%postname%/', '', 'yes');
        update_option('permalink-manager-permastructs', array('post_types' => array('touristic_sheet' => '')), '', 'yes');

        $wpseo_titles = get_option('wpseo_titles');
        if ($wpseo_titles['breadcrumbs-enable'] == false) {
            $wpseo_titles['breadcrumbs-enable'] = true;
            update_option('wpseo_titles', $wpseo_titles, '', 'yes');
        }
    }
}

// Execute Class
new WoodyTheme_Plugins_Options();
