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
    }

    public function enqueue_libraries()
    {

        // Deregister the jquery version bundled with WordPress.
        wp_deregister_script('jquery');

        // CDN hosted jQuery placed in the header, as some plugins require that jQuery is loaded in the header.
        wp_enqueue_script('jquery', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js', array(), '3.3.1', true);
        // wp_enqueue_script('photoswipe', 'https://cdnjs.cloudflare.com/ajax/libs/photoswipe/4.1.2/photoswipe.min.js', array(), '4.1.2', true);
        // wp_enqueue_script('photoswipe-ui-default', 'https://cdnjs.cloudflare.com/ajax/libs/photoswipe/4.1.2/photoswipe-ui-default.min.js', array('photoswipe'), '4.1.2', true);

        // Add the comment-reply library on pages where it is necessary
        if (is_singular() && comments_open() && get_option('thread_comments')) {
            wp_enqueue_script('comment-reply');
        }
    }

    public function enqueue_assets()
    {

        // Enqueue Founation scripts
        $main_dependencies = array('jquery');
        wp_enqueue_script('main-javascripts', get_stylesheet_directory_uri() . '/dist/js/' . $this->asset_path('main.js'), $main_dependencies, '1.0.0', true);

        // Enqueue the main Stylesheet.
        wp_enqueue_style('main-stylesheet', get_stylesheet_directory_uri() . '/dist/css/' . $this->asset_path('main.css'), array(), '1.0.0', 'all');
    }

    private function asset_path($filename)
    {
        if (strpos($filename, 'js') !== false) {
            $type = 'js';
        } elseif (strpos($filename, 'css') !== false) {
            $type = 'css';
        } else {
            $type = false;
        }

        if (!empty($type)) {
            $manifest_path = get_stylesheet_directory() . '/dist/' . $type .'/rev-manifest.json';
            if (file_exists($manifest_path)) {
                $manifest = json_decode(file_get_contents($manifest_path), true);
            } else {
                $manifest = [];
            }

            $filenames = [
                $filename,
                str_replace('.min', '', $filename)
            ];

            foreach ($filenames as $filename) {
                if (array_key_exists($filename, $manifest)) {
                    return $manifest[$filename];
                }
            }
        }

        return $filename;
    }
}

// Execute Class
$HawwwaiTheme_Enqueue_Assets = new HawwwaiTheme_Enqueue_Assets();
