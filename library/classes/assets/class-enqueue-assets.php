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

    public function __construct()
    {
        $this->mapKeys = getMapKeys(); // defined in functions touristic_maps
        $this->siteConfig = $this->getSiteConfig();
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
        add_filter('clean_url', array($this, 'so_handle_038'), 99, 3);
    }

    // TODO move elsewhere ?
    protected function getSiteConfig()
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


    protected function getGlobalScriptString()
    {
        return $globalScript = "function(){".
            // global vars
            "window.useLeafletLibrary = true;".
            "window.apirenderlistEnabled = true;".

            // inject siteConfig
            "window.siteConfig = ". json_encode($this->siteConfig) .";".

            // init DrupalAngularConfig if doesn't exist
            "window.DrupalAngularConfig = window.DrupalAngularConfig || {};".
            // fill DrupalAngularConfig (some properties may already exists)
            "window.DrupalAngularConfig.apiAccount = window.DrupalAngularConfig.apiAccount || {};".

            "window.DrupalAngularConfig.apiAccount.login = true;".
            "window.DrupalAngularConfig.apiAccount.login = ". json_encode($this->siteConfig['login']) .";".
            "window.DrupalAngularConfig.apiAccount.password = ". json_encode($this->siteConfig['password']) .";".
            // inject mapKeys in DrupalAngularAppConfig
            "window.DrupalAngularConfig.mapProviderKeys = ". json_encode($this->mapKeys) .";".

            // "console.warn(window.DrupalAngularConfig);".
        "}";
    }

    // print inline scripts after specified scripts (labJS only)
    public function labjsAfterMyScript($wait, $handle)
    {
        // after jQuery => add globalScript
        if ('jquery' === $handle) {
            $wait = $this->getGlobalScriptString();
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
        if (! function_exists('is_plugin_active')) {
            require_once(ABSPATH . '/wp-admin/includes/plugin.php');
        }
        //plugin deferred labJS is activated
        if (is_plugin_active('wp-deferred-javascripts/wp-deferred-javascripts.php')) {
            add_action('wdjs_deferred_script_wait', array($this, 'labjsAfterMyScript'), 10, 2);
        } else {
            wp_add_inline_script('jquery', $this->getGlobalScriptString(), 'after') . ';';
            wp_add_inline_script('ng_scripts', "function(){angular.bootstrap(document, ['drupalAngularApp']);}", 'after') . ';';
        }

        // Get page type
        global $post;
        $pageType = (!empty($post) && !empty($post->ID)) ? getTermsSlugs($post->ID, 'page_type') : [];

        $isTouristicPlaylist = in_array('playlist_tourism', $pageType);
        $isTouristicSheet = !empty($post) && $post->post_type === 'touristic_sheet';


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
                $jsModeSuffix = 'min';
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
        wp_enqueue_script('lightgallery', 'https://cdn.jsdelivr.net/npm/lightgallery@1.6.11/dist/js/lightgallery.min.js', array('jquery'), '', true);
        wp_enqueue_script('lg-pager', 'https://cdn.jsdelivr.net/npm/lightgallery@1.6.11/modules/lg-pager.min.js', array('lightgallery'), '', true);
        wp_enqueue_script('lg-thumbnail', 'https://cdn.jsdelivr.net/npm/lightgallery@1.6.11/modules/lg-thumbnail.min.js', array('lightgallery'), '', true);
        wp_enqueue_script('lg-video', 'https://cdn.jsdelivr.net/npm/lightgallery@1.6.11/modules/lg-video.min.js', array('lightgallery'), '', true);
        wp_enqueue_script('lg-zoom', 'https://cdn.jsdelivr.net/npm/lightgallery@1.6.11/modules/lg-zoom.min.js', array('lightgallery'), '', true);
        wp_enqueue_script('lg-fullscreen', 'https://cdn.jsdelivr.net/npm/lightgallery@1.6.11/modules/lg-fullscreen.min.js', array('lightgallery'), '', true);

        // Touristic maps libraries
        wp_enqueue_script('leaflet', 'https://cdn.jsdelivr.net/npm/leaflet@0.7.7/dist/leaflet-src.min.js', array(), '', true);
        if (isset($map_keys['otmKey']) || $isTouristicSheet) {
            // need to load tangram always in TOURISTIC SHEET for now (bug in vendor angular) ↓
            wp_enqueue_script('tangram', 'https://cdn.jsdelivr.net/npm/tangram@0.15.3/dist/tangram.min.js', array(), '', true);
        }

        if (isset($map_keys['gmKey'])) {
            wp_enqueue_script('gg_maps', 'https://maps.googleapis.com/maps/api/js?key='. $map_keys['gmKey'] .'&v=3.33&libraries=geometry,places', array(), '', true);
        } elseif ($isTouristicSheet) { // absolutely needed in angular
            wp_enqueue_script('gg_maps', 'https://maps.googleapis.com/maps/api/js?v=3.33&libraries=geometry,places', array(), '', true);
        }
        wp_enqueue_script('universal-map', $apirender_base_uri.'/assets/scripts/raccourci/universal-map.'. $jsModeSuffix .'.js', $js_dependencies_rcmap, '', true);

        // Playlist libraries
        if ($isTouristicPlaylist) {
            // TODO LATER get children page_type
            // $children_terms_ids = [];
            // $parent_term = get_term_by('playlist_tourism', $rule['value'], 'page_type');
            // $parent_term_id = $parent_term->term_id;
            // $children_terms = get_terms(array('taxonomy' => 'page_type', 'hide_empty' => false, 'parent' => $parent_term_id));

            wp_enqueue_script('bootstrap', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js', array(), '', true);
            wp_enqueue_script('match8', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.matchHeight/0.7.2/jquery.matchHeight-min.js', array('jquery'), '', true);
            wp_enqueue_script('nouislider', 'https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/10.1.0/nouislider.min.js', array('jquery'), '', true);
            wp_enqueue_script('wnumb', 'https://cdnjs.cloudflare.com/ajax/libs/wnumb/1.0.4/wNumb.min.js', array('jquery'), '', true);
            wp_enqueue_script('chosen', 'https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.2/chosen.jquery.min.js', array('jquery'), '', true);
            wp_enqueue_script('moment', 'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment-with-locales.min.js', array(), '', true);
            wp_enqueue_script('picker', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/2.1.27/daterangepicker.min.js', array('bootstrap'), '', true);
            wp_enqueue_script('twigjs', 'https://cdnjs.cloudflare.com/ajax/libs/twig.js/0.8.9/twig.min.js', array(), '', true);
            wp_enqueue_script('twigjs', 'https://cdnjs.cloudflare.com/ajax/libs/node-uuid/1.4.8/uuid.min.js', array(), '', true);
            wp_enqueue_script('lodash', 'https://cdnjs.cloudflare.com/ajax/libs/lodash.js/3.10.1/lodash.min.js', array(), '', true);
            wp_enqueue_script('arrive', 'https://cdn.jsdelivr.net/npm/arrive@2.4.1/src/arrive.min.js', array('jquery'), '', true);
            wp_enqueue_script('sheet_item', $apirender_base_uri.'/assets/scripts/raccourci/sheet_item.min.js', array('jquery'), '', true);

            $js_dependencies__playlist = ['bootstrap','match8','nouislider','wnumb','chosen','moment','picker','twigjs','lodash','arrive','sheet_item'];
            wp_enqueue_script('playlist', $apirender_base_uri.'/assets/scripts/raccourci/playlist.'. $jsModeSuffix .'.js', $js_dependencies__playlist, '', true);
            $playlist_map_query = !empty($map_keys) ? '?'.http_build_query($map_keys) : '';
            wp_enqueue_script('playlist_map', $apirender_base_uri.'/assets/scripts/raccourci/playlist-map.leaflet.'. $jsModeSuffix .'.js'.$playlist_map_query, array_merge($js_dependencies_rcmap, array('playlist')), '', true);
        }

        // Sheet libraries
        elseif ($isTouristicSheet) {
            wp_enqueue_script('ng_vendor', $apirender_base_uri.'/assets/scripts/vendor.js', array(), '', true);
            wp_enqueue_script('jsapi', 'https://www.google.com/jsapi', array(), '', true);
            wp_enqueue_script('lodash', 'https://cdnjs.cloudflare.com/ajax/libs/lodash.js/3.10.1/lodash.min.js', array(), '', true);
            wp_enqueue_script('ng_libs', $apirender_base_uri.'/assets/scripts/misclibs.js', array(), '', true);
            wp_enqueue_script('ng_app', $apirender_base_uri.'/assets/app.js', array(), '', true);
            wp_enqueue_script('ng_scripts', $apirender_base_uri.'/assets/scripts/scripts.js', array(), '', true);
            wp_enqueue_script('match8', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.matchHeight/0.7.2/jquery.matchHeight-min.js', array('jquery'), '', true);
            wp_enqueue_script('sheet_item', $apirender_base_uri.'/assets/scripts/raccourci/sheet_item.'. $jsModeSuffix .'.js', array('match8'), '', true);
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
        // Dependencies of admin.js
        // wp_enqueue_script('arrive', 'https://cdn.jsdelivr.net/npm/arrive@2.4.1/src/arrive.min.js', array(), '', true);
        // wp_enqueue_script('selectize', 'https://cdn.jsdelivr.net/npm/selectize@0.12.6/dist/js/standalone/selectize.min.js', array('jquery'), '', true);
        wp_enqueue_script('lazysizes', 'https://cdn.jsdelivr.net/npm/lazysizes@4.1.2/lazysizes.min.js', array(), '', true);

        // Enqueue the main Scripts
        $dependencies = [
            'jquery',
            // 'arrive',
            // 'selectize'
        ];
        wp_enqueue_script('admin-javascripts', WP_HOME . '/app/dist/' . WP_SITE_KEY . '/' . $this->assetPath('js/admin.js'), $dependencies, wp_get_theme(get_template())->get('Version'), true);

        // Added global vars
        wp_add_inline_script('admin-javascripts', 'var siteConfig = ' . json_encode($this->siteConfig), 'before') . ';';

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
}
