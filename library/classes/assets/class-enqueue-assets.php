<?php

/**
 * Assets enqueue
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_Enqueue_Assets
{
    protected $siteConfig;
    protected $globalScriptString;
    protected $isTouristicPlaylist;
    protected $isTouristicSheet;
    protected $wThemeVersion;

    public function __construct()
    {
        $this->siteConfig = $this->setSiteConfig();
        $this->globalScriptString = $this->setGlobalScriptString();

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
            $pageType = (!empty($mirror)) ? getTermsSlugs($mirror, 'page_type') : [];
        }

        $this->isRoadBookPlaylist = apply_filters('is_road_book_playlist', false, $post);
        $this->isTouristicPlaylist = in_array('playlist_tourism', $pageType);
        $this->isTouristicSheet = !empty($post) && $post->post_type === 'touristic_sheet';

        // Theme Version
        $this->wThemeVersion = get_option('woody_theme_version');
    }

    protected function registerHooks()
    {
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

        // Deregister the jquery version bundled with WordPress & define another
        wp_deregister_script('jquery');
        wp_deregister_script('jquery-migrate');
        $jQuery_version = '3.4.1';
        if ($this->isTouristicPlaylist || $this->isTouristicSheet || $this->isRoadBookPlaylist) {
            $jQuery_version = '2.1.4';
        }

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
            'jquery', 'jsdelivr_leaflet',
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
        if ($this->isTouristicSheet) {
            if (!in_array('touristicmaps_tangram', $js_dependencies_rcmap)) {
                array_push($js_dependencies_rcmap, 'touristicmaps_tangram');
            }
        }

        // CDN hosted jQuery placed in the header, as some plugins require that jQuery is loaded in the header.
        wp_enqueue_script('jquery', 'https://cdn.jsdelivr.net/npm/jquery@' . $jQuery_version . '/dist/jquery.min.js', [], '', true);
        wp_enqueue_script('jsdelivr_lazysizes', 'https://cdn.jsdelivr.net/npm/lazysizes@4.1.2/lazysizes.min.js', [], '', true);

        // if (WP_ENV == 'dev') {
        //     wp_enqueue_script('jsdelivr_jquery-migrate', 'https://cdn.jsdelivr.net/npm/jquery-migrate@3.0.1/dist/jquery-migrate.min.js', ['jquery'], '', true);
        // }

        // Dependencies of main.js
        wp_enqueue_script('jsdelivr_cookieconsent', 'https://cdn.jsdelivr.net/npm/cookieconsent@3.1.0/build/cookieconsent.min.js', [], '', true);

        if (!$this->isTouristicSheet) {
            wp_enqueue_script('jsdelivr_swiper', 'https://cdn.jsdelivr.net/npm/swiper@4.4.1/dist/js/swiper.min.js', [], '', true);
        }

        $current_lang = apply_filters('woody_pll_current_language', null);
        if (in_array($current_lang, ['fr', 'es', 'nl', 'it', 'de', 'ru', 'ja', 'pt'])) {
            wp_enqueue_script('jsdelivr_flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr@4.5.7/dist/flatpickr.min.js', [], '', true);
            wp_enqueue_script('jsdelivr_flatpickr_l10n', 'https://cdn.jsdelivr.net/npm/flatpickr@4.5.7/dist/l10n/' . $current_lang . '.js', ['jsdelivr_flatpickr'], '', true);
        } else {
            wp_enqueue_script('jsdelivr_flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr@4.5.7/dist/flatpickr.min.js', [], '', true);
            wp_enqueue_script('jsdelivr_flatpickr_l10n', 'https://cdn.jsdelivr.net/npm/flatpickr@4.5.7/dist/l10n/default.js', ['jsdelivr_flatpickr'], '', true);
        }

        //wp_enqueue_script('jsdelivr_webfontloader', 'https://cdn.jsdelivr.net/npm/webfontloader@1.6.28/webfontloader.js', [], '', true);
        wp_enqueue_script('jsdelivr_lightgallery', 'https://cdn.jsdelivr.net/npm/lightgallery@1.6.11/dist/js/lightgallery.min.js', ['jquery'], '', true);
        wp_enqueue_script('jsdelivr_lg-pager', 'https://cdn.jsdelivr.net/npm/lightgallery@1.6.11/modules/lg-pager.min.js', ['jsdelivr_lightgallery'], '', true);
        wp_enqueue_script('jsdelivr_lg-thumbnail', 'https://cdn.jsdelivr.net/npm/lightgallery@1.6.11/modules/lg-thumbnail.min.js', ['jsdelivr_lightgallery'], '', true);
        wp_enqueue_script('jsdelivr_lg-video', 'https://cdn.jsdelivr.net/npm/lightgallery@1.6.11/modules/lg-video.min.js', ['jsdelivr_lightgallery'], '', true);
        wp_enqueue_script('jsdelivr_lg-zoom', 'https://cdn.jsdelivr.net/npm/lightgallery@1.6.11/modules/lg-zoom.min.js', ['jsdelivr_lightgallery'], '', true);
        wp_enqueue_script('jsdelivr_lg-fullscreen', 'https://cdn.jsdelivr.net/npm/lightgallery@1.6.11/modules/lg-fullscreen.min.js', ['jsdelivr_lightgallery'], '', true);
        wp_enqueue_script('jsdelivr_nouislider', 'https://cdn.jsdelivr.net/npm/nouislider@10.1.0/distribute/nouislider.min.js', ['jquery'], '', true);
        wp_enqueue_script('jsdelivr_moment', 'https://cdn.jsdelivr.net/npm/moment@2.22.2/min/moment-with-locales.min.js', [], '', true);
        wp_enqueue_script('jsdelivr_jscookie', 'https://cdn.jsdelivr.net/npm/js-cookie@2/src/js.cookie.min.js', [], '', true);
        wp_enqueue_script('jsdelivr_rellax', 'https://cdn.jsdelivr.net/npm/rellax@1.10.0/rellax.min.js', [], '', true);
        wp_enqueue_script('jsdelivr_plyr', 'https://cdn.jsdelivr.net/npm/plyr@3.5.6/dist/plyr.min.js', [], '', true);

        // Touristic maps libraries
        wp_enqueue_script('jsdelivr_leaflet', 'https://cdn.jsdelivr.net/npm/leaflet@0.7.7/dist/leaflet-src.min.js', [], '', true);
        if (isset($map_keys['otmKey']) || $this->isTouristicSheet) {
            // need to load tangram always in TOURISTIC SHEET for now (bug in vendor angular) ↓
            wp_enqueue_script('touristicmaps_tangram', 'https://tiles.touristicmaps.com/libs/tangram.min.js?v=' . $this->wThemeVersion, [], '', true);
        }

        // Menus links obfuscation
        wp_enqueue_script('obf', get_template_directory_uri() . '/src/js/static/obf.min.js', [], '', true);

        if (isset($map_keys['gmKey'])) {
            wp_enqueue_script('gg_maps', 'https://maps.googleapis.com/maps/api/js?key=' . $map_keys['gmKey'] . '&v=3.33&libraries=geometry,places', [], '', true);
        } elseif ($this->isTouristicSheet) { // absolutely needed in angular
            wp_enqueue_script('gg_maps', 'https://maps.googleapis.com/maps/api/js?v=3.33&libraries=geometry,places', [], '', true);
        }
        wp_enqueue_script('hawwwai_universal_map', $apirender_base_uri . '/assets/scripts/raccourci/universal-map.' . $jsModeSuffix . '.js?v=' . $this->wThemeVersion, $js_dependencies_rcmap, '', true);

        // Playlist libraries
        if ($this->isTouristicPlaylist || $this->isRoadBookPlaylist) {
            // CSS_Libraries (todo replace when possible)
            wp_enqueue_style('hawwwai_font_css', 'https://api.tourism-system.com/static/assets/fonts/raccourci-font.css', [], '');
            wp_enqueue_style('jsdelivr_leaflet_css', 'https://cdn.jsdelivr.net/npm/leaflet@0.7.7/dist/leaflet.min.css', [], '');
            wp_enqueue_style('jsdelivr_bootstrap_css', 'https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css', [], '');
            wp_enqueue_style('jsdelivr_nouislider_css', 'https://cdn.jsdelivr.net/npm/nouislider@10.1.0/distribute/nouislider.min.css', [], '');
            wp_enqueue_style('jsdelivr_chosen_css', 'https://cdn.jsdelivr.net/npm/chosen-js@1.8.2/chosen.min.css', [], '');
            wp_enqueue_style('jsdelivr_picker_css', 'https://cdn.jsdelivr.net/npm/bootstrap-daterangepicker@2.1.27/daterangepicker.min.css', [], '');

            // JS Libraries
            wp_enqueue_script('jsdelivr_bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js', [], '', true);
            wp_enqueue_script('jsdelivr_match8', 'https://cdn.jsdelivr.net/npm/jquery-match-height@0.7.2/dist/jquery.matchHeight.min.js', ['jquery'], '', true);
            wp_enqueue_script('jsdelivr_wnumb', 'https://cdn.jsdelivr.net/npm/wnumb@1.0.4/wNumb.min.js', ['jquery'], '', true);
            wp_enqueue_script('jsdelivr_chosen', 'https://cdn.jsdelivr.net/npm/chosen-js@1.8.2/chosen.jquery.min.js', ['jquery'], '', true);
            wp_enqueue_script('jsdelivr_picker', 'https://cdn.jsdelivr.net/npm/bootstrap-daterangepicker@2.1.27/daterangepicker.min.js', ['jsdelivr_bootstrap'], '', true);
            wp_enqueue_script('jsdelivr_twigjs', 'https://cdn.jsdelivr.net/npm/twig@0.8.9/twig.min.js', [], '', true);
            wp_enqueue_script('jsdelivr_uuid', 'https://cdn.jsdelivr.net/npm/node-uuid@1.4.8/uuid.min.js', [], '', true);
            wp_enqueue_script('jsdelivr_lodash', 'https://cdn.jsdelivr.net/npm/lodash@3.8.0/index.min.js', [], '', true);
            wp_enqueue_script('jsdelivr_arrive', 'https://cdn.jsdelivr.net/npm/arrive@2.4.1/src/arrive.min.js', ['jquery'], '', true);
            wp_enqueue_script('hawwwai_sheet_item', $apirender_base_uri . '/assets/scripts/raccourci/sheet_item.min.js?v=' . $this->wThemeVersion, ['jquery'], '', true);

            $js_dependencies__playlist = ['jsdelivr_bootstrap', 'jsdelivr_match8', 'jsdelivr_nouislider', 'jsdelivr_wnumb', 'jsdelivr_chosen', 'jsdelivr_moment', 'jsdelivr_picker', 'jsdelivr_twigjs', 'jsdelivr_uuid', 'jsdelivr_lodash', 'jsdelivr_arrive', 'hawwwai_sheet_item'];
            wp_enqueue_script('hawwwai_playlist', $apirender_base_uri . '/assets/scripts/raccourci/playlist.' . $jsModeSuffix . '.js?v=' . $this->wThemeVersion, $js_dependencies__playlist, '', true);
            $playlist_map_query = !empty($map_keys) ? '?' . http_build_query($map_keys) : '';
            wp_enqueue_script('hawwwai_playlist_map', $apirender_base_uri . '/assets/scripts/raccourci/playlist-map.leaflet.' . $jsModeSuffix . '.js' . $playlist_map_query, array_merge($js_dependencies_rcmap, ['hawwwai_playlist']), $this->wThemeVersion, true);
        }

        // Sheet libraries
        elseif ($this->isTouristicSheet) {
            // CSS Libraries (todo replace when possible)
            wp_enqueue_style('hawwwai_font_css', 'https://api.tourism-system.com/static/assets/fonts/raccourci-font.css', [], '');
            wp_enqueue_style('hawwwai_fresco_css', 'https://api.tourism-system.com/render/assets/styles/lib/fresco.css', [], '');
            wp_enqueue_style('jsdelivr_leaflet_css', 'https://cdn.jsdelivr.net/npm/leaflet@0.7.7/dist/leaflet.min.css', [], '');
            wp_enqueue_style('jsdelivr_slick_css', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.css', [], '');
            wp_enqueue_style('jsdelivr_bootstrap_css', 'https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css', [], '');

            // JS Libraries
            wp_enqueue_script('jsapi', 'https://www.google.com/jsapi', [], '', true);
            wp_enqueue_script('google_recaptcha', 'https://www.google.com/recaptcha/api.js', [], '', true);
            wp_enqueue_script('jsdelivr_lodash', 'https://cdn.jsdelivr.net/npm/lodash@3.8.0/index.min.js"', [], '', true);
            wp_enqueue_script('jsdelivr_slick', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js', ['jquery'], '', true);
            wp_enqueue_script('jsdelivr_match8', 'https://cdn.jsdelivr.net/npm/jquery-match-height@0.7.2/dist/jquery.matchHeight.min.js', ['jquery'], '', true);
            wp_enqueue_script('jsdelivr_highcharts', 'https://cdn.jsdelivr.net/npm/highcharts@6.2.0/highcharts.min.js', ['jquery'], '', true);

            wp_enqueue_script('hawwwai_ng_vendor', $apirender_base_uri . '/assets/scripts/vendor.js?v=' . $this->wThemeVersion, [], '', true);
            wp_enqueue_script('hawwwai_ng_libs', $apirender_base_uri . '/assets/scripts/misclibs.js?v=' . $this->wThemeVersion, [], '', true);
            wp_enqueue_script('hawwwai_ng_app', $apirender_base_uri . '/assets/app.js?v=' . $this->wThemeVersion, [], '', true);
            wp_enqueue_script('hawwwai_ng_scripts', $apirender_base_uri . '/assets/scripts/scripts.js?v=' . $this->wThemeVersion, [], '', true);
            wp_enqueue_script('hawwwai_sheet_item', $apirender_base_uri . '/assets/scripts/raccourci/sheet_item.' . $jsModeSuffix . '.js?v=' . $this->wThemeVersion, ['jsdelivr_match8'], '', true);
            wp_enqueue_script('hawwwai_itinerary', $apirender_base_uri . '/assets/scripts/raccourci/itinerary.' . $jsModeSuffix . '.js?v=' . $this->wThemeVersion, ['jquery', 'hawwwai_ng_scripts'], '', true);
            wp_enqueue_script('hawwwai_fresco', $apirender_base_uri . '/assets/scripts/lib/fresco.js?v=' . $this->wThemeVersion, ['jquery'], '', true);
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
            $webfonts = json_decode($webfonts['window.WebFontConfig'], true);
            if (!empty($webfonts['google']) && !empty($webfonts['google']['families'])) {
                foreach ($webfonts['google']['families'] as $webfont) {
                    wp_enqueue_style('google-font-' . sanitize_title($webfont), 'https://fonts.googleapis.com/css?family=' . $webfont, [], '', 'all');
                }
            }
        }

        // Enqueue the main Scripts
        $dependencies = [
            'jquery',
            'jsdelivr_cookieconsent',
            'jsdelivr_flatpickr',
            'jsdelivr_flatpickr_l10n',
            'jsdelivr_lg-fullscreen',
            'jsdelivr_lg-pager',
            'jsdelivr_lg-thumbnail',
            'jsdelivr_lg-video',
            'jsdelivr_lg-zoom',
            'jsdelivr_lightgallery',
            'jsdelivr_plyr',
            'wp-i18n',
        ];

        if (!$this->isTouristicSheet) {
            $dependencies[] = 'jsdelivr_swiper';
        }
        wp_enqueue_script('main-javascripts', $this->assetPath('js/main.js'), $dependencies, '', true);

        // Enqueue the main Stylesheet.
        if ($this->isTouristicSheet || $this->isTouristicPlaylist || $this->isRoadBookPlaylist) {
            $tourism_css = apply_filters('woody_theme_stylesheets', 'tourism');
            $tourism_css = (!empty($tourism_css)) ? $tourism_css : 'tourism';
            wp_enqueue_style('main-stylesheet', $this->assetPath('css/' . $tourism_css . '.css'), [], '', 'screen');
        } else {
            $main_css = apply_filters('woody_theme_stylesheets', 'main');
            $main_css = (!empty($main_css)) ? $main_css : 'main';
            wp_enqueue_style('main-stylesheet', $this->assetPath('css/' . $main_css . '.css'), [], '', 'screen');
        }

        wp_enqueue_style('print-stylesheet', $this->assetPath('css/print.css'), [], '', 'print');
    }

    public function enqueueAdminAssets()
    {
        // Define $this->isTouristicPlaylist, $this->isTouristicSheet et $this->wThemeVersion
        $this->setGlobalVars();

        wp_enqueue_script('jsdelivr_lazysizes', 'https://cdn.jsdelivr.net/npm/lazysizes@4.1.2/lazysizes.min.js', [], '', true);

        // Enqueue the main Scripts
        $dependencies = ['jquery'];
        wp_enqueue_script('admin-javascripts', $this->assetPath('js/admin.js'), $dependencies, $this->wThemeVersion, true);
        wp_enqueue_script('admin_jsdelivr_flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr@4.5.7/dist/flatpickr.min.js', [], '');
        wp_enqueue_script('admin_jsdelivr_flatpickr_l10n', 'https://cdn.jsdelivr.net/npm/flatpickr@4.5.7/dist/l10n/fr.js', ['admin_jsdelivr_flatpickr'], '', true);

        // Added global vars
        wp_add_inline_script('admin-javascripts', 'var siteConfig = ' . json_encode($this->siteConfig) . ';', 'before');
        wp_add_inline_script('admin-javascripts', 'document.addEventListener("DOMContentLoaded",()=>{document.body.classList.add("windowReady")});', 'after');

        // Enqueue the main Stylesheet.
        wp_enqueue_style('admin-stylesheet', $this->assetPath('css/admin.css'), [], $this->wThemeVersion, 'all');
    }

    public function heartbeatSettings()
    {
        $settings['interval'] = 120; // default 15
        return $settings;
    }

    public function enqueueFavicons()
    {
        $return = [];
        $favicon_name = apply_filters('woody_favicon_name', 'favicon');

        foreach (['favicon', '16', '32', '64', '120', '128', '152', '167', '180', '192'] as $icon) {
            $return[$icon] = $this->assetPath('favicon/' .$favicon_name . '/' . (($icon == 'favicon') ? $favicon_name . '.ico' : $favicon_name . '.' . $icon . 'w-' . $icon . 'h.png'));
        }

        return $return;
    }

    private function assetPath($filename)
    {
        $manifest_path = WP_DIST_DIR . '/rev-manifest.json';
        if (file_exists($manifest_path)) {
            $manifest = json_decode(file_get_contents($manifest_path), true);

            if (!empty($manifest[$filename])) {
                $filename = $manifest[$filename];
            }
        }

        return WP_DIST_URL . '/' . $filename;
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

    protected function setSiteConfig()
    {
        // Added global vars
        $siteConfig = apply_filters('woody_theme_siteconfig', []);
        return $siteConfig;
    }

    protected function setGlobalScriptString()
    {
        $globalScriptString = [
            'window.useLeafletLibrary' => true,
            'window.apirenderlistEnabled' => true,
            // inject siteConfig
            'window.siteConfig' => json_encode($this->siteConfig),
            // init DrupalAngularConfig if doesn't exist
            'window.DrupalAngularConfig' => 'window.DrupalAngularConfig || {}',
            // fill DrupalAngularConfig (some properties may already exists)
            'window.DrupalAngularConfig.apiAccount' => 'window.DrupalAngularConfig.apiAccount || {}',
            'window.DrupalAngularConfig.apiAccount.login' => (!empty($this->siteConfig['login'])) ? json_encode($this->siteConfig['login']) : '{}',
            'window.DrupalAngularConfig.apiAccount.password' => (!empty($this->siteConfig['password'])) ? json_encode($this->siteConfig['password']) : '{}',
            // inject mapKeys in DrupalAngularAppConfig
            'window.DrupalAngularConfig.mapProviderKeys' => (!empty($this->siteConfig['mapProviderKeys'])) ? json_encode($this->siteConfig['mapProviderKeys']) : '{}',
        ];

        // Ancienne méthode pour appeler les fonts en asynchrone voir ligne 227
        //$globalScriptString = apply_filters('woody_theme_global_script_string', $globalScriptString);

        // Create inline script
        $return = "function(){";
        foreach ($globalScriptString as $name => $val) {
            $return .= $name . '=' . $val . ';';
        }
        $return .= "}";

        return $return;
    }
}
