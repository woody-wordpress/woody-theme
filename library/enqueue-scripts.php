<?php
/**
 * Enqueue all styles and scripts
 *
 * Learn more about enqueue_script: {@link https://codex.wordpress.org/Function_Reference/wp_enqueue_script}
 * Learn more about enqueue_style: {@link https://codex.wordpress.org/Function_Reference/wp_enqueue_style }
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

if (!function_exists( 'basetheme_libraries')):
    function basetheme_libraries() {

        // Deregister the jquery version bundled with WordPress.
        wp_deregister_script( 'jquery' );

        // CDN hosted jQuery placed in the header, as some plugins require that jQuery is loaded in the header.
        //TODO : la version 3.3.1 de jquery n'est pas/plus disponible :/
        wp_enqueue_script( 'jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js', array(), '3.3.1', false );

        // Enqueue FontAwesome from CDN. Uncomment the line below if you don't need FontAwesome.
        //wp_enqueue_script( 'fontawesome', 'https://use.fontawesome.com/5016a31c8c.js', array(), '4.7.0', true );

        // Add the comment-reply library on pages where it is necessary
        if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
            wp_enqueue_script( 'comment-reply' );
        }
    }

    add_action( 'wp_enqueue_scripts', 'basetheme_libraries' );
endif;

if (!function_exists( 'basetheme_assets')):
    function basetheme_assets() {

        // Enqueue Founation scripts
        wp_enqueue_script( 'foundation', get_current_template_directory_uri() . '/dist/js/' . asset_path('main.min.js'), array( 'jquery' ), '2.10.4', true );

        // Enqueue the main Stylesheet.
        wp_enqueue_style( 'main-stylesheet',  get_current_template_directory_uri() . '/dist/css/' . asset_path('main.min.css'), array(), '2.10.4', 'all' );
    }

    add_action( 'wp_enqueue_scripts', 'basetheme_assets' );
endif;

// Check to see if rev-manifest exists for CSS and JS static asset revisioning
//https://github.com/sindresorhus/gulp-rev/blob/master/integration.md
if (!function_exists( 'asset_path')):
    function asset_path($filename) {
        if(strpos($filename, 'js') !== false) $type = 'js';
        else if(strpos($filename, 'css') !== false) $type = 'css';
        else $type = false;

        if(!empty($type)) {
            $manifest_path = get_current_template_directory() . '/dist/' . $type .'/rev-manifest.json';
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
endif;

if (!function_exists( 'get_current_template_directory')):
    function get_current_template_directory() {
        return get_template_directory();
    }
endif;

if (!function_exists( 'get_current_template_directory_uri')):
    function get_current_template_directory_uri() {
        return get_template_directory_uri();
    }
endif;
