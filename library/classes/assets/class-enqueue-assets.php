<?php
/**
 * Assets enqueue
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_Enqueue_Assets
{
    protected $mapKeys;
    protected $siteConfig;
    protected $globalScriptString;

    public function __construct()
    {
        $this->mapKeys = getMapKeys(); // defined in functions touristic_maps
        $this->siteConfig = $this->setSiteConfig();
        $this->globalScriptString = $this->setGlobalScriptString();
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_action('wp_default_scripts', [$this, 'removeJqueryMigrate']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueLibraries']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
        add_action('login_enqueue_scripts', [$this, 'enqueueAdminAssets']);

        // Si vous utilisez HTML5, wdjs_use_html5 est un filtre qui enlève l’attribut type="text/javascript"
        add_filter('wdjs_use_html5', '__return_true');

        // hack for googlemap script enqueuing
        add_filter('clean_url', [$this, 'so_handle_038'], 99, 3);

        // if (!function_exists('is_plugin_active')) {
        //     require_once(ABSPATH . '/wp-admin/includes/plugin.php');
        // }

        //plugin deferred labJS is activated
        // if (is_plugin_active('wp-deferred-javascripts/wp-deferred-javascripts.php')) {
        add_action('wdjs_deferred_script_wait', array($this, 'labjsAfterMyScript'), 10, 2);
        // } else {
        //     wp_add_inline_script('jquery', $this->globalScriptString, 'after') . ';';
        //     wp_add_inline_script('ng_scripts', "function(){angular.bootstrap(document, ['drupalAngularApp']);}", 'after') . ';';
        // }
    }

    // print inline scripts after specified scripts (labJS only)
    public function labjsAfterMyScript($wait, $handle)
    {
        // after jQuery => add globalScript
        if ('jquery' === $handle) {
            $wait = $this->globalScriptString;
        }
        // after ngScripts => bootstrap angular app
        elseif ('ng_scripts' === $handle) {
            $wait = "function(){angular.bootstrap(document, ['drupalAngularApp']);}";
        }

        return $wait;
    }

    public function removeJqueryMigrate($scripts)
    {
        if (WP_ENV != 'dev' && isset($scripts->registered['jquery'])) {
            $script = $scripts->registered['jquery'];

            if ($script->deps) {
                $script->deps = array_diff($script->deps, array('jquery-migrate'));
            }
        }
    }

    public function enqueueLibraries()
    {
        // Get page type
        $post = get_post();
        $pageType = (!empty($post) && !empty($post->ID)) ? getTermsSlugs($post->ID, 'page_type') : [];

        $isTouristicPlaylist = in_array('playlist_tourism', $pageType);
        $isTouristicSheet = !empty($post) && $post->post_type === 'touristic_sheet';

        $wThemeVersion = get_option('woody_theme_version');

        // Deregister the jquery version bundled with WordPress & define another
        wp_deregister_script('jquery');
        $jQuery_version = '3.3.1';
        if ($isTouristicPlaylist || $isTouristicSheet) {
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
                $apirender_base_uri = 'https://api.tourism-system.rc-preprod.com/render';
                break;
            default:
                $jsModeSuffix = 'min';
                $apirender_base_uri = 'https://api.tourism-system.com/render';
                break;
        }

        // dependencies for raccourci map js
        $js_dependencies_rcmap = [
            'jquery', 'leaflet',
        ];

        // get map keys
        $map_keys = $this->mapKeys;
        if (!empty($map_keys)) {
            $mapKeys['ver'] = $wThemeVersion;
        }
        if (isset($map_keys['otmKey'])) {
            $js_dependencies_rcmap[] = 'tangram';
        }
        if (isset($map_keys['gmKey'])) {
            $js_dependencies_rcmap[] = 'gg_maps';
        }

        // SHEET: need to load tangram always for now (bug in vendor angular)
        if ($isTouristicSheet) {
            if (!in_array('tangram', $js_dependencies_rcmap)) {
                array_push($js_dependencies_rcmap, 'tangram');
            }
        }

        // CDN hosted jQuery placed in the header, as some plugins require that jQuery is loaded in the header.
        wp_enqueue_script('jquery', 'https://cdn.jsdelivr.net/npm/jquery@'. $jQuery_version .'/dist/jquery.min.js', array(), '', true);
        wp_enqueue_script('lazysizes', 'https://cdn.jsdelivr.net/npm/lazysizes@4.1.2/lazysizes.min.js', array(), '', true);

        // Dependencies of main.js
        wp_enqueue_script('cookieconsent', 'https://cdn.jsdelivr.net/npm/cookieconsent@3.1.0/build/cookieconsent.min.js', array(), '', true);
        wp_enqueue_script('swiper', 'https://cdn.jsdelivr.net/npm/swiper@4.4.1/dist/js/swiper.min.js', array(), '', true);
        wp_enqueue_script('webfontloader', 'https://cdn.jsdelivr.net/npm/webfontloader@1.6.28/webfontloader.js', array(), '', true);
        wp_enqueue_script('lightgallery', 'https://cdn.jsdelivr.net/npm/lightgallery@1.6.11/dist/js/lightgallery.min.js', array('jquery'), '', true);
        wp_enqueue_script('lg-pager', 'https://cdn.jsdelivr.net/npm/lightgallery@1.6.11/modules/lg-pager.min.js', array('lightgallery'), '', true);
        wp_enqueue_script('lg-thumbnail', 'https://cdn.jsdelivr.net/npm/lightgallery@1.6.11/modules/lg-thumbnail.min.js', array('lightgallery'), '', true);
        wp_enqueue_script('lg-video', 'https://cdn.jsdelivr.net/npm/lightgallery@1.6.11/modules/lg-video.min.js', array('lightgallery'), '', true);
        wp_enqueue_script('lg-zoom', 'https://cdn.jsdelivr.net/npm/lightgallery@1.6.11/modules/lg-zoom.min.js', array('lightgallery'), '', true);
        wp_enqueue_script('lg-fullscreen', 'https://cdn.jsdelivr.net/npm/lightgallery@1.6.11/modules/lg-fullscreen.min.js', array('lightgallery'), '', true);
        wp_enqueue_script('nouislider', 'https://cdn.jsdelivr.net/npm/nouislider@10.1.0/distribute/nouislider.min.js', array('jquery'), '', true);
        wp_enqueue_script('moment', 'https://cdn.jsdelivr.net/npm/moment@2.22.2/min/moment-with-locales.min.js', array(), '', true);

        // Touristic maps libraries
        wp_enqueue_script('leaflet', 'https://cdn.jsdelivr.net/npm/leaflet@0.7.7/dist/leaflet-src.min.js', array(), '', true);
        if (isset($map_keys['otmKey']) || $isTouristicSheet) {
            // need to load tangram always in TOURISTIC SHEET for now (bug in vendor angular) ↓
            wp_enqueue_script('tangram', 'https://tiles.touristicmaps.com/libs/tangram.min.js', array(), $wThemeVersion, true);
        }

        if (isset($map_keys['gmKey'])) {
            wp_enqueue_script('gg_maps', 'https://maps.googleapis.com/maps/api/js?key='. $map_keys['gmKey'] .'&v=3.33&libraries=geometry,places', array(), '', true);
        } elseif ($isTouristicSheet) { // absolutely needed in angular
            wp_enqueue_script('gg_maps', 'https://maps.googleapis.com/maps/api/js?v=3.33&libraries=geometry,places', array(), '', true);
        }
        wp_enqueue_script('universal-map', $apirender_base_uri.'/assets/scripts/raccourci/universal-map.'. $jsModeSuffix .'.js', $js_dependencies_rcmap, $wThemeVersion, true);

        // Playlist libraries
        if ($isTouristicPlaylist) {
            // CSS_Libraries (todo replace when possible)
            wp_enqueue_style('rc_font_css', 'https://api.tourism-system.com/static/assets/fonts/raccourci-font.css', array(), '');
            wp_enqueue_style('leaflet_css', 'https://cdn.jsdelivr.net/npm/leaflet@0.7.7/dist/leaflet.min.css', array(), '');
            wp_enqueue_style('bootstrap_css', 'https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css', array(), '');
            wp_enqueue_style('nouislider_css', 'https://cdn.jsdelivr.net/npm/nouislider@10.1.0/distribute/nouislider.min.css', array(), '');
            wp_enqueue_style('chosen_css', 'https://cdn.jsdelivr.net/npm/chosen-js@1.8.2/chosen.min.css', array(), '');
            wp_enqueue_style('picker_css', 'https://cdn.jsdelivr.net/npm/bootstrap-daterangepicker@2.1.27/daterangepicker.min.css', array(), '');

            // JS Libraries
            wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js', array(), '', true);
            wp_enqueue_script('match8', 'https://cdn.jsdelivr.net/npm/jquery-match-height@0.7.2/dist/jquery.matchHeight.min.js', array('jquery'), '', true);
            wp_enqueue_script('wnumb', 'https://cdn.jsdelivr.net/npm/wnumb@1.0.4/wNumb.min.js', array('jquery'), '', true);
            wp_enqueue_script('chosen', 'https://cdn.jsdelivr.net/npm/chosen-js@1.8.2/chosen.jquery.min.js', array('jquery'), '', true);
            wp_enqueue_script('picker', 'https://cdn.jsdelivr.net/npm/bootstrap-daterangepicker@2.1.27/daterangepicker.min.js', array('bootstrap'), '', true);
            wp_enqueue_script('twigjs', 'https://cdn.jsdelivr.net/npm/twig@0.8.9/twig.min.js', array(), '', true);
            wp_enqueue_script('uuid', 'https://cdn.jsdelivr.net/npm/node-uuid@1.4.8/uuid.min.js', array(), '', true);
            wp_enqueue_script('lodash', 'https://cdn.jsdelivr.net/npm/lodash@3.8.0/index.min.js', array(), '', true);
            wp_enqueue_script('arrive', 'https://cdn.jsdelivr.net/npm/arrive@2.4.1/src/arrive.min.js', array('jquery'), '', true);
            wp_enqueue_script('sheet_item', $apirender_base_uri.'/assets/scripts/raccourci/sheet_item.min.js', array('jquery'), $wThemeVersion, true);

            $js_dependencies__playlist = ['bootstrap','match8','nouislider','wnumb','chosen','moment','picker','twigjs','lodash','arrive','sheet_item'];
            wp_enqueue_script('playlist', $apirender_base_uri.'/assets/scripts/raccourci/playlist.'. $jsModeSuffix .'.js', $js_dependencies__playlist, $wThemeVersion, true);
            $playlist_map_query = !empty($map_keys) ? '?'.http_build_query($map_keys) : '';
            wp_enqueue_script('playlist_map', $apirender_base_uri.'/assets/scripts/raccourci/playlist-map.leaflet.'. $jsModeSuffix .'.js'.$playlist_map_query, array_merge($js_dependencies_rcmap, array('playlist')), '', true);
        }

        // Sheet libraries
        elseif ($isTouristicSheet) {
            // CSS Libraries (todo replace when possible)
            wp_enqueue_style('rc_font_css', 'https://api.tourism-system.com/static/assets/fonts/raccourci-font.css', array(), '');
            wp_enqueue_style('leaflet_css', 'https://cdn.jsdelivr.net/npm/leaflet@0.7.7/dist/leaflet.min.css', array(), '');
            wp_enqueue_style('slick_css', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.css', array(), '');
            wp_enqueue_style('fresco_css', 'https://api.tourism-system.com/render/assets/styles/lib/fresco.css', array(), '');
            wp_enqueue_style('bootstrap_css', 'https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css', array(), '');

            // JS Libraries
            wp_enqueue_script('ng_vendor', $apirender_base_uri.'/assets/scripts/vendor.js', array(), $wThemeVersion, true);
            wp_enqueue_script('jsapi', 'https://www.google.com/jsapi', array(), '', true);
            wp_enqueue_script('lodash', 'https://cdn.jsdelivr.net/npm/lodash@3.8.0/index.min.js"', array(), '', true);
            wp_enqueue_script('ng_libs', $apirender_base_uri.'/assets/scripts/misclibs.js', array(), $wThemeVersion, true);
            wp_enqueue_script('ng_app', $apirender_base_uri.'/assets/app.js', array(), $wThemeVersion, true);
            wp_enqueue_script('ng_scripts', $apirender_base_uri.'/assets/scripts/scripts.js', array(), $wThemeVersion, true);

            wp_enqueue_script('slick', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js', array('jquery'), '', true);
            wp_enqueue_script('match8', 'https://cdn.jsdelivr.net/npm/jquery-match-height@0.7.2/dist/jquery.matchHeight.min.js', array('jquery'), '', true);
            wp_enqueue_script('sheet_item', $apirender_base_uri.'/assets/scripts/raccourci/sheet_item.'. $jsModeSuffix .'.js', array('match8'), $wThemeVersion, true);
            wp_enqueue_script('itinerary', $apirender_base_uri.'/assets/scripts/raccourci/itinerary.'. $jsModeSuffix .'.js', array('jquery','ng_scripts'), $wThemeVersion, true);
            wp_enqueue_script('fresco', $apirender_base_uri.'/assets/scripts/lib/fresco.js', array('jquery'), $wThemeVersion, true);
        }

        // Add the comment-reply library on pages where it is necessary
        if (is_singular() && comments_open() && get_option('thread_comments')) {
            wp_enqueue_script('comment-reply');
        }
    }

    public function enqueueAssets()
    {
        // Enqueue the main Scripts
        $dependencies = [
            'jquery',
            'swiper',
            'cookieconsent',
            'lightgallery',
            'lg-pager',
            'lg-thumbnail',
            'lg-video',
            'lg-zoom',
            'lg-fullscreen'
        ];
        wp_enqueue_script('main-javascripts', WP_HOME . '/app/dist/' . WP_SITE_KEY . '/' . $this->assetPath('js/main.js'), $dependencies, '', true);

        // Enqueue the main Stylesheet.
        wp_enqueue_style('main-stylesheet', WP_HOME . '/app/dist/' . WP_SITE_KEY . '/' . $this->assetPath('css/main.css'), array(), '', 'all');
    }

    public function enqueueAdminAssets()
    {
        wp_enqueue_script('lazysizes', 'https://cdn.jsdelivr.net/npm/lazysizes@4.1.2/lazysizes.min.js', array(), '', true);

        // Enqueue the main Scripts
        $dependencies = ['jquery'];
        wp_enqueue_script('admin-javascripts', WP_HOME . '/app/dist/' . WP_SITE_KEY . '/' . $this->assetPath('js/admin.js'), $dependencies, wp_get_theme(get_template())->get('Version'), true);

        // Added global vars
        wp_add_inline_script('admin-javascripts', 'var siteConfig = ' . json_encode($this->siteConfig) . ';', 'before');

        // Enqueue the main Stylesheet.
        wp_enqueue_style('admin-stylesheet', WP_HOME . '/app/dist/' . WP_SITE_KEY . '/' . $this->assetPath('css/admin.css'), array(), wp_get_theme(get_template())->get('Version'), 'all');
    }

    private function assetPath($filename)
    {
        $manifest = [];
        $manifest_path = WP_CONTENT_DIR . '/dist/' . WP_SITE_KEY . '/rev-manifest.json';
        if (file_exists($manifest_path)) {
            $manifest = json_decode(file_get_contents($manifest_path), true);

            if (!empty($manifest[$filename])) {
                $filename = $manifest[$filename];
            }
        }

        return $filename;
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
        $siteConfig = [];
        $siteConfig['site_key'] = WP_SITE_KEY;
        $credentials = get_option('woody_credentials');
        if (!empty($credentials['login']) && !empty($credentials['password'])) {
            $siteConfig['login'] = $credentials['login'];
            $siteConfig['password'] = $credentials['password'];
        }
        $siteConfig['mapProviderKeys'] = $this->mapKeys;

        // Add hook to overide siteconfig
        $siteConfig = apply_filters('woody_theme_siteconfig', $siteConfig);
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
            'window.DrupalAngularConfig.apiAccount.login' => true,
            'window.DrupalAngularConfig.apiAccount.login' => json_encode($this->siteConfig['login']),
            'window.DrupalAngularConfig.apiAccount.password' => json_encode($this->siteConfig['password']),
            // inject mapKeys in DrupalAngularAppConfig
            'window.DrupalAngularConfig.mapProviderKeys' => json_encode($this->mapKeys),
        ];

        $globalScriptString = apply_filters('woody_theme_global_script_string', $globalScriptString);

        // Create inline script
        $return = "function(){";
        foreach ($globalScriptString as $name => $val) {
            $return .= $name . '=' . $val . ';';
        }
        $return .= "}";

        return $return;
    }
}
