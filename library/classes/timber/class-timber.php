<?php

/**
 * Twig Singleton
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.15.0
 */

if (!class_exists('Timber')) {
    class Timber
    {
        private static $twig = null;
        private static $context_cache = array();

        /**
         * Constructeur de la classe
         *
         * @param void
         * @return void
         */
        private function __construct()
        {
            // Nothing to do - there are no instances.
        }

        private static function init()
        {
            if (!defined('ABSPATH')) {
                return;
            }

            if (class_exists('\WP') && !defined('TIMBER_LOADED')) {
                // Init Twig Instance
                $twig_dirs = apply_filters('timber_locations', array(WOODY_THEME_DIR . '/views', WOODY_SUBTHEME_DIR . '/views', WoodyLibrary::getTemplatesDirname()));
                $twig_loader = new \Twig\Loader\FilesystemLoader($twig_dirs);
                $twig_options = ['autoescape' => false];
                if (!file_exists(WP_CACHE_DIR . '/deploy.lock') && WP_ENV != 'dev') {
                    $twig_options['cache'] = WP_TIMBER_DIR;
                }

                // Instance
                self::$twig = new \Twig\Environment($twig_loader, $twig_options);

                // Functions & Filters
                self::$twig = apply_filters('timber/twig', self::$twig);

                define('TIMBER_LOADED', true);
            }
        }

        public static function compile($tpl, $vars)
        {
            self::init();
            return self::$twig->render($tpl, $vars);
        }

        public static function render($tpl, $vars)
        {
            self::init();
            echo self::compile($tpl, $vars);
        }

        public static function get_context()
        {
            if (empty(self::$context_cache)) {
                // self::$context_cache['http_host'] = URLHelper::get_scheme().'://'.URLHelper::get_host();
                // self::$context_cache['wp_title'] = Helper::get_wp_title();
                self::$context_cache['body_class'] = implode(' ', get_body_class());
                // self::$context_cache['site'] = new Site();
            // self::$context_cache['request'] = new Request();
            // $user = new User();
            // self::$context_cache['user'] = ($user->ID) ? $user : false;
            // self::$context_cache['theme'] = self::$context_cache['site']->theme;
            // self::$context_cache['posts'] = new PostQuery();
            // self::$context_cache['http_host'] = '';
            // self::$context_cache['wp_title'] = '';
            // self::$context_cache['body_class'] = '';
            // self::$context_cache['site'] = '';
            // self::$context_cache['request'] = '';
            // $user = [];
            // self::$context_cache['user'] = ($user->ID) ? $user : false;
            // self::$context_cache['theme'] = '';
            // self::$context_cache['posts'] = '';
            }
            return self::$context_cache;
        }

        public static function get_post()
        {
            global $post;
            return $post;
        }
    }
}
