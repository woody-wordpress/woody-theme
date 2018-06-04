<?php
/**
 * The front-page template file
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 * @package HawwwaiTheme
 * @since HawwwaiTheme 1.0.0
 */

$context = Timber::get_context();
$context['post'] = new TimberPost();
$template = 'front.twig';
Timber::render($template, $context);
