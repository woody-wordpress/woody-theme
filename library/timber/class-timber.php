<?php

// class HawwwaiTheme_Timber extends TimberSite
// {

//     // Adding functionality to Twig
//     public function __construct()
//     {
//         parent::__construct();
//         $this->register_hooks();
//     }

//     protected function register_hooks()
//     {
//         // add_filter( 'timber/twig', array( $this, 'basetheme_add_to_twig'));
//         // add_filter('timber/context', array($this, 'add_to_context'));
//     }

//     // Global context, available to all templates
//     public function add_to_context($context)
//     {

//         // WP Templates
//         // $context['wp']['template'] = array(
//         //     'front_page' => is_front_page(),
//         //     'blog' => is_home(),
//         // );

//         // $context['wp']['theme'] = array(
//         //     'theme_mod' => get_theme_mod('wpt_mobile_menu_layout')
//         // );

//         // //Menus
//         // $context['wp']['menus'] = array(
//         //   "main" => new Timber\Menu('main'),
//         //   "footer" => new Timber\Menu('footer'),
//         // );

//         // $context['main_menu'] = new Timber\Menu('main-menu');
//         // d($context['main_menu']);

//         return $context;
//     }
// }

// // Execute Class
// $HawwwaiTheme_Timber = new HawwwaiTheme_Timber();
