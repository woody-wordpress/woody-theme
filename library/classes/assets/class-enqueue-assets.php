<?php

/**
 * Assets enqueue
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_Enqueue_Assets
{
    public $isRoadBookSheet;

    protected $siteConfig;

    protected $drupalAngularConfig;

    protected $drupalAngularConfigHawwwai;

    protected $assetPaths;

    protected $isTouristicPlaylist;

    protected $isTouristicSheet;

    protected $isTouristicSheet2018;

    protected $isTouristicSheet2024;

    protected $wThemeVersion;

    public function __construct()
    {
        $this->assetPaths = $this->setAssetPaths();
        $this->registerHooks();
    }

    private function setGlobalVars()
    {
        // Get page type
        global $post;
        $pageType = (!empty($post) && !empty($post->ID)) ? getTermsSlugs($post->ID, 'page_type') : [];

        // If page miroir, get page type of the referenced post
        if (in_array("mirror_page", $pageType)) {
            $mirror = get_field('mirror_page_reference', $post->ID);
            $pageType = (empty($mirror)) ? [] : getTermsSlugs($mirror, 'page_type');
        }

        //TODO: Déplacer dans l'addon hawwwai
        // Define vars for touristic content and allow to override it
        $this->isTouristicPlaylist = apply_filters('isTouristicPlaylist', in_array('playlist_tourism', $pageType));
        $this->isTouristicSheet = apply_filters('isTouristicSheet', !empty($post) && $post->post_type === 'touristic_sheet');
        $this->isTouristicSheet2018 = ($this->isTouristicSheet && !defined('IS_WOODY_HAWWWAI_SHEET_ENABLE'));
        $this->isTouristicSheet2024 = ($this->isTouristicSheet && defined('IS_WOODY_HAWWWAI_SHEET_ENABLE'));

        // Theme Version
        $this->wThemeVersion = get_option('woody_theme_version');
    }

    protected function registerHooks()
    {
        add_action('wp_enqueue_scripts', [$this, 'init'], 1); // Use Hook to have global post context
        add_action('admin_enqueue_scripts', [$this, 'init'], 1); // Use Hook to have global post context
        add_action('wp_print_scripts', [$this, 'wpPrintScripts'], 100); // Use Hook to have global post context

        add_action('woody_theme_update', [$this, 'woodyThemeUpdate']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueLibraries']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
        add_action('login_enqueue_scripts', [$this, 'enqueueAdminAssets']);
        add_filter('heartbeat_settings', [$this, 'heartbeatSettings']);
        add_filter('woody_enqueue_favicons', [$this, 'enqueueFavicons']);
        add_filter('wp_resource_hints', [$this, 'wpResourceHints'], 10, 2);
        add_action('admin_head', [$this, 'adminHead']);
        add_action('init', [$this, 'tinymceAddStylesheet']);

        // Si vous utilisez HTML5, wdjs_use_html5 est un filtre qui enlève l’attribut type="text/javascript"
        add_filter('wdjs_use_html5', '__return_true');

        // hack for googlemap script enqueuing
        add_filter('clean_url', [$this, 'so_handle_038'], 99, 3);

        // Enqueue
        add_filter('woody_custom_meta', [$this, 'woodyCustomMeta'], 1, 1);

        // Added defer on front
        if (!is_admin()) {
            add_filter('script_loader_tag', [$this, 'scriptLoaderTag'], 10, 2);
            add_filter('style_loader_tag', [$this, 'styleLoaderTag'], 10, 2);
        }
    }

    public function init()
    {
        $this->siteConfig = apply_filters('woody_theme_siteconfig', []);
        $this->drupalAngularConfig = $this->setDrupalAngularConfig();
        $this->drupalAngularConfigHawwwai = $this->setDrupalAngularConfigHawwwai();
    }

    public function wpPrintScripts()
    {
        global $wp_scripts;

        // Replace external file i18n-ltr.min.js
        unset($wp_scripts->registered['wp-i18n']->extra);
    }

    public function scriptLoaderTag($tag, $handle)
    {
        if(strpos($tag, '.mjs') !== false) {
            return str_replace(' src', ' type="module" src', $tag);
        } else {
            return str_replace(' src', ' defer src', $tag);
        }
    }

    public function styleLoaderTag($html, $handle)
    {
        if ((strpos($handle, 'addon') !== false && strpos($handle, 'roadbook') === false) || strpos($handle, 'jsdelivr') !== false || strpos($handle, 'hawwwai') !== false || strpos($handle, 'leaflet') !== false || strpos($handle, 'google') !== false || strpos($handle, 'wicon') !== false) {
            $fallback = '<noscript>' . $html . '</noscript>';
            $preload = str_replace("rel='stylesheet'", "rel='preload' as='style' onload='this.onload=null;this.rel=\"stylesheet\"'", $html);
            $html = $preload . $fallback;
        }
        return $html;
    }

    public function woodyCustomMeta($head_top)
    {
        // CDN hosted jQuery placed in the header, as some plugins require that jQuery is loaded in the header.
        $jQuery_version = $this->getJqueryVersion();
        $importmap = apply_filters('woody_importmap_js', [
            'jquery' => get_template_directory_uri() . '/src/lib/custom/jquery@' . $jQuery_version . '.min.mjs',
            'woody_library_filter' => woody_addon_asset_path('woody-library', 'js/filter.js'),
            'woody_library_summary_component' => woody_addon_asset_path('woody-library', 'js/modules/components/summary/summary-component.mjs'),
            'woody_library_summary_map_manager' => woody_addon_asset_path('woody-library', 'js/modules/managers/summary/summary-map-manager.mjs'),
            'woody_library_summary_accordion_manager' => woody_addon_asset_path('woody-library', 'js/modules/managers/summary/summary-accordion-manager.mjs'),
            'woody_library_card_toggler_component' => woody_addon_asset_path('woody-library', 'js/modules/components/card/card-toggler-component.mjs'),
            'woody_library_card_slider_component' => woody_addon_asset_path('woody-library', 'js/modules/components/card/card-slider-component.mjs'),
            'woody_library_card_map_slider_component' => woody_addon_asset_path('woody-library', 'js/modules/components/card/card-map-slider-component.mjs'),
            'woody_library_card_map_manager' => woody_addon_asset_path('woody-library', 'js/modules/managers/card/card-map-manager.mjs'),
            'woody_library_focus_map_controller' => woody_addon_asset_path('woody-library', 'js/modules/controllers/focus-map-controller.mjs'),
        ]);

        if(!empty($importmap)) {
            $head_top[] = '<script type="importmap">' . json_encode(['imports' => $importmap]) . '</script>';
        }

        return $head_top;
    }

    public function enqueueLibraries()
    {
        $this->setGlobalVars();

        // Remove heartbeat from front
        wp_deregister_script('heartbeat');

        // Remove Gutenberg CSS
        wp_dequeue_style('global-styles');
        wp_dequeue_style('wp-block-library');
        wp_dequeue_style('classic-theme-styles'); // /wp/wp-includes/css/classic-themes.min.css?ver=1

        // Deregister the jquery version bundled with WordPress & define another
        wp_deregister_script('jquery');
        wp_deregister_script('jquery-migrate');

        // Remove Gutenberg JS
        wp_deregister_script('masonry');
        wp_deregister_script('imagesloaded');

        // REVIEW: A décommenter si nous arrêtons d'utiliser i18n
        // wp_deregister_script('wp-polyfill');
        // wp_deregister_script('regenerator-runtime');
        // wp_deregister_script('hooks');

        // define apiurl according to WP_ENV
        // If preprod render is eneeded use $apirender_base_uri = 'https://api.tourism-system.rc-preprod.com/render';
        // TODO : in DEV, if jsModeSuffix is 'debug', "hwConfig undefined" error occurs - @rudy
        switch (WP_ENV) {
            case 'preprod':
                $jsModeSuffix = 'debug';
                $apirender_base_uri = 'https://api.tourism-system.com/render';
                break;
            case 'dev':
            default:
                $jsModeSuffix = 'min';
                $apirender_base_uri = 'https://api.tourism-system.com/render';
                //$apirender_base_uri = 'https://api.cloudly.space/render';
                break;
        }

        // CDN hosted jQuery placed in the header, as some plugins require that jQuery is loaded in the header.
        $jQuery_version = $this->getJqueryVersion();
        wp_enqueue_script('jquery', get_template_directory_uri() . '/src/lib/custom/jquery@' . $jQuery_version . '.min.mjs', [], null); // TODO: Latest 3.7.1
        wp_add_inline_script('jquery', 'window.siteConfig = ' . json_encode($this->siteConfig, JSON_THROW_ON_ERROR) . ';', 'before');

        if (!$this->isTouristicSheet2018) {
            wp_enqueue_script('jsdelivr_swiper', get_template_directory_uri() . '/src/lib/npm/swiper/dist/js/swiper.min.js', [], '4.5.1');
        }

        $current_lang = apply_filters('woody_pll_current_language', null);
        wp_enqueue_script('jsdelivr_flatpickr', get_template_directory_uri() . '/src/lib/npm/flatpickr/dist/flatpickr.min.js', [], '4.5.7'); // TODO: Latest 4.6.13
        if (in_array($current_lang, ['fr', 'es', 'nl', 'it', 'de', 'ru', 'ja', 'pt', 'pl'])) {
            wp_enqueue_script('jsdelivr_flatpickr_l10n', get_template_directory_uri() . '/src/lib/npm/flatpickr/dist/l10n/' . $current_lang . '.js', ['jsdelivr_flatpickr'], '4.5.7');
        } else {
            wp_enqueue_script('jsdelivr_flatpickr_l10n', get_template_directory_uri() . '/src/lib/npm/flatpickr/dist/l10n/default.js', ['jsdelivr_flatpickr'], '4.5.7');
        }

        wp_enqueue_script('jsdelivr_nouislider', get_template_directory_uri() . '/src/lib/custom/nouislider@10.1.0.min.js', ['jquery'], null); // TODO: Latest 14.7.0
        wp_enqueue_script('jsdelivr_lazysizes', get_template_directory_uri() . '/src/lib/custom/lazysizes@4.1.2.min.js', [], null); // TODO: Latest 5.3.2
        wp_enqueue_script('jsdelivr_moment', get_template_directory_uri() . '/src/lib/custom/moment-with-locales@2.22.2.min.js', [], null); // TODO: Latest 2.29.4
        wp_enqueue_script('jsdelivr_moment_tz', get_template_directory_uri() . '/src/lib/custom/moment-timezone-with-data.min.js', ['jsdelivr_moment'], null);
        wp_enqueue_script('jsdelivr_jscookie', get_template_directory_uri() . '/src/lib/custom/js.cookie@2.2.1.min.js', [], null);
        wp_enqueue_script('jsdelivr_rellax', get_template_directory_uri() . '/src/lib/custom/rellax@1.10.1.min.js', [], null); // TODO: Latest 1.12.1
        wp_enqueue_script('jsdelivr_iframeresizer', get_template_directory_uri() . '/src/lib/custom/iframeResizer@4.3.7.min.js', [], '4.3.7');
        wp_enqueue_script('jsdelivr_plyr', get_template_directory_uri() . '/src/lib/npm/plyr/dist/plyr.min.js', [], '3.6.8'); // TODO: Latest 3.7.8

        // HACK : i18n LTR (replace the inline added by Core)
        wp_enqueue_script('wp-i18n-ltr', get_template_directory_uri() . '/src/js/static/i18n-ltr.min.js', ['wp-i18n'], $this->wThemeVersion);

        // Menus links obfuscation
        wp_enqueue_script('obf', get_template_directory_uri() . '/src/js/static/obf.min.js', [], $this->wThemeVersion);

        if ($this->isTouristicPlaylist || $this->isTouristicSheet2018) {
            // dependencies for raccourci map js
            $js_dependencies_rcmap = [
                'jquery',
                'touristicmaps_leaflet',
            ];

            // get map keys
            $map_keys = $this->siteConfig['mapProviderKeys'];
            if (!empty($map_keys)) {
                $map_keys['v'] = $this->wThemeVersion;
            }

            if (isset($map_keys['otmKey'])) {
                $js_dependencies_rcmap[] = 'touristicmaps_tangram';
            }

            if (isset($map_keys['gmKey'])) {
                $js_dependencies_rcmap[] = 'gg_maps';
            }

            // SHEET: need to load tangram always for now (bug in vendor angular)
            if ($this->isTouristicSheet && !in_array('touristicmaps_tangram', $js_dependencies_rcmap)) {
                $js_dependencies_rcmap[] = 'touristicmaps_tangram';
            }

            // Touristic maps libraries
            wp_enqueue_script('touristicmaps_leaflet', 'https://tiles.touristicmaps.com/libs/leaflet.min.js', [], null);
            wp_enqueue_style('leaflet_css', 'https://tiles.touristicmaps.com/libs/tmaps.min.css', [], null);

            //TODO: fix bug with Gmap and Universal Map
            // if (isset($map_keys['otmKey']) || isset($map_keys['ignKey']) || $this->isTouristicSheet || $this->isRoadBookSheet) {
            // need to load tangram always in TOURISTIC SHEET for now (bug in vendor angular) ↓
            wp_enqueue_script('touristicmaps_tangram', 'https://tiles.touristicmaps.com/libs/tangram.min.js', [], null);
            wp_enqueue_script('touristicmaps_cluster', 'https://tiles.touristicmaps.com/libs/markercluster.min.js', [], null);
            wp_enqueue_script('touristicmaps_locate', 'https://tiles.touristicmaps.com/libs/locate.min.js', [], null);
            wp_enqueue_script('touristicmaps_geocoder', 'https://tiles.touristicmaps.com/libs/geocoder.min.js', [], null);
            wp_enqueue_script('touristicmaps_fullscreen', 'https://tiles.touristicmaps.com/libs/fullscreen.min.js', [], null);
            // }

            if (isset($map_keys['gmKey'])) {
                wp_enqueue_script('gg_maps', 'https://maps.googleapis.com/maps/api/js?key=' . $map_keys['gmKey'] . '&v=3.33&libraries=geometry,places', [], null);
            } elseif ($this->isTouristicSheet || $this->isRoadBookSheet) { // absolutely needed in angular
                wp_enqueue_script('gg_maps', 'https://maps.googleapis.com/maps/api/js?v=3.33&libraries=geometry,places', [], null);
            }

            // wp_enqueue_script('hawwwai_universal_map', $apirender_base_uri . '/assets/scripts/raccourci/universal-mapV2.' . $jsModeSuffix . '.js', $js_dependencies_rcmap, null);
        }

        // Playlist libraries
        if ($this->isTouristicPlaylist) {
            // CSS_Libraries (todo replace when possible)
            wp_enqueue_style('hawwwai_font_css', $this->assetPath('https://api.cloudly.space/static/assets/fonts/raccourci-font.min.css'), [], null);
            wp_enqueue_style('jsdelivr_bootstrap_css', get_template_directory_uri() . '/src/lib/npm/bootstrap/dist/css/bootstrap.min.css', [], '3.3.7');
            wp_enqueue_style('jsdelivr_nouislider_css', get_template_directory_uri() . '/src/lib/custom/nouislider@10.1.0.min.css', [], null);
            wp_enqueue_style('jsdelivr_chosen_css', get_template_directory_uri() . '/src/lib/custom/chosen@1.8.2.min.css', [], null);
            wp_enqueue_style('jsdelivr_picker_css', get_template_directory_uri() . '/src/lib/custom/daterangepicker@2.1.30.min.css', [], null);

            // JS Libraries
            wp_enqueue_script('jsdelivr_bootstrap', get_template_directory_uri() . '/src/lib/npm/bootstrap/dist/js/bootstrap.min.js', [], '3.3.7');
            wp_enqueue_script('jsdelivr_match8', get_template_directory_uri() . '/src/lib/custom/jquery.matchHeight@0.7.2.min.js', ['jquery'], null);
            wp_enqueue_script('jsdelivr_wnumb', get_template_directory_uri() . '/src/lib/custom/wNumb@1.0.4.min.js', ['jquery'], null);
            wp_enqueue_script('jsdelivr_chosen', get_template_directory_uri() . '/src/lib/custom/chosen.jquery@1.8.2.min.js', ['jquery'], null);
            wp_enqueue_script('jsdelivr_picker', get_template_directory_uri() . '/src/lib/custom/daterangepicker@2.1.30.min.js', ['jsdelivr_bootstrap'], null);
            wp_enqueue_script('jsdelivr_twigjs', get_template_directory_uri() . '/src/lib/custom/twig@0.8.9.min.js', [], null);
            wp_enqueue_script('jsdelivr_uuid', get_template_directory_uri() . '/src/lib/custom/uuid@1.4.8.min.js', [], null);
            wp_enqueue_script('jsdelivr_lodash', get_template_directory_uri() . '/src/lib/custom/lodash@3.8.0.min.js', [], null);
            wp_enqueue_script('jsdelivr_arrive', get_template_directory_uri() . '/src/lib/custom/arrive@2.4.1.min.js', ['jquery'], null);
            wp_enqueue_script('hawwwai_sheet_item', $apirender_base_uri . '/assets/scripts/raccourci/sheet_item.min.js', ['jquery'], null);

            $js_dependencies__playlist = ['jsdelivr_bootstrap', 'jsdelivr_match8', 'jsdelivr_nouislider', 'jsdelivr_wnumb', 'jsdelivr_chosen', 'jsdelivr_moment', 'jsdelivr_picker', 'jsdelivr_twigjs', 'jsdelivr_uuid', 'jsdelivr_lodash', 'jsdelivr_arrive', 'hawwwai_sheet_item'];
            // if ($this->isRoadBookPlaylist) {
            //     $js_dependencies__playlist = apply_filters('js_dependencies__playlist', $js_dependencies__playlist);
            // }
            wp_enqueue_script('hawwwai_playlist', $apirender_base_uri . '/assets/scripts/raccourci/playlist.' . $jsModeSuffix . '.js', $js_dependencies__playlist, null);
            $playlist_map_query = empty($map_keys) ? '' : '?' . http_build_query($map_keys);

            if (isset($map_keys['gmKey']) && !isset($map_keys['otmKey']) && !isset($map_keys['ignKey'])) {
                wp_enqueue_script('jsdelivr_rich_marker', get_template_directory_uri() . '/src/lib/custom/rich-marker@0.0.1.min.js', array_merge($js_dependencies_rcmap, ['hawwwai_playlist']), null);
                wp_enqueue_script('hawwwai_playlist_map', $apirender_base_uri . '/assets/scripts/raccourci/playlist-map.' . $jsModeSuffix . '.js' . $playlist_map_query, array_merge($js_dependencies_rcmap, ['hawwwai_playlist', 'jsdelivr_rich_marker']), $this->wThemeVersion);
            } else {
                wp_enqueue_script('hawwwai_playlist_map', $apirender_base_uri . '/assets/scripts/raccourci/playlist-map.leafletV2.' . $jsModeSuffix . '.js' . $playlist_map_query, array_merge($js_dependencies_rcmap, ['hawwwai_playlist']), $this->wThemeVersion);
            }
        } elseif ($this->isTouristicSheet2018) {
            // CSS Libraries (todo replace when possible)
            wp_enqueue_style('hawwwai_font_css', $this->assetPath('https://api.cloudly.space/static/assets/fonts/raccourci-font.min.css'), [], null);
            wp_enqueue_style('hawwwai_fresco_css', 'https://api.cloudly.space/render/assets/styles/lib/fresco.min.css', [], null);

            wp_enqueue_style('jsdelivr_leaflet_css', get_template_directory_uri() . '/src/lib/custom/leaflet@0.7.7.min.css', [], null);
            wp_enqueue_style('jsdelivr_slick_css', get_template_directory_uri() . '/src/lib/custom/slick@1.8.1.min.css', [], null);
            wp_enqueue_style('jsdelivr_bootstrap_css', get_template_directory_uri() . '/src/lib/npm/bootstrap/dist/css/bootstrap.min.css', [], '3.3.7');

            wp_enqueue_script('jsdelivr_lightgallery', get_template_directory_uri() . '/src/lib/custom/lightgallery@1.6.11.min.js', ['jquery'], null);
            wp_enqueue_script('jsdelivr_lg-pager', get_template_directory_uri() . '/src/lib/custom/lg-pager@1.6.11.min.js', ['jsdelivr_lightgallery'], null);
            wp_enqueue_script('jsdelivr_lg-thumbnail', get_template_directory_uri() . '/src/lib/custom/lg-thumbnail@1.6.11.min.js', ['jsdelivr_lightgallery'], null);
            wp_enqueue_script('jsdelivr_lg-video', get_template_directory_uri() . '/src/lib/custom/lg-video@1.6.11.min.js', ['jsdelivr_lightgallery'], null);
            wp_enqueue_script('jsdelivr_lg-zoom', get_template_directory_uri() . '/src/lib/custom/lg-zoom@1.6.11.min.js', ['jsdelivr_lightgallery'], null);
            wp_enqueue_script('jsdelivr_lg-fullscreen', get_template_directory_uri() . '/src/lib/custom/lg-fullscreen@1.6.11.min.js', ['jsdelivr_lightgallery'], null);
            wp_enqueue_script('jsapi', 'https://www.google.com/jsapi', [], null);
            wp_enqueue_script('google_recaptcha', 'https://www.google.com/recaptcha/api.js', [], null);
            wp_enqueue_script('jsdelivr_lodash', get_template_directory_uri() . '/src/lib/custom/lodash@3.8.0.min.js', [], null);
            wp_enqueue_script('jsdelivr_slick', get_template_directory_uri() . '/src/lib/custom/slick@1.8.1.min.js', ['jquery'], null);
            wp_enqueue_script('jsdelivr_match8', get_template_directory_uri() . '/src/lib/custom/jquery.matchHeight@0.7.2.min.js', ['jquery'], null);
            wp_enqueue_script('jsdelivr_highcharts', get_template_directory_uri() . '/src/lib/custom/highcharts@6.2.0.min.js', ['jquery'], null);

            wp_enqueue_script('hawwwai_ng_vendor', $apirender_base_uri . '/assets/scripts/vendor.js', [], null);
            wp_enqueue_script('hawwwai_ng_libs', $apirender_base_uri . '/assets/scripts/misclibs.js', [], null);
            wp_enqueue_script('hawwwai_ng_app', $apirender_base_uri . '/assets/app.js', [], null);
            wp_enqueue_script('hawwwai_ng_scripts', $apirender_base_uri . '/assets/scripts/scriptsV2.js', [], null);
            wp_enqueue_script('hawwwai_sheet_item', $apirender_base_uri . '/assets/scripts/raccourci/sheet_item.' . $jsModeSuffix . '.js', ['jsdelivr_match8'], null);
            wp_enqueue_script('hawwwai_itinerary', $apirender_base_uri . '/assets/scripts/raccourci/itinerary.' . $jsModeSuffix . '.js', ['jquery', 'hawwwai_ng_scripts'], null);
            wp_enqueue_script('hawwwai_fresco', $apirender_base_uri . '/assets/scripts/lib/fresco.js', ['jquery'], null);
            wp_enqueue_script('hawwwai_ng_init', get_template_directory_uri() . '/src/js/static/ng_init.min.js', ['hawwwai_ng_scripts'], $this->wThemeVersion);
        }

        // window.DrupalAngularConfig.mapProviderKeys
        wp_add_inline_script('jquery', $this->drupalAngularConfig, 'before');

        // window.DrupalAngularConfig.apiAccount
        if ($this->isTouristicPlaylist || $this->isTouristicSheet2018) {
            wp_enqueue_script('hawwwai_angular_config_footer', get_template_directory_uri() . '/src/js/static/angular-config.min.js', [], null, true);
            wp_add_inline_script('hawwwai_angular_config_footer', $this->drupalAngularConfigHawwwai, 'after');
        }

        // Add the comment-reply library on pages where it is necessary
        if (is_singular() && comments_open() && get_option('thread_comments')) {
            wp_enqueue_script('comment-reply');
        }
    }

    private function getJqueryVersion()
    {
        return ($this->isTouristicPlaylist || $this->isTouristicSheet2018) ? '2.1.4' : '3.7.1';
    }

    public function enqueueAssets()
    {
        // Define $this->isTouristicPlaylist, $this->isTouristicSheet et $this->wThemeVersion
        $this->setGlobalVars();

        // Nouvelle méthode pour appeler les fonts en synchrone voir ligne 342
        $webfonts = apply_filters('woody_theme_global_script_string', []);
        if (!empty($webfonts['window.WebFontConfig'])) {
            $webfonts = json_decode($webfonts['window.WebFontConfig'], true, 512, JSON_THROW_ON_ERROR);
            if (!empty($webfonts['google']) && !empty($webfonts['google']['families'])) {
                foreach ($webfonts['google']['families'] as $webfont) {
                    wp_enqueue_style('google-font-' . sanitize_title($webfont), 'https://fonts.googleapis.com/css?family=' . $webfont, [], null);
                }
            }
        }

        // Enqueue the main Scripts
        $dependencies = [
            'jquery',
            'jsdelivr_flatpickr',
            'jsdelivr_flatpickr_l10n',
            'jsdelivr_iframeresizer',
            'jsdelivr_plyr',
            'jsdelivr_jscookie',
            'jsdelivr_rellax',
            'wp-i18n',
        ];

        if (!$this->isTouristicSheet2018) {
            $dependencies[] = 'jsdelivr_swiper';
        }

        $dependencies = apply_filters('woody_mainjs_dependencies', $dependencies);
        wp_enqueue_script('main-javascripts', WP_DIST_URL . $this->assetPath('/js/main.mjs'), $dependencies, null);

        // Enqueue the main Stylesheet.
        if ($this->isTouristicSheet2018 || $this->isTouristicPlaylist) {
            $tourism_css = apply_filters('woody_theme_stylesheets', 'tourism');
            $tourism_css = (empty($tourism_css)) ? 'tourism' : $tourism_css;
            wp_enqueue_style('main-stylesheet', WP_DIST_URL . $this->assetPath('/css/' . $tourism_css . '.css'), [], null, 'screen');
        } else {
            $main_css = apply_filters('woody_theme_stylesheets', 'main');
            $main_css = (empty($main_css)) ? 'main' : $main_css;
            wp_enqueue_style('main-stylesheet', WP_DIST_URL . $this->assetPath('/css/' . $main_css . '.css'), [], null, 'screen');
        }

        // Enqueue css specificly for icons
        wp_enqueue_style('wicon-stylesheet', WP_DIST_URL . $this->assetPath('/css/wicon.css'), [], null, 'screen');
        wp_enqueue_style('print-stylesheet', WP_DIST_URL . $this->assetPath('/css/print.css'), [], null, 'print');
    }

    public function enqueueAdminAssets()
    {
        // Enqueue the main Scripts
        $dependencies = ['jquery', 'admin-jsdelivr_lazysizes', 'admin-jsdelivr_flatpickr', 'admin-jsdelivr_flatpickr_l10n'];
        wp_enqueue_script('admin-javascripts', WP_DIST_URL . $this->assetPath('/js/admin.js'), $dependencies, null, true);
        wp_enqueue_script('admin-jsdelivr_lazysizes', get_template_directory_uri() . '/src/lib/custom/lazysizes@4.1.2.min.js', [], null, true); // TODO: Latest 5.3.2
        wp_enqueue_script('admin-jsdelivr_flatpickr', get_template_directory_uri() . '/src/lib/npm/flatpickr/dist/flatpickr.min.js', [], '4.5.7', true); // TODO: Latest 4.6.13
        wp_enqueue_script('admin-jsdelivr_flatpickr_l10n', get_template_directory_uri() . '/src/lib/npm/flatpickr/dist/l10n/fr.js', [], '4.5.7', true); // TODO: Latest 4.6.13

        // Added global vars
        wp_add_inline_script('admin-javascripts', 'window.siteConfig = ' . json_encode($this->siteConfig, JSON_THROW_ON_ERROR) . ';', 'before');
        wp_add_inline_script('admin-javascripts', 'document.addEventListener("DOMContentLoaded",()=>{document.body.classList.add("windowReady")});', 'after');

        // Enqueue the main Stylesheet.
        wp_enqueue_style('admin-stylesheet', WP_DIST_URL . $this->assetPath('/css/admin.css'), [], null);

        // Enqueue css specificly for icons
        wp_enqueue_style('wicon-stylesheet', WP_DIST_URL . $this->assetPath('/css/wicon.css'), [], null, 'screen');
    }

    public function adminHead()
    {
        $favicons = $this->enqueueFavicons();
        echo '<link rel="shortcut icon" href="' . $favicons['favicon'] . '" />';

        $importmap = apply_filters('woody_admin_importmap_js', [
            // 'woody_lib_utils' => woody_addon_asset_path('woody-lib-utils', 'js/woody-lib-utils.mjs'),
        ]);
        if(!empty($importmap)) {
            echo '<script type="importmap">' . json_encode(['imports' => $importmap]) . '</script>';
        }
    }

    public function tinymceAddStylesheet()
    {
        add_editor_style(WP_DIST_URL . $this->assetPath('/css/admin.css'));
    }

    public function heartbeatSettings()
    {
        return ['interval' => 120];
    }

    public function enqueueFavicons()
    {
        $return = [];
        $favicon_name = apply_filters('woody_favicon_name', 'favicon');

        // rel="icon" type="image/x-icon"
        $return['favicon'] = WP_DIST_URL . $this->assetPath(sprintf('/favicon/%s/favicon.ico', $favicon_name));

        // rel="icon" type="image/png"
        foreach (['16', '32', '48'] as $size) {
            $return['icon'][$size] = WP_DIST_URL . $this->assetPath(sprintf('/favicon/%s/favicon-%sx%s.png', $favicon_name, $size, $size));
        }

        // rel="apple-touch-icon"
        foreach (['57', '60', '72', '76', '114', '120', '144', '152', '167', '180', '1024'] as $size) {
            $return['apple'][$size] = WP_DIST_URL . $this->assetPath(sprintf('/favicon/%s/apple-touch-icon-%sx%s.png', $favicon_name, $size, $size));
        }

        return $return;
    }

    public function wpResourceHints($hints, $relation_type)
    {
        if ($relation_type == 'dns-prefetch' || $relation_type == 'preconnect') {
            $hints[] = '//www.googletagmanager.com';
        }

        return $hints;
    }

    public function woodyThemeUpdate()
    {
        // Delete Cache
        wp_cache_delete('woody_asset_paths', 'woody');
    }

    private function assetPath($file_url)
    {
        if (!empty($this->assetPaths[$file_url])) {
            return $this->assetPaths[$file_url];
        }

        return $file_url;
    }

    public function so_handle_038($url, $original_url, $_context)
    {
        // array of strings to search for & make sure url are printing as it should
        $jsWithGetParams = [
            'googleapis.com', // googlemap js needle
            'assets/scripts/raccourci/playlist-map', // rc playlist-map js needle
        ];
        foreach ($jsWithGetParams as $jsScriptNeedle) {
            if (strstr($url, $jsScriptNeedle) !== false) {
                $url = str_replace("&#038;", "&", $url); // or $url = $original_url
            }
        }

        return $url;
    }

    protected function setAssetPaths()
    {
        $assetPaths = (WP_ENV != 'dev') ? wp_cache_get('woody_asset_paths', 'woody') : [];
        if (empty($assetPaths)) {
            $assetPaths = [];

            // Sources for Assets
            $directories = [
                WP_DIST_DIR,
                'https://api.cloudly.space/static/assets/fonts',
            ];

            foreach ($directories as $dir) {
                $manifest_path = $dir . '/rev-manifest.json';
                if (strpos($dir, 'http') !== false || file_exists($manifest_path)) {
                    $base_dir = strpos($dir, 'http') !== false ? $dir : '';

                    $assets = json_decode(file_get_contents($manifest_path), true, 512, JSON_THROW_ON_ERROR);
                    if (!empty($assets)) {
                        foreach ($assets as $origin => $compile) {
                            $assetPaths[$base_dir . '/' . $origin] = $base_dir . '/' . $compile;
                        }
                    }
                }
            }

            if (!empty($assetPaths) && WP_ENV != 'dev') {
                wp_cache_set('woody_asset_paths', $assetPaths, 'woody');
            }
        }

        return $assetPaths;
    }

    private function setDrupalAngularConfig()
    {
        $config = [
            'window.useLeafletLibrary' => 0,
            'window.DrupalAngularConfig' => 'window.DrupalAngularConfig || {}',
            'window.DrupalAngularConfig.mapProviderKeys' => (empty($this->siteConfig['mapProviderKeys'])) ? '{}' : json_encode($this->siteConfig['mapProviderKeys'], JSON_THROW_ON_ERROR),
        ];

        if (!empty($this->siteConfig['mapProviderKeys'])) {
            $map_keys = $this->siteConfig['mapProviderKeys'];
            if (isset($map_keys['otmKey']) || isset($map_keys['ignKey'])) {
                $config['window.useLeafletLibrary'] = 1;
            }
        }

        // Create inline script
        $return = [];
        foreach ($config as $name => $val) {
            $return[] = $name . '=' . $val . ';';
        }

        return implode('', $return);
    }

    private function setDrupalAngularConfigHawwwai()
    {
        // mapProviderKeys est présent dans le header et le footer car Render écrase DrupalAngularConfig, il faut donc le redéfinir
        $config = [
            'window.DrupalAngularConfig.apiAccount' => 'window.DrupalAngularConfig.apiAccount || {}',
            'window.DrupalAngularConfig.apiAccount.login' => (empty($this->siteConfig['login'])) ? '{}' : json_encode($this->siteConfig['login'], JSON_THROW_ON_ERROR),
            'window.DrupalAngularConfig.apiAccount.password' => (empty($this->siteConfig['password'])) ? '{}' : json_encode($this->siteConfig['password'], JSON_THROW_ON_ERROR),
            'window.DrupalAngularConfig.mapProviderKeys' => (empty($this->siteConfig['mapProviderKeys'])) ? '{}' : json_encode($this->siteConfig['mapProviderKeys'], JSON_THROW_ON_ERROR),
        ];

        // Create inline script
        $return = [];
        foreach ($config as $name => $val) {
            $return[] = $name . '=' . $val . ';';
        }

        return implode('', $return);
    }
}
