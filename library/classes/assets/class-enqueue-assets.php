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
    protected $globalScriptString;
    protected $assetPaths;
    protected $isTouristicPlaylist;
    protected $isTouristicSheet;
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


        // Theme Version
        $this->wThemeVersion = get_option('woody_theme_version');
    }

    protected function registerHooks()
    {
        add_action('wp_enqueue_scripts', [$this, 'init'], 1); // Use Hook to have global post context
        add_action('admin_enqueue_scripts', [$this, 'init'], 1); // Use Hook to have global post context

        add_action('woody_theme_update', [$this, 'woodyThemeUpdate']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueLibraries']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
        add_action('login_enqueue_scripts', [$this, 'enqueueAdminAssets']);
        add_filter('heartbeat_settings', [$this, 'heartbeatSettings']);
        add_filter('woody_enqueue_favicons', [$this, 'enqueueFavicons']);

        // Si vous utilisez HTML5, wdjs_use_html5 est un filtre qui enlève l’attribut type="text/javascript"
        add_filter('wdjs_use_html5', '__return_true');

        // hack for googlemap script enqueuing
        add_filter('clean_url', [$this, 'so_handle_038'], 99, 3);

        //plugin deferred labJS is activated
        add_action('wdjs_deferred_script_wait', [$this, 'labjsAfterMyScript'], 10, 2);
    }

    public function init()
    {
        $this->siteConfig = apply_filters('woody_theme_siteconfig', []);
        $this->globalScriptString = $this->setGlobalScriptString();
    }

    // print inline scripts after specified scripts (labJS only)
    public function labjsAfterMyScript($wait, $handle)
    {
        // after jQuery => add globalScript
        if ('jquery' === $handle) {
            $wait = $this->globalScriptString;
        }
        // after ngScripts => bootstrap angular app
        elseif ('hawwwai_ng_scripts' === $handle) {
            $wait = "function(){angular.bootstrap(document, ['drupalAngularApp']);}";
        }

        return $wait;
    }

    public function enqueueLibraries()
    {
        // Define $this->isTouristicPlaylist, $this->isTouristicSheet et $this->wThemeVersion
        $this->setGlobalVars();

        // Remove heartbeat from front
        wp_deregister_script('heartbeat');

        // Remove Gutenberg CSS
        wp_dequeue_style('wp-block-library');

        // Deregister the jquery version bundled with WordPress & define another
        wp_deregister_script('jquery');
        wp_deregister_script('jquery-migrate');

        // define apiurl according to WP_ENV
        switch (WP_ENV) {
            case 'dev':
                $jsModeSuffix = 'debug';
                $apirender_base_uri = 'https://api.tourism-system.com/render';
                // \PC::Debug($js_dependencies_rcmap);
                // $apirender_base_uri = 'https://api.tourism-system.rc-preprod.com/render';
                // $apirender_base_uri = 'http://127.0.0.1:8000'; // use localhost apirender (gulp serve)
                break;
            case 'preprod':
                $jsModeSuffix = 'debug';
                $apirender_base_uri = 'https://api.tourism-system.com/render';
                break;
            default:
                $jsModeSuffix = 'min';
                $apirender_base_uri = 'https://api.tourism-system.com/render';
                break;
        }

        // dependencies for raccourci map js
        $js_dependencies_rcmap = [
            'jquery', 'touristicmaps_leaflet',
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

        // CDN hosted jQuery placed in the header, as some plugins require that jQuery is loaded in the header.
        $jQuery_version = '3.6.0';
        if ($this->isTouristicPlaylist || ($this->isTouristicSheet && !defined('IS_WOODY_HAWWWAI_SHEET_ENABLE'))) {
            $jQuery_version = '2.1.4';
        }
        wp_enqueue_script('jquery', 'https://cdn.jsdelivr.net/npm/jquery@' . $jQuery_version . '/dist/jquery.min.js', [], null);

        if (!$this->isTouristicSheet || defined('IS_WOODY_HAWWWAI_SHEET_ENABLE')) {
            wp_enqueue_script('jsdelivr_swiper', 'https://cdn.jsdelivr.net/npm/swiper@4.4.1/dist/js/swiper.min.js', [], null);
        }

        $current_lang = apply_filters('woody_pll_current_language', null);
        wp_enqueue_script('jsdelivr_flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr@4.5.7/dist/flatpickr.min.js', [], null);
        if (in_array($current_lang, ['fr', 'es', 'nl', 'it', 'de', 'ru', 'ja', 'pt'])) {
            wp_enqueue_script('jsdelivr_flatpickr_l10n', 'https://cdn.jsdelivr.net/npm/flatpickr@4.5.7/dist/l10n/' . $current_lang . '.min.js', ['jsdelivr_flatpickr'], null);
        } else {
            wp_enqueue_script('jsdelivr_flatpickr_l10n', 'https://cdn.jsdelivr.net/npm/flatpickr@4.5.7/dist/l10n/default.min.js', ['jsdelivr_flatpickr'], null);
        }

        wp_enqueue_script('jsdelivr_nouislider', 'https://cdn.jsdelivr.net/npm/nouislider@10.1.0/distribute/nouislider.min.js', ['jquery'], null);
        wp_enqueue_script('jsdelivr_lazysizes', 'https://cdn.jsdelivr.net/npm/lazysizes@4.1.2/lazysizes.min.js', [], null);
        wp_enqueue_script('jsdelivr_moment', 'https://cdn.jsdelivr.net/npm/moment@2.22.2/min/moment-with-locales.min.js', [], null);
        wp_enqueue_script('jsdelivr_jscookie', 'https://cdn.jsdelivr.net/npm/js-cookie@2/src/js.cookie.min.js', [], null);
        wp_enqueue_script('jsdelivr_rellax', 'https://cdn.jsdelivr.net/npm/rellax@1.10.0/rellax.min.js', [], null);
        wp_enqueue_script('jsdelivr_plyr', 'https://cdn.jsdelivr.net/npm/plyr@3.6.8/dist/plyr.min.js', [], null);

        // Menus links obfuscation
        wp_enqueue_script('obf', get_template_directory_uri() . '/src/js/static/obf.min.js', [], null);

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

        wp_enqueue_script('hawwwai_universal_map', $apirender_base_uri . '/assets/scripts/raccourci/universal-mapV2.' . $jsModeSuffix . '.js', $js_dependencies_rcmap, null);

        // Playlist libraries
        if ($this->isTouristicPlaylist) {
            // CSS_Libraries (todo replace when possible)
            wp_enqueue_style('hawwwai_font_css', $this->assetPath('https://api.cloudly.space/static/assets/fonts/raccourci-font.min.css'), [], null);
            wp_enqueue_style('jsdelivr_bootstrap_css', 'https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css', [], null);
            wp_enqueue_style('jsdelivr_nouislider_css', 'https://cdn.jsdelivr.net/npm/nouislider@10.1.0/distribute/nouislider.min.css', [], null);
            wp_enqueue_style('jsdelivr_chosen_css', 'https://cdn.jsdelivr.net/npm/chosen-js@1.8.2/chosen.min.css', [], null);
            wp_enqueue_style('jsdelivr_picker_css', 'https://cdn.jsdelivr.net/npm/bootstrap-daterangepicker@2.1.27/daterangepicker.min.css', [], null);

            // JS Libraries
            wp_enqueue_script('jsdelivr_bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js', [], null);
            wp_enqueue_script('jsdelivr_match8', 'https://cdn.jsdelivr.net/npm/jquery-match-height@0.7.2/dist/jquery.matchHeight.min.js', ['jquery'], null);
            wp_enqueue_script('jsdelivr_wnumb', 'https://cdn.jsdelivr.net/npm/wnumb@1.0.4/wNumb.min.js', ['jquery'], null);
            wp_enqueue_script('jsdelivr_chosen', 'https://cdn.jsdelivr.net/npm/chosen-js@1.8.2/chosen.jquery.min.js', ['jquery'], null);
            wp_enqueue_script('jsdelivr_picker', 'https://cdn.jsdelivr.net/npm/bootstrap-daterangepicker@2.1.27/daterangepicker.min.js', ['jsdelivr_bootstrap'], null);
            wp_enqueue_script('jsdelivr_twigjs', 'https://cdn.jsdelivr.net/npm/twig@0.8.9/twig.min.js', [], null);
            wp_enqueue_script('jsdelivr_uuid', 'https://cdn.jsdelivr.net/npm/node-uuid@1.4.8/uuid.min.js', [], null);
            wp_enqueue_script('jsdelivr_lodash', 'https://cdn.jsdelivr.net/npm/lodash@3.8.0/index.min.js', [], null);
            wp_enqueue_script('jsdelivr_arrive', 'https://cdn.jsdelivr.net/npm/arrive@2.4.1/src/arrive.min.js', ['jquery'], null);
            wp_enqueue_script('hawwwai_sheet_item', $apirender_base_uri . '/assets/scripts/raccourci/sheet_item.min.js', ['jquery'], null);

            $js_dependencies__playlist = ['jsdelivr_bootstrap', 'jsdelivr_match8', 'jsdelivr_nouislider', 'jsdelivr_wnumb', 'jsdelivr_chosen', 'jsdelivr_moment', 'jsdelivr_picker', 'jsdelivr_twigjs', 'jsdelivr_uuid', 'jsdelivr_lodash', 'jsdelivr_arrive', 'hawwwai_sheet_item'];
            // if ($this->isRoadBookPlaylist) {
            //     $js_dependencies__playlist = apply_filters('js_dependencies__playlist', $js_dependencies__playlist);
            // }
            wp_enqueue_script('hawwwai_playlist', $apirender_base_uri . '/assets/scripts/raccourci/playlist.' . $jsModeSuffix . '.js', $js_dependencies__playlist, null);
            $playlist_map_query = empty($map_keys) ? '' : '?' . http_build_query($map_keys);

            if (isset($map_keys['gmKey']) && !isset($map_keys['otmKey']) && !isset($map_keys['ignKey'])) {
                wp_enqueue_script('jsdelivr_rich_marker', 'https://cdn.jsdelivr.net/npm/rich-marker@0.0.1/index.min.js', array_merge($js_dependencies_rcmap, ['hawwwai_playlist']), $this->wThemeVersion, true);
                wp_enqueue_script('hawwwai_playlist_map', $apirender_base_uri . '/assets/scripts/raccourci/playlist-map.' . $jsModeSuffix . '.js' . $playlist_map_query, array_merge($js_dependencies_rcmap, ['hawwwai_playlist', 'jsdelivr_rich_marker']), $this->wThemeVersion, true);
            } else {
                wp_enqueue_script('hawwwai_playlist_map', $apirender_base_uri . '/assets/scripts/raccourci/playlist-map.leafletV2.' . $jsModeSuffix . '.js' . $playlist_map_query, array_merge($js_dependencies_rcmap, ['hawwwai_playlist']), $this->wThemeVersion, true);
            }
        }

        // Sheet libraries
        elseif ($this->isTouristicSheet) {
            if(!defined('IS_WOODY_HAWWWAI_SHEET_ENABLE')){
                // CSS Libraries (todo replace when possible)
                wp_enqueue_style('hawwwai_font_css', $this->assetPath('https://api.cloudly.space/static/assets/fonts/raccourci-font.min.css'), [], null);
                wp_enqueue_style('hawwwai_fresco_css', 'https://api.tourism-system.com/render/assets/styles/lib/fresco.css', [], null);
                wp_enqueue_style('jsdelivr_leaflet_css', 'https://cdn.jsdelivr.net/npm/leaflet@0.7.7/dist/leaflet.min.css', [], null);
                wp_enqueue_style('jsdelivr_slick_css', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.css', [], null);
                wp_enqueue_style('jsdelivr_bootstrap_css', 'https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css', [], null);

                wp_enqueue_script('jsdelivr_lightgallery', 'https://cdn.jsdelivr.net/npm/lightgallery@1.6.11/dist/js/lightgallery.min.js', ['jquery'], null);
                wp_enqueue_script('jsdelivr_lg-pager', 'https://cdn.jsdelivr.net/npm/lightgallery@1.6.11/modules/lg-pager.min.js', ['jsdelivr_lightgallery'], null);
                wp_enqueue_script('jsdelivr_lg-thumbnail', 'https://cdn.jsdelivr.net/npm/lightgallery@1.6.11/modules/lg-thumbnail.min.js', ['jsdelivr_lightgallery'], null);
                wp_enqueue_script('jsdelivr_lg-video', 'https://cdn.jsdelivr.net/npm/lightgallery@1.6.11/modules/lg-video.min.js', ['jsdelivr_lightgallery'], null);
                wp_enqueue_script('jsdelivr_lg-zoom', 'https://cdn.jsdelivr.net/npm/lightgallery@1.6.11/modules/lg-zoom.min.js', ['jsdelivr_lightgallery'], null);
                wp_enqueue_script('jsdelivr_lg-fullscreen', 'https://cdn.jsdelivr.net/npm/lightgallery@1.6.11/modules/lg-fullscreen.min.js', ['jsdelivr_lightgallery'], null);

                wp_enqueue_script('hawwwai_ng_vendor', $apirender_base_uri . '/assets/scripts/vendor.js', [], null);
                wp_enqueue_script('hawwwai_ng_libs', $apirender_base_uri . '/assets/scripts/misclibs.js', [], null);
                wp_enqueue_script('hawwwai_ng_app', $apirender_base_uri . '/assets/app.js', [], null);
                wp_enqueue_script('hawwwai_ng_scripts', $apirender_base_uri . '/assets/scripts/scriptsV2.js', [], null);
                wp_enqueue_script('hawwwai_sheet_item', $apirender_base_uri . '/assets/scripts/raccourci/sheet_item.' . $jsModeSuffix . '.js', ['jsdelivr_match8'], null);
                wp_enqueue_script('hawwwai_itinerary', $apirender_base_uri . '/assets/scripts/raccourci/itinerary.' . $jsModeSuffix . '.js', ['jquery', 'hawwwai_ng_scripts'], null);
                wp_enqueue_script('hawwwai_fresco', $apirender_base_uri . '/assets/scripts/lib/fresco.js', ['jquery'], null);
            }

            // JS Libraries
            wp_enqueue_script('jsapi', 'https://www.google.com/jsapi', [], null);
            wp_enqueue_script('google_recaptcha', 'https://www.google.com/recaptcha/api.js', [], null);
            wp_enqueue_script('jsdelivr_lodash', 'https://cdn.jsdelivr.net/npm/lodash@3.8.0/index.min.js"', [], null);
            wp_enqueue_script('jsdelivr_slick', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js', ['jquery'], null);
            wp_enqueue_script('jsdelivr_match8', 'https://cdn.jsdelivr.net/npm/jquery-match-height@0.7.2/dist/jquery.matchHeight.min.js', ['jquery'], null);
            wp_enqueue_script('jsdelivr_highcharts', 'https://cdn.jsdelivr.net/npm/highcharts@6.2.0/highcharts.min.js', ['jquery'], null);


        }

        // Add the comment-reply library on pages where it is necessary
        if (is_singular() && comments_open() && get_option('thread_comments')) {
            wp_enqueue_script('comment-reply');
        }
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
            'jsdelivr_plyr',
            'jsdelivr_jscookie',
            'wp-i18n',
        ];

        if (!$this->isTouristicSheet || defined('IS_WOODY_HAWWWAI_SHEET_ENABLE')) {
            $dependencies[] = 'jsdelivr_swiper';
        }
        $dependencies = apply_filters('woody_mainjs_dependencies', $dependencies);
        wp_enqueue_script('main-javascripts', WP_DIST_URL . $this->assetPath('/js/main.js'), $dependencies, null);

        // Enqueue the main Stylesheet.
        if (($this->isTouristicSheet && !defined('IS_WOODY_HAWWWAI_SHEET_ENABLE')) || $this->isTouristicPlaylist) {
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
        $dependencies = ['jquery', 'admin-jsdelivr-lazysizes', 'admin_jsdelivr_flatpickr', 'admin_jsdelivr_flatpickr_l10n'];
        wp_enqueue_script('admin-jsdelivr-lazysizes', 'https://cdn.jsdelivr.net/npm/lazysizes@4.1.2/lazysizes.min.js', [], null, true);
        wp_enqueue_script('admin-javascripts', WP_DIST_URL . $this->assetPath('/js/admin.js'), $dependencies, null, true);
        wp_enqueue_script('admin_jsdelivr_flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr@4.5.7/dist/flatpickr.min.js', [], null, true);
        wp_enqueue_script('admin_jsdelivr_flatpickr_l10n', 'https://cdn.jsdelivr.net/npm/flatpickr@4.5.7/dist/l10n/fr.min.js', ['admin_jsdelivr_flatpickr'], null, true);

        // Added global vars
        wp_add_inline_script('admin-javascripts', 'var siteConfig = ' . json_encode($this->siteConfig, JSON_THROW_ON_ERROR) . ';', 'before');
        wp_add_inline_script('admin-javascripts', 'document.addEventListener("DOMContentLoaded",()=>{document.body.classList.add("windowReady")});', 'after');

        // Enqueue the main Stylesheet.
        wp_enqueue_style('admin-stylesheet', WP_DIST_URL . $this->assetPath('/css/admin.css'), [], null);
    }

    public function heartbeatSettings()
    {
        return ['interval' => 120];
    }

    public function enqueueFavicons()
    {
        $return = [];
        $favicon_name = apply_filters('woody_favicon_name', 'favicon');

        foreach (['favicon', '16', '32', '64', '120', '128', '152', '167', '180', '192'] as $icon) {
            $return[$icon] = WP_DIST_URL . $this->assetPath('/favicon/' . $favicon_name . '/' . (($icon == 'favicon') ? $favicon_name . '.ico' : $favicon_name . '.' . $icon . 'w-' . $icon . 'h.png'));
        }

        return $return;
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
        foreach ($jsWithGetParams as $key => $jsScriptNeedle) {
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

    protected function setGlobalScriptString()
    {
        $globalScriptString = [
            'window.useLeafletLibrary' => 0,
            'window.apirenderlistEnabled' => true,
            // inject siteConfig
            'window.siteConfig' => json_encode($this->siteConfig, JSON_THROW_ON_ERROR),
            // init DrupalAngularConfig if doesn't exist
            'window.DrupalAngularConfig' => 'window.DrupalAngularConfig || {}',
            // fill DrupalAngularConfig (some properties may already exists)
            'window.DrupalAngularConfig.apiAccount' => 'window.DrupalAngularConfig.apiAccount || {}',
            'window.DrupalAngularConfig.apiAccount.login' => (empty($this->siteConfig['login'])) ? '{}' : json_encode($this->siteConfig['login'], JSON_THROW_ON_ERROR),
            'window.DrupalAngularConfig.apiAccount.password' => (empty($this->siteConfig['password'])) ? '{}' : json_encode($this->siteConfig['password'], JSON_THROW_ON_ERROR),
            // inject mapKeys in DrupalAngularAppConfig
            'window.DrupalAngularConfig.mapProviderKeys' => (empty($this->siteConfig['mapProviderKeys'])) ? '{}' : json_encode($this->siteConfig['mapProviderKeys'], JSON_THROW_ON_ERROR),
        ];

        if (!empty($this->siteConfig['mapProviderKeys'])) {
            $map_keys = $this->siteConfig['mapProviderKeys'];
            if (isset($map_keys['otmKey']) || isset($map_keys['ignKey'])) {
                $globalScriptString['window.useLeafletLibrary'] = true;
            }
        }

        // Ancienne méthode pour appeler les fonts en asynchrone voir ligne 227
        //$globalScriptString = apply_filters('woody_theme_global_script_string', $globalScriptString);

        // Create inline script
        $return = "function(){";
        foreach ($globalScriptString as $name => $val) {
            $return .= $name . '=' . $val . ';';
        }

        return $return . "}";
    }
}
