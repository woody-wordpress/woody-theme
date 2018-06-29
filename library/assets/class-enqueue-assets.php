<?php
/**
 * Assets enqueue
 *
 * @package HawwwaiTheme
 * @since HawwwaiTheme 1.0.0
 */

class HawwwaiTheme_Enqueue_Assets
{
    public function __construct()
    {
        $this->register_hooks();
    }

    protected function register_hooks()
    {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_libraries'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    public function enqueue_libraries()
    {

        // Deregister the jquery version bundled with WordPress.
        wp_deregister_script('jquery');

        // CDN hosted jQuery placed in the header, as some plugins require that jQuery is loaded in the header.
        wp_enqueue_script('jquery', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js', array(), '', true);

        // Add the comment-reply library on pages where it is necessary
        if (is_singular() && comments_open() && get_option('thread_comments')) {
            wp_enqueue_script('comment-reply');
        }
    }

    public function enqueue_assets()
    {
        // Enqueue Founation scripts
        wp_enqueue_script('main-javascripts', get_stylesheet_directory_uri() . '/dist/' . $this->asset_path('js/main.js'), 'jquery', '', true);

        // Enqueue the main Stylesheet.
        wp_enqueue_style('main-stylesheet', get_stylesheet_directory_uri() . '/dist/' . $this->asset_path('css/main.css'), array(), '', 'all');
        wp_enqueue_style('font-awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css', array(), '', 'all');
    }

    public function enqueue_admin_assets()
    {
        // Enqueue Founation scripts
        //wp_enqueue_script('admin-javascripts', get_stylesheet_directory_uri() . '/dist/' . $this->asset_path('js/main.js'), 'jquery', '', true);

        // Enqueue the main Stylesheet.
        wp_enqueue_style('admin-stylesheet', get_stylesheet_directory_uri() . '/dist/' . $this->asset_path('css/admin.css'), array(), '', 'all');
        wp_enqueue_script('admin-javascripts', get_stylesheet_directory_uri() . '/dist/' . $this->asset_path('js/admin.js'), 'jquery', false, true);
    }

    private function asset_path($filename)
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

// Execute Class
new HawwwaiTheme_Enqueue_Assets();
