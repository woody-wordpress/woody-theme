<?php

$context = Timber::get_context();

// Creating Timber object to access twig keys
$context['post'] = new TimberPost();

// Creating object Timber Image for hero_image and post thumbnail
$context['post']->img = new TimberImage($post->hero_img);
$context['post']->thumbnail = new TimberImage($post->_thumbnail_id);

// Declare woody_parts
$context['post']->woody_parts = [];

// Get main menu
// $context['main_menu'] = new Timber\Menu('main-menu');
// d($context['main_menu']);

// We get all layouts used on the page and removed duplicates
if(!empty($context['post']->content_element)){
    $content_element_layouts = array_unique($context['post']->content_element);
}
foreach ($content_element_layouts as $key => $layout) {
    // Then, for each layout we get twig's templates paths
    $woody = new Woody($layout);
    if(!empty($woody->templates)){
        $context['post']->woody_parts[$layout] = $woody->getTwigsPaths($layout);
    }
}
if(in_array('content_selection', $content_element_layouts)){
    $woody_cards = new Woody('Cards', 'card');
    if(!empty($woody_cards->templates)){
        $context['post']->woody_parts['cards'] = $woody->getTwigsPaths($layout, 'card');
    }
}



Timber::render(array($context['post']->post_name.'.twig', 'page.twig'), $context);
