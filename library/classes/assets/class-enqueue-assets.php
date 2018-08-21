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
        wp_enqueue_script('jquery', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js', array(), '', true);
        // Touristic maps libraries - TODO:try to call in packagist
        wp_enqueue_script('leaflet', 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/leaflet.js', array(), '', true);
        wp_enqueue_script('tangram', 'https://unpkg.com/tangram/dist/tangram.min.js', array(), '', true);
        wp_enqueue_script('universal-map', 'https://api.tourism-system.com/render/assets/scripts/raccourci/universal-map.debug.js', array('jquery', 'leaflet', 'tangram'), '', true);


        // Add the comment-reply library on pages where it is necessary
        if (is_singular() && comments_open() && get_option('thread_comments')) {
            wp_enqueue_script('comment-reply');
        }
    }

    public function enqueueAssets()
    {
        // Enqueue the main Scripts
        wp_enqueue_script('main-javascripts', get_stylesheet_directory_uri() . '/dist/' . $this->assetPath('js/main.js'), 'jquery', '', true);

        // Enqueue the main Stylesheet.
        wp_enqueue_style('main-stylesheet', get_stylesheet_directory_uri() . '/dist/' . $this->assetPath('css/main.css'), array(), '', 'all');
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css', array(), '', 'all');
    }

    public function enqueueAdminAssets()
    {
        // Enqueue the main Scripts
        wp_enqueue_script('admin-javascripts', get_stylesheet_directory_uri() . '/dist/' . $this->assetPath('js/admin.js'), array('jquery'), false, true);

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
