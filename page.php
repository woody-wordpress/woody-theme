<?php

$context = Timber::get_context();
$context['post'] = new TimberPost();
$context['post']->img = new TimberImage($post->img);

// We get all layouts used on the page and removed duplicates
$content_element_layouts = array_unique($context['post']->content_element);
foreach ($content_element_layouts as $key => $layout) {
    // Then, for each layout we get twig's templates paths
    $woody = new Woody($layout);
    if(!empty($woody->templates)){
        $context['post']->woody_parts[$layout] = $woody->getTwigsPaths($layout);
    }
}

Timber::render(array($context['post']->post_name.'.twig', 'page.twig'), $context);
