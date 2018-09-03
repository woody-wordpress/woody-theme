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
        add_action('woody_update', array($this, 'defineOptions'));
    }

    public function defineOptions()
    {
        // Plugins Settings
        update_option('timezone_string', '', '', 'yes'); // Mettre vide si le serveur est déjà configuré sur la bonne timezone Europe/Paris
        update_option('WPLANG', 'fr_FR', '', 'yes');
        update_option('date_format', 'j F Y', '', 'yes');
        update_option('time_format', 'G\hi', '', 'yes');
        update_option('wp_php_console', ['password' => 'root', 'register' => true, 'short' => true, 'stack' => true], '', 'yes');
        update_option('rocket_lazyload_options', ['images' => true, 'iframes' => true, 'youtube' => true], '', 'yes');
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
        update_option('acm_server_settings', ['server_enable' => true], '', 'yes');
        update_option('permalink_structure', '/%postname%/', '', 'yes');
        update_option('permalink-manager-permastructs', ['post_types' => ['touristic_sheet' => '']], '', 'yes');

        // SSL Insecure Content Fixer
        $ssl_insecure_content_fixer = [
            'fix_level' => (WP_ENV == 'dev') ? 'off' : 'simple',
            'proxy_fix' => 'normal',
            'site_only' => true,
            'fix_specific' => [
                'woo_https' => false
            ]
        ];
        $this->updateOption('ssl_insecure_content_fixer', $ssl_insecure_content_fixer);

        // Media Library Taxonomy
        $wpuxss_eml_taxonomies = [
            'media_category' => [
                'assigned' => false,
                'eml_media' => false,
                'admin_filter' => false,
                'media_uploader_filter' => false,
                'media_popup_taxonomy_edit' => false,
            ],
            'attachment_categories' => [
                'assigned' => true,
                'eml_media' => false,
                'taxonomy_auto_assign' => false,
                'admin_filter' => true,
                'media_uploader_filter' => true,
                'media_popup_taxonomy_edit' => false,
            ],
            'attachment_types' => [
                'assigned' => true,
                'eml_media' => false,
                'taxonomy_auto_assign' => false,
                'admin_filter' => true,
                'media_uploader_filter' => true,
                'media_popup_taxonomy_edit' => false,
            ],
            'attachment_hashtags' => [
                'assigned' => true,
                'eml_media' => false,
                'taxonomy_auto_assign' => false,
                'admin_filter' => true,
                'media_uploader_filter' => true,
                'media_popup_taxonomy_edit' => false,
            ],
            'themes' => [
                'assigned' => true,
                'eml_media' => false,
                'taxonomy_auto_assign' => false,
                'admin_filter' => true,
                'media_uploader_filter' => true,
                'media_popup_taxonomy_edit' => false,
            ],
            'places' => [
                'assigned' => true,
                'eml_media' => false,
                'taxonomy_auto_assign' => false,
                'admin_filter' => true,
                'media_uploader_filter' => true,
                'media_popup_taxonomy_edit' => false,
            ],
            'seasons' => [
                'assigned' => true,
                'eml_media' => false,
                'taxonomy_auto_assign' => false,
                'admin_filter' => true,
                'media_uploader_filter' => true,
                'media_popup_taxonomy_edit' => false,
            ],
        ];
        $this->updateOption('wpuxss_eml_taxonomies', $wpuxss_eml_taxonomies);

        // ACF Key
        $acf_pro_license = ['key' => 'b3JkZXJfaWQ9MTIyNTQwfHR5cGU9ZGV2ZWxvcGVyfGRhdGU9MjAxOC0wMS0xNSAwOTozMToyMw==', 'url' => home_url()];
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
        $wpuxss_eml_lib_options = [
            'force_filters' => false,
            'filters_to_show' => ['types','dates','taxonomies'],
            'show_count' => 0,
            'include_children' => true,
            'media_orderby' => 'date',
            'media_order' => 'DESC',
            'natural_sort' => false,
            'grid_show_caption' => true,
            'grid_caption_type' => 'title',
            'enhance_media_shortcodes' => false,
        ];
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
            'ratio_8_1_small'       => ['active' => true, 'name' => 'Pano A'],
            'ratio_8_1_medium'      => ['active' => true, 'name' => 'Pano A'],
            'ratio_8_1_large'       => ['active' => true, 'name' => 'Pano A'],
            'ratio_8_1'             => ['active' => true, 'name' => 'Pano A'],
            'ratio_4_1_small'       => ['active' => true, 'name' => 'Pano B'],
            'ratio_4_1_medium'      => ['active' => true, 'name' => 'Pano B'],
            'ratio_4_1_large'       => ['active' => true, 'name' => 'Pano B'],
            'ratio_4_1'             => ['active' => true, 'name' => 'Pano B'],
            'ratio_2_1_small'       => ['active' => true, 'name' => 'Paysage A'],
            'ratio_2_1_medium'      => ['active' => true, 'name' => 'Paysage A'],
            'ratio_2_1_large'       => ['active' => true, 'name' => 'Paysage A'],
            'ratio_2_1'             => ['active' => true, 'name' => 'Paysage A'],
            'ratio_16_9_small'      => ['active' => true, 'name' => 'Paysage B'],
            'ratio_16_9_medium'     => ['active' => true, 'name' => 'Paysage B'],
            'ratio_16_9_large'      => ['active' => true, 'name' => 'Paysage B'],
            'ratio_16_9'            => ['active' => true, 'name' => 'Paysage B'],
            'ratio_4_3_small'       => ['active' => true, 'name' => 'Paysage C'],
            'ratio_4_3_medium'      => ['active' => true, 'name' => 'Paysage C'],
            'ratio_4_3_large'       => ['active' => true, 'name' => 'Paysage C'],
            'ratio_4_3'             => ['active' => true, 'name' => 'Paysage C'],
            'ratio_3_4_small'       => ['active' => true, 'name' => 'Portrait A'],
            'ratio_3_4_medium'      => ['active' => true, 'name' => 'Portrait A'],
            'ratio_3_4'             => ['active' => true, 'name' => 'Portrait A'],
            'ratio_10_16_small'     => ['active' => true, 'name' => 'Portrait B'],
            'ratio_10_16_medium'    => ['active' => true, 'name' => 'Portrait B'],
            'ratio_10_16'           => ['active' => true, 'name' => 'Portrait B'],
            'ratio_a4_small'        => ['active' => true, 'name' => 'Format A4'],
            'ratio_a4_medium'       => ['active' => true, 'name' => 'Format A4'],
            'ratio_a4'              => ['active' => true, 'name' => 'Format A4'],
            'ratio_square_small'    => ['active' => true, 'name' => 'Carr&eacute;'],
            'ratio_square_medium'   => ['active' => true, 'name' => 'Carr&eacute;'],
            'ratio_square'          => ['active' => true, 'name' => 'Carr&eacute;'],
        ];
        $this->updateOption('yoimg_crop_settings', $yoimg_crop_settings);

        // Update permalinks by posts titles
        $permalink_options = [
            'general' => [
                'auto_update_uris' => 1,
                'force_custom_slugs' => 1,
                'canonical_redirect' => 1,
                'pagination_redirect' => 0,
                'redirect' => 301,
                'trailing_slashes' => 0,
                'fix_language_mismatch' => 1,
                'auto_remove_duplicates' => 1,
                'setup_redirects' => 1,
                'deep_detect' => 1,
            ],
            'licence' => [
                'licence_key' => '8058C9F5-83C7421C-A57A61BC-D75B00E3',
                'expiration_date' => '4102398000',
            ]
        ];
        $this->updateOption('permalink-manager', $permalink_options);

        // Set default options for NestedPages
        $this->updateOption('nestedpages_menusync', 'nosync');
        $this->updateOption('nestedpages_disable_menu', 'true');
        $nestedpages_roles = ['editor'];
        $this->updateOption('nestedpages_allowsorting', $nestedpages_roles);
    }

    private function updateOption($option_name, $settings, $autoload = 'yes')
    {
        $option = get_option($option_name, array());

        if (empty($option)) {
            $option = array();
        }

        if (is_array($settings)) {
            $new_option = array_replace_recursive($option, $settings);
        } else {
            $new_option = $settings;
        }

        if (strcmp(json_encode($option), json_encode($new_option)) !== 0) { // Update if different
            update_option($option_name, $new_option, $autoload);
        }
    }
}
