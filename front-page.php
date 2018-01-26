<?php
$context = Timber::get_context();
$context['post'] = new TimberPost();
$template = 'front.twig';
Timber::render($template, $context);
