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
        // Plugins Settings
        update_option('timezone_string', 'Europe/Paris', '', 'yes');
        update_option('WPLANG', 'fr_FR', '', 'yes');
        update_option('date_format', 'j F Y', '', 'yes');
        update_option('time_format', 'G\hi', '', 'yes');
        update_option('wp_php_console', ['password' => 'root', 'register' => true, 'short' => true, 'stack' => true], '', 'yes');
        update_option('rocket_lazyload_options', ['images' => true, 'iframes' => true, 'youtube' => true], '', 'yes');
        update_option('minify_html_active', (WP_ENV == 'dev') ? 'no' : 'yes', '', 'yes');
        update_option('minify_javascript', 'yes', '', 'yes');
        update_option('minify_html_comments', 'yes', '', 'yes');
        update_option('minify_html_xhtml', 'no', '', 'yes');
        update_option('minify_html_relative', 'yes', '', 'yes');
        update_option('minify_html_scheme', 'no', '', 'yes');
        update_option('minify_html_utf8', 'no', '', 'yes');
        update_option('upload_path', WP_UPLOAD_DIR, '', 'yes');
        update_option('upload_url_path', WP_UPLOAD_URL, '', 'yes');
        update_option('uploads_use_yearmonth_folders', true, '', 'yes');
        update_option('thumbnail_crop', true, '', 'yes');
        update_option('acm_server_settings', ['server_enable' => true], '', 'yes');
        update_option('permalink_structure', '/%postname%/', '', 'yes');

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
        if (WP_ENV != 'prod') {
            update_option('blog_public', 0, '', 'yes');
        } else {
            update_option('blog_public', 1, '', 'yes');
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

        // Polylang
        $polylang = [
            'browser' => 0,
            'rewrite' => 1,
            'redirect_lang' => 0,
            'media_support' => 1,
            'uninstall' => 0,
            'sync' => [
                'taxonomies',
            ],
            'post_types' => [
                'touristic_sheet',
                'short_link',
            ],
            'taxonomies' => [
                'themes',
                'places',
                'seasons',
            ],
            'media' => [
                'duplicate' => 1,
            ],
        ];

        // En dev on travaille toujours en prefix
        if (WP_ENV == 'dev') {
            $polylang['force_lang'] = 0;
            $polylang['hide_default'] = 1;
        }

        $this->updateOption('polylang', $polylang);

        // Redirections
        global $wpdb;
        $rows = $wpdb->get_results("SELECT id FROM {$wpdb->prefix}redirection_groups WHERE name = 'Modified Posts'");
        $monitor_post = (!empty($rows[0]->id)) ? $rows[0]->id : 1;

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
            'redirect_cache' => 1,
            'ip_logging' => 0,
            'last_group_id' => $monitor_post,
            'rest_api' => 0,
            'https' => false
        ];
        $this->updateOption('redirection_options', $redirection_options);

        // Yoast SEO
        $wpseo = [
            'ms_defaults_set'            => false,
            'disableadvanced_meta'       => true,
            'onpage_indexability'        => false,
            // 'googleverify'               => '',
            'has_multiple_authors'       => false,
            'environment_type'           => (WP_ENV == 'prod') ? 'production' : '',
            'content_analysis_active'    => false,
            'keyword_analysis_active'    => false,
            'enable_admin_bar_menu'      => false,
            'enable_cornerstone_content' => false,
            'enable_xml_sitemap'         => false,
            'enable_text_link_counter'   => false,
            'show_onboarding_notice'     => false,
        ];
        $this->updateOption('wpseo', $wpseo);

        $wpseo_titles = [
            'title_test' => 0,
            'forcerewritetitle' => true,
            'separator' => 'sc-pipe',
            'title-home-wpseo' => '%%sitename%%',
            'title-author-wpseo' => '%%name%%, Auteur de %%sitename%% %%page%%',
            'title-archive-wpseo' => '%%date%% %%sep%% %%sitename%%',
            'title-search-wpseo' => 'Vous recherchez %%searchphrase%% %%sep%% %%sitename%%',
            'title-404-wpseo' => 'Page non trouvée %%sep%% %%sitename%%',
            'metadesc-home-wpseo' => '%%cf_page_teaser_desc%%',
            'metadesc-author-wpseo' => '',
            'metadesc-archive-wpseo' => '',
            'rssbefore' => '',
            'rssafter' => 'La page %%POSTLINK%% est apparu la 1ère fois sur %%BLOGLINK%%.',
            'noindex-author-wpseo' => true,
            'noindex-author-noposts-wpseo' => true,
            'noindex-archive-wpseo' => true,
            'disable-author' => true,
            'disable-date' => true,
            'disable-post_format' => true,
            'disable-attachment' => true,
            'is-media-purge-relevant' => false,
            'breadcrumbs-404crumb' => 'Erreur 404: Page non trouvée',
            'breadcrumbs-display-blog-page' => false,
            'breadcrumbs-boldlast' => false,
            'breadcrumbs-archiveprefix' => 'Archives',
            'breadcrumbs-enable' => true,
            'breadcrumbs-home' => 'Accueil',
            'breadcrumbs-prefix' => '',
            'breadcrumbs-searchprefix' => 'Vous recherchez',
            'breadcrumbs-sep' => '»',
            'website_name' => '',
            'person_name' => '',
            'alternate_website_name' => '',
            'company_logo' => '',
            'company_name' => '',
            'company_or_person' => '',
            'stripcategorybase' => true,
            'title-post' => '%%title%% %%sep%% %%sitename%%',
            'metadesc-post' => '',
            'noindex-post' => true,
            'showdate-post' => false,
            'display-metabox-pt-post' => false,
            'title-page' => '%%title%% %%sep%% %%sitename%%',
            'metadesc-page' => '%%cf_page_teaser_desc%%',
            'noindex-page' => false,
            'showdate-page' => false,
            'display-metabox-pt-page' => true,
            'title-attachment' => '%%title%% %%sep%% %%sitename%%',
            'metadesc-attachment' => '',
            'noindex-attachment' => false,
            'showdate-attachment' => false,
            'display-metabox-pt-attachment' => true,
            'title-tax-category' => '%%term_title%% Archives %%sep%% %%sitename%%',
            'metadesc-tax-category' => '',
            'display-metabox-tax-category' => false,
            'noindex-tax-category' => true,
            'title-tax-post_tag' => '%%term_title%% Archives %%page%% %%sep%% %%sitename%%',
            'metadesc-tax-post_tag' => '',
            'display-metabox-tax-post_tag' => false,
            'noindex-tax-post_tag' => true,
            'title-tax-post_format' => '%%term_title%% Archives %%sep%% %%sitename%%',
            'metadesc-tax-post_format' => '',
            'display-metabox-tax-post_format' => false,
            'noindex-tax-post_format' => true,
            'post_types-post-maintax' => 0,
            'title-touristic_sheet' => '%%title%% %%sep%% %%sitename%%',
            'metadesc-touristic_sheet' => '',
            'noindex-touristic_sheet' => false,
            'showdate-touristic_sheet' => false,
            'display-metabox-pt-touristic_sheet' => true,
            'title-short_link' => '%%title%% %%sep%% %%sitename%%',
            'metadesc-short_link' => '',
            'noindex-short_link' => true,
            'showdate-short_link' => false,
            'display-metabox-pt-short_link' => false,
            'title-woody_claims' => '%%title%% %%sep%% %%sitename%%',
            'metadesc-woody_claims' => '',
            'noindex-woody_claims' => true,
            'showdate-woody_claims' => false,
            'display-metabox-pt-woody_claims' => false,
            'title-tax-page_type' => '%%term_title%% Archives %%sep%% %%sitename%%',
            'metadesc-tax-page_type' => '',
            'display-metabox-tax-page_type' => false,
            'noindex-tax-page_type' => true,
            'title-tax-themes' => '%%term_title%% Archives %%sep%% %%sitename%%',
            'metadesc-tax-themes' => '',
            'display-metabox-tax-themes' => false,
            'noindex-tax-themes' => true,
            'title-tax-places' => '%%term_title%% Archives %%sep%% %%sitename%%',
            'metadesc-tax-places' => '',
            'display-metabox-tax-places' => false,
            'noindex-tax-places' => true,
            'title-tax-seasons' => '%%term_title%% Archives %%sep%% %%sitename%%',
            'metadesc-tax-seasons' => '',
            'display-metabox-tax-seasons' => false,
            'noindex-tax-seasons' => true,
            'title-tax-attachment_types' => '%%term_title%% Archives %%sep%% %%sitename%%',
            'metadesc-tax-attachment_types' => '',
            'display-metabox-tax-attachment_types' => false,
            'noindex-tax-attachment_types' => true,
            'title-tax-attachment_categories' => '%%term_title%% Archives %%sep%% %%sitename%%',
            'metadesc-tax-attachment_categories' => '',
            'display-metabox-tax-attachment_categories' => false,
            'noindex-tax-attachment_categories' => true,
            'title-tax-attachment_hashtags' => '%%term_title%% Archives %%sep%% %%sitename%%',
            'metadesc-tax-attachment_hashtags' => '',
            'display-metabox-tax-attachment_hashtags' => false,
            'noindex-tax-attachment_hashtags' => true,
            'post_types-page-maintax' => 0,
            'post_types-attachment-maintax' => 0,
            'post_types-touristic_sheet-maintax' => 0,
            'post_types-short_link-maintax' => 0,
            'taxonomy-page_type-ptparent' => 0,
            'taxonomy-themes-ptparent' => 0,
            'taxonomy-places-ptparent' => 0,
            'taxonomy-seasons-ptparent' => 0,
            'taxonomy-attachment_types-ptparent' => 0,
            'taxonomy-attachment_categories-ptparent' => 0,
            'taxonomy-attachment_hashtags-ptparent' => 0,
        ];
        $this->updateOption('wpseo_titles', $wpseo_titles);

        // Duplicate Post
        $duplicate_post_types_enabled = [
            'post',
            'page',
            'short_link',
            'woody_claims',
        ];
        $this->updateOption('duplicate_post_types_enabled', $duplicate_post_types_enabled);

        $duplicate_post_roles = [
            'administrator',
            'editor',
        ];
        $this->updateOption('duplicate_post_roles', $duplicate_post_roles);
        update_option('duplicate_post_title_suffix', '(contenu dupliqué)', '', 'yes');

        // Varnish
        update_option('varnish_caching_enable', (WP_ENV == 'dev') ? false : true, '', 'yes');
        update_option('varnish_caching_ttl', (WP_ENV == 'dev') ? 600 : 21600, '', 'yes');
        update_option('varnish_caching_homepage_ttl', (WP_ENV == 'dev') ? 600 : 21600, '', 'yes');
        if ((WP_SITE_KEY == 'crt-bretagne' || WP_SITE_KEY == 'broceliande') && WP_ENV == 'prod') {
            update_option('varnish_caching_ips', '10.75.10.13:6081', '', 'yes');
        } else {
            update_option('varnish_caching_ips', (WP_ENV == 'prod') ? 'wpv1.rc.prod:80' : '127.0.0.1:80', '', 'yes');
        }
        update_option('varnish_caching_purge_key', 'l6ka6sb3hff9fzhx4h2qa38iqgyedznou5hawcj4rgfxlvx9m69zyqtz78yfsmws', '', 'yes');
        update_option('varnish_caching_cookie', 'y0ecy4qrkcw5rkfdyxyuf9dsoi62omz5fnpkdou8er5xcfeg7hvkqskyn7ps961j', '', 'yes');
        update_option('varnish_caching_debug', (WP_ENV == 'prod') ? false : true, '', 'yes');
        if ((WP_SITE_KEY != 'crt-bretagne')) {
            update_option('varnish_caching_dynamic_host', true, '', 'yes');
        }
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
