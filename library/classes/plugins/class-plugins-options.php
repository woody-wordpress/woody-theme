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
        add_action('woody_theme_update', [$this, 'defineOptions'], 1);
        add_action('woody_theme_update', [$this, 'flushRewriteRules'], 10);
    }

    public function flushRewriteRules()
    {
        flush_rewrite_rules();
    }

    public function defineOptions()
    {
        $yoimg_crop_settings = [];
        // Plugins Settings
        update_option('timezone_string', WOODY_TIMEZONE, true);
        update_option('WPLANG', 'fr_FR', true);
        update_option('date_format', 'j F Y', true);
        update_option('time_format', 'G\hi', true);
        update_option('wp_php_console', ['password' => 'root', 'register' => true, 'short' => true, 'stack' => true], true);
        update_option('rocket_lazyload_options', ['images' => true, 'iframes' => true, 'youtube' => true], true);
        // update_option('minify_html_active', (WP_ENV == 'dev') ? 'no' : 'yes', true);
        update_option('minify_html_active', 'no', true);
        update_option('minify_javascript', 'yes', true);
        update_option('minify_html_comments', 'yes', true);
        update_option('minify_html_xhtml', 'no', true);
        update_option('minify_html_relative', 'yes', true);
        update_option('minify_html_scheme', 'no', true);
        update_option('minify_html_utf8', 'no', true);
        update_option('upload_path', WP_UPLOAD_DIR, true);
        update_option('uploads_use_yearmonth_folders', true, true);
        update_option('thumbnail_crop', true, true);
        update_option('acm_server_settings', ['server_enable' => true], true);
        update_option('permalink_structure', WOODY_PERMALINK_STRUCTURE, true);

        // Members : Disable review notice
        update_option('members_review_prompt_removed', true, true);

        // Cleaning Permalink Manager Pro
        delete_option('permalink-manager-permastructs');
        delete_option('permalink-manager');
        delete_option('permalink-manager-uris');
        delete_option('external_updates-permalink-manager-pro');
        delete_option('permalink-manager-redirects');
        delete_option('permalink-manager-external-redirects');
        delete_option('permalink-manager-uris_backup');
        delete_option('permalink-manager-redirects_backup');

        // Force Disable indexation
        if (WP_ENV != 'dev' || (WP_ENV == 'dev' && empty(get_option('upload_url_path')))) {
            update_option('upload_url_path', WP_UPLOAD_URL, true);
        }

        // Force Disable indexation
        if (WP_ENV != 'prod' || WOODY_ACCESS_STAGING) {
            update_option('blog_public', 0, true);
        } else {
            update_option('blog_public', 1, true);
        }

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
            'language' => [
                'eml_media' => false,
                'taxonomy_auto_assign' => false,
                'assigned' => false,
                'admin_filter' => false,
                'media_uploader_filter' => false,
                'media_popup_taxonomy_edit' => false
            ],
            'post_translations' => [
                'eml_media' => false,
                'taxonomy_auto_assign' => false,
                'assigned' => false,
                'admin_filter' => false,
                'media_uploader_filter' => false,
                'media_popup_taxonomy_edit' => false
            ],
            'term_language' => [
                'eml_media' => false,
                'taxonomy_auto_assign' => false,
                'assigned' => false,
                'admin_filter' => false,
                'media_uploader_filter' => false,
                'media_popup_taxonomy_edit' => false
            ],
            'term_translations' => [
                'eml_media' => false,
                'taxonomy_auto_assign' => false,
                'assigned' => false,
                'admin_filter' => false,
                'media_uploader_filter' => false,
                'media_popup_taxonomy_edit' => false
            ],
            'page_type' => [
                'eml_media' => false,
                'taxonomy_auto_assign' => false,
                'assigned' => false,
                'admin_filter' => false,
                'media_uploader_filter' => false,
                'media_popup_taxonomy_edit' => false
            ]
        ];
        $this->updateOption('wpuxss_eml_taxonomies', $wpuxss_eml_taxonomies);

        // ACF Key
        $acf_pro_license = ['key' => WOODY_ACF_PRO_KEY, 'url' => home_url()];
        $acf_pro_license = base64_encode(maybe_serialize($acf_pro_license));
        $this->updateOption('acf_pro_license', $acf_pro_license);

        // SSO
        $woody_sso_options = [
            'client_id' => WOODY_SSO_CLIENT_ID,
            'client_secret' => WOODY_SSO_CLIENT_SECRET,
            'server_url' => WOODY_SSO_SECRET_URL,
            'redirect_to_dashboard' => 1,
        ];
        $this->updateOption('woody_sso_options', $woody_sso_options);

        // Enhanced Media Library
        $wpuxss_eml_lib_options = [
            'force_filters' => false,
            'filters_to_show' => ['types', 'dates', 'taxonomies'],
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
        $yoimg_crop_settings['webp_is_active'] = WOODY_IMAGE_WEBP_ENABLE;
        $yoimg_crop_settings['retina_cropping_is_active'] = false;
        $yoimg_crop_settings['sameratio_cropping_is_active'] = true;
        $yoimg_crop_settings['crop_qualities'] = array(75);
        $yoimg_crop_settings['cachebusting_is_active'] = true;
        $yoimg_crop_settings['crop_sizes'] = [
            'thumbnail'             => ['active' => true, 'name' => 'Miniature'],
            'medium'                => ['active' => true, 'name' => 'Medium'],
            'large'                 => ['active' => true, 'name' => 'Large'],
            'ratio_8_1_small'       => ['active' => true, 'name' => 'Pano A'],
            'ratio_8_1_medium'      => ['active' => true, 'name' => 'Pano A'],
            'ratio_8_1_large'       => ['active' => true, 'name' => 'Pano A'],
            'ratio_8_1'             => ['active' => true, 'name' => 'Pano A'],
            'ratio_4_1_small'       => ['active' => true, 'name' => 'Pano B'],
            'ratio_4_1_medium'      => ['active' => true, 'name' => 'Pano B'],
            'ratio_4_1_large'       => ['active' => true, 'name' => 'Pano B'],
            'ratio_4_1'             => ['active' => true, 'name' => 'Pano B'],
            'ratio_3_1_small'       => ['active' => true, 'name' => 'Pano C'],
            'ratio_3_1_medium'      => ['active' => true, 'name' => 'Pano C'],
            'ratio_3_1_large'       => ['active' => true, 'name' => 'Pano C'],
            'ratio_3_1'             => ['active' => true, 'name' => 'Pano C'],
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

        // Set image default size
        update_option('thumbnail_size_w', 150);
        update_option('thumbnail_size_h', 150);
        update_option('thumbnail_crop', 1);

        update_option('medium_size_w', 300);
        update_option('medium_size_h', 300);
        update_option('medium_crop', 1);

        update_option('large_size_w', 1024);
        update_option('large_size_h', 1024);
        update_option('large_crop', 1);

        // Polylang
        $polylang = [
            'browser' => 0,
            'rewrite' => 1,
            'media_support' => 1,
            'uninstall' => 0,
            'sync' => [
                'taxonomies',
            ],
            'post_types' => [
                'touristic_sheet',
                'short_link',
                'woody_topic'
            ],
            'taxonomies' => [
                'themes',
                'places',
                'seasons',
                'expression_category',
                'profile_category',
            ],
            'media' => [
                'duplicate' => 0,
            ],
        ];

        // En dev on travaille toujours en prefix
        if (WP_ENV == 'dev') {
            $polylang['force_lang'] = 1;
            $polylang['hide_default'] = 1;
        }

        $polylang = apply_filters('woody_polylang_update_options', $polylang);
        $this->updateOption('polylang', $polylang);
        $this->updateOption('pll_dismissed_notices', ['wizard']);

        // Redirections
        global $wpdb;
        $rows = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}redirection_groups");
        $monitor_post = 1;
        $auto_redirect = false;
        foreach ($rows as $row) {
            if (strpos($row->name, 'Articles modifiés') !== false || strpos($row->name, 'Modified Posts') !== false) {
                $monitor_post = $row->id;
            } elseif (strpos($row->name, 'Automatiques') !== false) {
                $auto_redirect = true;
            }
        }

        if (!$auto_redirect) {
            $wpdb->insert($wpdb->prefix . 'redirection_groups', ['name' => 'Automatiques', 'module_id' => 1]);
            $this->updateOption('woody_auto_redirect', $wpdb->insert_id);
        }

        $redirection_options = [
            'support' => false,
            'monitor_post' => $monitor_post,
            'monitor_types' => [
                'post',
                'page',
                'touristic_sheet',
                'short_link',
                'trash',
            ],
            'associated_redirect' => '',
            'auto_target' => '',
            'expire_redirect' => -1,
            'expire_404' => 30,
            'newsletter' => false,
            'redirect_cache' => 0,
            'ip_logging' => 0,
            'last_group_id' => $monitor_post,
            'rest_api' => 0,
            'https' => false,
            'flag_query' => 'exact',
            'flag_case' => true,
            'flag_trailing' => true,
            'flag_regex' => false,
        ];
        $this->updateOption('redirection_options', $redirection_options);

        // Duplicate Post
        $duplicate_post_types_enabled = [
            'post',
            'page',
            'profile',
            'short_link',
            'testimony',
            'woody_model',
            'woody_section_model',
            'woody_claims',
        ];
        $this->updateOption('duplicate_post_types_enabled', $duplicate_post_types_enabled);
        update_option('duplicate_post_show_notice', false);

        $duplicate_post_roles = [
            'administrator',
            'editor',
        ];
        $this->updateOption('duplicate_post_roles', $duplicate_post_roles);
        update_option('duplicate_post_title_suffix', '(contenu dupliqué)', true);

        // Varnish
        delete_option('varnish_caching_enable');
        delete_option('varnish_caching_debug');
        delete_option('varnish_caching_ttl');
        delete_option('varnish_caching_homepage_ttl');
        delete_option('varnish_caching_purge_key');
        delete_option('varnish_caching_cookie');
        delete_option('varnish_caching_dynamic_host');
        delete_option('varnish_caching_override');
        delete_option('varnish_caching_stats_json_file');
        delete_option('varnish_caching_truncate_notice');
        delete_option('varnish_caching_purge_menu_save');
        delete_option('varnish_caching_ssl');
        delete_option('varnish_caching_ips');
        delete_option('varnish_caching_hosts');
    }

    private function updateOption($option_name, $settings, $autoload = true)
    {
        $option = get_option($option_name, []);

        if (empty($option)) {
            $option = [];
        }

        $new_option = is_array($settings) ? array_replace_recursive($option, $settings) : $settings;

        $new_option = $this->cleanUpOption($option_name, $new_option);

        if (strcmp(json_encode($option, JSON_THROW_ON_ERROR), json_encode($new_option, JSON_THROW_ON_ERROR)) !== 0) { // Update if different
            update_option($option_name, $new_option, $autoload);
        }
    }

    private function cleanUpOption($option_name, $option)
    {
        if ($option_name == 'polylang') {
            // On nettoie les doublons dans les posts types
            $option['post_types'] = array_values(array_unique($option['post_types']));
            $option['taxonomies'] = array_values(array_unique($option['taxonomies']));
        }

        return $option;
    }
}
