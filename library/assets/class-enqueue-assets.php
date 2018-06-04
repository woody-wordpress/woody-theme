<?php
/**
 * Assets enqueue
 *
 * @package HawwwaiTheme
 * @since HawwwaiTheme 1.0.0
 */

class HawwwaiTheme_Enqueue_Assets {

    public function __construct() {
        $this->register_hooks();
    }

    protected function register_hooks() {
        add_action( 'wp_enqueue_scripts', array($this, 'enqueue_libraries'));
        add_action( 'wp_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    public function enqueue_libraries() {

        // Deregister the jquery version bundled with WordPress.
        wp_deregister_script( 'jquery' );

        // CDN hosted jQuery placed in the header, as some plugins require that jQuery is loaded in the header.
        wp_enqueue_script( 'jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js', array(), '3.3.1', false );

        // Enqueue FontAwesome from CDN. Uncomment the line below if you don't need FontAwesome.
        //wp_enqueue_script( 'fontawesome', 'https://use.fontawesome.com/5016a31c8c.js', array(), '4.7.0', true );

        // Add the comment-reply library on pages where it is necessary
        if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
            wp_enqueue_script( 'comment-reply' );
        }
    }

    public function enqueue_assets() {

        // Enqueue Founation scripts
        wp_enqueue_script('main-javascripts', get_stylesheet_directory_uri() . '/dist/js/' . $this->asset_path('main.min.js'), array( 'jquery' ), '2.10.4', true);

        // Enqueue the main Stylesheet.
        wp_enqueue_style('main-stylesheet',  get_stylesheet_directory_uri() . '/dist/css/' . $this->asset_path('main.min.css'), array(), '2.10.4', 'all');
    }

    private function asset_path($filename) {
        if(strpos($filename, 'js') !== false) $type = 'js';
        else if(strpos($filename, 'css') !== false) $type = 'css';
        else $type = false;

        if(!empty($type)) {
            $manifest_path = get_stylesheet_directory() . '/dist/' . $type .'/rev-manifest.json';
            if (file_exists($manifest_path)) {
                $manifest = json_decode(file_get_contents($manifest_path), TRUE);
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
