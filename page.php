<?php

$context = Timber::get_context();

// Creating Timber object to access twig keys
$context['post'] = new TimberPost();

// Creating object Timber Image for hero_image and post thumbnail
$context['post']->img = new TimberImage($post->hero_img);
$context['post']->thumbnail = new TimberImage($post->_thumbnail_id);

// Declare woody_parts
$context['post']->woody_parts = [];
$context['post']->hawwwai_blocks = [];

// We get all layouts used on the page and removed duplicates
// if (!empty($context['post']->content_element)) {
//
//     $content_element_layouts = array_unique($context['post']->content_element);
//
//     $context = $plugin_hawwwai_kernel->handleBlockContext($context);
//
//     foreach ($content_element_layouts as $layout) {
//         $type = 'blocks';
//         $woody = new Woody($layout, $type);
//         $templates = $woody->getTwigsPaths($layout, $type);
//         if (!empty($templates)) {
//             $context['post']->woody_parts[$layout] = $templates;
//         }
//     }
//     if (in_array('content_selection', $content_element_layouts)) {
//         $woody_cards = new Woody('Cards', 'card');
//         $cardTemplates = $woody_cards->getTwigsPaths($layout, 'card');
//         if (!empty($cardTemplates)) {
//             $context['post']->woody_parts['cards'] = $cardTemplates;
//         }
//     }
//
// }
Timber::render(array($context['post']->post_name . '.twig', 'page.twig'), $context);
