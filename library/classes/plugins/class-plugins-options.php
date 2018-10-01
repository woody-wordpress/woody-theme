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
        add_action('woody_theme_update', array($this, 'defineOptions'), 1);
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
                'force_custom_slugs' => 0,
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

        $redirection_options = [
            'expire_404' => '-1',
            'expire_redirect' => '-1',
            'ip_logging' => 0,
        ];
        $this->updateOption('redirection_options', $redirection_options);

        // Set default options for NestedPages
        $this->updateOption('nestedpages_menusync', 'nosync');
        $this->updateOption('nestedpages_disable_menu', 'true');
        $nestedpages_roles = ['editor'];
        $this->updateOption('nestedpages_allowsorting', $nestedpages_roles);

        // Yoast SEO
        $wpseo = [
            'ms_defaults_set'            => false,
            'disableadvanced_meta'       => true,
            'onpage_indexability'        => false,
            // 'googleverify'               => '',
            'has_multiple_authors'       => false,
            'environment_type'           => (WP_ENV == 'prod' && WP_ENV !=  'crt-bretagne') ? 'production' : '', // TODO remove crt-bretagne after MEL
            'content_analysis_active'    => true,
            'keyword_analysis_active'    => false,
            'enable_admin_bar_menu'      => false,
            'enable_cornerstone_content' => false,
            'enable_xml_sitemap'         => true,
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
            'title-touristic_sheet' => '%%title%% %%sep%% %%sitename%%',
            'metadesc-touristic_sheet' => '%%cf_page_teaser_desc%%',
            'noindex-touristic_sheet' => false,
            'showdate-touristic_sheet' => false,
            'display-metabox-pt-touristic_sheet' => true,
            'title-tax-category' => '%%term_title%% Archives %%sep%% %%sitename%%',
            'metadesc-tax-category' => '',
            'display-metabox-tax-category' => false,
            'noindex-tax-category' => true,
            'title-tax-post_format' => '%%term_title%% Archives %%sep%% %%sitename%%',
            'metadesc-tax-post_format' => '',
            'display-metabox-tax-post_format' => false,
            'noindex-tax-post_format' => true,
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
            'post_types-post-maintax' => 0,
            'post_types-page-maintax' => 0,
            'post_types-attachment-maintax' => 0,
            'taxonomy-page_type-ptparent' => 0,
            'taxonomy-themes-ptparent' => 0,
            'taxonomy-places-ptparent' => 0,
            'taxonomy-seasons-ptparent' => 0,
            'taxonomy-attachment_types-ptparent' => 0,
            'taxonomy-attachment_categories-ptparent' => 0,
            'taxonomy-attachment_hashtags-ptparent' => 0,
            'title-tax-post_tag' => '%%term_title%% Archives %%page%% %%sep%% %%sitename%%',
            'metadesc-tax-post_tag' => '',
            'noindex-tax-post_tag' => false,
        ];
        $this->updateOption('wpseo_titles', $wpseo_titles);
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
