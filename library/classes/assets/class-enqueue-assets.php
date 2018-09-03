<?php
/**
 * Assets enqueue
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.0.0
 */

class WoodyTheme_Enqueue_Assets
{
    public function __construct()
    {
        $this->registerHooks();
    }

    protected function registerHooks()
    {
        add_action('wp_enqueue_scripts', array($this, 'enqueueLibraries'));
        add_action('wp_enqueue_scripts', array($this, 'enqueueAssets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueueAdminAssets'));
        add_action('login_enqueue_scripts', array($this, 'enqueueAdminAssets'));
    }

    public function enqueueLibraries()
    {

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
        $apirender_base_uri = 'https://api.tourism-system.com/render';
        switch (WP_ENV) {
            case 'dev':
                // $apirender_base_uri = 'http://127.0.0.1:8000'; // use localhost apirender (gulp serve)
                // $apirender_base_uri = 'https://api.tourism-system.rc-preprod.com/render';
                break;
            case 'preprod':
                $apirender_base_uri = 'https://api.tourism-system.rc-preprod.com/render';
                break;
        }

        // dependencies for raccourci map js
        $js_dependencies_rcmap = [
            'jquery', 'leaflet',
        ];

        // get map keys
        $map_keys = getMapKeys();
        if ($isTouristicSheet) {
            $js_dependencies_rcmap[] = 'tangram'; // need to load tangram always for now (bug in vendor angular)
        }
        if (isset($map_keys['otmKey'])) {
            $js_dependencies_rcmap[] = 'tangram';
        }
        if (isset($map_keys['gmKey'])) {
            $js_dependencies_rcmap[] = 'gg_maps';
        }

        // TODO print mapkeys in every page (DrupalAngularConfig & siteConfig)
        // wp_add_inline_script('leaflet', 'var helloworld = ' . wp_json_encode($map_keys) . ';', 'after');

        if (WP_ENV === 'dev' && ($isTouristicPlaylist || $isTouristicSheet)) {
            // $map_keys['otmKey'] = 'raccourci';
            // $js_dependencies_rcmap[] = 'tangram';
            // $map_keys['gmKey'] = 'AIzaSyCfQ5H4V8Q7YqtgxaeHxhIqdq42x5QbyEs';
            // $js_dependencies_rcmap[] = 'gg_maps';
            \PC::Debug($js_dependencies_rcmap);
            \PC::Debug($map_keys);
        }

        // CDN hosted jQuery placed in the header, as some plugins require that jQuery is loaded in the header.
        wp_enqueue_script('jquery', 'https://cdn.jsdelivr.net/npm/jquery@'. $jQuery_version .'/dist/jquery.min.js', array(), '', true);
        wp_enqueue_script('lazysizes', 'https://cdn.jsdelivr.net/npm/lazysizes@4.1.1/lazysizes.min.js', array(), '', true);

        // Dependencies of main.js
        wp_enqueue_script('cookieconsent', 'https://cdn.jsdelivr.net/npm/cookieconsent@3.1.0/build/cookieconsent.min.js', array(), '', true);
        wp_enqueue_script('swiper', 'https://cdn.jsdelivr.net/npm/swiper@4.3.5/dist/js/swiper.min.js', array(), '', true);
        wp_enqueue_script('lightgallery', 'https://cdn.jsdelivr.net/npm/lightgallery@1.6.11/dist/js/lightgallery.min.js', array('jquery'), '', true);
        wp_enqueue_script('lg-pager', 'https://cdn.jsdelivr.net/npm/lightgallery@1.6.11/modules/lg-pager.min.js', array('lightgallery'), '', true);
        wp_enqueue_script('lg-thumbnail', 'https://cdn.jsdelivr.net/npm/lightgallery@1.6.11/modules/lg-thumbnail.min.js', array('lightgallery'), '', true);
        wp_enqueue_script('lg-video', 'https://cdn.jsdelivr.net/npm/lightgallery@1.6.11/modules/lg-video.min.js', array('lightgallery'), '', true);
        wp_enqueue_script('lg-zoom', 'https://cdn.jsdelivr.net/npm/lightgallery@1.6.11/modules/lg-zoom.min.js', array('lightgallery'), '', true);
        wp_enqueue_script('lg-fullscreen', 'https://cdn.jsdelivr.net/npm/lightgallery@1.6.11/modules/lg-fullscreen.min.js', array('lightgallery'), '', true);

        // Touristic maps libraries
        wp_enqueue_script('leaflet', 'https://cdn.jsdelivr.net/npm/leaflet@0.7.7/dist/leaflet-src.min.js', array(), '', true);
        if (isset($map_keys['otmKey'])) {
            // need to load tangram always for now (bug in vendor angular) ↓
        } // ↓
        wp_enqueue_script('tangram', 'https://cdn.jsdelivr.net/npm/tangram@0.15.3/dist/tangram.min.js', array(), '', true);

        if (isset($map_keys['gmKey'])) {
            wp_enqueue_script('gg_maps', 'https://maps.googleapis.com/maps/api/js?key='. $map_keys['gmKey'] .'&v=3.exp&libraries=geometry,places', array(), '', true);
        } elseif ($isTouristicSheet) { // absolutely needed in angular
            wp_enqueue_script('gg_maps', 'https://maps.googleapis.com/maps/api/js?v=3.exp&libraries=geometry,places', array(), '', true);
        }
        wp_enqueue_script('universal-map', $apirender_base_uri.'/assets/scripts/raccourci/universal-map.debug.js', $js_dependencies_rcmap, '', true);

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
            // https://cdnjs.cloudflare.com/ajax/libs/node-uuid/1.4.8/uuid.min.js
            // wp_enqueue_script('lodash', 'https://cdnjs.cloudflare.com/ajax/libs/lodash.js/3.8.0/lodash.min.js', array(), '', true);
            wp_enqueue_script('lodash', 'https://cdnjs.cloudflare.com/ajax/libs/lodash.js/3.10.1/lodash.min.js', array(), '', true);
            wp_enqueue_script('arrive', 'https://cdnjs.cloudflare.com/ajax/libs/arrive/2.4.1/arrive.min.js', array('jquery'), '', true);
            wp_enqueue_script('sheet_item', $apirender_base_uri.'/assets/scripts/raccourci/sheet_item.min.js', array('jquery'), '', true);

            $js_dependencies__playlist = [
                'bootstrap','match8','nouislider','wnumb','chosen','moment','picker','twigjs','lodash','arrive','sheet_item'];
            wp_enqueue_script('playlist', $apirender_base_uri.'/assets/scripts/raccourci/playlist.debug.js', $js_dependencies__playlist, '', true);
            $playlist_map_query = !empty($map_keys) ? '?'.http_build_query($map_keys) : '';
            wp_enqueue_script('playlist_map', $apirender_base_uri.'/assets/scripts/raccourci/playlist-map.leaflet.debug.js'.$playlist_map_query, array_merge($js_dependencies_rcmap, array('playlist')), '', true);
        }

