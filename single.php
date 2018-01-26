<?php

$context = Timber::get_context();
$context['post'] =  new TimberPost();

$template = 'single.twig';
Timber::render( $template, $context );
