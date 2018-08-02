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
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        // Plugins Settings
        update_option('timezone_string', '', '', 'yes'); // Mettre vide si le serveur est déjà configuré sur la bonne timezone Europe/Paris
        update_option('WPLANG', 'fr_FR', '', 'yes');
        update_option('date_format', 'j F Y', '', 'yes');
        update_option('time_format', 'G\hi', '', 'yes');
        update_option('wp_php_console', array('password' => 'root', 'register' => true, 'short' => true, 'stack' => true), '', 'yes');
        update_option('rocket_lazyload_options', array('images' => true, 'iframes' => true, 'youtube' => true), '', 'yes');
        update_option('minify_html_active', (WP_ENV == 'dev') ? 'no' : 'yes', '', 'yes');
        update_option('minify_javascript', 'yes', '', 'yes');
        update_option('minify_html_comments', (WP_ENV == 'dev') ? 'no' : 'yes', '', 'yes');
        update_option('minify_html_xhtml', 'yes', '', 'yes');
        update_option('minify_html_relative', 'yes', '', 'yes');
        update_option('minify_html_scheme', 'no', '', 'yes');
        update_option('minify_html_utf8', 'no', '', 'yes');
        update_option('upload_path', WP_UPLOAD_DIR, '', 'yes');
        update_option('upload_url_path', WP_UPLOAD_URL, '', 'yes');
        update_option('uploads_use_yearmonth_folders', true, '', 'yes');
        update_option('thumbnail_crop', true, '', 'yes');
        update_option('acm_server_settings', array('server_enable' => true), '', 'yes');
        update_option('permalink_structure', '/%postname%/', '', 'yes');
        update_option('permalink-manager-permastructs', array('post_types' => array('touristic_sheet' => '')), '', 'yes');

        // Media Library
        $wpuxss_eml_taxonomies = array('media_category' => array('assigned' => false));
        $this->updateOption('wpuxss_eml_taxonomies', $wpuxss_eml_taxonomies);

        // ACF Key
        $acf_pro_license = array('key'	=> 'b3JkZXJfaWQ9MTIyNTQwfHR5cGU9ZGV2ZWxvcGVyfGRhdGU9MjAxOC0wMS0xNSAwOTozMToyMw==', 'url' => home_url());
        $acf_pro_license = base64_encode(maybe_serialize($acf_pro_license));
        $this->updateOption('acf_pro_license', $acf_pro_license);

        // Yoast settings
        $wpseo_titles['breadcrumbs-enable'] = true;
        $this->updateOption('wpseo_titles', $wpseo_titles);

        // SSO
        $wposso_options = [
            'client_id' => '3_582geteg4eckcwcscwo4kwwgcowk8cgo00cccksc0w040s8s4c',
            'client_secret' => '4qm2ajhrj1mo4oc8sgc0s484c8kkg4g4oo8o8sswkk0gc8wscw',
            'server_url' => 'https://connect.studio.raccourci.fr',
            'redirect_to_dashboard' => 1,
        ];
        $this->updateOption('wposso_options', $wposso_options);

        // Enhanced Media Library
        $wpuxss_eml_lib_options['grid_show_caption-enable'] = true;
        $wpuxss_eml_lib_options['grid_caption_type'] = 'title';
        $this->updateOption('wpuxss_eml_lib_options', $wpuxss_eml_lib_options);

        // YoImages settings
        $yoimg_crop_settings['cropping_is_active'] = true;
        $yoimg_crop_settings['retina_cropping_is_active'] = false;
        $yoimg_crop_settings['sameratio_cropping_is_active'] = true;
        $yoimg_crop_settings['crop_qualities'] = array(75);
        $yoimg_crop_settings['cachebusting_is_active'] = true;
        $yoimg_crop_settings['crop_sizes'] = [
            'thumbnail'             => ['active' => false, 'name' => 'Miniature'],
            'medium'                => ['active' => false, 'name' => 'Medium'],
            'ratio_8_1_small'       => ['active' => true, 'name' => 'Pano A (360x45)'],
            'ratio_8_1_medium'      => ['active' => true, 'name' => 'Pano A (640x80)'],
            'ratio_8_1'             => ['active' => true, 'name' => 'Pano A (1200x150)'],
            'ratio_8_1_xlarge'      => ['active' => true, 'name' => 'Pano A'],
            'ratio_4_1_small'       => ['active' => true, 'name' => 'Pano B (360x90)'],
            'ratio_4_1_medium'      => ['active' => true, 'name' => 'Pano B (640x160)'],
            'ratio_4_1'             => ['active' => true, 'name' => 'Pano B (1200x300)'],
            'ratio_4_1_xlarge'      => ['active' => true, 'name' => 'Pano B'],
            'ratio_2_1_small'       => ['active' => true, 'name' => 'Paysage A (360x180)'],
            'ratio_2_1_medium'      => ['active' => true, 'name' => 'Paysage A (640x220)'],
            'ratio_2_1'             => ['active' => true, 'name' => 'Paysage A (1200x600)'],
            'ratio_2_1_xlarge'      => ['active' => true, 'name' => 'Paysage A'],
            'ratio_16_9_small'      => ['active' => true, 'name' => 'Paysage B (360x203)'],
            'ratio_16_9_medium'     => ['active' => true, 'name' => 'Paysage B (640x360)'],
            'ratio_16_9'            => ['active' => true, 'name' => 'Paysage B (1200x675)'],
            'ratio_16_9_xlarge'     => ['active' => true, 'name' => 'Paysage B'],
            'ratio_4_3_small'       => ['active' => true, 'name' => 'Paysage C (360x270)'],
            'ratio_4_3_medium'      => ['active' => true, 'name' => 'Paysage C (640x480)'],
            'ratio_4_3'             => ['active' => true, 'name' => 'Paysage C (1200x900)'],
            'ratio_4_3_xlarge'      => ['active' => true, 'name' => 'Paysage C'],
            'ratio_3_4_small'       => ['active' => true, 'name' => 'Portrait A (360x480)'],
            'ratio_3_4_medium'      => ['active' => true, 'name' => 'Portrait A (640x854)'],
            'ratio_3_4'             => ['active' => true, 'name' => 'Portrait A'],
            'ratio_10_16_small'     => ['active' => true, 'name' => 'Portrait B (360x576)'],
            'ratio_10_16_medium'    => ['active' => true, 'name' => 'Portrait B (360x576)'],
            'ratio_10_16'           => ['active' => true, 'name' => 'Portrait B'],
            'ratio_a4_small'        => ['active' => true, 'name' => 'Format A4 (360x509)'],
            'ratio_a4_medium'       => ['active' => true, 'name' => 'Format A4 (640x905)'],
            'ratio_a4'              => ['active' => true, 'name' => 'Format A4'],
            'ratio_square_small'    => ['active' => true, 'name' => 'Carr&eacute; (360x360)'],
            'ratio_square_medium'   => ['active' => true, 'name' => 'Carr&eacute; (640x640)'],
            'ratio_square'          => ['active' => true, 'name' => 'Carr&eacute;'],
        ];
        $this->updateOption('yoimg_crop_settings', $yoimg_crop_settings);
    }

    private function updateOption($option_name, $settings, $autoload = 'yes')
    {
        $option = get_option($option_name, array());

        if (is_array($settings)) {
            $new_option = array_replace_recursive($option, $settings);
        } else {
            $new_option = $settings;
        }

        if (strcmp(json_encode($option), json_encode($new_option)) !== 0) { // Update if different
            update_option($option_name, $new_option, '', $autoload);
        }
    }
}
