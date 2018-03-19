<?php

$context = Timber::get_context();

// Creating Timber object to access twig keys
$context['post'] = new TimberPost();

// Creating object Timber Image for hero_image and post thumbnail
$context['post']->img = new TimberImage($post->hero_img);
$context['post']->thumbnail = new TimberImage($post->_thumbnail_id);

// Declare woody_parts
$context['post']->woody_parts = [];

// We get all layouts used on the page and removed duplicates
if(!empty($context['post']->content_element)){

    $content_element_layouts = array_unique($context['post']->content_element);


    if (in_array('hawwwai_block', $context['post']->content_element)) {

        $hawwwai_blocks = [];

        // get all hawwwai blocks post slugs
        foreach ($context['post']->content_element as $key => $elm) {
            if ($elm === 'hawwwai_block') {

                // add hawwwai_block taxonomy (weather, tide, ...) to $hawwwai_blocks
                $elm_key = 'content_element_'.$key.'_'.'hawwwai_block';
                if (isset($context['post']->{$elm_key})) {
                    $post_ID = $context['post']->{$elm_key};

                    $hawwwai_terms = wp_get_post_terms($post_ID, 'hawwwai_block_type');
                    if (!empty($hawwwai_terms) && !empty($hawwwai_terms[0]->slug)) {
                        $hawwwai_blocks[] = $hawwwai_terms[0]->slug;
                    }
                }

            }
        }

        // add $hawwwai_blocks to $content_element_layouts array
        $hawwwai_blocks = array_unique($hawwwai_blocks);
        $content_element_layouts = array_merge($content_element_layouts, $hawwwai_blocks);
    }

}


foreach ($content_element_layouts as $key => $layout) {
    // Then, for each layout we get twig's templates paths
    $type = 'block';
    if (strpos($layout, 'wp_hawwwai') !== false) {
        $type = 'hawwwai';
    }
    $woody = new Woody($layout, $type);
    $templates = $woody->getTwigsPaths($layout, $type);
    if(!empty($templates)){
        if ($type == 'hawwwai') {
            $context['post']->woody_parts[$type][$layout] = $templates;
        }
        else {
            $context['post']->woody_parts[$layout] = $templates;
        }
    }
}
if(in_array('content_selection', $content_element_layouts)){
    $woody_cards = new Woody('Cards', 'card');
    $cardTemplates = $woody_cards->getTwigsPaths($layout, 'card');
    if(!empty($cardTemplates)){
        $context['post']->woody_parts['cards'] = $cardTemplates;
    }
}

// print_r('<pre>');
// var_dump($context['post']->woody_parts);
// exit;

Timber::render(array($context['post']->post_name.'.twig', 'page.twig'), $context);
