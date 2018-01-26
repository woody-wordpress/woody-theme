<?php

class base_theme_Timber extends TimberSite {

  public function execute() {

    // Define Twig directories
    Timber::$dirname = array('views', 'views/parts', 'views/parts/content', 'views/parts/page', 'views/parts/fields');
  }

  // Adding functionality to Twig
  public function __construct(){
      parent::__construct();

      // add_filter( 'timber/twig', array( $this, 'basetheme_add_to_twig'));
      add_filter('timber/context', array($this, 'basetheme_add_to_context'));
  }

  // Functions from foundation we want to add to twig
  // public function foundation_add_to_twig($twig) {
  //   $twig->addFunction(new Timber\Twig_Function(
  //       'basetheme_mobile_menu_id',
  //       array( $this, 'basetheme_mobile_menu_id')
  //   ) );
  //
  //   return $twig;
  // }

  // Global context, available to all templates
  function basetheme_add_to_context($context) {

    // WP Templates
    $context['wp']['template'] = array(
      'front_page' => is_front_page(),
      'blog' => is_home(),
    );

    $context['wp']['theme'] = array(
        'theme_mod' => get_theme_mod('wpt_mobile_menu_layout')
    );
    // //Menus
    // $context['wp']['menus'] = array(
    //   "main" => new Timber\Menu('main'),
    //   "footer" => new Timber\Menu('footer'),
    // );

    return $context;
  }
}
