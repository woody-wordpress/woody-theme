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

        // Deregister the jquery version bundled with WordPress.
        wp_deregister_script('jquery');

        // CDN hosted jQuery placed in the header, as some plugins require that jQuery is loaded in the header.
        wp_enqueue_script('jquery', 'https://cdn.jsdelivr.net/npm/jquery@3.3.1/dist/jquery.min.js', array(), '', true);
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
        wp_enqueue_script('lg-autoplay', 'https://cdn.jsdelivr.net/npm/lightgallery@1.6.11/modules/lg-autoplay.min.js', array('lightgallery'), '', true);

        // Touristic maps libraries - TODO:try to call in packagist
        wp_enqueue_script('leaflet', 'https://cdn.jsdelivr.net/npm/leaflet@0.7.7/dist/leaflet-src.min.js', array(), '', true);
        wp_enqueue_script('tangram', 'https://cdn.jsdelivr.net/npm/tangram@0.15.3/dist/tangram.min.js', array(), '', true);
        wp_enqueue_script('universal-map', 'https://api.tourism-system.com/render/assets/scripts/raccourci/universal-map.debug.js', array('jquery', 'leaflet', 'tangram'), '', true);


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
            'lg-fullscreen',
            'lg-autoplay'
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
