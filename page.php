<?php

$context = Timber::get_context();
$context['post'] = new TimberPost();
$context['post']->img = new TimberImage($post->hero_img);
$context['post']->woody_parts = [];

// We get all layouts used on the page and removed duplicates
$content_element_layouts = array_unique($context['post']->content_element);
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
