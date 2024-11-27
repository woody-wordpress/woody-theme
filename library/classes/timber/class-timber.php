<?php

/**
 * Twig Singleton
 *
 * @package WoodyTheme
 * @since WoodyTheme 1.15.0
 */

use WoodyLibrary\Library\WoodyLibrary\WoodyLibrary;

if (!class_exists('Timber')) {
    class Timber
    {
        private static $twig;

        private static array $context_cache = [];

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
                $dirs = [WOODY_THEME_DIR . '/views', WOODY_SUBTHEME_DIR . '/views'];

                $woodyLibrary = new WoodyLibrary();
                $library_dirs = $woodyLibrary->getTemplatesDirname();
                if (is_array($library_dirs)) {
                    $dirs = array_merge($dirs, $library_dirs);
                } else {
                    $dirs[] = $library_dirs;
                }

                $twig_dirs = apply_filters('timber_locations', $dirs);
                $twig_loader = new \Twig\Loader\FilesystemLoader($twig_dirs);
                $twig_options = ['autoescape' => false];
                if (!WOODY_TWIG_CACHE_DISABLE && WP_ENV != 'dev') {
                    $twig_options['cache'] = WP_TIMBER_DIR;
                }

                // Instance
                self::$twig = new \Twig\Environment($twig_loader, $twig_options);

                // Functions & Filters
                self::$twig = apply_filters('timber/twig', self::$twig);

                define('TIMBER_LOADED', true);
            }
        }

        public static function ob_function($function, $args = array(null))
        {
            ob_start();
            call_user_func_array($function, $args);
            $data = ob_get_contents();
            ob_end_clean();
            return $data;
        }

        public static function compile($tpl, $vars = [])
        {
            if (!empty($tpl)) {
                self::init();
                $vars = apply_filters('timber_compile_data', $vars);
                return self::$twig->render($tpl, $vars);
            }
        }

        public static function render($tpl, $vars = [])
        {
            if (!empty($tpl)) {
                self::init();
                $vars = apply_filters('timber_compile_data', $vars);
                $vars['globals_json'] = self::get_globals_json($vars);
                $vars['gtm']['datalayer'] = self::get_datalayer($vars);
                echo apply_filters('timber_render', self::compile($tpl, $vars));
            }
        }

        public static function get_context()
        {
            if (empty(self::$context_cache)) {
                self::$context_cache['http_host'] = home_url();
                self::$context_cache['body_class'] = implode(' ', get_body_class());
                self::$context_cache['wp_head'] = self::ob_function('wp_head');
                self::$context_cache['wp_footer'] = self::ob_function('wp_footer');
                self::$context_cache['site'] = [
                    'charset' => get_bloginfo('charset'),
                    'pingback' => get_bloginfo('pingback_url'),
                    'language' => get_bloginfo('language'),
                    'language_attributes' => get_language_attributes(),
                    'url' => home_url(),
                    'title' => get_bloginfo('name'),
                    'name' => get_bloginfo('name'),
                    'description' => get_bloginfo('description'),
                ];
            }

            return self::$context_cache;
        }

        private static function get_globals_json($vars)
        {
            $return = [];
            if (!empty($vars['globals'])) {
                $keys = [
                    'ancestors',
                    'area',
                    'context',
                    'current_lang',
                    'current_locale',
                    'current_locale_region',
                    'current_season',
                    'env',
                    'is_mobile',
                    'languages',
                    'options',
                    'page_type',
                    'post_id',
                    'post_image',
                    'post_title',
                    'post_type',
                    'post_modified',
                    'sheet_id',
                    'sheet_current_aspect',
                    'site_key',
                    'tags',
                    'woody_options_pages',
                ];
                foreach ($keys as $key) {
                    if (!empty($vars['globals'][$key])) {
                        $return[$key] = $vars['globals'][$key];
                    }
                }
            }

            return apply_filters('woody_globals_json', $return);
        }

        private static function get_datalayer($vars)
        {
            $datalayer = [
                'event' => 'globals',
                'data' => [
                    'env' => (empty($vars['globals']['env'])) ? null : $vars['globals']['env'],
                    'site_key' => (empty($vars['globals']['site_key'])) ? null : $vars['globals']['site_key'],
                    'page' => [
                        'id_page' => (empty($vars['globals']['post_id'])) ? null : $vars['globals']['post_id'],
                        'name' => (empty($vars['globals']['post_title'])) ? null : $vars['globals']['post_title'],
                        'page_type' => (empty($vars['globals']['page_type'])) ? (!empty($vars['globals']['post_type']) ? $vars['globals']['post_type'] : null) : $vars['globals']['page_type'],
                        'tags' => (empty($vars['globals']['tags'])) ? null : $vars['globals']['tags'],
                    ]
                ]
            ];

            if(!empty($vars['globals']['current_season']) && !empty($vars['globals']['current_locale'])) {
                $datalayer['data']['lang'] = $vars['globals']['current_locale'];
            } elseif(!empty($vars['globals']['current_lang'])) {
                $datalayer['data']['lang'] = $vars['globals']['current_lang'];
            }

            if(!empty($vars['globals']['current_season'])) {
                $datalayer['data']['season'] = $vars['globals']['current_season'];
            }

            if(!empty($vars['globals']['area'])) {
                $datalayer['data']['area'] = $vars['globals']['area'];
            }

            return apply_filters('woody_gtm_datalayer', $datalayer, $vars);
        }
    }
}
