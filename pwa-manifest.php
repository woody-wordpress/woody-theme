<?php

header('Content-Type: application/json; charset=UTF-8');
$context = \Timber::get_context();
$context['home_url'] = WP_HOME;
$context['site_key'] = WP_SITE_KEY;
$context['name'] = get_bloginfo();
$context['description'] = get_bloginfo('description');
\Timber::render('manifest.twig', $context);
