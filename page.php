<?php

$context = Timber::get_context();

// Creating Timber object to access twig keys
$context['post'] = new TimberPost();

// Creating object Timber Image for hero_image and post thumbnail
$context['post']->img = new TimberImage($post->hero_img);
$context['post']->thumbnail = new TimberImage($post->_thumbnail_id);

$woody = new Woody();
$context['woody_parts'] = $woody->getTwigsPaths();

// print '<pre>';
// print_r($context['post']->get_field('section'));
// exit();

Timber::render('page.twig', $context);