        // Sheet libraries
        elseif ($isTouristicSheet) {
            wp_enqueue_script('jsapi', 'https://www.google.com/jsapi', array('gg_maps'), '', true);
            // wp_enqueue_script('lodash', 'https://cdnjs.cloudflare.com/ajax/libs/lodash.js/3.8.0/lodash.min.js', array(), '', true);
            wp_enqueue_script('lodash', 'https://cdnjs.cloudflare.com/ajax/libs/lodash.js/3.10.1/lodash.min.js', array(), '', true);
            wp_enqueue_script('ng_vendor', $apirender_base_uri.'/assets/scripts/vendor.js', array(), '', true);
            wp_enqueue_script('ng_libs', $apirender_base_uri.'/assets/scripts/misclibs.js', array(), '', true);
            wp_enqueue_script('ng_app', $apirender_base_uri.'/assets/app.js', array(), '', true);
            wp_enqueue_script('ng_scripts', $apirender_base_uri.'/assets/scripts/scripts.js', array(), '', true);
            wp_enqueue_script('match8', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.matchHeight/0.7.2/jquery.matchHeight-min.js', array('jquery'), '', true);
            wp_enqueue_script('sheet_item', $apirender_base_uri.'/assets/scripts/raccourci/sheet_item.min.js', array('match8'), '', true);
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
        wp_enqueue_script('main-javascripts', get_stylesheet_directory_uri() . '/dist/' . $this->assetPath('js/main.js'), $dependencies, '', true);

        // Enqueue the main Stylesheet.
        wp_enqueue_style('main-stylesheet', get_stylesheet_directory_uri() . '/dist/' . $this->assetPath('css/main.css'), array(), '', 'all');
        wp_enqueue_style('font-awesome', 'https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css', array(), '', 'all');
    }

    public function enqueueAdminAssets()
    {
        // Dependencies of admin.js
        wp_enqueue_script('arrive', 'https://cdn.jsdelivr.net/npm/arrive@2.4.1/src/arrive.min.js', array(), '', true);
        wp_enqueue_script('selectize', 'https://cdn.jsdelivr.net/npm/selectize@0.12.6/dist/js/standalone/selectize.min.js', array('jquery'), '', true);

        // Enqueue the main Scripts
        $dependencies = [
            'jquery',
            'arrive',
            'selectize'
        ];
        wp_enqueue_script('admin-javascripts', get_stylesheet_directory_uri() . '/dist/' . $this->assetPath('js/admin.js'), $dependencies, false, true);

        // Added global vars
        $siteConfig = [];
        $siteConfig['site_key'] = WP_SITE_KEY;
        $credentials = get_option('woody_credentials');
        if (!empty($credentials['login']) && !empty($credentials['password'])) {
            $siteConfig['login'] = $credentials['login'];
            $siteConfig['password'] = $credentials['password'];
        }
        wp_add_inline_script('admin-javascripts', 'var siteConfig = ' . json_encode($siteConfig), 'before') . ';';

        // Enqueue the main Stylesheet.
        wp_enqueue_style('admin-stylesheet', get_stylesheet_directory_uri() . '/dist/' . $this->assetPath('css/admin.css'), array(), '', 'all');
    }

    private function assetPath($filename)
    {
        $manifest = [];
        $manifest_path = get_stylesheet_directory() . '/dist/rev-manifest.json';
        if (file_exists($manifest_path)) {
            $manifest = json_decode(file_get_contents($manifest_path), true);

            if (!empty($manifest[$filename])) {
                $filename = $manifest[$filename];
            }
        }

        return $filename;
    }
}
