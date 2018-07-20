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

        $yoimg_crop_settings = get_option('yoimg_crop_settings');
        $yoimg_crop_settings['sameratio_cropping_is_active'] = true;
        $yoimg_crop_settings['crop_sizes'] = [
            'thumbnail' => [
                    'active' => '0',
                    'name' => 'Miniature'
            ],
            'ratio_8_1_small' => [
                    'active' => '1',
                    'name' => 'Panoramique 1 (360x45px)'
            ],
            'ratio_8_1_medium' => [
                    'active' => '1',
                    'name' => 'Panoramique 1 (640x80px)'
            ],

            'ratio_8_1' => [
                    'active' => '1',
                    'name' => 'Panoramique 1 (1200x150px)'
            ],
            'ratio_8_1_xlarge' => [
                    'active' => '1',
                    'name' => 'Panoramique 1 (1920x240px)',
            ],
            'ratio_4_1_small' => [
                    'active' => '1',
                    'name' => 'Panoramique 2 (360x90px)'
            ],

            'ratio_4_1_medium' => [
                    'active' => '1',
                    'name' => 'Panoramique 2 (640x160px)'
            ],

            'ratio_4_1' => [
                    'active' => '1',
                    'name' => 'Panoramique 2 (1200x300px)'
            ],

            'ratio_4_1_xlarge' => [
                    'active' => '1',
                    'name' => 'Panoramique 2 (1920x480px)'
            ],

            'ratio_2_1_small' => [
                    'active' => '1',
                    'name' => 'Paysage 1 (360x180px)'
            ],

            'ratio_2_1_medium' => [
                    'active' => '1',
                    'name' => 'Paysage 1 (640x220px)'
            ],

            'ratio_2_1' => [
                    'active' => '1',
                    'name' => 'Paysage 1 (1200x600px)'
            ],

            'ratio_2_1_xlarge' => [
                    'active' => '1',
                    'name' => 'Paysage 1 (1920x960px)'
            ],

            'ratio_16_9_small' => [
                    'active' => '1',
                    'name' => 'Paysage 2 (360x203px)'
            ],

            'ratio_16_9_medium' => [
                    'active' => '1',
                    'name' => 'Paysage 2 (640x360px)'
            ],

            'ratio_16_9' => [
                    'active' => '1',
                    'name' => 'Paysage 2 (1200x675px)'
            ],

            'ratio_16_9_xlarge' => [
                    'active' => '1',
                    'name' => 'Paysage 2 (1920x1080px)'
            ],

            'ratio_4_3_small' => [
                    'active' => '1',
                    'name' => 'Paysage 3 (360x270px)'
            ],

            'ratio_4_3_medium' => [
                    'active' => '1',
                    'name' => 'Paysage 3 (640x480px)'
            ],

            'ratio_4_3' => [
                    'active' => '1',
                    'name' => 'Paysage 3 (1200x900px)'
            ],

            'ratio_4_3_xlarge' => [
                    'active' => '1',
                    'name' => 'Paysage 3 (1920x1440px)'
            ],

            'ratio_square_small' => [
                    'active' => '1',
                    'name' => 'Carré (360x360px)'
            ],

            'ratio_square_medium' => [
                    'active' => '1',
                    'name' => 'Carré (640x640px)'
            ],

            'ratio_square' => [
                    'active' => '1',
                    'name' => 'Carré (1200x1200px)'
            ],

            'ratio_3_4_small' => [
                    'active' => '1',
                    'name' => 'Portrait 1 (360x480px)'
            ],

            'ratio_3_4_medium' => [
                    'active' => '1',
                    'name' => 'Portrait 1 (640x854px)'
            ],

            'ratio_3_4' => [
                    'active' => '1',
                    'name' => 'Portrait 1 (1200x1600px)'
            ],

            'ratio_10_16_small' => [
                    'active' => '1',
                    'name' => 'Portrait 2 (360x576px)'
            ],

            'ratio_10_16_medium' => [
                    'active' => '1',
                    'name' => 'Portrait 2 (360x576px)'
            ],

            'ratio_10_16' => [
                    'active' => '1',
                    'name' => 'Portrait 2 (1200x1920px)'
            ],

            'ratio_a4_small' => [
                    'active' => '1',
                    'name' => 'Format A4 (360x509px)'
            ],

            'ratio_a4_medium' => [
                    'active' => '1',
                    'name' => 'Format A4 (640x905px)'
            ],

            'ratio_a4' => [
                    'active' => '1',
                    'name' => 'Format A4 (1200x1697px)'
            ],
        ];

        update_option('yoimg_crop_settings', $wpseo_titles, '', 'yes');

        // print_r($yoimg_crop_settings, true);
        // exit;
    }
}

// Execute Class
new WoodyTheme_Plugins_Options();
