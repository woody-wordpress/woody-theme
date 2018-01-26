<?php

$context = Timber::get_context();
$context['post'] = new TimberPost();
$context['post']->img = new TimberImage($post->img);

Timber::render(array($context['post']->post_name.'.twig', 'page.twig'), $context);
